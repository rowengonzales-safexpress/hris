<?php

namespace App\Models\Tracking;
use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingClientStoreAddress extends MainModel
{
    protected $table = 'tms_tracking_client_store_address';

    protected $fillable = [
        'client_id',
        'store_code',
        'store_name',
        'email',
        'phone',
        'address',
        'city',
        'state_province',
        'zip_code',
        'is_active',
        'is_verified',
        'verification_date',
        'last_transaction_date',
        'total_shipments',
        'remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verification_date' => 'datetime',
        'last_transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];



    public function client(): BelongsTo
    {
        return $this->belongsTo(TrackingClient::class, 'client_id');
    }
}
