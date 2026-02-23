<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Core\CoreApp;
class AppsController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();

        $userApps = $this->getApplication($userId);

        return Inertia::render('Application', [
            'userApps' => $userApps
        ]);
    }

     protected function getApplication($userId)
     {
        if (!$userId) {
            return [];
        }

        $applist = CoreApp::select('core_app.code','core_app.name','core_app.description','core_app.route as href','core_app.logo','core_appuser.is_active')
            ->join('core_appuser','core_app.id', '=','core_appuser.app_id')
            ->where('core_appuser.user_id',$userId)
            ->where('core_app.status','A')
            ->orderBy('core_app.name', 'ASC')
            ->get();

        return $applist;

     }


}
