<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Core\Menu;
use App\Models\Core\CoreApp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Core\CoreAppUserMenu;

class MenuController extends Controller
{
    public function index()
    {
        $data = Menu::with(['app', 'parent'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->append(['creator']);

        $apps = CoreApp::where('status', 'A')->get();
        $parentMenus = Menu::Select('id', 'name')
            ->where('parent_id', 0)
            ->where('is_active', 1)
            ->get();

        return Inertia::render('Admin/Menu/index', [
            'masterlist' => $data,
            'apps' => $apps,
            'parentMenus' => $parentMenus
        ]);
    }

    public function byApp($appId)
    {
        $data = Menu::with(['app', 'parent'])
            ->where('app_id', $appId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->append(['creator']);

        $app = CoreApp::findOrFail($appId);
        $parentMenus = Menu::select('id', 'name')
            ->where('app_id', $appId)
            ->where('parent_id', 0)
            ->where('is_active', 1)
            ->get();

        return Inertia::render('Admin/Application/Menu', [
            'masterlist' => $data,
            'app' => $app,
            'parentMenus' => $parentMenus,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apps = CoreApp::where('status', 'A')->get();
        $parentMenus = Menu::where('parent_id', 0)->where('is_active', 1)->get();

        return Inertia::render('Admin/Menu/manage', [
            'action' => 'create',
            'data' => null,
            'apps' => $apps,
            'parentMenus' => $parentMenus,
            'formdata' => [
                'app_id' => null,
                'name' => '',
                'group' => '',
                'route' => '',
                'parent_id' => null,
                'sort_order' => 0,
                'is_active' => true,
                'icon' => '',
                'description' => '',
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'app_id' => 'required|exists:core_app,id',
            'name' => 'required|string|max:100',
            'route' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:core_menu,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $existingRecord = Menu::where([
            'app_id' => $request->app_id,
            'name' => $request->name,
            'route' => $request->route
        ])->first();

        if($existingRecord && !$request->id){
            return response()->json(['message' => 'This menu already exists in the database.'], 422);
        }

        Menu::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'app_id' => $request->app_id,
                'name' => $request->name,
                'group' => $request->group,
                'route' => $request->route,
                'parent_id' => $request->parent_id ?? 0,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->is_active ?? true,
                'icon' => $request->icon
            ]
        );

        return redirect()->route('admin.menu.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Menu::with(['app', 'parent', 'children'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $menu = Menu::with(['app', 'parent'])->findOrFail($id);
        $apps = \App\Models\Core\CoreApp::where('status', 'A')->get();
        $parentMenus = Menu::where('parent_id', 0)->where('is_active', 1)->where('id', '!=', $id)->get();

        return Inertia::render('Admin/Menu/manage', [
            'action' => 'edit',
            'data' => $menu,
            'apps' => $apps,
            'parentMenus' => $parentMenus,
            'formdata' => [
                'id' => $menu->id,
                'app_id' => $menu->app_id,
                'name' => $menu->name,
                'group' => $menu->group,
                'route' => $menu->route,
                'parent_id' => $menu->parent_id,
                'sort_order' => $menu->sort_order,
                'is_active' => $menu->is_active,
                'icon' => $menu->icon,
                'description' => $menu->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'app_id' => 'required|exists:core_app,id',
            'name' => 'required|string|max:100',
            'group' => 'required|string|max:100',
            'route' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:core_menu,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $menu = Menu::findOrFail($id);

        // Check for duplicates excluding current record
        $existingRecord = Menu::where([
            'app_id' => $request->app_id,
            'name' => $request->name,
            'route' => $request->route
        ])->where('id', '!=', $id)->first();

        if($existingRecord){
            return back()->withErrors(['message' => 'This menu already exists in the database.']);
        }

        $menu->update($request->all());

        return redirect()->route('admin.menu.index')->with('success', 'Menu updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // Check if menu has children
        if ($menu->children()->count() > 0) {
            return response()->json(['message' => 'Cannot delete menu with submenus.'], 422);
        }

        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully.']);
    }

    /**
     * Get user menu permissions for a specific app
     */
   protected function getUserMenusData($userId, $appId = null)
    {

          $menu = Menu::select('core_menu.*')
                    ->join('core_usermenus', 'core_menu.menu_id', '=', 'usermenus.menu_id')
                    ->where('core_menu.is_active', 1)
                    ->where('core_menu.parent_id', 0)
                    ->where('core_menu.app_id', $appId)
                    ->where('core_usermenus.user_id', $userId)
                    ->orderBy('core_menu.sort_order', 'ASC')
                    ->get();

                // For each top-level menu item, fetch and attach its submenus based on user access
                $menu->each(function ($menuItem) use ($userId, $appId) {
                    $menuItem->submenus = Menu::select('core_menu.*')
                        ->join('core_usermenus', 'core_menu.menu_id', '=', 'core_usermenus.menu_id')
                        ->where('core_menu.is_active', 1)
                          ->where('core_menu.app_id', $appId)
                        ->where('core_menu.parent_id', $menuItem->menu_id)
                        ->where('core_usermenus.user_id', $userId)
                        ->orderBy('core_menu.sort_order', 'ASC')
                        ->get();
                });



        return $menu;
    }

    /**
     * Assign menus to user for specific app
     */
    public function assignUserMenus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:core_users,id',
            'app_id' => 'required|exists:core_app,id',
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:core_menu,id',
            'menus.*.name' => 'required|string',
            'menus.*.group' => 'required|string',
            'menus.*.route' => 'required|string',
            'menus.*.isCheck' => 'required|boolean',
            'menus.*.permission' => 'required|in:view,manage',
        ]);

        // Prepare menu data in the new format
        $menuData = [];
        foreach ($request->menus as $menu) {
            $menuData[] = [
                'id' => $menu['id'],
                'name' => $menu['name'],
                'group' => $menu['group'],
                'route' => $menu['route'],
                'isCheck' => $menu['isCheck'],
                'permission' => $menu['permission']
            ];
        }

        CoreAppUserMenu::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'app_id' => $request->app_id,
            ],
            [
                'menus' => json_encode($menuData),
            ]
        );

        return response()->json(['message' => 'User menus assigned successfully.']);
    }

    /**
     * Remove user menu permissions for specific app
     */
    public function removeUserMenus($userId, $appId)
    {
        CoreAppUserMenu::where('user_id', $userId)
            ->where('app_id', $appId)
            ->delete();

        return response()->json(['message' => 'User menu permissions removed successfully.']);
    }

    /**
     * Get menu tree for user permission assignment
     */
    public function getMenuTreeForPermissions($appId = null)
    {
        $query = Menu::where('is_active', 1)->where('parent_id', 0);

        if ($appId) {
            $query->where('app_id', $appId);
        }

        $menus = $query->with(['children' => function($q) use ($appId) {
            $q->where('is_active', 1);
            if ($appId) {
                $q->where('app_id', $appId);
            }
        }])->orderBy('sort_order')->get();

        return response()->json($menus);
    }

    /**
     * Check if user has access to specific menu
     */
    public function checkUserMenuAccess($userId, $menuId, $appId = null)
    {
        if ($userId == 1) {
            return response()->json(['has_access' => true]);
        }

        $query = CoreAppUserMenu::where('user_id', $userId);

        if ($appId) {
            $query->where('app_id', $appId);
        }

        $userMenus = $query->get();

        foreach ($userMenus as $userMenu) {
            $menus = json_decode($userMenu->menus, true);
            if (is_array($menus)) {
                foreach ($menus as $menu) {
                    // Handle both old format (array of IDs) and new format (array of objects)
                    if (is_array($menu) && isset($menu['id']) && $menu['id'] == $menuId) {
                        return response()->json([
                            'has_access' => true,
                            'permission' => $menu['permission'] ?? 'view',
                            'isCheck' => $menu['isCheck'] ?? true
                        ]);
                    } elseif (is_numeric($menu) && $menu == $menuId) {
                        // Backward compatibility for old format
                        return response()->json([
                            'has_access' => true,
                            'permission' => 'view',
                            'isCheck' => true
                        ]);
                    }
                }
            }
        }

        return response()->json(['has_access' => false]);
    }
}
