<?php

namespace App\Models\Core;

use App\Models\MainModel;

class CoreAppUser extends MainModel
{
    protected $table = 'core_appuser';

    public function users()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function apps()
    {
        return $this->hasMany(CoreApp::class, 'app_id', 'id');
    }

    protected $fillable = [
        'uuid',
        'app_id',
        'user_id',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
