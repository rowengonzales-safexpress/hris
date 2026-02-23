<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoreBranch extends MainModel
{
    use HasFactory;

    protected $table = 'core_branch';

    protected $fillable = [
        'uuid',
        'branch_code',
        'branch_name',
        'fulladdress',
        'immediate_head',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get users belonging to this branch
     */
    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'id');
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Get the creator of this branch
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the updater of this branch
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
