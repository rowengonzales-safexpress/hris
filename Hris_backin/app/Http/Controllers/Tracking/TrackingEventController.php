<?php

namespace App\Http\Controllers\Tracking;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tracking\TrackingType;
use App\Models\Tracking\TrackingClient;
use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TrackingStatus;
use App\Models\Tracking\TmsTrackingDroptrip;
use App\Models\Tracking\TrackingClientStoreAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class TrackingEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headers = TrackingHeader::select([
                'id',
                'branch_id',
                'tracking_number',
                'reference_number',
                'tracking_type_id',
                'estimated_delivery_date',
                'current_status_id',
                'current_location',
                'priority_level',
                'is_active',
                'created_at',
                'driver_id'
            ])
            ->with([
                'trackingType:id,type_code,type_name',
                'currentStatus:id,status_code,status_name',
                'driver:id,full_name,mobile_no,contact_no'

            ])
            ->orderByDesc('created_at')
            ->where('is_active', 1)
            ->paginate(10);

         $clients = TrackingClient::where('is_active', 1)
            ->orderBy('client_name')
            ->get(['id', 'client_name', 'client_code']);

        $trackingTypes = TrackingType::active()->get();
        $trackingStatuses = TrackingStatus::active()->get();

        return Inertia::render('Tracking/TrackingEvent/index', [
            'masterlist' => $headers,
            'trackingTypes' => $trackingTypes,
            'trackingStatuses' => $trackingStatuses,
            'clients' => $clients
        ]);
    }

    /**
     * Return a tracking header by id with relationships used in list/details.
     */
    public function headerById(int $id): JsonResponse
    {
        $header = TrackingHeader::with(['trackingType', 'currentStatus', 'driver'])
            ->findOrFail($id);

        $driver = $header->driver;
        $driverName = null;
        if ($driver) {
            $driverName = $driver->full_name ?? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) ?? ($driver->name ?? null);
        }

        $driverContact = null;
        if ($driver) {
            $driverContact = $driver->mobile_no ?? $driver->contact_no ?? null;
        }

        return response()->json([
            'id' => $header->id,
            'tracking_number' => $header->tracking_number,
            'reference_number' => $header->reference_number,
            'tracking_type' => optional($header->trackingType)->type_code,
            'current_status' => optional($header->currentStatus)->status_code,
            'estimated_delivery_date' => $header->estimated_delivery_date,
            'current_location' => $header->current_location,
            'priority_level' => $header->priority_level,
            // Time tracking values
            'call_time' => $header->call_time,
            'whse_in' => $header->whse_in,
            'loading_start' => $header->loading_start,
            'loading_end' => $header->loading_end,
            'whse_out' => $header->whse_out,
            // Driver and helper info
            'driver_id' => $header->driver_id,
            'driver_name' => $driverName,
            'driver_contact_no' => $driverContact,
            'helper_name' => $header->helper_name,
        ]);
    }

    /**
     * Reporting API: list headers with type and droptrip info.
     */
    public function report(Request $request): JsonResponse
    {
        // Optional filters can be added later; for now, return all
        $droptrips = TmsTrackingDroptrip::with([
            'trackingHeader:id,tracking_number,tracking_type_id',
            'trackingHeader.trackingType:id,type_code,type_name'
        ])->select([
            'id',
            'trackingheader_id',
            'sqno',
            'drsino',
            'delivery_status',
            'store_time_in',
            'unloading_start',
            'unloading_end',
            'store_time_out'
        ])->get();

        // Shape response for simpler consumption on the frontend
        $data = $droptrips->map(function ($dt) {
            return [
                'id' => $dt->id,
                'tracking_number' => optional($dt->trackingHeader)->tracking_number,
                'type_code' => optional(optional($dt->trackingHeader)->trackingType)->type_code,
                'type_name' => optional(optional($dt->trackingHeader)->trackingType)->type_name,
                'sqno' => $dt->sqno,
                'drsino' => $dt->drsino,
                'delivery_status' => $dt->delivery_status,
                'store_time_in' => $dt->store_time_in,
                'unloading_start' => $dt->unloading_start,
                'unloading_end' => $dt->unloading_end,
                'store_time_out' => $dt->store_time_out,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Prepare datasets expected by TrackingEvent/manage.vue
        $clients = TrackingClient::where('is_active', 1)
            ->orderBy('client_name')
            ->get(['id', 'client_name', 'client_code']);

        $trackingTypes = TrackingType::active()->get();
        $trackingStatuses = TrackingStatus::active()->get();

        return Inertia::render('Tracking/TrackingEvent/manage', [
            'clients' => $clients,
            'trackingTypes' => $trackingTypes,
            'trackingStatuses' => $trackingStatuses,
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

    /**
     * Mark a tracking header as Warehouse OUT (set whse_out timestamp).
     */
    public function warehouseOut(int $id): JsonResponse
    {
        $header = TrackingHeader::findOrFail($id);
        $header->whse_out = now();
        $header->updated_by = auth()->id() ?? 0;
        $header->save();

        return response()->json(['status' => 'ok', 'message' => 'Warehouse OUT recorded', 'whse_out' => $header->whse_out]);
    }

    /**
     * Update store times for a droptrip identified by trackingheader_id and store_id.
     * If all four times are provided, mark as COMPLETED; if some are provided, mark as IN_PROGRESS.
     * When all droptrips of the header are COMPLETED, set TrackingHeader.is_active = 0.
     */
    public function updateDroptripTimes(Request $request, int $headerId, int $storeId): JsonResponse
    {
        $request->validate([
            'store_time_in' => 'nullable|date',
            'unloading_start' => 'nullable|date',
            'unloading_end' => 'nullable|date',
            'store_time_out' => 'nullable|date',
        ]);

        $droptrip = TmsTrackingDroptrip::where('trackingheader_id', $headerId)
            ->where('trackingclient_store_id', $storeId)
            ->firstOrFail();

        // Apply updates
        $droptrip->store_time_in = $request->input('store_time_in');
        $droptrip->unloading_start = $request->input('unloading_start');
        $droptrip->unloading_end = $request->input('unloading_end');
        $droptrip->store_time_out = $request->input('store_time_out');
        $droptrip->updated_by = auth()->id() ?? 0;

        // Determine delivery_status
        $times = [
            $droptrip->store_time_in,
            $droptrip->unloading_start,
            $droptrip->unloading_end,
            $droptrip->store_time_out,
        ];
        $allProvided = collect($times)->every(fn($t) => !empty($t));
        $anyProvided = collect($times)->contains(fn($t) => !empty($t));

        if ($allProvided) {
            $droptrip->delivery_status = 'COMPLETED';
        } elseif ($anyProvided) {
            $droptrip->delivery_status = 'IN_PROGRESS';
        }

        $droptrip->save();

        // If all droptrips for header are COMPLETED, mark header inactive
        $remaining = TmsTrackingDroptrip::where('trackingheader_id', $headerId)
            ->where('delivery_status', '!=', 'COMPLETED')
            ->count();
        $headerUpdated = false;
        if ($remaining === 0) {
            $header = TrackingHeader::find($headerId);
            if ($header) {
                $header->isactive = 0;
                $header->updated_by = auth()->id() ?? 0;
                $header->save();
                $headerUpdated = true;
            }
        }

        return response()->json([
            'status' => 'ok',
            'droptrip_id' => $droptrip->id,
            'delivery_status' => $droptrip->delivery_status,
            'header_updated' => $headerUpdated,
        ]);
    }
}
