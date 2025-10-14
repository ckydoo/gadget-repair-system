<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'message',
        'type',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mark as sent
    public function markAsSent($response = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_response' => $response,
        ]);
    }

    // Mark as failed
    public function markAsFailed($response = null)
    {
        $this->update([
            'status' => 'failed',
            'provider_response' => $response,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
