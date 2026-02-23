<?php

namespace App\Models\WeeklyTaskSchedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeVsc extends Model
{
    use HasFactory;

    protected $table = 'theme_vscs';

    protected $fillable = [
        'id',
        'userid',
        'background',
        'active_background',
        'font_background',
        'created_at'

    ];
}
