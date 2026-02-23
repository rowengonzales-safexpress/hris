<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking\TrackingClient;
use App\Models\Tracking\TrackingClientStoreAddress;
use App\Models\Tracking\TmsTrackingDroptrip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackingClientController extends Controller
{
    public function index()
    {
        $clients = TrackingClient::with(['storeAddresses'])
            ->get()
            ->append(['creator']);

        return Inertia::render('Tracking/Client/index', [
            'masterlist' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'client_code' => 'required|string|max:50',
            'client_name' => 'required|string|max:100',
            'is_active' => 'boolean'
        ]);

        $existingClient = TrackingClient::where('client_code', $request->client_code)->first();
        if ($existingClient) {
            return response()->json(['message' => 'Client code already exists'], 422);
        }

        TrackingClient::create($request->all());
        return redirect()->back()->with('success', 'Client created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'client_code' => 'required|string|max:50',
            'client_name' => 'required|string|max:100',
            'is_active' => 'boolean'
        ]);

        $client = TrackingClient::findOrFail($id);
        $client->update($request->all());

        return redirect()->back()->with('success', 'Client updated successfully');
    }

    public function destroy($id)
    {
        // Block deletion if client is referenced by any droptrip
        $hasDroptrips = TmsTrackingDroptrip::where('trackingclient_id', $id)->exists();
        if ($hasDroptrips) {
            return response()->json(['message' => 'Cannot delete client with existing drop trips.'], 422);
        }

        $client = TrackingClient::findOrFail($id);

        // Also delete related store addresses of this client
        TrackingClientStoreAddress::where('client_id', $id)->delete();

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully.']);
    }
}
