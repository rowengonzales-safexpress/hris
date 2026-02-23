<?php

use App\Http\Controllers\Tracking\DashboardController;
use App\Http\Controllers\Tracking\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tracking Routes
|--------------------------------------------------------------------------
|
| Here is where you can register trackin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group.
|
*/

Route::prefix('tracking')->name('tracking.')->middleware(['auth', 'tracking', 'check.app.user:2'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::resource('client', \App\Http\Controllers\Tracking\TrackingClientController::class)->names([
        'index' => 'client.index',
        'create' => 'client.create',
        'store' => 'client.store',
        'show' => 'client.show',
        'edit' => 'client.edit',
        'update' => 'client.update',
        'destroy' => 'client.destroy'
    ]);
    Route::resource('store', \App\Http\Controllers\Tracking\TrackingClientStoreAddressController::class)->names([
        'index' => 'store.index',
        'create' => 'store.create',
        'store' => 'store.store',
        'show' => 'store.show',
        'edit' => 'store.edit',
        'update' => 'store.update',
        'destroy' => 'store.destroy'
    ]);
    Route::get('store/api/by-client/{clientId}', [\App\Http\Controllers\Tracking\TrackingClientStoreAddressController::class, 'getByClientId'])->name('store.by-client');
    Route::resource('tracker', \App\Http\Controllers\Tracking\TrackingHeaderController::class)->names([
        'index' => 'tracker.index',
        'create' => 'tracker.create',
        'store' => 'tracker.store',
        'show' => 'tracker.show',
        'edit' => 'tracker.edit',
        'update' => 'tracker.update',
        'destroy' => 'tracker.destroy'
    ]);
    Route::get('tracker/api/drivers', [\App\Http\Controllers\Tracking\TrackingHeaderController::class, 'drivers'])->name('tracker.drivers');
    Route::get('tracker/api/droptrip-summary/{headerId}', [\App\Http\Controllers\Tracking\TrackingHeaderController::class, 'droptripSummary'])->name('tracker.droptrip-summary');
    Route::resource('driver', \App\Http\Controllers\Tracking\TrackingDriverController::class)->names([
        'index' => 'driver.index',
        'create' => 'driver.create',
        'store' => 'driver.store',
        'show' => 'driver.show',
        'edit' => 'driver.edit',
        'update' => 'driver.update',
        'destroy' => 'driver.destroy'
    ]);
    Route::resource('vehicle', \App\Http\Controllers\Tracking\TrackingVehicleController::class)->names([
        'index' => 'vehicle.index',
        'create' => 'vehicle.create',
        'store' => 'vehicle.store',
        'show' => 'vehicle.show',
        'edit' => 'vehicle.edit',
        'update' => 'vehicle.update',
        'destroy' => 'vehicle.destroy'
    ]);
    Route::resource('tracking-event', \App\Http\Controllers\Tracking\TrackingEventController::class)->names([
        'index' => 'tracking-event.index',
        'create' => 'tracking-event.create',
        'store' => 'tracking-event.store',
        'show' => 'tracking-event.show',
        'edit' => 'tracking-event.edit',
        'update' => 'tracking-event.update',
        'destroy' => 'tracking-event.destroy'
    ]);
    // Header-by-id retrieval API for tracking events
    Route::get('tracking-event/header/{id}', [\App\Http\Controllers\Tracking\TrackingEventController::class, 'headerById'])
        ->name('tracking-event.header');
    // Warehouse OUT for a tracking header
    Route::post('tracking-event/{id}/warehouse-out', [\App\Http\Controllers\Tracking\TrackingEventController::class, 'warehouseOut'])
        ->name('tracking-event.warehouse-out');

    // Update droptrip times for a store within a header
    Route::post('tracking-event/{headerId}/store/{storeId}/update-times', [\App\Http\Controllers\Tracking\TrackingEventController::class, 'updateDroptripTimes'])
        ->name('tracking-event.update-times');

    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('shipment-status', [ReportController::class, 'shipmentStatusReport'])->name('reports.shipment-status');
        Route::get('delivery-route-summary', [ReportController::class, 'deliveryRouteSummary'])->name('reports.delivery-route-summary');
        Route::get('package-tracking-overview', [ReportController::class, 'packageTrackingOverview'])->name('reports.package-tracking-overview');
        Route::get('logistics-performance', [ReportController::class, 'logisticsPerformanceReport'])->name('reports.logistics-performance');
        Route::get('system-activity', [ReportController::class, 'systemActivityReport'])->name('reports.system-activity');
        Route::get('tracking-summary-dashboard', [ReportController::class, 'trackingSummaryDashboard'])->name('reports.tracking-summary-dashboard');
        Route::get('performance-metrics', [ReportController::class, 'performanceMetricsReport'])->name('reports.performance-metrics');
        Route::get('audit-trail', [ReportController::class, 'auditTrailReport'])->name('reports.audit-trail');
    });

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');

    Route::patch('/notifications/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});
