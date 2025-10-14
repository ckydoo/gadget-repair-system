<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'country',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function technician()
    {
        return $this->hasOne(Technician::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'technician_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function jobProgress()
    {
        return $this->hasMany(JobProgress::class, 'technician_id');
    }

    // Helper methods
    public function isTechnician()
    {
        return $this->hasRole('technician');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isSupervisor()
    {
        return $this->hasRole('supervisor');
    }

    public function isFrontDesk()
    {
        return $this->hasRole('front_desk');
    }

    public function isClient()
    {
        return $this->hasRole('client');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    // Get unread notifications count
    public function unreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }
}
