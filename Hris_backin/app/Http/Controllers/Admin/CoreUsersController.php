<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Models\Core\CoreBranch;
use App\Models\Core\CoreApp;
use App\Models\Core\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Core\Role;

class CoreUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::with(['role'])
            ->orderBy('name')
            ->get()
            ->append(['creator']);

        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'app_id']);

        // Get applications with their menus for the menu rights section
        $applications = CoreApp::where('status', 'A')
            ->with(['menus' => function($query) {
                $query->where('is_active', 1)->orderBy('name');
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Users/index', [
            'masterlist' => $data,
            'roles' => $roles,
            'applications' => $applications
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = CoreBranch::where('status', 'A')->get();

        return Inertia::render('Admin/Users/Create', [
            'branches' => $branches
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:core_users,email',
            'user_type' => 'required|string|max:50',
            'member_role' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'nullable',
            'app_ids' => 'sometimes|array',
            'app_ids.*' => 'integer|exists:core_app,id',
            'menu_permissions_by_app' => 'sometimes|array'
        ]);

        $existingRecord = User::where([
            'email' => $request->email
        ])->orWhere('name', $request->name)->first();

        if($existingRecord){
            return back()->withErrors(['message' => 'This email or username is already in the database.']);
        }

        $statusVal = $request->input('status');
        $status = in_array($statusVal, ['A', 'I'], true) ? $statusVal : ($request->boolean('status') ? 'A' : 'I');

        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'member_role' => $request->member_role,
            'password' => $request->password,
            'status' => $status,
            'email_verified_at' => now(),
            'sitehead_user_id' => $request->sitehead_user_id,
            'branch_id' => $request->branch_id,
        ]);

        if ($request->member_role) {
            $this->syncRolePermissions($user, $request->member_role);
        }

        if ($request->filled('app_ids') && is_array($request->app_ids)) {
            DB::table('core_appuser')->where('user_id', $user->id)->delete();
            $assignments = [];
            foreach ($request->app_ids as $appId) {
                $assignments[] = [
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'app_id' => $appId,
                    'is_active' => true,
                    'created_by' => auth()->id() ?? 0,
                    'updated_by' => auth()->id() ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            if (!empty($assignments)) {
                DB::table('core_appuser')->insert($assignments);
            }
        }

        // Auto-assign app_id 4 for FRLS requests
        if ($request->is('frls/*')) {
            $exists = DB::table('core_appuser')
                ->where('user_id', $user->id)
                ->where('app_id', 4)
                ->exists();

            if (!$exists) {
                DB::table('core_appuser')->insert([
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'app_id' => 4,
                    'is_active' => true,
                    'created_by' => auth()->id() ?? 0,
                    'updated_by' => auth()->id() ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $byApp = $request->input('menu_permissions_by_app');
        if (is_array($byApp)) {
            foreach ($byApp as $appId => $menuPermissions) {
                DB::table('core_usermenus')
                    ->where('user_id', $user->id)
                    ->whereIn('menu_id', function($query) use ($appId) {
                        $query->select('id')
                              ->from('core_menu')
                              ->where('app_id', $appId);
                    })
                    ->delete();

                $rows = [];
                if (is_array($menuPermissions)) {
                    foreach ($menuPermissions as $mp) {
                        if (!isset($mp['menu_id'], $mp['permission'])) {
                            continue;
                        }
                        if ($mp['permission'] === 'none') {
                            continue;
                        }
                        $rows[] = [
                            'user_id' => $user->id,
                            'menu_id' => $mp['menu_id'],
                            'is_manage' => $mp['permission'] === 'manage',
                            'is_active' => true,
                            'created_by' => auth()->id() ?? 0,
                            'updated_by' => auth()->id() ?? 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
                if (!empty($rows)) {
                    DB::table('core_usermenus')->insert($rows);
                }
            }
        }

        if ($request->is('frls/*')) {
            try {
                Mail::to($user->email)->send(new UserCreated($user, $request->password));
            } catch (\Exception $e) {
                // Ignore email errors
            }

            return redirect()->back()->with('success', 'User created successfully!');
        }

        try {
            Mail::to($user->email)->send(new UserCreated($user, $request->password));
        } catch (\Exception $e) {
            // Ignore email errors
        }

        return redirect()->route('admin.user.index')->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = User::with(['branch', 'role'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with(['branch'])->findOrFail($id);
        $branches = CoreBranch::where('status', 'A')->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'branches' => $branches
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        \Log::info('CoreUsersController@update request data:', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:core_users,email,' . $id . ',id',
            'password' => 'nullable|string|min:8|confirmed',
            'member_role' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'status' => 'nullable|in:A,I',
            'is_active' => 'boolean',
            'app_ids' => 'sometimes|array',
            'app_ids.*' => 'integer|exists:core_app,id',
            'menu_permissions_by_app' => 'sometimes|array'
        ]);

        $user = User::findOrFail($id);

        // Check for duplicates excluding current record
        $existingRecord = User::where(function($query) use ($request) {
            $query->where('email', $request->email)
                  ->orWhere('name', $request->name);
        })->where('id', '!=', $id)->first();

        if($existingRecord){
            return back()->withErrors(['message' => 'This email or username is already in the database.']);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'member_role' => $request->member_role,
            'user_type' => $request->user_type,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'status' => $request->status,
            'sitehead_user_id' => $request->sitehead_user_id,
            'branch_id' => $request->branch_id,
        ];

        // Only update password if provided
        if ($request->password) {
            $userData['password'] = $request->password;
        }

        $user->update($userData);

        if ($request->member_role) {
            $this->syncRolePermissions($user, $request->member_role);
        }

        // Handle application assignments
        if ($request->has('app_ids')) {
            // Delete existing assignments
            DB::table('core_appuser')->where('user_id', $user->id)->delete();

            if (is_array($request->app_ids) && !empty($request->app_ids)) {
                $assignments = [];
                foreach ($request->app_ids as $appId) {
                    $assignments[] = [
                        'uuid' => Str::uuid(),
                        'user_id' => $user->id,
                        'app_id' => $appId,
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 0,
                        'updated_by' => auth()->id() ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                DB::table('core_appuser')->insert($assignments);
            }
        }

        // Handle menu permissions
        if ($request->has('menu_permissions_by_app') && is_array($request->menu_permissions_by_app)) {
            $byApp = $request->menu_permissions_by_app;

            // Only delete and update permissions for applications that are in the request
            foreach ($byApp as $appId => $menuPermissions) {
                if (!is_array($menuPermissions)) {
                    continue;
                }

                // Delete existing permissions for this specific app only
                DB::table('core_usermenus')
                    ->where('user_id', $user->id)
                    ->whereIn('menu_id', function($query) use ($appId) {
                        $query->select('id')
                              ->from('core_menu')
                              ->where('app_id', $appId);
                    })
                    ->delete();

                $rows = [];
                foreach ($menuPermissions as $mp) {
                    if (!isset($mp['menu_id'], $mp['permission'])) {
                        continue;
                    }

                    // Skip 'none' permission
                    if ($mp['permission'] === 'none') {
                        continue;
                    }

                    $rows[] = [
                        'user_id' => $user->id,
                        'menu_id' => $mp['menu_id'],
                        'is_manage' => $mp['permission'] === 'manage',
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 0,
                        'updated_by' => auth()->id() ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (!empty($rows)) {
                    DB::table('core_usermenus')->insert($rows);
                }
            }

            // Important: Do NOT delete permissions for apps that were not in the request
            // This preserves permissions for other applications
        }

        if ($request->is('frls/*')) {
            return redirect()->back()->with('success', 'User updated successfully!');
        }

        return redirect()->route('admin.user.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion of user with ID 1 (super admin)
        if ($user->id == 1) {
            return response()->json(['message' => 'Cannot delete super admin user.'], 422);
        }

        $user->delete();

        if ($request->is('frls/*')) {
            return redirect()->back();
        }

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * Get active users for dropdown
     */
    public function getActiveUsers()
    {
        $users = User::where('status', 'A')
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(function ($user) {
                $user->full_name = trim($user->first_name . ' ' . $user->last_name);
                return $user;
            });

        return response()->json($users);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Prevent status change of user with ID 1 (super admin)
        if ($user->id == 1) {
            return response()->json(['message' => 'Cannot change super admin status.'], 422);
        }

        $user->status = $user->status === 'A' ? 'I' : 'A';
        $user->save();

        return response()->json(['message' => 'User status updated successfully.', 'status' => $user->status]);
    }

    /**
     * Get list of core applications
     */
    public function getCoreApps()
    {
        $apps = CoreApp::where('status', 'A')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'description']);

        return response()->json($apps);
    }

    /**
     * Get menus by application ID
     */
    public function getMenusByAppId($appId)
    {
        $menus = Menu::where('app_id', $appId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'route', 'icon', 'parent_id', 'sort_order', 'is_active']);

        return response()->json($menus);
    }

    /**
     * Get user menu permissions for a specific application
     */
    public function getUserMenuPermissions($userId, $appId)
    {
        $permissions = DB::table('core_usermenus')
            ->join('core_menu', 'core_usermenus.menu_id', '=', 'core_menu.id')
            ->where('core_usermenus.user_id', $userId)
            ->where('core_menu.app_id', $appId)
            ->where('core_menu.is_active', 1)
            ->select(
                'core_menu.*',
                DB::raw("CASE WHEN core_usermenus.is_manage = 1 THEN 'manage' ELSE 'view' END as permission")
            )
            ->get();

        if ($permissions->isEmpty()) {
            $permissions = DB::table('core_rolemenu')
                ->join('core_menu', 'core_rolemenu.menu_id', '=', 'core_menu.id')
                ->join('core_users', 'core_rolemenu.role_id', '=', 'core_users.role_id')
                ->where('core_users.id', $userId)
                ->where('core_menu.app_id', $appId)
                ->where('core_menu.is_active', 1)
                ->select('core_menu.*', 'core_rolemenu.permission')
                ->get();
        }

        return response()->json($permissions);
    }

    /**
     * Get user applications (apps assigned to user)
     */
    public function getUserApplications($userId)
    {
        $userApps = DB::table('core_appuser')
            ->join('core_app', 'core_appuser.app_id', '=', 'core_app.id')
            ->where('core_appuser.user_id', $userId)
            ->where('core_app.status', 'A')
            ->select('core_app.*')
            ->get();

        return response()->json($userApps);
    }

    /**
     * Get role menu permissions for a specific role and application
     */
    public function getRoleMenuPermissions($roleId, $appId)
    {
        $permissions = DB::table('core_rolemenu')
            ->join('core_menu', 'core_rolemenu.menu_id', '=', 'core_menu.id')
            ->where('core_rolemenu.role_id', $roleId)
            ->where('core_menu.app_id', $appId)
            ->where('core_menu.is_active', 1)
            ->select('core_menu.*', 'core_rolemenu.permission')
            ->get();

        return response()->json($permissions);
    }

    /**
     * Save or update user application assignments
     */
    public function saveUserApplications(Request $request, $userId)
    {
        $request->validate([
            'app_ids' => 'required|array',
            'app_ids.*' => 'exists:core_app,id'
        ]);

        // Delete existing assignments
        DB::table('core_appuser')->where('user_id', $userId)->delete();

        // Insert new assignments
        $assignments = [];
        foreach ($request->app_ids as $appId) {
            $assignments[] = [
                'user_id' => $userId,
                'app_id' => $appId,
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($assignments)) {
            DB::table('core_appuser')->insert($assignments);
        }

        return response()->json(['message' => 'User applications updated successfully']);
    }

    /**
     * Save or update role menu permissions
     */
    public function saveRoleMenuPermissions(Request $request, $roleId, $appId)
    {
        $request->validate([
            'menu_permissions' => 'required|array',
            'menu_permissions.*.menu_id' => 'required|exists:core_menu,id',
            'menu_permissions.*.permission' => 'required|in:view,manage'
        ]);

        // Delete existing role menu permissions for this app
        DB::table('core_rolemenu')
            ->whereIn('menu_id', function($query) use ($appId) {
                $query->select('id')
                      ->from('core_menu')
                      ->where('app_id', $appId);
            })
            ->where('role_id', $roleId)
            ->delete();

        // Insert new permissions
        $permissions = [];
        foreach ($request->menu_permissions as $menuPermission) {
            $permissions[] = [
                'role_id' => $roleId,
                'menu_id' => $menuPermission['menu_id'],
                'permission' => $menuPermission['permission'],
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($permissions)) {
            DB::table('core_rolemenu')->insert($permissions);
        }

        return response()->json(['message' => 'Role menu permissions updated successfully']);
    }

    /**
     * Save or update user menu permissions
     */
    public function saveUserMenuPermissions(Request $request, $userId, $appId)
    {
        $request->validate([
            'menu_permissions' => 'required|array',
            'menu_permissions.*.menu_id' => 'required|exists:core_menu,id',
            'menu_permissions.*.permission' => 'required|in:view,manage,none'
        ]);

        // Only delete permissions for this specific app
        DB::table('core_usermenus')
            ->where('user_id', $userId)
            ->whereIn('menu_id', function($query) use ($appId) {
                $query->select('id')
                      ->from('core_menu')
                      ->where('app_id', $appId);
            })
            ->delete();

        $permissions = [];
        foreach ($request->menu_permissions as $menuPermission) {
            if ($menuPermission['permission'] === 'none') {
                continue;
            }
            $permissions[] = [
                'user_id' => $userId,
                'menu_id' => $menuPermission['menu_id'],
                'is_manage' => $menuPermission['permission'] === 'manage',
                'is_active' => true,
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($permissions)) {
            DB::table('core_usermenus')->insert($permissions);
        }

        return response()->json(['message' => 'User menu permissions updated successfully']);
    }

    /**
     * Get list of roles for API
     */
    public function getRoleList(Request $request)
    {
        $query = Role::where('is_active', true);

        if ($request->is('frls/*')) {
            $query->where('app_id', 4);
        }

        $roles = $query->orderBy('name')->get(['id', 'name']);

        return response()->json(['data' => $roles]);
    }

    /**
     * Get list of users for API
     */
    public function getList()
    {
        $data = User::with(['role'])
            ->orderBy('name')
            ->get()
            ->append(['creator']);

        return response()->json(['data' => $data]);
    }

    private function syncRolePermissions($user, $roleId)
    {
        if (!$roleId) return;

        $roleMenuIds = DB::table('core_rolemenu')
            ->where('role_id', $roleId)
            ->pluck('menu_id')
            ->toArray();

        // Remove menus not in the role
        DB::table('core_usermenus')
            ->where('user_id', $user->id)
            ->whereNotIn('menu_id', $roleMenuIds)
            ->delete();

        foreach ($roleMenuIds as $menuId) {
            $existing = DB::table('core_usermenus')
                ->where('user_id', $user->id)
                ->where('menu_id', $menuId)
                ->first();

            if ($existing) {
                DB::table('core_usermenus')
                    ->where('user_id', $user->id)
                    ->where('menu_id', $menuId)
                    ->update([
                        'is_manage' => 1,
                        'is_active' => 1,
                        'updated_by' => auth()->id() ?? 0,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('core_usermenus')->insert([
                    'user_id' => $user->id,
                    'menu_id' => $menuId,
                    'is_manage' => 1,
                    'is_active' => 1,
                    'created_by' => auth()->id() ?? 0,
                    'updated_by' => auth()->id() ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
