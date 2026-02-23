<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking\TmsTrackingDroptrip;
use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TrackingClient;
use App\Models\Tracking\TrackingClientStoreAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TmsTrackingDroptripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $droptrips = TmsTrackingDroptrip::with(['trackingHeader', 'trackingClient', 'trackingClientStore'])
            ->get()
            ->append(['creator']);

        return Inertia::render('Tracking/Droptrip/index', [
            'masterlist' => $droptrips
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $trackingHeaders = TrackingHeader::select('id', 'tracking_number', 'reference_number')->get();
        $trackingClients = TrackingClient::select('id', 'client_name')->where('is_active', true)->get();
        $trackingClientStores = TrackingClientStoreAddress::select('id', 'store_name', 'client_id')->where('is_active', true)->get();

        return Inertia::render('Tracking/Droptrip/manage', [
            'trackingHeaders' => $trackingHeaders,
            'trackingClients' => $trackingClients,
            'trackingClientStores' => $trackingClientStores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'trackingheader_id' => 'required|integer|exists:tms_tracking_headers,id',
            'trackingclient_id' => 'required|integer|exists:tms_tracking_client,id',
            'trackingclient_store_id' => 'required|integer|exists:tms_tracking_client_store_address,id',
            'sqno' => 'required|integer',
            'drsino' => 'required|string|max:50',
            'store_time_in' => 'nullable|date',
            'unloading_start' => 'nullable|date',
            'unloading_end' => 'nullable|date',
            'store_time_out' => 'nullable|date',
            'receiver_name' => 'nullable|string|max:255',
            'delivery_status' => 'required|string|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED'
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        TmsTrackingDroptrip::create($data);
        return redirect()->route('tracking.droptrip.index')->with('success', 'Droptrip created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $droptrip = TmsTrackingDroptrip::with(['trackingHeader', 'trackingClient', 'trackingClientStore'])
            ->findOrFail($id);

        return Inertia::render('Tracking/Droptrip/show', [
            'droptrip' => $droptrip
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $droptrip = TmsTrackingDroptrip::findOrFail($id);
        $trackingHeaders = TrackingHeader::select('id', 'tracking_number', 'reference_number')->get();
        $trackingClients = TrackingClient::select('id', 'client_name')->where('is_active', true)->get();
        $trackingClientStores = TrackingClientStoreAddress::select('id', 'store_name', 'client_id')->where('is_active', true)->get();

        return Inertia::render('Tracking/Droptrip/manage', [
            'droptrip' => $droptrip,
            'trackingHeaders' => $trackingHeaders,
            'trackingClients' => $trackingClients,
            'trackingClientStores' => $trackingClientStores,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'trackingheader_id' => 'required|integer|exists:tms_tracking_headers,id',
            'trackingclient_id' => 'required|integer|exists:tms_tracking_client,id',
            'trackingclient_store_id' => 'required|integer|exists:tms_tracking_client_store_address,id',
            'sqno' => 'required|integer',
            'drsino' => 'required|string|max:50',
            'store_time_in' => 'nullable|date',
            'unloading_start' => 'nullable|date',
            'unloading_end' => 'nullable|date',
            'store_time_out' => 'nullable|date',
            'receiver_name' => 'nullable|string|max:255',
            'delivery_status' => 'required|string|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED'
        ]);

        $droptrip = TmsTrackingDroptrip::findOrFail($id);
        $data = $request->all();
        $data['updated_by'] = auth()->id();
        
        $droptrip->update($data);

        return redirect()->route('tracking.droptrip.index')->with('success', 'Droptrip updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $droptrip = TmsTrackingDroptrip::findOrFail($id);
        $droptrip->delete();

        return redirect()->back()->with('success', 'Droptrip deleted successfully');
    }
}