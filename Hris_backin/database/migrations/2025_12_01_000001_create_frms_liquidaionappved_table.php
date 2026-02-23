<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('frms_liquidaionappved', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('disbursement_id');
            $table->unsignedInteger('approvedby_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frms_liquidaionappved');
    }
};

