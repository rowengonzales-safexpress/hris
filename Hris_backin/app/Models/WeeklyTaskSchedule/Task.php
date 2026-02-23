<?php

namespace App\Models\WeeklyTaskSchedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\TaskType;
class Task extends Model
{
    use HasFactory;
    protected $table = 'task_dailytask';
    // protected $primaryKey = 'dailytask_id'; // Default is id
    protected $fillable = [
        // 'dailytask_id', // Default is id
        'user_id',
        'branch_id',
        'taskdate',
        'project',
        'plandate',
        'planenddate',
        'startdate',
        'enddate',
        'tasktype_id',
        'status',
        'attachment',
        'PWS',
        'remarks',
        'immediatehead_id',
        'status_task',
        'created_at'

    ];

    public function taskLists()
    {
        return $this->hasMany(ListTask::class, 'dailytask_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Core\User::class, 'user_id');
    }


    protected $casts = [
        'taskdate' => 'datetime',
        'created_at' => 'datetime',
        'plandate' => 'datetime',
        'planenddate' => 'datetime',
        'startdate' => 'datetime',
        'enddate' => 'datetime',
        // If you need enum casting, map it to tasktype_id
        // 'tasktype_id' => TaskType::class,
    ];

}
