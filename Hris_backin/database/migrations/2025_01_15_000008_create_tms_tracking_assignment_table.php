<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tms_tracking_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('vehicle_id');
            $table->dateTime('assignment_date');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            // Assignment Details
            $table->string('assignment_type', 50)->default('REGULAR');
            // REGULAR, TEMPORARY, EMERGENCY, BACKUP

            $table->string('assignment_status', 20)->default('ACTIVE');
            // ACTIVE, COMPLETED, CANCELLED, SUSPENDED

            $table->text('assignment_notes')->nullable();
            $table->string('assigned_by', 50);

            // Performance Tracking for this Assignment
            $table->integer('trips_completed')->default(0);
            $table->decimal('total_distance_km', 18, 2)->default(0);
            $table->decimal('fuel_consumed_liters', 18, 2)->default(0);
            $table->decimal('performance_rating', 3, 2)->default(0.00);

            // System Fields
            $table->tinyInteger('is_active')->default(1);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tms_tracking_assignment');
    }
};
