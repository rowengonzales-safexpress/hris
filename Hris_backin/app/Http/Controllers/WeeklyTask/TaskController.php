<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTaskSchedule\Task;
use App\Models\WeeklyTaskSchedule\ListTask;
use App\Models\Core\CoreBranch;
use App\Enums\TaskType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function getTaskTypes()
    {
        try {
            $types = collect(TaskType::cases())->map(function ($case) {
                return [
                    'id' => $case->value,
                    'name' => $case->listtask(),
                ];
            });
        } catch (\Throwable $e) {
            $types = collect([
                ['id' => 1, 'name' => 'LEAVE'],
                ['id' => 2, 'name' => 'ON BUSINESS TRAVEL'],
                ['id' => 3, 'name' => 'SITE VISIT'],
                ['id' => 4, 'name' => 'COA'],
                ['id' => 5, 'name' => 'WORK FROM HOME'],
                ['id' => 6, 'name' => 'HOLIDAY'],
            ]);
        }
        return response()->json(['tasktypes' => $types]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Task::query()
            ->join('core_branch', 'core_branch.id', '=', 'task_dailytask.branch_id')
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('core_branch.branch_name', 'like', "%{$searchQuery}%");
            })
            ->when(request('type'), function ($query, $type) {
                $query->where('tasktype_id', $type);
            })
            ->when(request('start_date') && request('end_date'), function ($query) {
                $fromDate = request('start_date');
                $toDate = Carbon::parse(request('end_date'))->endOfDay();
                $query->whereBetween('task_dailytask.taskdate', [$fromDate, $toDate]);
            })
            ->where(function ($query) {
                $query->whereNull('task_dailytask.status_task')
                    ->orWhere('task_dailytask.status_task', '!=', 1);
            })
            ->where('task_dailytask.user_id', auth()->id())
            ->orderBy('task_dailytask.taskdate', 'asc')
            ->select('task_dailytask.*', 'core_branch.branch_name as site_name');

        $tasks = $query->paginate(10)->through(function ($dailytask) {
            return [
                'dailytask_id' => $dailytask->id,
                'site_name' => $dailytask->site_name,
                'user_id' => $dailytask->user_id,
                'taskdate' => $dailytask->taskdate, // already cast in model?
                'project' => $dailytask->project,
                'plandate' => $dailytask->plandate ? $dailytask->plandate->format('m/d/Y h:i A') : null,
                'planenddate' => $dailytask->planenddate ? $dailytask->planenddate->format('m/d/Y h:i A') : null,
                'startdate' => $dailytask->startdate,
                'enddate' => $dailytask->enddate,
                'tasktype' => $dailytask->tasktype_id,
                'status' => $dailytask->status,
                'attachment' => $dailytask->attachment,
                'PWS' => $dailytask->PWS,
                'remarks' => $dailytask->remarks,
                'immediate_hid' => $dailytask->immediate_hid,
                'status_task' => $dailytask->status_task,
                'created_at' => $dailytask->created_at->format('m/d/Y h:i A'),
            ];
        });

        return Inertia::render('WeeklyTask/MyPrio/MyPrio', [
            'tasks' => $tasks,
            'filters' => request()->all(['query', 'type', 'start_date', 'end_date']),
            'sites' => CoreBranch::where('status', 'A')->orderBy('branch_name')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site' => 'required',
            'tasktype' => 'required',
            'plandate' => 'required|date',
            'planenddate' => 'required|date|after_or_equal:plandate',
            // Add other validations as needed
        ]);

        // Parse the date range
        $startDate = Carbon::parse($request->plandate);
        $endDate = Carbon::parse($request->planenddate);

        DB::transaction(function () use ($startDate, $endDate, $request) {
            // Generate tasks per day from the NEXT day after plandate up to endDate (inclusive)
            $currentDate = $startDate->copy()->addDay();
            while ($currentDate <= $endDate) {
                $planStartDate = $currentDate->copy()->setTimeFrom($startDate);
                $planEndDate = $currentDate->copy()->setTimeFrom($endDate);

                Task::create([
                    'branch_id' => $request->site,
                    'user_id' => auth()->id(), // Use auth user id
                    'taskdate' => $currentDate,
                    'project' => $request->project,
                    'plandate' => $planStartDate,
                    'planenddate' => $planEndDate,
                    'startdate' => $request->startdate,
                    'enddate' => $request->enddate,
                    'tasktype_id' => $request->tasktype,
                    'status' => $request->status,
                    'attachment' => $request->attachment,
                    'PWS' => $request->PWS,
                    'remarks' => $request->remarks,
                    'immediatehead_id' => 1, // Default from old controller
                    'status_task' => $request->status_task,
                ]);

                // Move to the next day
                $currentDate->addDay();
            }
        });

        return redirect()->route('weekly-task-schedule.index')->with('success', 'Tasks created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $data = [];
        if ($request->has('site')) {
            $data['branch_id'] = $request->site;
        }
        if ($request->has('tasktype')) {
            $data['tasktype_id'] = $request->tasktype;
        }
        if ($request->has('project')) {
            $data['project'] = $request->project;
        }
        if ($request->has('remarks')) {
            $data['remarks'] = $request->remarks;
        }
        if (!empty($data)) {
            $task->update($data);
        }
        return redirect()->back()->with('success', 'Task Updated successfully.');
    }


    public function onhandler(Request $request, $dailytask_id)
    {
        //dd($request);

        $task = Task::where('id', $dailytask_id)->first();


        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        if (empty($request->input('startdate'))) {
            $task->startdate = now(); // Set the start date to the current date and time
            $task->status = "On Going";
        } else {
            // Start date is not empty, update end date and status
            $task->enddate = now(); // You may want to replace 'now()' with the appropriate logic to set the end date
            $task->status = $request->status; // Set the status accordingly
            $task->remarks = $request->remarks; // Set the status accordingly
            $task->status_task = 1;
        }

        $task->save();

        return redirect()->back()->with('success', 'Task updated successfully!');
    }
    public function onTashHoliday(Request $request, $dailytask_id)
    {

        $task = Task::where('id', $dailytask_id)->first();


        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }


            $task->startdate = now();
            $task->enddate = now(); // You may want to replace 'now()' with the appropriate logic to set the end date
            $task->status = 'HOLIDAY'; // Set the status accordingly
            $task->remarks = 'HIT'; // Set the status accordingly
            $task->status_task = 1;


        $task->save();

        return redirect()->back()->with('success', 'Holiday Task completed successfully!');
    }
     public function getSite()
    {
        $userId = auth()->user()->id;
        $sites = CoreBranch::select('core_branch.id','core_branch.branch_name as name')
            ->join('user_sites', 'core_branch.id', '=', 'user_sites.site_id')
            ->where('core_branch.status', 1)
            ->where('user_sites.user_id', '=', $userId)
            ->orderBy('core_branch.branch_name', 'ASC')
            ->get();

        return response()->json([ 'sites' => $sites]);
    }
    public function getTask($id)
    {
        $task = ListTask::where('dailytask_id', $id)
            ->get();

        return response()->json($task);
    }
    public function addTask(Request $request)
    {

        ListTask::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'dailytask_id' => $request->dailytask_id,
                'task_name' => $request->task_name,
                'status' => $request->status,

            ]
        );

        return redirect()->back()->with('success', 'Task added successfully.')->with('open_task_id', $request->dailytask_id);
    }

      public function destroy($id)
    {
        // Find the Task by $id
        $task = Task::find($id);
        // Check if the Task exists
        if (!$task) {
             return redirect()->back()->with('error', 'Task not found');
        }
        // Find and delete the associated ListTask
        $listTask = ListTask::where('dailytask_id', $id)->first();
        if ($listTask) {
            $listTask->delete();
        }
        // Delete the Task
        $task->delete();
        return redirect()->back()->with('success', 'Task and associated ListTask successfully deleted');
    }
    public function deleteTask($id)
    {
        $data = ListTask::find($id);
        $dailytask_id = $data->dailytask_id;
        $data->delete();
        return redirect()->back()->with('success', 'Task successfull Deleted')->with('open_task_id', $dailytask_id);
    }
    public function FilterTaskdate(Request $request)
    {
        return redirect()->route('weekly-task-schedule.index', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
    }
}
