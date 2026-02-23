<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTaskSchedule\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
class VirtualASController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfNextWeek = Carbon::now()->startOfWeek()->addWeek();
        $endOfNextWeek = Carbon::now()->endOfWeek()->addWeek();

        $dailyTasks = Task::orderBy('task_dailytask.taskdate', 'asc')
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->join('theme_vscs', 'theme_vscs.userid', '=', 'task_dailytask.user_id')
            ->with('taskLists')
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startOfWeek, $endOfWeek])
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name', 'theme_vscs.id as theme_id', 'theme_vscs.background', 'theme_vscs.active_background', 'theme_vscs.font_background')
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $nextWeekTasks = Task::orderBy('task_dailytask.taskdate', 'asc')
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->join('theme_vscs', 'theme_vscs.userid', '=', 'task_dailytask.user_id')
            ->with('taskLists')
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startOfNextWeek, $endOfNextWeek])
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name', 'theme_vscs.id as theme_id', 'theme_vscs.background', 'theme_vscs.active_background', 'theme_vscs.font_background')
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $hasNextWeekTasks = !$nextWeekTasks->isEmpty();
        $allTasks = $hasNextWeekTasks ? $dailyTasks->merge($nextWeekTasks) : $dailyTasks;

        $tasksList = Task::withCount([
            'taskLists',
            'taskLists as completed_task_count' => function ($query) {
                $query->where('status', 1);
            }
        ])
            ->where('task_dailytask.user_id', $userId)
            ->whereIn('task_dailytask.taskdate', $allTasks->pluck('taskdate')->unique())
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $tasksList->transform(function ($task) {
            $task->percentage_completed = round(
                ($task->task_lists_count > 0)
                    ? ($task->completed_task_count / $task->task_lists_count) * 100
                    : 0,
                2
            );
            return $task;
        });

        $totalcompleted_task_count = $tasksList->sum('completed_task_count');
        $totaltask_lists_count = $tasksList->sum('task_lists_count');
        $totalpercentcompleted = $tasksList->sum('percentage_completed');
        $averagepercentcompleted = ($tasksList->count() > 0) ? round($totalpercentcompleted / $tasksList->count(), 2) : 0;

        return Inertia::render('WeeklyTask/Mycoa/Mycoa', [
            'dailyTasks' => $allTasks,
            'TaskList' => $tasksList,
            'hasNextWeekTasks' => $hasNextWeekTasks,
            'totalcomplettask' => $totalcompleted_task_count,
            'totaltasklist' => $totaltask_lists_count,
            'totalpercentcomplete' => $averagepercentcompleted
        ]);
    }
    public function apiIndex()
    {
        $userId = auth()->id();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfNextWeek = Carbon::now()->startOfWeek()->addWeek();
        $endOfNextWeek = Carbon::now()->endOfWeek()->addWeek();

        $dailyTasks = Task::orderBy('task_dailytask.taskdate', 'asc')
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->join('theme_vscs', 'theme_vscs.userid', '=', 'task_dailytask.user_id')
            ->with('taskLists')
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startOfWeek, $endOfWeek])
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name', 'theme_vscs.id as theme_id', 'theme_vscs.background', 'theme_vscs.active_background', 'theme_vscs.font_background')
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $nextWeekTasks = Task::orderBy('task_dailytask.taskdate', 'asc')
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->join('theme_vscs', 'theme_vscs.userid', '=', 'task_dailytask.user_id')
            ->with('taskLists')
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startOfNextWeek, $endOfNextWeek])
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name', 'theme_vscs.id as theme_id', 'theme_vscs.background', 'theme_vscs.active_background', 'theme_vscs.font_background')
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $hasNextWeekTasks = !$nextWeekTasks->isEmpty();
        $allTasks = $hasNextWeekTasks ? $dailyTasks->merge($nextWeekTasks) : $dailyTasks;

        $tasksList = Task::withCount([
            'taskLists',
            'taskLists as completed_task_count' => function ($query) {
                $query->where('status', 1);
            }
        ])
            ->where('task_dailytask.user_id', $userId)
            ->whereIn('task_dailytask.taskdate', $allTasks->pluck('taskdate')->unique())
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $tasksList->transform(function ($task) {
            $task->percentage_completed = round(
                ($task->task_lists_count > 0)
                    ? ($task->completed_task_count / $task->task_lists_count) * 100
                    : 0,
                2
            );
            return $task;
        });

        $totalcompleted_task_count = $tasksList->sum('completed_task_count');
        $totaltask_lists_count = $tasksList->sum('task_lists_count');
        $totalpercentcompleted = $tasksList->sum('percentage_completed');
        $averagepercentcompleted = ($tasksList->count() > 0) ? round($totalpercentcompleted / $tasksList->count(), 2) : 0;

        return response()->json([
            'dailyTasks' => $allTasks,
            'TaskList' => $tasksList,
            'hasNextWeekTasks' => $hasNextWeekTasks,
            'totalcomplettask' => $totalcompleted_task_count,
            'totaltasklist' => $totaltask_lists_count,
            'totalpercentcomplete' => $averagepercentcompleted
        ]);
    }

    public function vscfilter(Request $request)
    {
        $userId = auth()->id();

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $dailyTasks = Task::orderBy('task_dailytask.taskdate', 'asc')
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->join('theme_vscs', 'theme_vscs.userid', '=', 'task_dailytask.user_id')
            ->with('taskLists')
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startDate, $endDate])
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name', 'theme_vscs.id as theme_id', 'theme_vscs.background', 'theme_vscs.active_background', 'theme_vscs.font_background')
            ->get();

        $tasksList = Task::withCount([
            'taskLists',
            'taskLists as completed_task_count' => function ($query) {
                $query->where('status', 1);
            }
        ])
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startDate, $endDate])
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->get();

        $tasksList->transform(function ($task) {
            $task->percentage_completed = ($task->task_lists_count > 0)
                ? ($task->completed_task_count / $task->task_lists_count) * 100
                : 0;
            return $task;
        });

        return response()->json(['dailyTasks' => $dailyTasks, 'TaskList' => $tasksList]);
    }

    public function changethemes(Request $request)
    {
        DB::table('theme_vscs')->updateOrInsert(
            ['userid' => $request->userid],
            [
                'background' => $request->background,
                'active_background' => $request->active_background,
                'font_background' => $request->font_background,
                'updated_at' => now()
            ]
        );

        return response()->json(['message' => 'success']);
    }
}
