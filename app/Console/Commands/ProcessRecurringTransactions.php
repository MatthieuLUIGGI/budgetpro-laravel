<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring';
    protected $description = 'Créer automatiquement les transactions récurrentes du jour';

    public function handle(): int
    {
        $today = Carbon::today();
        $day = (int)$today->format('d');

        // Récupérer les transactions modèles récurrentes
        $recurrings = Transaction::where('is_recurring', true)
            ->where(function($q) use ($day, $today) {
                // Si le jour d'origine dépasse la fin du mois, on ajuste le dernier jour
                $q->where('recurrence_day', $day)
                  ->orWhere(function($q2) use ($day, $today) {
                      $lastDay = (int)$today->clone()->endOfMonth()->format('d');
                      $q2->where('recurrence_day', '>', $lastDay)
                         ->where('recurrence_day', '>=', $lastDay) // sécurité double
                         ->whereRaw('? = ?', [$day, $lastDay]);
                  });
            })
            ->get();

        $count = 0;
        foreach ($recurrings as $template) {
            // Empêcher doublon si déjà créé aujourd'hui (même description, montant, user, date aujourd'hui)
            $exists = Transaction::where('user_id', $template->user_id)
                ->whereDate('date', $today)
                ->where('description', $template->description)
                ->where('amount', $template->amount)
                ->where('category', $template->category)
                ->exists();
            if ($exists) {
                continue;
            }

            Transaction::create([
                'user_id' => $template->user_id,
                'description' => $template->description,
                'amount' => $template->amount,
                'type' => $template->type,
                'category' => $template->category,
                'date' => $today->toDateString(),
                'is_recurring' => false, // la ligne générée n'est pas un modèle
                'recurrence_day' => null,
            ]);
            $count++;
        }

        $this->info("Transactions récurrentes créées: $count");
        return Command::SUCCESS;
    }
}
