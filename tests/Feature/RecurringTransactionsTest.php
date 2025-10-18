<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateRecurringTransactions;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure default sqlite database exists per composer script
    }

    public function test_monthly_salary_generates_next_month()
    {
        $user = User::factory()->create();
        $baseDate = Carbon::create(2025, 9, 5);

        $base = Transaction::create([
            'user_id' => $user->id,
            'description' => 'Salaire',
            'amount' => 2500.00,
            'type' => 'income',
            'category' => 'Salaire',
            'date' => $baseDate->toDateString(),
            'is_recurring' => true,
            'recurrence_frequency' => 'monthly',
            'recurrence_interval' => 1,
            'recurrence_day' => 5,
            'last_generated_date' => $baseDate->toDateString(),
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6));
        $cmd = new GenerateRecurringTransactions();
        $this->artisan($cmd->getName() ?? 'transactions:generate-recurring')->assertExitCode(0);

        $this->assertDatabaseHas('transactions', [
            'parent_id' => $base->id,
            'date' => '2025-10-05',
            'amount' => 2500.00,
        ]);
    }

    public function test_idempotent_generation()
    {
        $user = User::factory()->create();
        $baseDate = Carbon::create(2025, 9, 5);

        $base = Transaction::create([
            'user_id' => $user->id,
            'description' => 'Salaire',
            'amount' => 2500.00,
            'type' => 'income',
            'category' => 'Salaire',
            'date' => $baseDate->toDateString(),
            'is_recurring' => true,
            'recurrence_frequency' => 'monthly',
            'recurrence_interval' => 1,
            'recurrence_day' => 5,
            'last_generated_date' => $baseDate->toDateString(),
        ]);

        Carbon::setTestNow(Carbon::create(2025, 11, 6));
        $this->artisan('transactions:generate-recurring');
        $this->artisan('transactions:generate-recurring');

        $children = Transaction::where('parent_id', $base->id)
            ->whereBetween('date', ['2025-10-01','2025-11-30'])
            ->pluck('date')
            ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string) $d)
            ->toArray();
        sort($children);
        $this->assertEquals(['2025-10-05','2025-11-05'], $children);
    }

    public function test_backdated_recurring_generates_up_to_today()
    {
        $user = User::factory()->create();
        // Base créée au 5 juillet 2025, on se place au 18 octobre 2025
        Carbon::setTestNow(Carbon::create(2025, 10, 18));

        $base = Transaction::create([
            'user_id' => $user->id,
            'description' => 'Salaire',
            'amount' => 2500.00,
            'type' => 'income',
            'category' => 'Salaire',
            'date' => '2025-07-05',
            'is_recurring' => true,
            'recurrence_frequency' => 'monthly',
            'recurrence_interval' => 1,
            'recurrence_day' => 5,
            'last_generated_date' => '2025-07-05',
        ]);

        // Appeler le service via la commande
        $this->artisan('transactions:generate-recurring')->assertExitCode(0);

        // On doit avoir 08/05, 09/05 et 10/05
        $expected = ['2025-08-05','2025-09-05','2025-10-05'];
        $children = Transaction::where('parent_id', $base->id)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string) $d)
            ->toArray();

        $this->assertEquals($expected, $children);
    }
}
