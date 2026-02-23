<?php

namespace App\Models\Tracking;
use App\Models\MainModel;

use App\Models\TrackingEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingStatus extends MainModel
{
    use HasFactory;

    protected $table = 'tms_tracking_status';

    protected $fillable = [
        'status_code',
        'status_name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    /**
     * Get the tracking headers for this status.
     */
    public function trackingHeaders(): HasMany
    {
        return $this->hasMany(TrackingHeader::class, 'current_status_id');
    }

    /**
     * Get the tracking events for this status.
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'status_id');
    }

    /**
     * Scope a query to only include active statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
