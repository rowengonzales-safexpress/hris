<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TmsTrackingDriver;
use App\Models\Tracking\TmsTrackingVehicle;
use App\Models\Tracking\TmsTrackingDroptrip;

class ReportController extends Controller
{
     // 📦 Shipment Status Report
    public function shipmentStatusReport()
    {
        $shipments = TrackingHeader::with(['currentStatus', 'trackingType'])
            ->select('tracking_number', 'reference_number', 'tracking_type_id', 'current_status_id', 'estimated_delivery_date', 'actual_delivery_date')
            ->where('is_active', 1)
            ->get();

        return Inertia::render('Reports/ShipmentStatus', [
            'shipments' => $shipments
        ]);
    }

    // 🚚 Delivery Route Summary
    public function deliveryRouteSummary()
    {
        $routes = TmsTrackingDroptrip::with(['trackingHeader', 'trackingClientStore'])
            ->select('trackingheader_id', 'store_time_in', 'store_time_out', 'delivery_status', 'receiver_name')
            ->get();

        return Inertia::render('Reports/DeliveryRouteSummary', [
            'routes' => $routes
        ]);
    }

    // 📦 Package Tracking Overview
    public function packageTrackingOverview()
    {
        $packages = TrackingHeader::select('tracking_number', 'package_count', 'total_weight', 'total_volume', 'current_location')
            ->where('is_active', 1)
            ->get();

        return Inertia::render('Reports/PackageTrackingOverview', [
            'packages' => $packages
        ]);
    }

    // 📊 Logistics Performance Report
    public function logisticsPerformanceReport()
    {
        $drivers = TmsTrackingDriver::select('full_name', 'total_deliveries', 'successful_deliveries', 'average_rating')->get();
        $vehicles = TmsTrackingVehicle::select('vehicle_name', 'total_trips', 'total_distance_km', 'total_fuel_consumed_liters')->get();

        return Inertia::render('Reports/LogisticsPerformance', [
            'drivers' => $drivers,
            'vehicles' => $vehicles
        ]);
    }

    // 🖥️ System Activity Report
    public function systemActivityReport()
    {
        $activities = TrackingHeader::select('tracking_number', 'created_at', 'updated_at', 'created_by', 'updated_by')
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();

        return Inertia::render('Reports/SystemActivity', [
            'activities' => $activities
        ]);
    }

    // 📋 Tracking Summary Dashboard
    public function trackingSummaryDashboard()
    {
        $summary = [
            'total_shipments' => TrackingHeader::count(),
            'active_shipments' => TrackingHeader::where('is_active', 1)->count(),
            'drivers_on_trip' => TmsTrackingDriver::where('current_status', 'ON_TRIP')->count(),
            'vehicles_in_use' => TmsTrackingVehicle::where('current_status', 'IN_USE')->count(),
        ];

        return Inertia::render('Reports/TrackingSummaryDashboard', [
            'summary' => $summary
        ]);
    }

    // 📈 Performance Metrics Report
    public function performanceMetricsReport()
    {
        $metrics = TmsTrackingVehicle::select('vehicle_name', 'avg_fuel_consumption_per_km', 'total_distance_km', 'total_trips')
            ->get();

        return Inertia::render('Reports/PerformanceMetrics', [
            'metrics' => $metrics
        ]);
    }

    // 🧾 Audit Trail Report
    public function auditTrailReport()
    {
        $audit = TmsTrackingDroptrip::select('trackingheader_id', 'created_by', 'updated_by', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();

        return Inertia::render('Reports/AuditTrail', [
            'audit' => $audit
        ]);
    }

}
