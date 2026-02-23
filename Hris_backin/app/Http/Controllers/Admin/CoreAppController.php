<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\CoreApp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoreAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CoreApp::latest()->paginate(10);

        // Debug the data structure
        logger('CoreApp data:', ['data' => $data->toArray()]);

        return Inertia::render('Admin/Application/index', [
            'masterlist' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/CoreApp/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'required|in:A,I,M',
            'route' => 'nullable|string|max:100',
        ]);

        $existingRecord = CoreApp::where([
            'code' => $request->code,
            'name' => $request->name
        ])->first();

        if($existingRecord && !$request->id){
            return response()->json(['message' => 'This application is already in the database.'], 422);
        }

        CoreApp::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'status_message' => $request->status_message,
                'route' => $request->route,
            ]
        );

        return redirect()->route('admin.application.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = CoreApp::with(['menus'])
            ->where('id', $id)
            ->first();

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $coreApp = CoreApp::findOrFail($id);

        return Inertia::render('Admin/CoreApp/Edit', [
            'coreApp' => $coreApp
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'required|in:A,I,M',
            'status_message' => 'required|string|max:150',
            'route' => 'nullable|string|max:100',
        ]);

        $coreApp = CoreApp::findOrFail($id);
        $coreApp->update($request->all());

        return redirect()->route('admin.application.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $coreApp = CoreApp::findOrFail($id);

        // Check if app has menus
        if ($coreApp->menus()->count() > 0) {
            return response()->json(['message' => 'Cannot delete application with associated menus.'], 422);
        }

        $coreApp->delete();

         return redirect()->route('admin.application.index');
    }

    /**
     * Get active applications for dropdown
     */
    public function getActiveApps()
    {
        $apps = CoreApp::where('status', 'A')->select('id', 'name', 'code')->get();
        return response()->json($apps);
    }
}
