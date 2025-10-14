<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'service_cost',
        'size',
        'is_active',
    ];

    protected $casts = [
        'service_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Helper method to get storage fee rate
    public function getStorageFeeRate()
    {
        return match($this->size) {
            'small' => 0.25,
            'medium' => 0.50,
            'large' => 1.00,
            default => 0.25,
        };
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
