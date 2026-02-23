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
       Schema::create('tms_tracking_header', function (Blueprint $table) {
        $table->id(); // This creates a BIGINT UNSIGNED auto-increment primary key

        // Use foreignId to ensure correct type and FK creation
        $table->foreignId('current_status_id')->constrained('tms_tracking_status');
        $table->foreignId('tracking_type_id')->constrained('tms_tracking_type');

        $table->integer('branch_id');
        $table->string('tracking_number', 50)->unique();
        $table->string('reference_number', 50)->nullable();
        $table->dateTime('estimated_delivery_date')->nullable();
        $table->dateTime('actual_delivery_date')->nullable();
        $table->string('priority_level', 20)->nullable();
        $table->decimal('total_weight', 18, 2)->nullable();
        $table->decimal('total_volume', 18, 2)->nullable();
        $table->integer('package_count')->nullable();
        $table->string('special_instructions', 500)->nullable();
        $table->boolean('is_active')->nullable();
        $table->integer('driver_id')->nullable();
        $table->string('helper_name', 50)->nullable();
        $table->string('current_location', 255)->nullable();
        $table->dateTime('call_time')->nullable();
        $table->dateTime('whse_in')->nullable();
        $table->dateTime('loading_start')->nullable();
        $table->dateTime('loading_end')->nullable();
        $table->dateTime('whse_out')->nullable();
        $table->boolean('isactive')->nullable()->default(1);
        $table->integer('created_by')->default(0);
        $table->integer('updated_by')->nullable();
        $table->timestamps();

        // Foreign Key Constraints (not needed when using foreignId->constrained above)
        // $table->foreign('current_status_id')
        //       ->references('id')->on('tms_tracking_status');
        // $table->foreign('tracking_type_id')
        //       ->references('id')->on('tms_tracking_type');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tms_tracking_header');
    }
};
