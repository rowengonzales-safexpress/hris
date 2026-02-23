<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Models\MainModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserMenu extends MainModel
{
    use HasFactory;

    protected $table = 'core_usermenus';

    protected $fillable = [
        'user_id',
        'menu_id',
        'is_manage',
        'is_active',
        'created_by',
        'updated_by',
    ];

    
}
