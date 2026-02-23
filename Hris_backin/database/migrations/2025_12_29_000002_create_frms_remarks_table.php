<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frms_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('documentId');
            $table->string('aliase')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['A','C'])->default('A');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frms_remarks');
    }
};
