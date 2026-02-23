<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends MainModel
{
    use HasFactory;

    protected $table = 'core_menu';

    protected $fillable = [
        'app_id',
        'parent_id',
        'name',
        'route',
        'icon',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the app that owns this menu
     */
    public function app()
    {
        return $this->belongsTo(CoreApp::class, 'app_id', 'id');
    }

    /**
     * Get the parent menu
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'id');
    }

    /**
     * Get the child menus
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->orderBy('sort_order');
    }

    /**
     * Get role menus for this menu
     */
    public function roleMenus() {
        return $this->hasMany(RoleMenu::class, 'menu_id', 'id');
    }

    /**
     * Get users that have access to this menu
     */
    public function users() {
        return $this->belongsToMany(User::class,'core_usermenu','menu_id','user_id');
    }

    /**
     * Scope a query to only include active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include top-level menus.
     */
    public function scopeTopLevel($query)
    {
        return $query->where('parent_id', 0);
    }

    /**
     * Get permission attribute for appends
     */
    public function getPermissionAttribute()
    {
        return 'manage'; // Default permission
    }
}
