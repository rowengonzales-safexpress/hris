<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('WeeklyTask/Dashboard');
    }

    public function getChartData()
    {
        // Get the authenticated user's ID
        $userId = auth()->user()->id;
        $currentYear = date('Y');

        // Fetch data from task_dailytask (was tbl_dailytask)
        $tasks = DB::table('task_dailytask')
            ->selectRaw('DATE_FORMAT(taskdate, "%Y-%m") as month, COUNT(*) as total_tasks')
            ->where('user_id', $userId)
            ->where('status_task', '=', 1)
            ->whereYear('taskdate', '=', $currentYear) // Filter for the current year
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch data from task_list (was tbl_tasklist)
        // task_list joins with task_dailytask to check user_id and year
        $allTodos = DB::table('task_list')
            ->selectRaw('DATE_FORMAT(task_list.created_at, "%Y-%m") as month, COUNT(*) as total_todos')
            ->join('task_dailytask', 'task_dailytask.id', '=', 'task_list.dailytask_id')
            ->where('task_dailytask.user_id', $userId)
            ->whereYear('task_dailytask.taskdate', '=', $currentYear) // Filter for the current year
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch completed tasks from task_list where status = 1
        $completedTasks = DB::table('task_list')
            ->selectRaw('DATE_FORMAT(task_list.created_at, "%Y-%m") as month, COUNT(*) as completed_todos')
            ->join('task_dailytask', 'task_dailytask.id', '=', 'task_list.dailytask_id')
            ->where('task_list.status', 1)
            ->where('task_dailytask.user_id', $userId)
            ->whereYear('task_dailytask.taskdate', '=', $currentYear) // Filter for the current year
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch incomplete tasks from task_list where status = 0
        $incompleteTasks = DB::table('task_list')
            ->selectRaw('DATE_FORMAT(task_list.created_at, "%Y-%m") as month, COUNT(*) as incomplete_todos')
            ->join('task_dailytask', 'task_dailytask.id', '=', 'task_list.dailytask_id')
            ->where('task_list.status', 0)
            ->where('task_dailytask.user_id', $userId)
            ->whereYear('task_dailytask.taskdate', '=', $currentYear) // Filter for the current year
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare month names
        $monthNames = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];

        // Initialize chart data array
        $chartData = [
            'labels' => array_values($monthNames), // Use month names as labels
            'datasets' => [
                [
                    'label' => 'Total Todos',
                    'data' => array_fill(0, 12, 0), // Initialize data array with zeros for each month
                    'backgroundColor' => '#1976D2',
                ],
                [
                    'label' => 'Completed Todos',
                    'data' => array_fill(0, 12, 0), // Initialize data array with zeros for each month
                    'backgroundColor' => '#00B489',
                ],
                [
                    'label' => 'Incomplete Todos',
                    'data' => array_fill(0, 12, 0), // Initialize data array with zeros for each month
                    'backgroundColor' => '#CD201F',
                ],
            ],
        ];

        // Populate chart data arrays with actual data
        foreach ($tasks as $task) {
            $monthIndex = intval(substr($task->month, 5)) - 1; // Get month index (0-based)
            $chartData['datasets'][0]['data'][$monthIndex] = $task->total_tasks;
        }

        foreach ($completedTasks as $completedTask) {
            $monthIndex = intval(substr($completedTask->month, 5)) - 1; // Get month index (0-based)
            $chartData['datasets'][1]['data'][$monthIndex] = $completedTask->completed_todos;
        }

        foreach ($incompleteTasks as $incompleteTask) {
            $monthIndex = intval(substr($incompleteTask->month, 5)) - 1; // Get month index (0-based)
            $chartData['datasets'][2]['data'][$monthIndex] = $incompleteTask->incomplete_todos;
        }

        // Populate total todos data
        foreach ($allTodos as $todo) {
            $monthIndex = intval(substr($todo->month, 5)) - 1; // Get month index (0-based)
            $chartData['datasets'][0]['data'][$monthIndex] = $todo->total_todos;
        }

        return response()->json($chartData);
    }
}
