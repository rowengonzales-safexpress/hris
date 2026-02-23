<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoreRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Role::orderBy('name')
            ->get()
            ->append(['creator']);

        return Inertia::render('Admin/Role/index', [
            'masterlist' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/CoreRole/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'status' => 'boolean',
        ]);

        $existingRecord = Role::where([
            'name' => $request->name
        ])->first();

        if($existingRecord && !$request->id){
            return response()->json(['message' => 'This role is already in the database.'], 422);
        }

        Role::updateOrCreate([
                'id' => $request->id
            ],[
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status
            ]);

         return redirect()->route('admin.application.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Role::with(['roleMenus.menu'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::with(['roleMenus'])->findOrFail($id);

        return Inertia::render('Admin/CoreRole/Edit', [
            'role' => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ]);

        $role = Role::findOrFail($id);

        // Check for duplicates excluding current record
        $existingRecord = Role::where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();

        if($existingRecord){
            return back()->withErrors(['message' => 'This role is already in the database.']);
        }

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.core-role.index')->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Check if role has menu assignments
        if ($role->roleMenus()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with associated menu permissions.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    /**
     * Get active roles for dropdown
     */
    public function getActiveRoles()
    {
        $roles = Role::where('status', 'A')
            ->select('id', 'name', 'description')
            ->get();

        return response()->json($roles);
    }

    /**
     * Toggle role status
     */
    public function toggleStatus($id)
    {
        $role = Role::findOrFail($id);

        $role->status = $role->status === 'A' ? 'I' : 'A';
        $role->save();

        return response()->json(['message' => 'Role status updated successfully.', 'status' => $role->status]);
    }

    /**
     * Get role permissions
     */
    public function getPermissions($id)
    {
        $role = Role::with(['roleMenus.menu.app'])->findOrFail($id);

        $permissions = $role->roleMenus->map(function ($roleMenu) {
            return [
                'menu_id' => $roleMenu->menu_id,
                'menu_name' => $roleMenu->menu->name,
                'app_name' => $roleMenu->menu->app->name,
                'permission' => $roleMenu->permission,
                'route' => $roleMenu->menu->route,
            ];
        });

        return response()->json($permissions);
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:core_menu,id',
            'permissions' => 'required|array',
            'permissions.*' => 'in:manage,view',
        ]);

        $role = Role::findOrFail($id);

        // Remove existing permissions
        $role->roleMenus()->delete();

        // Add new permissions
        foreach ($request->menu_ids as $index => $menuId) {
            $role->roleMenus()->create([
                'menu_id' => $menuId,
                'permission' => $request->permissions[$index] ?? 'view',
            ]);
        }

        return response()->json(['message' => 'Permissions assigned successfully.']);
    }
}
