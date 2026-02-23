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
        Schema::create('tms_tracking_driver', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_id');
            $table->string('driver_code', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('full_name', 201)->nullable();

            // Essential Contact Information
            $table->string('mobile_no', 20);
            $table->string('email', 100)->nullable();

            // License Information
            $table->string('license_no', 20)->unique();
            $table->string('license_type', 50);
            $table->dateTime('license_expiry');

            // Current Status for Tracking
            $table->string('current_status', 20)->default('AVAILABLE');
            // AVAILABLE, ON_TRIP, OFF_DUTY, BREAK, MAINTENANCE

            $table->string('current_location', 255)->nullable();
            $table->dateTime('last_location_update')->nullable();
            $table->unsignedInteger('current_vehicle_id');

            // Performance Tracking
            $table->integer('total_deliveries')->default(0);
            $table->integer('successful_deliveries')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);

            // Emergency Contact
            $table->string('emergency_contact_name', 100);
            $table->string('emergency_contact_no', 20);

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
        Schema::dropIfExists('tms_tracking_driver');
    }
};
