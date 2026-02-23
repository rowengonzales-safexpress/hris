<?php

use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\Admin\CoreAppController;
use App\Http\Controllers\Admin\CoreBranchController;
use App\Http\Controllers\Admin\CoreRoleController;
use App\Http\Controllers\Admin\CoreRoleMenuController;
use App\Http\Controllers\Admin\CoreUsersController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use Database\Seeders\core\CoreRoleMenuSeeder;
use Illuminate\Support\Facades\Route;




/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group.
|
*/



Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // Application CRUD routes
    Route::resource('application', CoreAppController::class)->names([
        'index' => 'application.index',
        'create' => 'application.create',
        'store' => 'application.store',
        'show' => 'application.show',
        'edit' => 'application.edit',
        'update' => 'application.update',
        'destroy' => 'application.destroy'
    ]);

    // Menu CRUD routes
    Route::resource('menu', MenuController::class)->names([
        'index' => 'menu.index',
        'create' => 'menu.create',
        'store' => 'menu.store',
        'show' => 'menu.show',
        'edit' => 'menu.edit',
        'update' => 'menu.update',
        'destroy' => 'menu.destroy'
    ]);

    // Application-specific menus
    Route::get('application/{appId}/menu', [MenuController::class, 'byApp'])->name('application.menu');

    // Role CRUD routes
    Route::resource('role', CoreRoleController::class)->names([
        'index' => 'role.index',
        'create' => 'role.create',
        'store' => 'role.store',
        'show' => 'role.show',
        'edit' => 'role.edit',
        'update' => 'role.update',
        'destroy' => 'role.destroy'
    ]);

    // Role Menu CRUD routes
    Route::delete('menu-role/bulk-destroy', [CoreRoleMenuController::class, 'bulkDestroy'])->name('menu-role.bulk-destroy');
    Route::resource('menu-role', CoreRoleMenuController::class)->names([
        'index' => 'menu-role.index',
        'create' => 'menu-role.create',
        'store' => 'menu-role.store',
        'show' => 'menu-role.show',
        'edit' => 'menu-role.edit',
        'update' => 'menu-role.update',
        'destroy' => 'menu-role.destroy'
    ]);

    // Core Branch routes
    Route::get('core-branch/list', [CoreBranchController::class, 'getList']);
    Route::resource('core-branch', CoreBranchController::class);

    // Core Users routes
    Route::get('user/list', [CoreUsersController::class, 'getList']);
    Route::resource('user', CoreUsersController::class);
    Route::get('core-apps', [CoreUsersController::class, 'getCoreApps']);
    Route::get('menus/app/{appId}', [CoreUsersController::class, 'getMenusByAppId']);
    Route::get('user/{userId}/menus/app/{appId}', [CoreUsersController::class, 'getUserMenuPermissions']);
    Route::get('user/{userId}/applications', [CoreUsersController::class, 'getUserApplications']);
    Route::get('role/{roleId}/menus/app/{appId}', [CoreUsersController::class, 'getRoleMenuPermissions']);
    Route::post('user/{userId}/applications', [CoreUsersController::class, 'saveUserApplications']);
    Route::post('role/{roleId}/menus/app/{appId}', [CoreUsersController::class, 'saveRoleMenuPermissions']);
    Route::post('user/{userId}/menus/app/{appId}', [CoreUsersController::class, 'saveUserMenuPermissions']);

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');

    Route::patch('/notifications/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});
