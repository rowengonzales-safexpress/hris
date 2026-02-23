<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrmsDisbursement extends MainModel
{
    protected $table = 'frms_disbursement';

    protected $fillable = [
        'branch_id',
        'frms_id',
        'disbursement_no',
        'status',
        'created_by',
        'updated_by',
    ];


    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'frms_id', 'id');
    }
}
