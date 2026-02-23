<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\CoreBranch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoreBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CoreBranch::with(['users'])->get()->append(['creator']);
        return Inertia::render('Admin/CoreBranch/Index', [
            'masterlist' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/CoreBranch/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_code' => 'required|string|max:20',
            'branch_name' => 'required|string|max:150|unique:core_branch,branch_name',
            'fulladdress' => 'nullable|string',
            'status' => 'required|in:A,I',
        ]);

        CoreBranch::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'branch_code' => $request->branch_code,
                'branch_name' => $request->branch_name,
                'fulladdress' => $request->fulladdress,
                'status' => $request->status,
            ]
        );

        if ($request->is('frls/*')) {
            return redirect()->back();
        }

        return redirect()->route('admin.core-branch.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = CoreBranch::with(['users'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $branch = CoreBranch::findOrFail($id);

        return Inertia::render('Admin/CoreBranch/Edit', [
            'branch' => $branch
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_code' => 'required|string|max:20',
            'branch_name' => 'required|string|max:150|unique:core_branch,branch_name,' . $id,
            'fulladdress' => 'nullable|string',
            'status' => 'required|in:A,I',
        ]);

        $branch = CoreBranch::findOrFail($id);
        $branch->update($request->all());

        if ($request->is('frls/*')) {
            return redirect()->back();
        }

        return redirect()->route('admin.core-branch.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $branch = CoreBranch::findOrFail($id);

        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete branch with associated users.'], 422);
        }

        $branch->delete();

        if ($request->is('frls/*')) {
            return redirect()->back();
        }

        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    /**
     * Get active branches for dropdown
     */
    public function getActiveBranches()
    {
        $branches = CoreBranch::where('status', 'A')
            ->select('id', 'branch_code', 'branch_name')
            ->get();

        return response()->json($branches);
    }

    /**
     * Toggle branch status
     */
    public function toggleStatus($id)
    {
        $branch = CoreBranch::findOrFail($id);

        $branch->status = $branch->status === 'A' ? 'I' : 'A';
        $branch->save();

        return response()->json(['message' => 'Branch status updated successfully.', 'status' => $branch->status]);
    }

    /**
     * Get branch statistics
     */
    public function getStatistics($id)
    {
        $branch = CoreBranch::with(['users'])->findOrFail($id);

        $statistics = [
            'total_users' => $branch->users()->count(),
            'active_users' => $branch->users()->where('status', 'A')->count(),
            'inactive_users' => $branch->users()->where('status', 'I')->count(),
        ];

        return response()->json($statistics);
    }

    /**
     * Get list of branches for API
     */
    public function getList()
    {
        $data = CoreBranch::with(['users'])->get()->append(['creator']);
        return response()->json(['data' => $data]);
    }
}
