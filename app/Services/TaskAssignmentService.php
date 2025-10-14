<?php

namespace App\Services;

use App\Models\Technician;
use App\Models\User;

class TaskAssignmentService
{
    /**
     * Assign a technician based on category, workload, and availability
     *
     * @param int $categoryId
     * @return User|null
     */
    public function assignTechnician($categoryId)
    {
        // Get all available technicians with the required specialization
        $technicians = Technician::available()
            ->withSpecialization($categoryId)
            ->with(['user', 'activeTasks'])
            ->get();

        if ($technicians->isEmpty()) {
            return null;
        }

        // Filter technicians who can take more jobs
        $availableTechnicians = $technicians->filter(function ($technician) {
            return $technician->canTakeMoreJobs();
        });

        if ($availableTechnicians->isEmpty()) {
            return null;
        }

        // Sort by current workload weight (ascending) - assign to least busy
        $assignedTechnician = $availableTechnicians->sortBy(function ($technician) {
            return $technician->getCurrentWorkloadWeight();
        })->first();

        return $assignedTechnician->user;
    }

    /**
     * Reassign task to different technician
     *
     * @param int $taskId
     * @param int $newTechnicianId
     * @return bool
     */
    public function reassignTask($taskId, $newTechnicianId)
    {
        $task = \App\Models\Task::findOrFail($taskId);

        $task->update([
            'technician_id' => $newTechnicianId,
            'assigned_at' => now(),
        ]);

        return true;
    }

    /**
     * Get technician workload statistics
     *
     * @param int $technicianId
     * @return array
     */
    public function getTechnicianWorkload($technicianId)
    {
        $technician = Technician::where('user_id', $technicianId)->first();

        if (!$technician) {
            return null;
        }

        $activeTasks = $technician->activeTasks()->get();
        $workloadWeight = $technician->getCurrentWorkloadWeight();
        $capacity = $technician->max_workload;

        return [
            'active_tasks_count' => $activeTasks->count(),
            'workload_weight' => $workloadWeight,
            'max_workload' => $capacity,
            'percentage' => ($workloadWeight / $capacity) * 100,
            'can_take_more' => $technician->canTakeMoreJobs(),
        ];
    }

    /**
     * Get all technicians with their workload
     *
     * @return Collection
     */
    public function getAllTechniciansWorkload()
    {
        $technicians = Technician::with(['user', 'activeTasks'])->get();

        return $technicians->map(function ($technician) {
            return [
                'id' => $technician->user_id,
                'name' => $technician->user->name,
                'specializations' => $technician->specializations,
                'is_available' => $technician->is_available,
                'active_tasks' => $technician->getActiveTasksCount(),
                'workload_weight' => $technician->getCurrentWorkloadWeight(),
                'max_workload' => $technician->max_workload,
                'percentage' => ($technician->getCurrentWorkloadWeight() / $technician->max_workload) * 100,
            ];
        });
    }
}
