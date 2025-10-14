<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobProgress extends Model
{
    use HasFactory;

    protected $table = 'job_progress';

    protected $fillable = [
        'task_id',
        'technician_id',
        'stage',
        'notes',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
