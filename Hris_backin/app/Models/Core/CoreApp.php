<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoreApp extends MainModel
{
    use HasFactory;
    protected $table = 'core_app';

    public function menus()
    {
        return $this->hasMany(Menu::class, 'app_id', 'id');
    }

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'status',
        'status_message',
        'logo',
        'route',
        'created_by',
        'updated_by'
    ];
}
