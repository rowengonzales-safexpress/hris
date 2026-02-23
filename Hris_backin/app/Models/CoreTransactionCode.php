<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreTransactionCode extends Model
{
    protected $table = 'core_transaction_code';

    protected $fillable = [
        'transaction_name',
        'transaction_key',
        'identitycode',
        'trans_value',
        'description',
        'sortorder',
        'isactive',
        'createdby',
        'updatedby'
    ];

    protected $casts = [
        'isactive' => 'boolean',
        'sortorder' => 'integer'
    ];

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    // Scope for FRMS transactions
    public function scopeFrms($query)
    {
        return $query->where('transaction_name', 'FRMS');
    }

    // Scope for specific identity code
    public function scopeBytransaction_name($query, $code)
    {
        return $query->where('transaction_name', $code);
    }
}
