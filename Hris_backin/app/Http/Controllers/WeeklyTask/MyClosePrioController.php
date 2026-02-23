<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTaskSchedule\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
class MyClosePrioController extends Controller
{
    public function index()
    {
        return Inertia::render('WeeklyTask/Myclosedprio/MyClosePrioList');
    }

    public function apiIndex()
    {
        $data = Task::query()
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('core_branch.branch_name', 'like', "%{$searchQuery}%");
            })
            ->where('task_dailytask.status_task', 1)
            ->where('task_dailytask.user_id', auth()->id())
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name')
            ->get()
            ->map(function ($dailytask) {
                return [
                    'id' => $dailytask->id,
                    'site' => $dailytask->branch_id,
                    'sitename' => $dailytask->site_name,
                    'user_id' => $dailytask->user_id,
                    'taskname' => $dailytask->project,
                    'tasktype' => [
                        'listtask' => method_exists($dailytask, 'tasktype') ? ($dailytask->tasktype ? $dailytask->tasktype->listtask() : null) : null,
                    ],
                    'taskdate' => $dailytask->taskdate ? $dailytask->taskdate->format('m-d-Y') : null,
                    'project' => $dailytask->project,
                    'plandate' => $dailytask->plandate ? $dailytask->plandate->format('m-d-Y') : null,
                    'planenddate' => $dailytask->planenddate ? $dailytask->planenddate->format('m-d-Y') : null,
                    'startdate' => $dailytask->startdate ? $dailytask->startdate->format('m-d-Y h:i A') : null,
                    'enddate' => $dailytask->enddate ? $dailytask->enddate->format('m-d-Y h:i A') : null,
                    'status' => $dailytask->status,
                    'attachment' => $dailytask->attachment,
                    'PWS' => $dailytask->PWS,
                    'remarks' => $dailytask->remarks,
                    'immediate_hid' => $dailytask->immediate_hid,
                    'created_at' => $dailytask->created_at ? $dailytask->created_at->format('m/d/Y h:i A') : null,
                ];
            });
        return response()->json($data);
    }

    public function FilterClosePrio(Request $request)
    {
        $userId = auth()->id();

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $data = Task::query()
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('core_branch.branch_name', 'like', "%{$searchQuery}%");
            })
            ->where('task_dailytask.status_task', 1)
            ->where('task_dailytask.user_id', $userId)
            ->whereBetween('task_dailytask.taskdate', [$startDate, $endDate])
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name')
            ->get()
            ->map(function ($dailytask) {
                return [
                    'id' => $dailytask->id,
                    'site' => $dailytask->branch_id,
                    'sitename' => $dailytask->site_name,
                    'user_id' => $dailytask->user_id,
                    'taskname' => $dailytask->project,
                    'tasktype' => [
                        'listtask' => method_exists($dailytask, 'tasktype') ? ($dailytask->tasktype ? $dailytask->tasktype->listtask() : null) : null,
                    ],
                    'taskdate' => $dailytask->taskdate ? $dailytask->taskdate->format('m-d-Y') : null,
                    'project' => $dailytask->project,
                    'plandate' => $dailytask->plandate ? $dailytask->plandate->format('m-d-Y') : null,
                    'planenddate' => $dailytask->planenddate ? $dailytask->planenddate->format('m-d-Y') : null,
                    'startdate' => $dailytask->startdate ? $dailytask->startdate->format('m-d-Y h:i A') : null,
                    'enddate' => $dailytask->enddate ? $dailytask->enddate->format('m-d-Y h:i A') : null,
                    'status' => $dailytask->status,
                    'attachment' => $dailytask->attachment,
                    'PWS' => $dailytask->PWS,
                    'remarks' => $dailytask->remarks,
                    'immediate_hid' => $dailytask->immediate_hid,
                    'created_at' => $dailytask->created_at ? $dailytask->created_at->format('m/d/Y h:i A') : null,
                ];
            });

        return response()->json($data);
    }
}
