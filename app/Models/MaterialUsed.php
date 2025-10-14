<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialUsed extends Model
{
    use HasFactory;

    protected $table = 'materials_used';

    protected $fillable = [
        'task_id',
        'material_name',
        'part_number',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Auto-calculate total price
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($material) {
            $material->total_price = $material->quantity * $material->unit_price;
        });
    }
}
