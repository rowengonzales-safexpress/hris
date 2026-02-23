<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use App\Models\Core\User;
use App\Models\Core\CoreBranch;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends MainModel
{
    protected $table = 'frms_form';

    protected $fillable = [
        'frm_no',
        'user_id',
        'branch_id',
        'request_date',
        'expectedliquidation_date',
        'purpose',
        'quotation',
        'status_request',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FrmsList::class, 'requesting_id', 'id');
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(FrmsDisbursement::class, 'frms_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CoreBranch::class, 'branch_id', 'id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(FrmsDocument::class, 'documentable');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(FrmsRemarks::class, 'documentId', 'id');
    }
}
