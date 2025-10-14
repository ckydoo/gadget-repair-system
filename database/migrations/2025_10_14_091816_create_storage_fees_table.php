<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->integer('days_stored')->default(0);
            $table->decimal('daily_rate', 8, 2);
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->timestamp('fee_started_at')->nullable();
            $table->boolean('sms_day3_sent')->default(false);
            $table->boolean('sms_day4_sent')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_fees');
    }
};
