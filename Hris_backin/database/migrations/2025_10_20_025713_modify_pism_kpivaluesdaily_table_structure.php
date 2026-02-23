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
        Schema::table('sqdcm_kpivaluesdaily', function (Blueprint $table) {
            // Rename performance_perc to achievement_perc


            $table->string('kpi_category', 50)->nullable()->after('site_code');

            // Rename achieved_value to actual_value to match model
            $table->renameColumn('achieved_value', 'actual_value');

            // Add variance column
            $table->decimal('variance', 15, 2)->default(0.00)->after('achievement_perc');

            // Add trend column
            $table->string('trend', 20)->nullable()->after('variance');

            // Add is_critical column
            $table->boolean('is_critical')->default(false)->after('trend');

            // Add alert_level column
            $table->integer('alert_level')->default(0)->after('is_critical');

            // Modify remarks column to match model expectations
            $table->text('remarks')->nullable()->change();

            // Drop columns that don't exist in the model
            $table->dropColumn(['kpi_code', 'entity_type', 'weightage', 'weighted_score', 'unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sqdcm_kpivaluesdaily', function (Blueprint $table) {
            // Reverse all changes
            $table->renameColumn('achievement_perc', 'performance_perc');
            $table->dropColumn(['site_code', 'dept_code', 'emp_code', 'kpi_category', 'variance', 'trend', 'is_critical', 'alert_level']);
            $table->renameColumn('actual_value', 'achieved_value');

            // Add back the dropped columns
            $table->string('kpi_code', 20)->after('date');
            $table->string('entity_type', 20)->after('kpi_name');
            $table->string('entity_code', 20)->after('entity_type');
            $table->decimal('weightage', 5, 2)->default(0.00)->after('achievement_perc');
            $table->decimal('weighted_score', 5, 2)->default(0.00)->after('weightage');
            $table->string('unit', 20)->nullable()->after('weighted_score');
        });
    }
};
