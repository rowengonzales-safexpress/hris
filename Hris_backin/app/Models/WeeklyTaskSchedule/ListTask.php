<?php

namespace App\Models\WeeklyTaskSchedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListTask extends Model
{
    use HasFactory;
    protected $table = 'task_list';
    protected $fillable = [
        'id',
        'dailytask_id',
        'task_name',
        'status'

    ];

}
