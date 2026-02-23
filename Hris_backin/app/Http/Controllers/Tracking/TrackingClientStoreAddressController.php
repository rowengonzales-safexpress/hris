<?php

namespace App\Http\Controllers\Tracking;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tracking\TrackingClient;
use App\Models\Tracking\TrackingClientStoreAddress;
use App\Models\Tracking\TmsTrackingDroptrip;

class TrackingClientStoreAddressController extends Controller
{
    public function index()
    {
        $stores = TrackingClientStoreAddress::with(['client'])
            ->get()
            ->append(['creator']);

        $clients = TrackingClient::where('is_active', 1)
            ->orderBy('client_name')
            ->get(['id', 'client_name', 'client_code']);

        return Inertia::render('Tracking/Store/index', [
            'masterlist' => $stores,
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:tms_tracking_client,id',
            'store_code' => 'required|string|max:150',
            'store_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20'
        ]);

        TrackingClientStoreAddress::create($request->all());
        return redirect()->back()->with('success', 'Store address created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'store_code' => 'required|string|max:150',
            'store_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20'
        ]);

        $store = TrackingClientStoreAddress::findOrFail($id);
        $store->update($request->all());

        return redirect()->back()->with('success', 'Store address updated successfully');
    }

    public function getByClientId($clientId)
    {
        $stores = TrackingClientStoreAddress::with(['client'])
            ->where('client_id', $clientId)
            ->where('is_active', 1)
            ->orderBy('store_name')
            ->get()
            ->append(['creator'])
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'client_id' => $store->client_id,
                    'store_code' => $store->store_code,
                    'store_name' => $store->store_name,
                    'address' => $store->address,
                    'city' => $store->city,
                    'state' => $store->state_province,
                    'postal_code' => $store->zip_code,
                    'country' => 'Philippines', // Default country
                    'contact_person' => '', // Not available in current model
                    'contact_phone' => $store->phone,
                    'contact_email' => $store->email,
                    'latitude' => null, // Not available in current model
                    'longitude' => null, // Not available in current model
                    'is_active' => $store->is_active,
                    'created_at' => $store->created_at,
                    'updated_at' => $store->updated_at,
                    'client_name' => $store->client ? $store->client->client_name : null,
                    'creator' => $store->creator
                ];
            });

        return response()->json($stores);
    }

    public function destroy($id)
    {
        // Prevent deletion if the store address is referenced by any droptrip
        $hasDroptrips = TmsTrackingDroptrip::where('trackingclient_store_id', $id)->exists();
        if ($hasDroptrips) {
            return response()->json(['message' => 'Cannot delete store address with existing drop trips.'], 422);
        }

        $store = TrackingClientStoreAddress::findOrFail($id);
        $store->delete();

        return response()->json(['message' => 'Store address deleted successfully.']);
    }
}
