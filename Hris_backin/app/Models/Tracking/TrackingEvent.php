<?php

namespace App\Models;

use App\Models\Tracking\TrackingHeader;
use App\Models\Tracking\TrackingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'tms_tracking_events';

    protected $fillable = [
        'warehouse_id',
        'tracking_header_id',
        'event_date',
        'status_id',
        'location',
        'event_description',
        'remarks',
        'handled_by',
        'vehicle_id',
        'driver_id',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'created_at' => 'datetime',
    ];



    /**
     * Get the tracking header that owns the tracking event.
     */
    public function trackingHeader(): BelongsTo
    {
        return $this->belongsTo(TrackingHeader::class, 'tracking_header_id');
    }

    /**
     * Get the status that owns the tracking event.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TrackingStatus::class, 'status_id');
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by tracking header.
     */
    public function scopeByTrackingHeader($query, $trackingHeaderId)
    {
        return $query->where('tracking_header_id', $trackingHeaderId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    /**
     * Scope a query to order by event date.
     */
    public function scopeOrderByEventDate($query, $direction = 'asc')
    {
        return $query->orderBy('event_date', $direction);
    }
}
