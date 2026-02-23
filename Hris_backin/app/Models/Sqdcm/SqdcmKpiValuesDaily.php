<?php

namespace App\Models\Sqdcm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SqdcmKpiValuesDaily extends Model
{
    use HasFactory;

    protected $table = 'sqdcm_kpivaluesdaily';

    protected $fillable = [
        'date',
        'kpi_code',
        'dept_code',
        'site_code',
        'entity_type',
        'kpi_name',
        'target_value',
        'actual_value',
        'achievement_perc',
        'variance',
        'trend',
        'is_critical',
        'alert_level',
        'remarks'
    ];

    protected $casts = [
        'date' => 'date',
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'achievement_perc' => 'decimal:2',
        'variance' => 'decimal:2',
        'is_critical' => 'boolean',
        'alert_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function site()
    {
        return $this->belongsTo(SqdcmAttendPerfSiteDaily::class, 'site_code', 'site_code')
                    ->whereDate('date', $this->date);
    }

    public function department()
    {
        return $this->belongsTo(SqdcmAttendPerfDeptDaily::class, 'dept_code', 'dept_code')
                    ->whereDate('date', $this->date);
    }

    public function employee()
    {
        return $this->belongsTo(SqdcmAttendPerfEmpDaily::class, 'emp_code', 'emp_code')
                    ->whereDate('date', $this->date);
    }

    // Scopes
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeBySite($query, $siteCode)
    {
        return $query->where('entity_code', $siteCode)
                     ->where('entity_type', 'SITE');
    }

    public function scopeByDepartment($query, $deptCode)
    {
        return $query->where('entity_code', $deptCode)
                     ->where('entity_type', 'DEPT');
    }

    public function scopeByEmployee($query, $empCode)
    {
        return $query->where('entity_code', $empCode)
                     ->where('entity_type', 'EMP');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('kpi_category', $category);
    }

    public function scopeByKpi($query, $kpiName)
    {
        return $query->where('kpi_name', $kpiName);
    }

    public function scopeHighAchievement($query, $threshold = 80)
    {
        return $query->where('achievement_perc', '>=', $threshold);
    }

    public function scopeByAlertLevel($query, $level)
    {
        return $query->where('alert_level', $level);
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getPerformanceStatusAttribute()
    {
        if ($this->achievement_perc >= 100) return 'Exceeded';
        if ($this->achievement_perc >= 90) return 'Outstanding';
        if ($this->achievement_perc >= 80) return 'Good';
        if ($this->achievement_perc >= 70) return 'Satisfactory';
        return 'Below Target';
    }

    public function getTrendStatusAttribute()
    {
        switch (strtolower($this->trend)) {
            case 'up':
            case 'increasing':
                return 'Improving';
            case 'down':
            case 'decreasing':
                return 'Declining';
            case 'stable':
                return 'Stable';
            default:
                return 'Unknown';
        }
    }

    public function getVarianceStatusAttribute()
    {
        if ($this->variance > 0) return 'Above Target';
        if ($this->variance < 0) return 'Below Target';
        return 'On Target';
    }

    public function getAlertStatusAttribute()
    {
        switch ($this->alert_level) {
            case 1:
                return 'Low';
            case 2:
                return 'Medium';
            case 3:
                return 'High';
            case 4:
                return 'Critical';
            default:
                return 'Normal';
        }
    }
}
