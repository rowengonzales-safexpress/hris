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
        Schema::create('core_transaction_code', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_name');
            $table->string('transaction_key');
            $table->string('identitycode');
            $table->decimal('trans_value', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->integer('sortorder')->default(0);
            $table->tinyInteger('isactive')->default(1);
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_transaction_code');
    }
};
