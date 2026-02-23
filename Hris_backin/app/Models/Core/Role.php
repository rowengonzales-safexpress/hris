<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends MainModel {
    use HasFactory;

    protected $table = 'core_role';

    public function roleMenus() {
        return $this->hasMany(RoleMenu::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}

