<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'description',
        'amount',
        'type',
        'category',
        'date',
        'is_recurring',
        'recurrence_day',
        'recurrence_frequency',
        'recurrence_interval',
        'recurrence_end_date',
        'last_generated_date',
    ];

    protected $casts = [
        'date' => 'date',
        'recurrence_end_date' => 'date',
        'last_generated_date' => 'date',
        'is_recurring' => 'bool',
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Transaction::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Transaction::class, 'parent_id');
    }
}