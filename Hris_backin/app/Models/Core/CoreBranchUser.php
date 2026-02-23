<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class CoreBranchUser extends Model
{
  protected $table = 'core_branchuser';
  protected $primaryKey = 'id';

  protected $fillable = [
    'branch_id',
    'user_id',
  ];

  public $timestamps = false;

  
}
