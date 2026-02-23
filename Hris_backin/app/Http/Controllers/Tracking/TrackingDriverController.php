<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking\TmsTrackingDriver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackingDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = TmsTrackingDriver::get()
            ->append(['creator']);

        return Inertia::render('Tracking/Driver/index', [
            'masterlist' => $drivers
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
        $validated = $request->validate([
            'branch_id' => 'required|integer',
            'driver_code' => 'required|string|max:50|unique:tms_tracking_driver,driver_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'license_no' => 'required|string|max:50',
            'license_type' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'emergency_contact_no' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['full_name'] = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

        TmsTrackingDriver::create($validated);
        return redirect()->back()->with('success', 'Driver created successfully');
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
        $validated = $request->validate([
            'branch_id' => 'required|integer',
            'driver_code' => 'required|string|max:50|unique:tms_tracking_driver,driver_code,' . $id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'license_no' => 'required|string|max:50',
            'license_type' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'emergency_contact_no' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['full_name'] = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

        $driver = TmsTrackingDriver::findOrFail($id);
        $driver->update($validated);

        return redirect()->back()->with('success', 'Driver updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $driver = TmsTrackingDriver::findOrFail($id);
        $driver->delete();

        return redirect()->back()->with('success', 'Driver deleted successfully');
    }
}
