<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrmsLiquidationdetail extends MainModel
{
    protected $table = 'frms_liquidationdetail';

    protected $fillable = [
        'disbursement_id',
        'frmslist_id',
        'ref_num',
        'variance',
        'description',
        'or_no',
        'amount',
        'reason',
        'tin',
        'address',
        'vatcode',
        'vat_non_vat',
        'expense_amount',
        'input_vat',
        'created_by',
        'updated_by',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(FrmsDocument::class, 'liquidation_id', 'id');
    }


}
