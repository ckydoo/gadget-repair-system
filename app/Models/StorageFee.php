<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'days_stored',
        'daily_rate',
        'total_fee',
        'fee_started_at',
        'sms_day3_sent',
        'sms_day4_sent',
        'is_paid',
    ];

    protected $casts = [
        'days_stored' => 'integer',
        'daily_rate' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'fee_started_at' => 'datetime',
        'sms_day3_sent' => 'boolean',
        'sms_day4_sent' => 'boolean',
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Calculate storage fee
    public function calculateFee()
    {
        if ($this->days_stored > 5) {
            $chargeableDays = $this->days_stored - 5;
            $this->total_fee = $chargeableDays * $this->daily_rate;
        } else {
            $this->total_fee = 0;
        }

        return $this;
    }

    // Update days stored
    public function updateDaysStored()
    {
        if ($this->task->ready_at) {
            $this->days_stored = now()->diffInDays($this->task->ready_at);
            $this->calculateFee();
            $this->save();
        }

        return $this;
    }
}
