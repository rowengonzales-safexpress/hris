<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\RoleMenu;
use App\Models\Core\Role;
use App\Models\Core\Menu;
use App\Models\Core\CoreApp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoreRoleMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RoleMenu::with(['role', 'menu.app'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->append(['creator']);

        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $menus = Menu::with(['app'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'app_id']);

        $apps = CoreApp::where('status', 'A')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/RoleMenu/index', [
            'masterlist' => $data,
            'roles' => $roles,
            'menus' => $menus,
            'apps' => $apps
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::active()->orderBy('name')->get();
        $menus = Menu::with(['app'])->where('is_active', 1)->get();

        return Inertia::render('Admin/CoreRoleMenu/Create', [
            'roles' => $roles,
            'menus' => $menus
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:core_role,id',
            'app_id' => 'required|exists:core_app,id',
            'menu_id' => 'required|exists:core_menu,id',
            'permission' => 'required|in:manage,view',
        ]);

        $existingRecord = RoleMenu::where([
            'role_id' => $request->role_id,
            'app_id' => $request->app_id,
            'menu_id' => $request->menu_id
        ])->first();

        if($existingRecord && !$request->id){
            return back()->withErrors(['message' => 'This role-menu permission is already in the database.']);
        }

        RoleMenu::create([
            'role_id' => $request->role_id,
            'app_id' => $request->app_id,
            'menu_id' => $request->menu_id,
            'permission' => $request->permission,
        ]);

        return redirect()->route('admin.menu-role.index')->with('success', 'Role menu assignment created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = RoleMenu::with(['role', 'menu.app'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $roleMenu = RoleMenu::with(['role', 'menu'])->findOrFail($id);
        $roles = Role::active()->orderBy('name')->get();
        $menus = Menu::with(['app'])->where('is_active', 1)->get();

        return Inertia::render('Admin/CoreRoleMenu/Edit', [
            'roleMenu' => $roleMenu,
            'roles' => $roles,
            'menus' => $menus
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:core_role,id',
            'app_id' => 'required|exists:core_app,id',
            'menu_id' => 'required|exists:core_menu,id',
            'permission' => 'required|in:manage,view',
        ]);

        $roleMenu = RoleMenu::findOrFail($id);

        // Check for duplicates excluding current record
        $existingRecord = RoleMenu::where([
            'role_id' => $request->role_id,
            'app_id' => $request->app_id,
            'menu_id' => $request->menu_id
        ])->where('id', '!=', $id)->first();

        if($existingRecord){
            return back()->withErrors(['message' => 'This role-menu permission is already in the database.']);
        }

        $roleMenu->update([
            'role_id' => $request->role_id,
            'app_id' => $request->app_id,
            'menu_id' => $request->menu_id,
            'permission' => $request->permission,
        ]);

        return redirect()->route('admin.menu-role.index')->with('success', 'Role menu assignment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $roleMenu = RoleMenu::findOrFail($id);
        $roleMenu->delete();

        return redirect()->route('admin.menu-role.index');

    }

    /**
     * Bulk remove the specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:core_rolemenu,id',
        ]);

        RoleMenu::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.menu-role.index')->with('success', 'Selected role menu assignments deleted successfully!');
    }

    /**
     * Get permissions by role
     */
    public function getByRole($roleId)
    {
        $permissions = RoleMenu::with(['menu.app'])
            ->where('role_id', $roleId)
            ->get();

        return response()->json($permissions);
    }

    /**
     * Get permissions by menu
     */
    public function getByMenu($menuId)
    {
        $permissions = RoleMenu::with(['role'])
            ->where('menu_id', $menuId)
            ->get();

        return response()->json($permissions);
    }

    /**
     * Bulk assign permissions to role
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:core_role,id',
            'permissions' => 'required|array',
            'permissions.*.menu_id' => 'required|exists:core_menu,id',
            'permissions.*.permission' => 'required|in:manage,view',
        ]);

        $roleId = $request->role_id;

        // Remove existing permissions for this role
        RoleMenu::where('role_id', $roleId)->delete();

        // Add new permissions
        foreach ($request->permissions as $permission) {
            RoleMenu::create([
                'role_id' => $roleId,
                'menu_id' => $permission['menu_id'],
                'permission' => $permission['permission'],
            ]);
        }

        return response()->json(['message' => 'Permissions assigned successfully.']);
    }

    /**
     * Get role permissions tree
     */
    public function getPermissionsTree($roleId)
    {
        $role = Role::findOrFail($roleId);

        $permissions = RoleMenu::with(['menu.app', 'menu.parent', 'menu.children'])
            ->where('role_id', $roleId)
            ->get();

        // Group by app
        $tree = $permissions->groupBy('menu.app.name')->map(function ($appPermissions) {
            return $appPermissions->map(function ($permission) {
                return [
                    'menu_id' => $permission->menu_id,
                    'menu_name' => $permission->menu->name,
                    'parent_id' => $permission->menu->parent_id,
                    'permission' => $permission->permission,
                    'route' => $permission->menu->route,
                    'icon' => $permission->menu->icon,
                    'sort_order' => $permission->menu->sort_order,
                ];
            });
        });

        return response()->json($tree);
    }

    /**
     * Check if role has permission for menu
     */
    public function checkPermission($roleId, $menuId)
    {
        $permission = RoleMenu::where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->first();

        return response()->json([
            'has_permission' => $permission ? true : false,
            'permission_type' => $permission ? $permission->permission : null
        ]);
    }

    /**
     * Copy permissions from one role to another
     */
    public function copyPermissions(Request $request)
    {
        $request->validate([
            'from_role_id' => 'required|exists:core_role,id',
            'to_role_id' => 'required|exists:core_role,id',
        ]);

        $fromRoleId = $request->from_role_id;
        $toRoleId = $request->to_role_id;

        // Get permissions from source role
        $sourcePermissions = RoleMenu::where('role_id', $fromRoleId)->get();

        // Remove existing permissions for target role
        RoleMenu::where('role_id', $toRoleId)->delete();

        // Copy permissions to target role
        foreach ($sourcePermissions as $permission) {
            RoleMenu::create([
                'role_id' => $toRoleId,
                'menu_id' => $permission->menu_id,
                'permission' => $permission->permission,
            ]);
        }

        return response()->json(['message' => 'Permissions copied successfully.']);
    }
}
