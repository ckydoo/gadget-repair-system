<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_category_id')->constrained();
            $table->enum('type', ['service', 'repair']);

            // Service specific fields
            $table->integer('device_count')->nullable();
            $table->decimal('service_cost_total', 10, 2)->nullable();

            // Repair specific fields
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->text('problem_description')->nullable();
            $table->json('problem_images')->nullable();

            // Transport details
            $table->boolean('needs_transport')->default(false);
            $table->enum('transport_type', ['pickup', 'delivery', 'both'])->nullable();
            $table->text('pickup_address')->nullable();
            $table->string('pickup_lat')->nullable();
            $table->string('pickup_lng')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('transport_fee', 8, 2)->default(0);
            $table->decimal('diagnostic_fee', 8, 2)->default(25.00);
            $table->decimal('total_fee', 10, 2)->default(0);

            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->enum('status', ['pending', 'confirmed', 'assigned', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
