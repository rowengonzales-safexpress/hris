<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Core\CoreBranchUser;
use App\Models\Core\CoreBranch;
use App\Models\Core\User;
use Illuminate\Support\Facades\DB;

class CoreBranchUserController extends Controller
{
    public function myBranches(Request $request)
    {
        $userId = Auth::id();
        $branches = DB::table('core_branchuser')
            ->join('core_branch', 'core_branch.id', '=', 'core_branchuser.branch_id')
            ->where('core_branchuser.user_id', $userId)
            ->orderBy('core_branch.branch_name')
            ->select('core_branch.id', 'core_branch.branch_code', 'core_branch.branch_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    public function updateMyBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:core_branch,id',
        ]);

        $userId = Auth::id();

        $allowed = DB::table('core_branchuser')
            ->where('user_id', $userId)
            ->where('branch_id', $request->branch_id)
            ->exists();

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to switch to this branch.',
            ], 403);
        }

        User::where('id', $userId)->update(['branch_id' => $request->branch_id]);

        $branch = CoreBranch::find($request->branch_id);

        return response()->json([
            'success' => true,
            'branch' => [
                'id' => $branch?->id,
                'name' => $branch?->branch_name,
                'code' => $branch?->branch_code,
            ],
        ]);
    }
}
