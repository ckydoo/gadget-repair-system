<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add fields to track who collected the device and their ID details
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('collected_by')->nullable()->after('collected_at');
            $table->string('collector_id_type')->nullable()->after('collected_by');
            $table->string('collector_id_number')->nullable()->after('collector_id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['collected_by', 'collector_id_type', 'collector_id_number']);
        });
    }
};
