<?php

namespace App\Models\Core;

use App\Models\MainModel;

class HelpSection extends MainModel
{
    public function coreApp()
    {
        return $this->belongsTo(CoreApp::class, 'id', 'systemID');
    }



    protected $table = 'help_sections';

    protected $fillable = [
        'id',
        'uuid',
        'systemID',
        'section_name',
        'created_at',
        'updated_at'
    ];
}
