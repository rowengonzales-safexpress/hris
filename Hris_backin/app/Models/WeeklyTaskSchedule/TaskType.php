<?php

namespace App\Models\WeeklyTaskSchedule;

use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $table = 'task_type';

    protected $fillable = [
        'id',
        'code',
        'name',
        'created_at'
    ];

    public $timestamps = false;
}
