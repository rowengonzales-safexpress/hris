<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTaskSchedule\Task;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $tasks = Task::with('user')
            ->withCount('taskLists')
            ->orderBy('taskdate', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'taskdate' => $task->taskdate ? $task->taskdate->format('Y-m-d') : null,
                    'user_name' => $task->user ? ($task->user->first_name . ' ' . $task->user->last_name) : 'N/A',
                    'project' => $task->project,
                    'total_tasks' => $task->task_lists_count,
                    'status' => $task->remarks, // HIT or MISS
                ];
            });

        return Inertia::render('WeeklyTask/Report/index', [
            'tasks' => $tasks
        ]);
    }
}
