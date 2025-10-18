<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'recurrence_frequency')) {
                $table->enum('recurrence_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('recurrence_day');
            }
            if (!Schema::hasColumn('transactions', 'recurrence_interval')) {
                $table->unsignedSmallInteger('recurrence_interval')->default(1)->after('recurrence_frequency');
            }
            if (!Schema::hasColumn('transactions', 'recurrence_end_date')) {
                $table->date('recurrence_end_date')->nullable()->after('recurrence_interval');
            }
            if (!Schema::hasColumn('transactions', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('transactions')->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('transactions', 'last_generated_date')) {
                $table->date('last_generated_date')->nullable()->after('recurrence_end_date');
            }

            // Empêche les doublons par parent/date
            $table->unique(['parent_id', 'date'], 'transactions_parent_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'last_generated_date')) {
                $table->dropColumn('last_generated_date');
            }
            if (Schema::hasColumn('transactions', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('transactions', 'recurrence_end_date')) {
                $table->dropColumn('recurrence_end_date');
            }
            if (Schema::hasColumn('transactions', 'recurrence_interval')) {
                $table->dropColumn('recurrence_interval');
            }
            if (Schema::hasColumn('transactions', 'recurrence_frequency')) {
                $table->dropColumn('recurrence_frequency');
            }
            // Suppression de l'index unique
            $table->dropUnique('transactions_parent_date_unique');
        });
    }
};
