<?php

namespace App\Models\Sqdcm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SqdcmAttendPerfSiteDaily extends Model
{
    use HasFactory;

    protected $table = 'sqdcm_attendperfsitedaily';

    protected $fillable = [
        'date',
        'site_code',
        'total_emp',
        'present_emp',
        'attendance_perc',
        'total_departments',
        'active_departments',
        'total_target',
        'total_achieved',
        'performance_perc',
        'revenue_generated',
        'operational_cost',
        'profit_margin',
        'customer_satisfaction',
        'safety_incidents',
        'overall_rating'
    ];

    protected $casts = [
        'date' => 'date',
        'total_emp' => 'integer',
        'present_emp' => 'integer',
        'attendance_perc' => 'decimal:2',
        'total_departments' => 'integer',
        'active_departments' => 'integer',
        'total_target' => 'integer',
        'total_achieved' => 'integer',
        'performance_perc' => 'decimal:2',
        'revenue_generated' => 'decimal:2',
        'operational_cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'customer_satisfaction' => 'decimal:2',
        'safety_incidents' => 'integer',
        'overall_rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function departments()
    {
        return $this->hasMany(SqdcmAttendPerfDeptDaily::class, 'site_code', 'site_code')
                    ->whereDate('date', $this->date);
    }

    public function kpiValues()
    {
        return $this->hasMany(SqdcmKpiValuesDaily::class, 'site_code', 'site_code')
                    ->whereDate('date', $this->date);
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
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

    public function scopeProfitable($query)
    {
        return $query->where('profit_margin', '>', 0);
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

    public function getNetProfitAttribute()
    {
        return $this->revenue_generated - $this->operational_cost;
    }

    public function getDepartmentUtilizationAttribute()
    {
        return $this->total_departments > 0 ?
            ($this->active_departments / $this->total_departments) * 100 : 0;
    }
}
