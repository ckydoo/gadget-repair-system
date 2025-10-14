<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_category_id',
        'type',
        'device_count',
        'service_cost_total',
        'device_brand',
        'device_model',
        'problem_description',
        'problem_images',
        'needs_transport',
        'transport_type',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'distance_km',
        'transport_fee',
        'diagnostic_fee',
        'total_fee',
        'payment_status',
        'payment_reference',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'problem_images' => 'array',
        'device_count' => 'integer',
        'service_cost_total' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'transport_fee' => 'decimal:2',
        'diagnostic_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'needs_transport' => 'boolean',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deviceCategory()
    {
        return $this->belongsTo(DeviceCategory::class);
    }

    public function task()
    {
        return $this->hasOne(Task::class);
    }

    // Helper methods
    public function isService()
    {
        return $this->type === 'service';
    }

    public function isRepair()
    {
        return $this->type === 'repair';
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function calculateTotalFee()
    {
        $total = 0;

        if ($this->isService()) {
            $total = $this->service_cost_total;
        } else {
            $total = $this->diagnostic_fee;
        }

        if ($this->needs_transport) {
            $total += $this->transport_fee;
        }

        return $total;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
