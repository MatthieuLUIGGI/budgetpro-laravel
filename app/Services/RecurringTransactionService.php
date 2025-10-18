<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    public function generateDueOccurrences(Transaction $base, ?Carbon $now = null): int
    {
        if (!$base->is_recurring || $base->parent_id) {
            return 0; // Not a base recurring template
        }
        $now = ($now ?: Carbon::now())->startOfDay();

        $created = 0;
        $last = $base->last_generated_date ? Carbon::parse($base->last_generated_date) : Carbon::parse($base->date);

        while (true) {
            $next = $this->nextDateFrom($last, $base->recurrence_frequency, (int)($base->recurrence_interval ?: 1), $base->recurrence_day);
            if (!$next) break;

            if ($base->recurrence_end_date && $next->gt(Carbon::parse($base->recurrence_end_date))) {
                break;
            }
            if ($next->gt($now)) {
                break;
            }

            $exists = Transaction::query()
                ->where(function ($q) use ($base) {
                    $q->where('id', $base->id)->orWhere('parent_id', $base->id);
                })
                ->whereDate('date', $next->toDateString())
                ->exists();

            if (!$exists) {
                DB::transaction(function () use ($base, $next, &$created) {
                    Transaction::create([
                        'user_id' => $base->user_id,
                        'parent_id' => $base->id,
                        'description' => $base->description,
                        'amount' => $base->amount,
                        'type' => $base->type,
                        'category' => $base->category,
                        'date' => $next->toDateString(),
                        'is_recurring' => false,
                    ]);
                    $base->last_generated_date = $next->toDateString();
                    $base->save();
                    $created++;
                });
            } else {
                // if exists, still bump last to progress the loop
                $base->last_generated_date = $next->toDateString();
                $base->save();
            }

            $last = $next;
        }

        return $created;
    }

    public function nextDateFrom(Carbon $anchor, ?string $frequency, int $interval, ?int $recurrenceDay): ?Carbon
    {
        if (!$frequency) return null;
        $next = $anchor->copy();
        switch ($frequency) {
            case 'daily':
                $next->addDays($interval);
                break;
            case 'weekly':
                $next->addWeeks($interval);
                break;
            case 'monthly':
                $next->addMonthsNoOverflow($interval);
                if ($recurrenceDay) {
                    $day = min($recurrenceDay, $next->daysInMonth);
                    $next->day($day);
                }
                break;
            case 'yearly':
                $next->addYears($interval);
                if ($recurrenceDay) {
                    $day = min($recurrenceDay, $next->daysInMonth);
                    $next->day($day);
                }
                break;
            default:
                return null;
        }
        return $next->startOfDay();
    }
}
