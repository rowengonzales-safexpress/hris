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
        Schema::create('tms_tracking_droptrip', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('trackingheader_id');
            $table->unsignedInteger('trackingclient_id');
            $table->unsignedInteger('trackingclient_store_id');
            $table->integer('sqno')->nullable();
            $table->string('drsino', 50)->nullable();
            $table->dateTime('store_time_in')->nullable();
            $table->dateTime('unloading_start')->nullable();
            $table->dateTime('unloading_end')->nullable();
            $table->dateTime('store_time_out')->nullable();
            $table->string('receiver_name', 100)->nullable();
            $table->string('delivery_status', 50)->nullable();

            // Audit fields
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
        Schema::dropIfExists('tms_tracking_droptrip');
    }
};
