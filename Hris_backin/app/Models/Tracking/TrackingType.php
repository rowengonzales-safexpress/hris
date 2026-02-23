<?php

namespace App\Models\Tracking;
use App\Models\MainModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingType extends MainModel
{
    use HasFactory;

    protected $table = 'tms_tracking_type';

    protected $fillable = [
        'type_code',
        'type_name',
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

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get the tracking headers for this type.
     */
    public function trackingHeaders(): HasMany
    {
        return $this->hasMany(TrackingHeader::class, 'tracking_type_id');
    }

    /**
     * Scope a query to only include active types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
