<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking\TmsTrackingVehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackingVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = TmsTrackingVehicle::get()
            ->append(['creator']);

        return Inertia::render('Tracking/Vehicle/index', [
            'masterlist' => $vehicles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'vehicle_code' => 'required|string|max:50|unique:tms_tracking_vehicle,vehicle_code',
            'plate_no' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:50',
            'current_status' => 'nullable|string|max:50',
            'warehouse_id' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        TmsTrackingVehicle::create($request->all());
        return redirect()->back()->with('success', 'Vehicle created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'vehicle_code' => 'required|string|max:50|unique:tms_tracking_vehicle,vehicle_code,' . $id,
            'plate_no' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:50',
            'current_status' => 'nullable|string|max:50',
            'warehouse_id' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        $vehicle = TmsTrackingVehicle::findOrFail($id);
        $vehicle->update($request->all());

        return redirect()->back()->with('success', 'Vehicle updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicle = TmsTrackingVehicle::findOrFail($id);
        $vehicle->delete();

        return redirect()->back()->with('success', 'Vehicle deleted successfully');
    }
}
