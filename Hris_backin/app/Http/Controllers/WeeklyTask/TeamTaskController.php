<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use App\Models\Core\CoreBranch;
use App\Models\Core\User;
use App\Models\WeeklyTaskSchedule\Task;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TeamTaskController extends Controller
{
    public function index()
    {
        return Inertia::render('WeeklyTask/TeamTask/index');
    }

    public function users()
    {
        $branchId = auth()->user()?->branch_id;
        $branchName = CoreBranch::query()
            ->where('id', $branchId)
            ->value('branch_name');

        $users = User::query()
            ->where('branch_id', $branchId)
            ->select(['id', 'first_name', 'last_name'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                ];
            });

        return response()->json([
            'branch' => [
                'id' => $branchId,
                'name' => $branchName,
            ],
            'users' => $users,
        ]);
    }

    public function week(User $user)
    {
        $authUser = auth()->user();
        if (!$authUser || (int) $authUser->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = (clone $start)->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $tasks = Task::query()
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->where('task_dailytask.user_id', $user->id)
            ->whereBetween('task_dailytask.taskdate', [$start, $end])
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name')
            ->get()
            ->map(function ($task) {
                return [
                    'dailytask_id' => $task->id,
                    'site_name' => $task->site_name,
                    'user_id' => $task->user_id,
                    'branch_id' => $task->branch_id,
                    'taskdate' => optional($task->taskdate)->toISOString(),
                    'project' => $task->project,
                    'plandate' => optional($task->plandate)->toISOString(),
                    'planenddate' => optional($task->planenddate)->toISOString(),
                    'startdate' => optional($task->startdate)->toISOString(),
                    'enddate' => optional($task->enddate)->toISOString(),
                    'tasktype_id' => $task->tasktype_id,
                    'status' => $task->status,
                    'remarks' => $task->remarks,
                    'status_task' => $task->status_task,
                ];
            });

        return response()->json([
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            ],
            'tasks' => $tasks,
        ]);
    }
}
