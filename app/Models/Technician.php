<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specializations',
        'max_workload',
        'is_available',
        'hourly_rate',
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'technician_id', 'user_id');
    }

    public function activeTasks()
    {
        return $this->tasks()
            ->whereIn('status', ['assigned', 'checked_in', 'in_progress', 'waiting_parts']);
    }

    // Helper methods
    public function getActiveTasksCount()
    {
        return $this->activeTasks()->count();
    }

    public function canTakeMoreJobs()
    {
        return $this->is_available && $this->getActiveTasksCount() < $this->max_workload;
    }

    public function hasSpecialization($categoryId)
    {
        return in_array($categoryId, $this->specializations);
    }

    // Get workload weight (considering complexity)
    public function getCurrentWorkloadWeight()
    {
        return $this->activeTasks()->sum('complexity_weight');
    }

    // Scope for available technicians
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // Scope for technicians with specific specialization
    public function scopeWithSpecialization($query, $categoryId)
    {
        return $query->whereJsonContains('specializations', $categoryId);
    }
}
