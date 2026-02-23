<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrmsLiquidaionappved extends MainModel
{
    protected $table = 'frms_liquidaionappved';

    protected $fillable = [
        'disbursement_id',
        'approvedby_id',
    ];

    public function disbursement(): BelongsTo
    {
        return $this->belongsTo(FrmsDisbursement::class, 'disbursement_id', 'id');
    }
}

