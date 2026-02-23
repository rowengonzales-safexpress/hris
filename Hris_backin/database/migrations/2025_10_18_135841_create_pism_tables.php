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
        // Create pism_attendperfdeptdaily table
        Schema::create('sqdcm_attendperfdeptdaily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('dept_code', 10);
            $table->string('site_code', 10);
            $table->integer('total_emp')->default(0);
            $table->integer('present_emp')->default(0);
            $table->decimal('attendance_perc', 5, 2)->default(0.00);
            $table->integer('total_target')->default(0);
            $table->integer('total_achieved')->default(0);
            $table->decimal('achievement_perc', 5, 2)->default(0.00);
            $table->decimal('overall_score', 5, 2)->default(0.00);
            $table->string('grade', 2)->nullable();
            $table->timestamps();

            $table->index(['date', 'dept_code', 'site_code']);
        });

        // Create pism_attendperfempdaily table
        Schema::create('sqdcm_attendperfempdaily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('emp_code', 20);
            $table->string('emp_name', 100);
            $table->string('dept_code', 10);
            $table->string('site_code', 10);
            $table->boolean('is_present')->default(false);
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->decimal('working_hours', 4, 2)->default(0.00);
            $table->integer('target_value')->default(0);
            $table->integer('achieved_value')->default(0);
            $table->decimal('performance_perc', 5, 2)->default(0.00);
            $table->decimal('attendance_score', 5, 2)->default(0.00);
            $table->decimal('performance_score', 5, 2)->default(0.00);
            $table->decimal('overall_score', 5, 2)->default(0.00);
            $table->string('grade', 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['date', 'emp_code']);
            $table->index(['date', 'dept_code', 'site_code']);
        });

        // Create pism_attendperfsitedaily table
        Schema::create('sqdcm_attendperfsitedaily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('site_code', 10);
            $table->string('site_name', 100);
            $table->string('district_code', 10);
            $table->integer('total_emp')->default(0);
            $table->integer('present_emp')->default(0);
            $table->decimal('attendance_perc', 5, 2)->default(0.00);
            $table->integer('total_target')->default(0);
            $table->integer('total_achieved')->default(0);
            $table->decimal('performance_perc', 5, 2)->default(0.00);
            $table->decimal('overall_score', 5, 2)->default(0.00);
            $table->string('grade', 2)->nullable();
            $table->timestamps();

            $table->index(['date', 'site_code']);
            $table->index(['date', 'district_code']);
        });

        // Create pism_kpivaluesdaily table
        Schema::create('sqdcm_kpivaluesdaily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('kpi_code', 20);
            $table->string('kpi_name', 100);
            $table->string('entity_type', 20); // 'SITE', 'DEPT', 'EMP'
            $table->string('site_code', 20);
            $table->decimal('target_value', 15, 2)->default(0.00);
            $table->decimal('achieved_value', 15, 2)->default(0.00);
            $table->decimal('achievement_perc', 5, 2)->default(0.00);
            $table->decimal('weightage', 5, 2)->default(0.00);
            $table->decimal('weighted_score', 5, 2)->default(0.00);
            $table->string('unit', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['date', 'kpi_code', 'entity_type', 'site_code']);
            $table->index(['date', 'entity_type', 'site_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pism_kpivaluesdaily');
        Schema::dropIfExists('pism_attendperfsitedaily');
        Schema::dropIfExists('pism_attendperfempdaily');
        Schema::dropIfExists('pism_attendperfdeptdaily');
    }
};
