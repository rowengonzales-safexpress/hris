<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tms_tracking_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type', 50);
            // DELIVERY_UPDATE, DELAY_ALERT, ARRIVAL_NOTIFICATION, EXCEPTION_ALERT, STATUS_CHANGE

            $table->string('recipient_type', 50);
            // CUSTOMER, DRIVER, ADMIN, DISPATCHER

            $table->string('recipient_id', 50);
            $table->string('recipient_contact', 100); // Email or Phone

            // Message Content
            $table->string('subject', 200);
            $table->text('message_body');
            $table->string('template_id', 50)->nullable();

            // Delivery Information
            $table->string('delivery_method', 50);
            // EMAIL, SMS, PUSH_NOTIFICATION, IN_APP

            $table->string('notification_status', 50)->default('PENDING');
            // PENDING, SENT, DELIVERED, FAILED, CANCELLED

            $table->dateTime('scheduled_time')->nullable();
            $table->dateTime('sent_time')->nullable();
            $table->dateTime('delivered_time')->nullable();

            // Related Entities
            $table->string('related_entity_type', 50)->nullable();
            // TRIP, DRIVER, VEHICLE, DELIVERY

            $table->string('related_entity_id', 50)->nullable();

            // Priority and Retry Logic
            $table->string('priority', 20)->default('NORMAL');
            // HIGH, NORMAL, LOW

            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->dateTime('next_retry_time')->nullable();

            // Error Handling
            $table->text('error_message')->nullable();
            $table->text('delivery_response')->nullable();

            // System Fields
            $table->tinyInteger('is_active')->default(1);
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_date')->nullable();

          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tms_tracking_notifications');
    }
};
