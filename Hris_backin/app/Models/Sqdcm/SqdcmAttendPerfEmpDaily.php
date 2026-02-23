<?php

namespace App\Models\Sqdcm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SqdcmAttendPerfEmpDaily extends Model
{
    use HasFactory;

    protected $table = 'sqdcm_attendperfempdaily';

    protected $fillable = [
        'date',
        'emp_code',
        'dept_code',
        'site_code',
        'is_present',
        'check_in_time',
        'check_out_time',
        'total_hours',
        'overtime_hours',
        'target_assigned',
        'target_achieved',
        'performance_perc',
        'productivity_score',
        'quality_rating',
        'remarks'
    ];

    protected $casts = [
        'date' => 'date',
        'is_present' => 'boolean',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'target_assigned' => 'integer',
        'target_achieved' => 'integer',
        'performance_perc' => 'decimal:2',
        'productivity_score' => 'decimal:2',
        'quality_rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(SqdcmAttendPerfDeptDaily::class, 'dept_code', 'dept_code')
                    ->whereDate('date', $this->date);
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByEmployee($query, $empCode)
    {
        return $query->where('emp_code', $empCode);
    }

    public function scopeByDepartment($query, $deptCode)
    {
        return $query->where('dept_code', $deptCode);
    }

    public function scopeBySite($query, $siteCode)
    {
        return $query->where('site_code', $siteCode);
    }

    public function scopePresent($query)
    {
        return $query->where('is_present', true);
    }

    public function scopeAbsent($query)
    {
        return $query->where('is_present', false);
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
        return $this->is_present ? 'Present' : 'Absent';
    }

    public function getPerformanceStatusAttribute()
    {
        if ($this->performance_perc >= 90) return 'Outstanding';
        if ($this->performance_perc >= 80) return 'Good';
        if ($this->performance_perc >= 70) return 'Satisfactory';
        return 'Needs Improvement';
    }

    public function getWorkingHoursAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInHours($this->check_out_time);
        }
        return 0;
    }
}
