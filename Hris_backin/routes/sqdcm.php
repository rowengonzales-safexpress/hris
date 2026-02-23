<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sqdcm\SqdcmController;
use App\Http\Controllers\Sqdcm\DashboardController;
/*
|--------------------------------------------------------------------------
| Pism Routes
|--------------------------------------------------------------------------
|
| Here is where you can register pism routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "pism" middleware group.
|
*/

Route::prefix('sqdcm')->name('sqdcm.')->middleware(['auth', 'sqdcm', 'check.app.user:5'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::resource('client', SqdcmController::class)->names([
        'index' => 'client.index',
        'create' => 'client.create',
        'store' => 'client.store',
        'show' => 'client.show',
        'edit' => 'client.edit',
        'update' => 'client.update',
        'destroy' => 'client.destroy'
    ]);


    // API routes for dashboard data
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/sites', [SqdcmController::class, 'getSites'])->name('sites');
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData'])->name('dashboard');
        Route::get('/attendance-performance', [SqdcmController::class, 'getAttendancePerformance'])->name('attendance-performance');
        Route::get('/kpi-values', [SqdcmController::class, 'getKpiValues'])->name('kpi-values');
        Route::post('/default-site', [SqdcmController::class, 'updateDefaultSite'])->name('update-default-site');
    });

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');

    Route::patch('/notifications/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});
