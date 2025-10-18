<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\RecurringTransactionService;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring {--dry-run} {--user=}';
    protected $description = 'Génère automatiquement les prochaines occurrences des transactions récurrentes.';

    public function handle(RecurringTransactionService $service): int
    {
        $now = Carbon::now()->startOfDay();
        $dry = (bool) $this->option('dry-run');
        $userId = $this->option('user');

        $query = Transaction::query()
            ->where('is_recurring', true)
            ->whereNull('parent_id'); // base templates

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $bases = $query->get();

        $created = 0;
        foreach ($bases as $base) {
            if ($dry) {
                // Simule mais n'écrit pas: on itère à partir de last/anchor et on logue
                $last = $base->last_generated_date ? Carbon::parse($base->last_generated_date) : Carbon::parse($base->date);
                while (true) {
                    $next = $service->nextDateFrom($last, $base->recurrence_frequency, (int)($base->recurrence_interval ?: 1), $base->recurrence_day);
                    if (!$next || $next->gt($now)) break;
                    if ($base->recurrence_end_date && $next->gt(Carbon::parse($base->recurrence_end_date))) break;
                    $exists = Transaction::query()
                        ->where(function ($q) use ($base) {
                            $q->where('id', $base->id)->orWhere('parent_id', $base->id);
                        })
                        ->whereDate('date', $next->toDateString())
                        ->exists();
                    if (!$exists) {
                        $this->line("[DRY] Création prévue le {$next->toDateString()} pour base #{$base->id}");
                    }
                    $last = $next;
                }
            } else {
                $created += $service->generateDueOccurrences($base, $now);
            }
        }

        $this->info($dry ? '[DRY] Terminé.' : "Créations: $created");
        return self::SUCCESS;
    }

    // nextDateFrom déplacé dans le service pour réutilisation
}
