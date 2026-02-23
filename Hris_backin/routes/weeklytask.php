<?php

use App\Http\Controllers\WeeklyTaskScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('weekly-task-schedule')->name('weekly-task-schedule.')->middleware(['auth', 'weeklytask', 'check.app.user:6'])->group(function () {

    // Dashboard
     Route::get('/dashboard', [\App\Http\Controllers\WeeklyTask\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [\App\Http\Controllers\WeeklyTask\DashboardController::class, 'index'])->name('index');

    Route::get('/dashboard/chart', [\App\Http\Controllers\WeeklyTask\DashboardController::class, 'getChartData'])->name('dashboard.chart');



    Route::get('/task-types', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'getTaskTypes'])->name('tasktypes');

    Route::resource('/myprio', \App\Http\Controllers\WeeklyTask\TaskController::class)->names([
        'index' => 'index',
        'create' => 'create',
        'store' => 'store',
        'show' => 'show',
        'edit' => 'edit',
        'update' => 'update',
        'destroy' => 'destroy',
    ]);

    Route::put('/myprio/onhandler/{dailytask_id}', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'onhandler'])->name('onhandler');
    Route::put('/myprio/onTashHoliday/{dailytask_id}', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'onTashHoliday'])->name('onTashHoliday');
    Route::post('/myprio/addnewTask', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'addTask'])->name('addTask');
    Route::delete('/myprio/deleteTask/{id}', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'deleteTask'])->name('deleteTask');
    Route::get('/myprio/{id}/tasks', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'getTask'])->name('getTask');
    Route::post('/myprio/migrate-status', [\App\Http\Controllers\WeeklyTask\TaskController::class, 'migrateStatus'])->name('migrate.status');

    Route::get('/my-closed-prio', [\App\Http\Controllers\WeeklyTask\MyClosePrioController::class, 'index'])->name('mycloseprio.index');
    Route::get('/my-closed-prio/filter', [\App\Http\Controllers\WeeklyTask\MyClosePrioController::class, 'FilterClosePrio'])->name('mycloseprio.filter');

    Route::get('/mycoa', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'index'])->name('myvsc.index');
    Route::get('/mycoa/filter', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'vscfilter'])->name('myvsc.filter');
    Route::post('/mycoa/changethemes', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'changethemes'])->name('myvsc.changethemes');

    Route::get('/mycloseprio', [\App\Http\Controllers\WeeklyTask\MyClosePrioController::class, 'apiIndex'])->name('mycloseprio.data');
    Route::get('/filter-closeprio', [\App\Http\Controllers\WeeklyTask\MyClosePrioController::class, 'FilterClosePrio'])->name('mycloseprio.filter-data');

    Route::get('/api/myvsc', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'apiIndex'])->name('myvsc.data');
    Route::get('/api/filter-vsc', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'vscfilter'])->name('myvsc.filter-data');
    Route::post('/api/changethemes', [\App\Http\Controllers\WeeklyTask\VirtualASController::class, 'changethemes'])->name('myvsc.changethemes-data');

    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\WeeklyTask\CalendarController::class, 'index'])->name('calendar.index');

    // TeamTask
    Route::get('/team-tasks', [\App\Http\Controllers\WeeklyTask\TeamTaskController::class, 'index'])->name('teamtask.index');
    Route::get('/team-tasks/users', [\App\Http\Controllers\WeeklyTask\TeamTaskController::class, 'users'])->name('teamtask.users');
    Route::get('/team-tasks/{user}/week', [\App\Http\Controllers\WeeklyTask\TeamTaskController::class, 'week'])->name('teamtask.week');

    // Report
    Route::get('/reports', [\App\Http\Controllers\WeeklyTask\ReportController::class, 'index'])->name('report.index');
});
