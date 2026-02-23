<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('frms_form', function (Blueprint $table) {
            $table->unsignedInteger('approved_finance_staff_by')->nullable()->after('status_request');
            $table->timestamp('approved_finance_staff_at')->nullable()->after('approved_finance_staff_by');

            $table->unsignedInteger('approved_finance_manager_by')->nullable()->after('approved_finance_staff_at');
            $table->timestamp('approved_finance_manager_at')->nullable()->after('approved_finance_manager_by');

            $table->unsignedInteger('approved_cfo_by')->nullable()->after('approved_finance_manager_at');
            $table->timestamp('approved_cfo_at')->nullable()->after('approved_cfo_by');
        });
    }

    public function down(): void
    {
        Schema::table('frms_form', function (Blueprint $table) {
            $table->dropColumn([
                'approved_finance_staff_by',
                'approved_finance_staff_at',
                'approved_finance_manager_by',
                'approved_finance_manager_at',
                'approved_cfo_by',
                'approved_cfo_at'
            ]);
        });
    }
};

