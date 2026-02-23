<?php

namespace App\Models\Core;

use App\Models\MainModel;

class RoleMenu extends MainModel {
    protected $table = 'core_rolemenu';

    protected $fillable = [
        'app_id',
        'role_id',
        'menu_id',
        'permission',
    ];

    public function menu() {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

  
}

