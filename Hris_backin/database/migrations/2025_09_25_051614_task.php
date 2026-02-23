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
        Schema::create('task_type', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 50)->index()->default('');
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('task_dailytask', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 50)->index()->default('');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('core_users');
            $table->unsignedInteger('immediatehead_id')->nullable();
            $table->foreign('immediatehead_id')->references('id')->on('core_users');
            $table->unsignedInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('core_branch');
            $table->timestamp('taskdate');
            $table->unsignedBigInteger('tasktype_id');
            $table->foreign('tasktype_id')->references('id')->on('task_type');
            $table->timestamp('plandate')->nullable();
            $table->timestamp('planenddate')->nullable();
            $table->string('project')->nullable();
            $table->timestamp('startdate')->nullable();
            $table->timestamp('enddate')->nullable();
            $table->string('status')->nullable();
            $table->integer('attachment')->default(0)->nullable();
            $table->string('PWS')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('status_task')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('task_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dailytask_id');
            $table->foreign('dailytask_id')->references('id')->on('task_dailytask')->onDelete('cascade');
            $table->string('task_name');
            $table->integer('status')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('asset_monitoring', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('core_branch');
            $table->string('asset_name')->nullable(false);
            $table->string('asset_type')->nullable(false);
            $table->string('serial')->nullable();
            $table->date('date_acquired')->nullable();
            $table->string('man_supplier')->nullable();
            $table->string('unit')->nullable();
            $table->string('location')->nullable();
            $table->string('paccountable')->nullable();
            $table->integer('locationchangetranfer')->nullable();
            $table->string('condition')->nullable();
            $table->text('maintenancenotes')->nullable();
            $table->date('lastmaintenance')->nullable();
            $table->date('nextmaintenance')->nullable();
            $table->integer('operationhours')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('purchasecost', 10, 2)->nullable();
            $table->decimal('depreciationcost', 10, 2)->nullable();
            $table->boolean('is_active')->nullable()->default(1);
            $table->text('insurancewarrantyinfo')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('categorycode');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('itemcode');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
            $table->boolean('status')->default(true)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('techrecomms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('recommnum', 20);
            $table->unsignedInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('core_branch');
            $table->string('user', 20);
            $table->string('brand', 20);
            $table->string('model', 50)->nullable();
            $table->string('assettag', 50)->nullable();
            $table->string('serialnum', 50)->nullable();
            $table->text('problem');
            $table->text('assconducted');
            $table->text('recommendation');
            $table->tinyInteger('status');
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('techrecomms');
        Schema::dropIfExists('asset_items');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('asset_monitoring');
        Schema::dropIfExists('task_list');
        Schema::dropIfExists('task_dailytask');
        Schema::dropIfExists('task_type');
    }
};
