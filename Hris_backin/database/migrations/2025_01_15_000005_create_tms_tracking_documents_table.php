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
        Schema::create('tms_tracking_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_id');
            // Intentionally not adding FK here due to migration order; will be added later when core_branch exists
            $table->foreignId('tracking_header_id')->constrained('tms_tracking_header');
            $table->string('document_type', 50);
            $table->string('document_name', 255);
            $table->string('file_path', 500);
            $table->bigInteger('file_size')->nullable();
            $table->string('content_type', 100)->nullable();
            $table->string('file_extension', 10)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('uploaded_date')->useCurrent();
            $table->string('uploaded_by', 50)->nullable();
            $table->timestamp('verified_date')->nullable();
            $table->string('verified_by', 50)->nullable();
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
        Schema::dropIfExists('tms_tracking_documents');
    }
};
