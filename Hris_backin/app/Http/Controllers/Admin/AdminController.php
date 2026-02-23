<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Core\Menu;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Core\CoreBranch;
use App\Http\Controllers\Controller;
use App\Models\Core\CoreAppUserMenu;

class AdminController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();
        $appId = 1;
        $userMenus = $this->getUserMenusData($userId, $appId);

        return Inertia::render('Admin/Dashboard', [
            'userMenus' => $userMenus,
            'auth' => [ // Add auth data explicitly
                'user' => auth()->user()
            ]
        ]);
    }

    public function getBranchName($branchId)
    {
        $branch = CoreBranch::find($branchId);
        return $branch ? $branch->branch_name : 'Unknown Branch';
    }
    public function getUserMenusData($userId, $appId = null)
    {
        $user = User::find($userId);
        $roleId = $user ? $user->member_role : null;

        // Helper to apply permission filters
        $applyPermissions = function ($query) use ($userId, $roleId) {
            $query->where(function($q) use ($userId, $roleId) {
                // Check User Menus
                $q->whereExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('core_usermenus')
                        ->whereColumn('core_usermenus.menu_id', 'core_menu.id')
                        ->where('core_usermenus.user_id', $userId);
                });

                // Check Role Menus
                if ($roleId) {
                    $q->orWhereExists(function ($sub) use ($roleId) {
                        $sub->select(DB::raw(1))
                            ->from('core_rolemenu')
                            ->whereColumn('core_rolemenu.menu_id', 'core_menu.id')
                            ->where('core_rolemenu.role_id', $roleId);
                    });
                }
            });
        };

        // Get Top Level Menus
        $menuQuery = Menu::select('core_menu.*')
                    ->where('core_menu.is_active', 1)
                    ->where('core_menu.parent_id', 0)
                    ->where('core_menu.app_id', $appId);

        $applyPermissions($menuQuery);

        $menu = $menuQuery->orderBy('core_menu.sort_order', 'ASC')->get();

        // For each top-level menu item, fetch and attach its submenus based on user access
        $menu->each(function ($menuItem) use ($userId, $roleId, $appId, $applyPermissions) {
            $subMenuQuery = Menu::select('core_menu.*')
                ->where('core_menu.is_active', 1)
                ->where('core_menu.app_id', $appId)
                ->where('core_menu.parent_id', $menuItem->id);

            $applyPermissions($subMenuQuery);

            $menuItem->submenus = $subMenuQuery->orderBy('core_menu.sort_order', 'ASC')->get();
        });

        return $menu;
    }
}
