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



    /**
     * Get all device categories this technician specializes in
     */
    public function deviceCategories()
    {
        return DeviceCategory::whereIn('id', $this->specializations ?? [])->get();
    }


    /**
     * Get completed tasks for this technician
     */
    public function completedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    /**
     * Check if technician can handle a specific device category
     */
    public function canHandle($categoryId)
    {
        return in_array($categoryId, $this->specializations ?? []);
    }

    /**
     * Get current workload (active tasks count)
     */
    public function getCurrentWorkload()
    {
        return $this->activeTasks()->count();
    }

    /**
     * Check if technician has capacity for more tasks
     */
    public function hasCapacity()
    {
        return $this->is_available &&
               $this->getCurrentWorkload() < $this->max_workload;
    }

    /**
     * Get workload percentage
     */
    public function getWorkloadPercentage()
    {
        if ($this->max_workload == 0) {
            return 0;
        }

        return min(100, ($this->getCurrentWorkload() / $this->max_workload) * 100);
    }



    /**
     * Scope to get technicians with capacity
     */
    public function scopeWithCapacity($query)
    {
        return $query->where('is_available', true)
                    ->whereRaw('(SELECT COUNT(*) FROM tasks WHERE technician_id = technicians.user_id AND status IN ("assigned", "checked_in", "in_progress", "waiting_parts")) < max_workload');
    }

    public function deviceCategoryRelations()
{
    return $this->belongsToMany(DeviceCategory::class, 'technician_specializations', 'technician_id', 'device_category_id');
}
}
