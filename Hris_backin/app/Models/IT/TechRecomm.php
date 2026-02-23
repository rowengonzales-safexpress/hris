<?php

namespace App\Models\IT;

use App\Enums\TechRecomStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TechRecomm extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'techrecomms';

    protected $fillable = [
        'id',
        'recommnum',
        'company',
        'branch',
        'department',
        'warehouse',
        'user',
        'brand',
        'model',
        'assettag',
        'serialnum',
        'problem',
        'assconducted',
        'recommendation',
        'status',
        'created_by',
        'updated_by',
        'is_active',
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'status' => TechRecomStatus::class,
    ];

    public function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at->format('Y-m-d h:i A'),
        );
    }
    protected $appends = [
        'formatted_created_at',
    ];



    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format(setting('date_format'));
    }
}
