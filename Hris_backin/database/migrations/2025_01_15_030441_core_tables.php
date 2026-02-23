<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CoreTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create tables without foreign keys first
        Schema::create('core_app', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->nullable();
            $table->string('code', 20);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->char('status',1)->default('A')->index();
            $table->string('status_message', 150);
            $table->string('route', 150)->nullable();
            $table->string('logo')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('core_branch', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->default('');
            $table->string('branch_code')->nullable();
            $table->string('branch_name')->unique();
            $table->string('fulladdress')->nullable();
            $table->char('status',1)->default('A')->index();
            $table->string('immediate_head')->nullable();
            $table->timestamps(); // Added missing timestamps
        });

        // Create core_users AFTER core_branch
        Schema::create('core_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->default('');
            $table->unsignedInteger('branch_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type', 20)->default('guest');
            $table->string('member_role',50)->nullable();
            $table->string('first_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('google_id')->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('language', 5)->default('EN');
            $table->char('status',1)->default('A')->index();
            $table->softDeletes();
             $table->integer('sitehead_user_id')->nullable();
            $table->rememberToken();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        // Now create tables that depend on core_users and core_branch
        Schema::create('core_appuser', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->nullable();
            $table->unsignedInteger('app_id');
            $table->unsignedInteger('user_id');
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('id')->on('core_app');
            $table->foreign('user_id')->references('id')->on('core_users');
        });

        Schema::create('core_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->nullable();
            $table->unsignedInteger('app_id');
            $table->integer('parent_id')->default(0);
            $table->string('name', 100);
            $table->string('route');
            $table->text('icon',)->nullable();
            $table->integer('sort_order')->default(100)->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('id')->on('core_app');
        });

        Schema::create('core_role', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->default('');
            $table->string('code', 50)->nullable()->index();
            $table->string('name', 50);
            $table->string('description', 150)->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('core_rolemenu', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('menu_id');
            $table->string('permission')->default('manage');
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('core_role');
            $table->foreign('menu_id')->references('id')->on('core_menu');
        });

        Schema::create('core_usermenus', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('menu_id');
            $table->boolean('is_manage')->default(true)->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('core_users');
            $table->foreign('menu_id')->references('id')->on('core_menu');
        });

        Schema::create('core_branchusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->default('');
            $table->unsignedInteger('branch_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('core_branch');
            $table->foreign('user_id')->references('id')->on('core_users');
        });

        // Add foreign key to core_users AFTER core_branchusers is created
        Schema::table('core_users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('core_branch');
        });

        // Rest of your tables...
        Schema::create('core_message', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_type', 255);
            $table->string('message_code', 50);
            $table->string('message', 255);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('core_passwordresets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('core_helpsections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->nullable();
            $table->integer('systemID');
            $table->string('section_name', 500);
            $table->timestamps();
        });

        Schema::create('core_helppages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->index()->nullable();
            $table->unsignedInteger('section_id');
            $table->string('page_name', 500);
            $table->longText('page_body');
            $table->boolean('is_publish')->default(false);
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('core_helpsections');
        });

        Schema::create('core_failedjobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('core_personalaccesstokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop in reverse order to handle foreign key dependencies
        Schema::dropIfExists('audits');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('core_personalaccesstokens');
        Schema::dropIfExists('core_failedjobs');
        Schema::dropIfExists('core_helppages');
        Schema::dropIfExists('core_helpsections');
        Schema::dropIfExists('core_passwordresets');
        Schema::dropIfExists('core_message');
        Schema::dropIfExists('core_branchusers');
        Schema::dropIfExists('core_usermenus');
        Schema::dropIfExists('core_rolemenu');
        Schema::dropIfExists('core_role');
        Schema::dropIfExists('core_menu');
        Schema::dropIfExists('core_appuser');
        Schema::dropIfExists('core_users');
        Schema::dropIfExists('core_branch');
        Schema::dropIfExists('core_app');
    }
}
