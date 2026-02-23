<?php

namespace App\Models\Tracking;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TmsTrackingDroptrip extends MainModel
{
    protected $table = 'tms_tracking_droptrip';

    protected $fillable = [
        'trackingheader_id',
        'trackingclient_id',
        'trackingclient_store_id',
        'sqno',
        'drsino',
        'store_time_in',
        'unloading_start',
        'unloading_end',
        'store_time_out',
        'receiver_name',
        'delivery_status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'store_time_in' => 'datetime',
        'unloading_start' => 'datetime',
        'unloading_end' => 'datetime',
        'store_time_out' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the tracking header that owns the droptrip.
     */
    public function trackingHeader(): BelongsTo
    {
        return $this->belongsTo(TrackingHeader::class, 'trackingheader_id');
    }

    /**
     * Get the tracking client that owns the droptrip.
     */
    public function trackingClient(): BelongsTo
    {
        return $this->belongsTo(TrackingClient::class, 'trackingclient_id');
    }

    /**
     * Get the tracking client store address that owns the droptrip.
     */
    public function trackingClientStore(): BelongsTo
    {
        return $this->belongsTo(TrackingClientStoreAddress::class, 'trackingclient_store_id');
    }

    /**
     * Scope a query to only include completed deliveries.
     */
    public function scopeCompleted($query)
    {
        return $query->where('delivery_status', 'COMPLETED');
    }

    /**
     * Scope a query to only include in progress deliveries.
     */
    public function scopeInProgress($query)
    {
        return $query->where('delivery_status', 'IN_PROGRESS');
    }

    /**
     * Scope a query to only include pending deliveries.
     */
    public function scopePending($query)
    {
        return $query->where('delivery_status', 'PENDING');
    }
}