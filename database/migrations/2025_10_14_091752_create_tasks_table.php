<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_category_id')->constrained();
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('type', ['service', 'repair']);
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->text('problem_description')->nullable();
            $table->json('problem_images')->nullable();

            $table->integer('complexity_weight')->default(1);
            $table->boolean('is_walkin')->default(false);

            $table->enum('status', [
                'assigned',
                'checked_in',
                'in_progress',
                'waiting_parts',
                'completed',
                'ready_for_collection',
                'collected',
                'archived'
            ])->default('assigned');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('collected_at')->nullable();

            $table->integer('warranty_days')->default(30);
            $table->timestamp('warranty_expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
