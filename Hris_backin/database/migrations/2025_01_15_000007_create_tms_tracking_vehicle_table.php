<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tms_tracking_vehicle', function (Blueprint $table) {
            $table->id();
         $table->unsignedInteger('branch_id');
            $table->string('vehicle_code', 50)->unique();
            $table->string('plate_no', 20)->unique();
            $table->string('vehicle_name', 100);
            $table->string('vehicle_size', 100);
            // Vehicle Classification
            $table->string('vehicle_type', 50);
            // MOTORCYCLE, VAN, TRUCK_SMALL, TRUCK_MEDIUM, TRUCK_LARGE, TRAILER

            $table->string('vehicle_category', 50);
            // DELIVERY, PICKUP, LONG_HAUL, LOCAL

            // Basic Vehicle Info
            $table->string('brand', 50);
            $table->string('model', 50);
            $table->integer('year');
            $table->string('color', 50);

            // Capacity Information
            $table->decimal('max_weight_kg', 18, 2);
            $table->decimal('max_volume_cbm', 18, 2);
            $table->decimal('max_length_m', 18, 2)->nullable();
            $table->decimal('max_width_m', 18, 2)->nullable();
            $table->decimal('max_height_m', 18, 2)->nullable();

            // Fuel Information
            $table->string('fuel_type', 50); // DIESEL, GASOLINE, ELECTRIC
            $table->decimal('fuel_capacity_liters', 18, 2)->nullable();
            $table->decimal('avg_fuel_consumption_per_km', 18, 4)->nullable();

            // Current Status for Tracking
            $table->string('current_status', 20)->default('AVAILABLE');
            // AVAILABLE, IN_USE, MAINTENANCE, OUT_OF_SERVICE, LOADING, UNLOADING

            $table->string('current_location', 255)->nullable();
            $table->dateTime('last_location_update')->nullable();
 

            $table->decimal('current_odometer_km', 18, 2)->default(0);

            // GPS and Tracking
            $table->string('gps_device_id', 50)->nullable();
            $table->string('gps_status', 20)->default('ACTIVE'); // ACTIVE, INACTIVE, FAULTY

            // Performance Tracking
            $table->integer('total_trips')->default(0);
            $table->decimal('total_distance_km', 18, 2)->default(0);
            $table->decimal('total_fuel_consumed_liters', 18, 2)->default(0);

            // Maintenance
            $table->dateTime('last_maintenance_date')->nullable();
            $table->dateTime('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_km')->default(10000);

            // Legal Requirements
            $table->dateTime('registration_expiry');
            $table->dateTime('insurance_expiry');

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
        Schema::dropIfExists('tms_tracking_vehicle');
    }
};
