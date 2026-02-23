<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        // frms_form
        Schema::create('frms_form', function (Blueprint $table) {
            $table->id();
            $table->string('frm_no')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('branch_id');
            $table->datetime('request_date');
            $table->datetime('expectedliquidation_date')->nullable();
            $table->string('purpose');
            $table->enum('status_request', ['FA','FD','FL','A','C','X'])->default('FA'); // FA=For Approved, FD=For Disbursement, FL=For Liquidation, A=Approved, C=Closed, X=Canceled
            $table->timestamps();
        });

        // frms_list
        Schema::create('frms_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requesting_id');
            $table->string('account_code_title')->nullable();
            $table->unsignedInteger('frequency');
            $table->string('description');
            $table->unsignedInteger('qty')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('requesting_id')->references('id')->on('frms_form');
        });

         Schema::create('frms_disbursement', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_id');
             $table->unsignedInteger('frms_id');
            $table->string('disbursement_no')->nullable();
            $table->enum('status', ['P','O','A','C','X','FS','FM'])->default('P'); // P=Pending, O=Open, A=Approved, C=Closed, F=Failed
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
         });

     

        Schema::create('frms_liquidationdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('disbursement_id');
            $table->unsignedInteger('frmslist_id');
            $table->string('ref_num')->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('or_no')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('tin')->nullable();
            $table->text('address')->nullable();
            $table->string('vat_non_vat')->nullable();
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('input_vat', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('frms_documents', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship columns
            $table->unsignedBigInteger('documentable_id'); // Can store frms_form ID or frms_liquidationdetail ID
            $table->string('documentable_type'); // Will store 'frms_form' or 'frms_liquidationdetail'
            $table->string('document_name', 255);
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->string('file_extension', 10);
            $table->string('mime_type', 100);
            $table->bigInteger('file_size');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Auditing
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['documentable_id', 'documentable_type']);
        });


        //trigger
        DB::unprepared('DROP TRIGGER IF EXISTS trgr_create_disbursement_no');
        DB::unprepared("
            CREATE TRIGGER trgr_create_disbursement_no
            BEFORE INSERT ON frms_disbursement
            FOR EACH ROW
            SET NEW.disbursement_no = IF(NEW.disbursement_no IS NULL OR NEW.disbursement_no = '', CONCAT('DS', LPAD((SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'frms_disbursement'), 8, '0')), NEW.disbursement_no);
        ");

        DB::unprepared('DROP TRIGGER IF EXISTS trgr_create_frm_no');
        DB::unprepared("
            CREATE TRIGGER trgr_create_frm_no
            BEFORE INSERT ON frms_form
            FOR EACH ROW
            SET NEW.frm_no = IF(NEW.frm_no IS NULL OR NEW.frm_no = '', CONCAT('FRM', LPAD((SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'frms_form'), 8, '0')), NEW.frm_no);
        ");
    }



    public function down(): void
    {
        Schema::dropIfExists('frms_documents');
        Schema::dropIfExists('frms_liquidationdetail');
        Schema::dropIfExists('frms_disbursement');
        Schema::dropIfExists('frms_list');
        Schema::dropIfExists('frms_form');

        //trigger
        DB::unprepared('DROP TRIGGER IF EXISTS trgr_create_disbursement_no');
        DB::unprepared('DROP TRIGGER IF EXISTS trgr_create_frm_no');
    }
};
