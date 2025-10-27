<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'booking_id',
        'user_id',
        'device_category_id',
        'technician_id',
        'type',
        'device_brand',
        'device_model',
        'problem_description',
        'problem_images',
        'complexity_weight',
        'is_walkin',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'ready_at',
        'collected_at',
        'warranty_days',
        'warranty_expires_at',
    ];

    protected $casts = [
        'problem_images' => 'array',
        'complexity_weight' => 'integer',
        'is_walkin' => 'boolean',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'ready_at' => 'datetime',
        'collected_at' => 'datetime',
        'warranty_expires_at' => 'datetime',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deviceCategory()
    {
        return $this->belongsTo(DeviceCategory::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function progress()
    {
        return $this->hasMany(JobProgress::class);
    }

    public function materials()
    {
        return $this->hasMany(MaterialUsed::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function storageFee()
    {
        return $this->hasOne(StorageFee::class);
    }

    // Static method to generate unique task ID
    public static function generateTaskId($clientName, $categoryCode)
    {
        $prefix = strtoupper(substr($clientName, 0, 3));
        $date = date('ymd');
        $salt = strtoupper(Str::random(4));

        return $prefix . $categoryCode . $date . $salt;
    }

    // Helper methods
    public function isActive()
    {
        return in_array($this->status, ['assigned', 'checked_in', 'in_progress', 'waiting_parts']);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isReadyForCollection()
    {
        return $this->status === 'ready_for_collection';
    }

    public function isCollected()
    {
        return $this->status === 'collected';
    }


    public function shouldSendReminderDay3()
    {
        return $this->getDaysUncollected() >= 3 &&
               $this->storageFee &&
               !$this->storageFee->sms_day3_sent;
    }

    public function shouldSendReminderDay4()
    {
        return $this->getDaysUncollected() >= 4 &&
               $this->storageFee &&
               !$this->storageFee->sms_day4_sent;
    }

    public function shouldApplyStorageFee()
    {
        return $this->getDaysUncollected() >= 5;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'checked_in', 'in_progress', 'waiting_parts']);
    }

    public function scopeReadyForCollection($query)
    {
        return $query->where('status', 'ready_for_collection');
    }

    public function scopeUncollected($query)
    {
        return $query->where('status', 'ready_for_collection')
                    ->whereNotNull('ready_at');
    }



/**
 * Get days device has been uncollected
 * Works for both 'completed' and 'ready_for_collection' statuses
 */
public function getDaysUncollected()
{
    // If already collected, return 0
    if ($this->status === 'collected' || $this->collected_at) {
        return 0;
    }

    // For ready_for_collection status, use ready_at
    if ($this->status === 'ready_for_collection' && $this->ready_at) {
        return now()->diffInDays($this->ready_at);
    }

    // For completed status (before marking ready), use completed_at
    if ($this->status === 'completed' && $this->completed_at) {
        return now()->diffInDays($this->completed_at);
    }

    return 0;
}

/**
 * Get the reference date for collection calculations
 * Returns the earliest date from which collection time should be counted
 */
public function getCollectionReferenceDate()
{
    // Priority: ready_at > completed_at
    if ($this->ready_at) {
        return $this->ready_at;
    }

    if ($this->completed_at) {
        return $this->completed_at;
    }

    return now();
}

/**
 * Check if device is overdue for collection (5+ days)
 */
public function isOverdueForCollection()
{
    return $this->getDaysUncollected() >= 5;
}

/**
 * Check if storage fees should apply
 */
public function shouldChargeStorageFee()
{
    return $this->getDaysUncollected() > 5;
}

}
