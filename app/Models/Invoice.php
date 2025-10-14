<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'task_id',
        'user_id',
        'materials_cost',
        'labour_cost',
        'transport_cost',
        'diagnostic_fee',
        'subtotal',
        'tax',
        'total',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'materials_cost' => 'decimal:2',
        'labour_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'diagnostic_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Generate unique invoice number
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();

        $number = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -6) + 1 : 1;

        return 'INV-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // Calculate invoice totals
    public function calculateTotals()
    {
        $this->subtotal = $this->materials_cost + $this->labour_cost +
                         $this->transport_cost + $this->diagnostic_fee;

        $this->tax = $this->subtotal * 0.15; // 15% tax rate
        $this->total = $this->subtotal + $this->tax;

        return $this;
    }

    // Helper methods
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }
}
