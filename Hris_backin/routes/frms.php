<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FRMS\FormController;
use App\Http\Controllers\FRMS\ReporsController;
use App\Http\Controllers\FRMS\ReviewController;
use App\Http\Controllers\FRMS\SConfigController;
use App\Http\Controllers\FRMS\ApprovalController;
use App\Http\Controllers\FRMS\DocumentController;
use App\Http\Controllers\FRMS\DashboardController;
use App\Http\Controllers\CoreTransactionCodeController;
use App\Http\Controllers\Admin\CoreBranchController;
use App\Http\Controllers\Admin\CoreUsersController;
use App\Http\Controllers\FRMS\FrmsLiquidationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Mailtest;

Route::prefix('frls')->name('frls.')->middleware(['auth', 'frms', 'check.app.user:4'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::match(['get','post'], 'mail/test', function (Request $request) {
        $to = $request->input('to', auth()->user()->email);
        $subject = $request->input('subject', 'Test Mail');
        $jobRequest = $request->input('data', []);
        if (empty($jobRequest)) {
            $message = $request->input('message');
            if ($message !== null) {
                $jobRequest = ['message' => $message];
            }
        }
        Mail::to($to)->send(new Mailtest($jobRequest, $subject));
        return response()->json(['status' => 'sent']);
    })->name('mail.test');

    Route::resource('form', FormController::class)->only(['index','store','destroy','show'])->names([
        'index' => 'form.index',
        'store' => 'form.store',
        'destroy' => 'form.destroy',
        'show' => 'form.show',
    ]);

    // Custom destroy route to match frontend URL pattern
    Route::delete('form/destroy/{id}', [FormController::class, 'destroy'])->name('form.destroy');

    // Finance LqLiquidation Expenses pages
    Route::resource('liquidation-expenses', FrmsLiquidationController::class)->only(['index','store'])->names([
        'index' => 'frms-liquidation.index',
        'store' => 'frms-liquidation.store',
    ]);
    Route::get('finance-disbursement/breakdown/{formId}', [FrmsLiquidationController::class, 'breakdown'])->name('finance-disbursement.breakdown');
    Route::post('finance-disbursement/store', [FrmsLiquidationController::class, 'store'])->name('finance-disbursement.store');

    // Approval pages
    Route::resource('approval', ApprovalController::class)->only(['index'])->names([
        'index' => 'approval.index',
    ]);

    Route::get('approval/details/{id}', [ApprovalController::class, 'details'])->name('approval.details');
    Route::get('approval/liquidation-details/{formId}', [ApprovalController::class, 'liquidationDetails'])->name('approval.liquidation-details');
    Route::post('approval/finish', [ApprovalController::class, 'finish'])->name('approval.finish');
    Route::post('approval/reject', [ApprovalController::class, 'reject'])->name('approval.reject');


    // Review pages
    Route::resource('review', ReviewController::class)->only(['index'])->names([
        'index' => 'review.index',
    ]);
    Route::get('review/{form}', [ReviewController::class, 'show'])->name('review.show');
    Route::get('review/finance-detail/{formId}', [ReviewController::class, 'FinanceDetails'])->name('review.finance-detail');



    // Transaction Code API endpoints
    Route::get('/transaction-codes/frls-types', [CoreTransactionCodeController::class, 'getFrmsTypes'])->name('transaction-codes.frls-types');
    Route::get('/transaction-code/frls-frequincy',[CoreTransactionCodeController::class,'getFrmsFrequincy'])->name('trancsaction-codes.frls-frequincy');
    Route::get('/transaction-code/frls-vat',[CoreTransactionCodeController::class,'getFrmsVat'])->name('trancsaction-codes.frls-vat');
    Route::get('/transaction-code/frls-account-code',[CoreTransactionCodeController::class,'getFrmsAccountCode'])->name('trancsaction-codes.frls-account-code');
    Route::resource('transaction-codes', CoreTransactionCodeController::class);

    // Document upload routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/{frm_id}', [DocumentController::class, 'index'])->name('index');
        Route::post('/{frm_id}/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::get('/download/{id}', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::get('/api/{frm_id}', [DocumentController::class, 'getDocuments'])->name('api');
    });

    // Reports routes
    Route::get('/reports/liquidation-report', [ReporsController::class, 'LiquidationReport'])->name('reports.liquidation');
    Route::get('/reports/liquidation-detail/{formId}', [ReporsController::class, 'LiquidationDetails'])->name('reports.liquidation-detail');

    Route::get('/reports/finance-disbursement-report', [ReporsController::class, 'FinanceReport'])->name('reports.finance');
    Route::get('/reports/finance-detail/{formId}', [ReporsController::class, 'FinanceDetails'])->name('reports.finance-detail');

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Core Branch routes
    Route::get('core-branch/list', [CoreBranchController::class, 'getList']);
    Route::resource('core-branch', CoreBranchController::class);

    // Core Users routes
    Route::get('user/list', [CoreUsersController::class, 'getList']);
    Route::get('role/list', [CoreUsersController::class, 'getRoleList']);
    Route::resource('user', CoreUsersController::class);

    // System Configuration routes
    Route::get('/system-configuration', [SConfigController::class, 'index'])->name('system-configuration.index');

    Route::patch('/notifications/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});
