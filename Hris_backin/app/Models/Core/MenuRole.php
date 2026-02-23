<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    protected $table = 'core_rolemenu';

    protected $fillable = [
        'role_id',
        'menu_id',
        'permission'
    ];
}
