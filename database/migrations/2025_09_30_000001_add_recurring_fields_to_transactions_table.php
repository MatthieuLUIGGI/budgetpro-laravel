<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('date');
            }
            if (!Schema::hasColumn('transactions', 'recurrence_day')) {
                $table->unsignedTinyInteger('recurrence_day')->nullable()->after('is_recurring');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'recurrence_day')) {
                $table->dropColumn('recurrence_day');
            }
            if (Schema::hasColumn('transactions', 'is_recurring')) {
                $table->dropColumn('is_recurring');
            }
        });
    }
};
