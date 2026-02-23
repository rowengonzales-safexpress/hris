<?php

namespace App\Models\Sqdcm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SqdcmAttendPerfDeptDaily extends Model
{
    use HasFactory;

    protected $table = 'sqdcm_attendperfdeptdaily';

    protected $fillable = [
        'date',
        'dept_code',
        'site_code',
        'total_emp',
        'present_emp',
        'attendance_perc',
        'total_target',
        'total_achieved',
        'performance_perc',
        'overall_score',
        'grade'
    ];

    protected $casts = [
        'date' => 'date',
        'total_emp' => 'integer',
        'present_emp' => 'integer',
        'attendance_perc' => 'decimal:2',
        'total_target' => 'integer',
        'total_achieved' => 'integer',
        'performance_perc' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function employees()
    {
        return $this->hasMany(SqdcmAttendPerfEmpDaily::class, 'dept_code', 'dept_code')
                    ->whereDate('date', $this->date);
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByDepartment($query, $deptCode)
    {
        return $query->where('dept_code', $deptCode);
    }

    public function scopeBySite($query, $siteCode)
    {
        return $query->where('site_code', $siteCode);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                    ->whereMonth('date', $month);
    }

    public function scopeHighPerformance($query, $threshold = 80)
    {
        return $query->where('performance_perc', '>=', $threshold);
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getAttendanceStatusAttribute()
    {
        if ($this->attendance_perc >= 95) return 'Excellent';
        if ($this->attendance_perc >= 85) return 'Good';
        if ($this->attendance_perc >= 75) return 'Average';
        return 'Poor';
    }

    public function getPerformanceStatusAttribute()
    {
        if ($this->performance_perc >= 90) return 'Outstanding';
        if ($this->performance_perc >= 80) return 'Good';
        if ($this->performance_perc >= 70) return 'Satisfactory';
        return 'Needs Improvement';
    }

    public function getAbsentEmpAttribute()
    {
        return $this->total_emp - $this->present_emp;
    }
}
