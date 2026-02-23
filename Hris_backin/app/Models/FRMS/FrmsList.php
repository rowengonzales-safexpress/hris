<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use App\Models\CoreTransactionCode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrmsList extends MainModel
{
    protected $table = 'frms_list';

    protected $fillable = [
        'requesting_id',
        'account_code_title',
        'frequency',
        'description',
        'qty',
        'unit_price',
        'amount',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'requesting_id', 'id');
    }

    public function frequency(): BelongsTo
    {
        return $this->belongsTo(CoreTransactionCode::class, 'frequency', 'id');
    }
}
