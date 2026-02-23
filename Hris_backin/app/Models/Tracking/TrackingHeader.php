<?php

namespace App\Models\Tracking;
use App\Models\MainModel;

use App\Models\Tracking\TrackingEvent;
use App\Models\Tracking\TrackingStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracking\TmsTrackingDriver;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingHeader extends MainModel
{
    use HasFactory;

    protected $table = 'tms_tracking_header';

    protected $fillable = [
        'branch_id',
        'tracking_number',
        'reference_number',
        'tracking_type_id',
        'estimated_delivery_date',
        'actual_delivery_date',
        'current_status_id',
        'current_location',
        'priority_level',
        'total_weight',
        'total_volume',
        'package_count',
        'special_instructions',
        'is_active',
        'created_by',
        'updated_by',
        'driver_id', 
        'helper_name',
        'call_time',
        'whse_in',
        'loading_start',
        'loading_end',
        'whse_out',
        'isactive',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
        'call_time' => 'datetime',
        'whse_in' => 'datetime',
        'loading_start' => 'datetime',
        'loading_end' => 'datetime',
        'whse_out' => 'datetime',
        'total_weight' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'package_count' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the tracking type that owns the tracking header.
     */
    public function trackingType(): BelongsTo
    {
        return $this->belongsTo(TrackingType::class, 'tracking_type_id');
    }

    /**
     * Get the current status that owns the tracking header.
     */
    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(TrackingStatus::class, 'current_status_id');
    }

    /**
     * Get the assigned driver for this header.
     */
    public function driver(): BelongsTo
    {
        // Note: column name 'drvier_id' based on migration
        return $this->belongsTo(TmsTrackingDriver::class, 'drvier_id');
    }

    /**
     * Get the tracking events for the tracking header.
     */
    // public function trackingEvents(): HasMany
    // {
    //     // return $this->hasMany(TrackingEvent::class, 'tracking_header_id');
    // }

    /**
     * Get the tracking documents for the tracking header.
     */
    public function trackingDocuments(): HasMany
    {
        return $this->hasMany(TrackingDocument::class, 'tracking_header_id');
    }

    /**
     * Scope a query to only include active tracking headers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by tracking number.
     */
    public function scopeByTrackingNumber($query, $trackingNumber)
    {
        return $query->where('tracking_number', $trackingNumber);
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by priority level.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }
}
