<?php

namespace App\Models\Tracking;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingClient extends MainModel
{
    protected $table = 'tms_tracking_client';

    protected $fillable = [
        'branch_id',
        'client_code',
        'client_name',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function storeAddresses(): HasMany
    {
        return $this->hasMany(TrackingClientStoreAddress::class, 'client_id');
    }
}
