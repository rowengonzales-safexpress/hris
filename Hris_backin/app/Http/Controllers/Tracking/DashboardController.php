<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TmsTrackingDriver;
use App\Models\Tracking\TmsTrackingVehicle;
use App\Models\Tracking\TmsTrackingDroptrip;
use App\Models\Tracking\TrackingClient;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        
        $summary = [
            // Shipment Metrics
            'total_shipments' => TrackingHeader::count(),
            'active_shipments' => TrackingHeader::where('isactive', 1)->count(),
            'delivered_shipments' => TrackingHeader::whereNotNull('actual_delivery_date')->count(),

            // Driver Metrics
            'total_drivers' => TmsTrackingDriver::count(),
            'drivers_on_trip' => TmsTrackingDriver::where('current_status', 'ON_TRIP')->count(),

            // Vehicle Metrics
            'total_vehicles' => TmsTrackingVehicle::count(),
            'vehicles_in_use' => TmsTrackingVehicle::where('current_status', 'IN_USE')->count(),

            // Client Metrics
            'total_clients' => TrackingClient::count(),

            // Droptrip Metrics
            'total_droptrips' => TmsTrackingDroptrip::count(),
            'completed_droptrips' => TmsTrackingDroptrip::where('delivery_status', 'COMPLETED')->count(),
        ];

        // Recent Shipments (Top 10)
        $recentDroptrips = TmsTrackingDroptrip::with(['trackingHeader.currentStatus', 'trackingClient', 'trackingClientStore'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentShipments = $recentDroptrips->map(function ($dt) {
            $header = $dt->trackingHeader;
            $client = $dt->trackingClient;
            $store = $dt->trackingClientStore;

            $destination = '';
            if ($store) {
                $destination = $store->store_name ?: ($store->city ?: ($store->address ?: ''));
            }

            return [
                'tracking_number' => $header?->tracking_number ?? 'N/A',
                'client_name' => $client?->client_name ?? 'Unknown Client',
                'destination' => $destination ?: 'N/A',
                'status' => $header?->currentStatus?->status_name ?? ($dt->delivery_status ?? 'Pending'),
                'created_date' => optional($dt->created_at ?? $header?->created_at)->format('Y-m-d H:i'),
            ];
        })->toArray();

        // Quick Actions with navigation URLs
        $quickActions = [
            [
                'name' => 'New Shipment',
                'icon' => 'M12 4v16m8-8H4',
                'color' => 'from-blue-500 to-blue-600',
                'description' => 'Create a new shipment',
                'url' => route('tracking.tracker.create'),
            ],
            [
                'name' => 'Track Package',
                'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
                'color' => 'from-green-500 to-green-600',
                'description' => 'Search and track packages',
                'url' => route('tracking.tracker.index'),
            ],
            [
                'name' => 'Generate Report',
                'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'color' => 'from-purple-500 to-purple-600',
                'description' => 'Export analytics reports',
                'url' => route('tracking.reports.tracking-summary-dashboard'),
            ],
            [
                'name' => 'Manage Fleet',
                'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
                'color' => 'from-orange-500 to-orange-600',
                'description' => 'Monitor fleet status',
                'url' => route('tracking.vehicle.index'),
            ],
        ];

        // Recent Activities (Top 10 Tracking Headers)
        $recentActivities = TrackingHeader::with(['currentStatus'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($header) {
                $statusName = optional($header->currentStatus)->status_name;
                $lower = $statusName ? strtolower($statusName) : '';
                $type = 'system';
                if ($lower) {
                    if (str_contains($lower, 'deliver')) {
                        $type = 'delivery';
                    } elseif (str_contains($lower, 'create') || str_contains($lower, 'ship')) {
                        $type = 'shipment';
                    }
                }

                $actionText = $statusName
                    ? "Shipment {$header->tracking_number}: {$statusName}"
                    : "Shipment {$header->tracking_number}: Status Update";

                return [
                    'action' => $actionText,
                    'time' => optional($header->updated_at)->diffForHumans() ?? optional($header->created_at)->diffForHumans(),
                    'type' => $type,
                ];
            })
            ->toArray();


        // Performance Overview
        $shipmentsLast7Days = [];
        $deliveriesLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $shipmentsLast7Days[] = [
                'date' => $date,
                'count' => TrackingHeader::whereDate('created_at', $date)->count(),
            ];
            $deliveriesLast7Days[] = [
                'date' => $date,
                'count' => TrackingHeader::whereDate('actual_delivery_date', $date)->count(),
            ];
        }

        $fleetUtilization = ($summary['total_vehicles'] ?? 0) > 0
            ? (int) round((($summary['vehicles_in_use'] ?? 0) / max(1, ($summary['total_vehicles'] ?? 0))) * 100)
            : 0;

        $performance = [
            'shipments_last_7_days' => $shipmentsLast7Days,
            'deliveries_last_7_days' => $deliveriesLast7Days,
            'fleet_utilization' => $fleetUtilization,
        ];

        return Inertia::render('Tracking/Dashboard', [
             'summary' => $summary,
             'recentShipments' => $recentShipments,
             'quickActions' => $quickActions,
             'recentActivities' => $recentActivities,
             'performance' => $performance,
        ]);
    }
}
