<?php

namespace App\Models\FRMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrmsRemarks extends Model
{
    use HasFactory;

    protected $table = 'frms_remarks';

    protected $fillable = [
        'documentId',
        'aliase',
        'remarks',
        'status',
    ];
}
