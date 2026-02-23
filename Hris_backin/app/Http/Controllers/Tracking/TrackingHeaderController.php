<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TrackingType;
use App\Models\Tracking\TrackingStatus;
use App\Models\Tracking\TmsTrackingDriver;
use App\Models\Tracking\TmsTrackingDroptrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TrackingHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headers = TrackingHeader::with(['trackingType', 'currentStatus'])
            ->get()
            ->append(['creator']);

         $clients = \App\Models\Tracking\TrackingClient::where('is_active', 1)
            ->orderBy('client_name')
            ->get(['id', 'client_name', 'client_code']);

        $trackingTypes = TrackingType::active()->get();
        $trackingStatuses = TrackingStatus::active()->get();

        return Inertia::render('Tracking/Header/index', [
            'masterlist' => $headers,
            'trackingTypes' => $trackingTypes,
            'trackingStatuses' => $trackingStatuses,
            'clients' => $clients
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
            'branch_id' => 'nullable|integer',
            'tracking_number' => 'nullable|string|max:100|unique:tms_tracking_header,tracking_number',
            'reference_number' => 'nullable|string|max:100',
            'tracking_type_id' => 'required|integer|exists:tms_tracking_type,id',
            'estimated_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'current_status_id' => 'required|integer|exists:tms_tracking_status,id',
            'current_location' => 'nullable|string|max:255',
            'priority_level' => 'nullable|string|max:50',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'package_count' => 'nullable|integer|min:0',
            'special_instructions' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'helper_name' => 'nullable|string|max:100',
            'call_time' => 'nullable|date',
            'whse_in' => 'nullable|date',
            'loading_start' => 'nullable|date',
            'loading_end' => 'nullable|date',
            'whse_out' => 'nullable|date',
            'is_active' => 'boolean',
            // Droptrip validation rules
            'droptrips' => 'nullable|array',
            'droptrips.*.trackingclient_id' => 'required_with:droptrips|integer|exists:tms_tracking_client,id',
            'droptrips.*.trackingclient_store_id' => 'required_with:droptrips|integer|exists:tms_tracking_client_store_address,id',
            'droptrips.*.sqno' => 'nullable|integer',
            'droptrips.*.drsino' => 'nullable|string|max:100',
            'droptrips.*.store_time_in' => 'nullable|date',
            'droptrips.*.unloading_start' => 'nullable|date',
            'droptrips.*.unloading_end' => 'nullable|date',
            'droptrips.*.store_time_out' => 'nullable|date',
            'droptrips.*.receiver_name' => 'nullable|string|max:255',
            'droptrips.*.delivery_status' => 'nullable|string|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
        ]);

        DB::transaction(function () use ($request) {
            // Create the tracking header first
            $data = $request->except('droptrips');

            // Ensure created_by is set
            $data['created_by'] = auth()->id() ?? 1;

            // Set branch_id from authenticated user or request
            $data['branch_id'] = auth()->user()->branch_id ?? $request->branch_id ?? 1;

            // Generate tracking number if not provided
            if (empty($data['tracking_number'])) {
                // Get the next available ID for tracking number generation
                $nextId = TrackingHeader::max('id') + 1;
                $data['tracking_number'] = 'SLITR' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }

            // Clean and validate the data to prevent SQL injection
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });

            $trackingHeader = TrackingHeader::create($data);

            // Create associated droptrips if provided
            if ($request->has('droptrips') && is_array($request->droptrips)) {
                foreach ($request->droptrips as $droptrip) {
                    $droptrip['trackingheader_id'] = $trackingHeader->id;
                    $droptrip['created_by'] = auth()->id() ?? 1;
                    TmsTrackingDroptrip::create($droptrip);
                }
            }
        });

        return redirect()->back()->with('success', 'Tracking Header created successfully');
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
            'tracking_number' => 'required|string|max:100|unique:tms_tracking_headers,tracking_number,' . $id,
            'reference_number' => 'nullable|string|max:100',
            'tracking_type_id' => 'required|integer|exists:tms_tracking_type,id',
            'estimated_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'current_status_id' => 'required|integer|exists:tms_tracking_status,id',
            'current_location' => 'nullable|string|max:255',
            'priority_level' => 'nullable|string|max:50',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'package_count' => 'nullable|integer|min:0',
            'special_instructions' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'helper_name' => 'nullable|string|max:100',
            'call_time' => 'nullable|date',
            'whse_in' => 'nullable|date',
            'loading_start' => 'nullable|date',
            'loading_end' => 'nullable|date',
            'whse_out' => 'nullable|date',
            'is_active' => 'boolean',
            // Droptrip validation rules
            'droptrips' => 'nullable|array',
            'droptrips.*.id' => 'nullable|integer|exists:tms_tracking_droptrip,id',
            'droptrips.*.trackingclient_id' => 'required_with:droptrips|integer|exists:tms_tracking_client,id',
            'droptrips.*.trackingclient_store_id' => 'required_with:droptrips|integer|exists:tms_tracking_client_store_address,id',
            'droptrips.*.sqno' => 'nullable|integer',
            'droptrips.*.drsino' => 'nullable|string|max:100',
            'droptrips.*.store_time_in' => 'nullable|date',
            'droptrips.*.unloading_start' => 'nullable|date',
            'droptrips.*.unloading_end' => 'nullable|date',
            'droptrips.*.store_time_out' => 'nullable|date',
            'droptrips.*.receiver_name' => 'nullable|string|max:255',
            'droptrips.*.delivery_status' => 'nullable|string|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
        ]);

        DB::transaction(function () use ($request, $id) {
            // Update the tracking header
            $header = TrackingHeader::findOrFail($id);
            $header->update($request->except('droptrips'));

            // Handle droptrips if provided
            if ($request->has('droptrips') && is_array($request->droptrips)) {
                // Get existing droptrips for this header
                $existingDroptrips = TmsTrackingDroptrip::where('trackingheader_id', $id)->get();
                $submittedIds = collect($request->droptrips)->pluck('id')->filter();

                // Delete droptrips that are not in the submitted data
                $existingDroptrips->whereNotIn('id', $submittedIds)->each(function ($droptrip) {
                    $droptrip->delete();
                });

                // Update or create droptrips
                foreach ($request->droptrips as $droptrip) {
                    $droptrip['trackingheader_id'] = $id;
                    $droptrip['updated_by'] = auth()->id() ?? 0;

                    if (isset($droptrip['id']) && $droptrip['id']) {
                        // Update existing droptrip
                        $existingDroptrip = TmsTrackingDroptrip::find($droptrip['id']);
                        if ($existingDroptrip) {
                            $existingDroptrip->update($droptrip);
                        }
                    } else {
                        // Create new droptrip
                        $droptrip['created_by'] = auth()->id() ?? 0;
                        unset($droptrip['id']); // Remove null/empty id
                        TmsTrackingDroptrip::create($droptrip);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Tracking Header updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $header = TrackingHeader::findOrFail($id);
        $header->delete();

        return redirect()->back()->with('success', 'Tracking Header deleted successfully');
    }

    /**
     * Get active drivers for dropdown/selection
     */
    public function drivers()
    {
        $drivers = TmsTrackingDriver::where('is_active', 1)
            ->select('id', 'driver_code', 'first_name', 'last_name', 'mobile_no', 'license_no', 'license_type', 'current_vehicle_id')
            ->orderBy('first_name')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'driver_code' => $driver->driver_code,
                    'full_name' => trim($driver->first_name . ' ' . $driver->last_name),
                    'mobile_no' => $driver->mobile_no,
                    'license_no' => $driver->license_no,
                    'license_type' => $driver->license_type,
                    'current_vehicle_id' => $driver->current_vehicle_id,
                ];
            });

        return response()->json($drivers);
    }

    /**
     * Return droptrip summary rows for a tracking header.
     */
    public function droptripSummary(int $headerId)
    {
        $rows = DB::table('tms_tracking_droptrip as d')
            ->leftJoin('tms_tracking_header as h', 'h.id', '=', 'd.trackingheader_id')
            ->leftJoin('tms_tracking_client as c', 'c.id', '=', 'd.trackingclient_id')
            ->leftJoin('tms_tracking_client_store_address as s', 's.id', '=', 'd.trackingclient_store_id')
            ->leftJoin('tms_tracking_driver as dr', 'dr.id', '=', 'h.driver_id')
            ->leftJoin('tms_tracking_vehicle as v', 'v.id', '=', 'dr.current_vehicle_id')
            ->where('d.trackingheader_id', $headerId)
            ->orderBy('d.sqno')
            ->select([
                'd.id',
                'd.sqno',
                'd.trackingclient_id',
                'h.tracking_number as trackingno',
                'c.client_name as Client',
                's.id as store_id',
                's.store_name as ClientStore',
                DB::raw("CONCAT(COALESCE(s.address,''), ', ', COALESCE(s.city,'')) as Address"),
                's.address',
                's.city',
                'v.plate_no as Plateno',
                'v.vehicle_type as trucktype',
                'v.vehicle_size as trucksize',
                DB::raw("CONCAT(COALESCE(dr.first_name,''), ' ', COALESCE(dr.last_name,'')) as drivername"),
                // Added droptrip fields needed by manage.vue
                'd.drsino',
                'd.store_time_in',
                'd.unloading_start',
                'd.unloading_end',
                'd.store_time_out',
                'd.receiver_name',
                'd.delivery_status',
            ])
            ->get();

        return response()->json($rows);
    }
}
