<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ListTable extends MainModel {
    use HasFactory;

    protected $table = 'core_listtable';
}

