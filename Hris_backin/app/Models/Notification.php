<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'app_id',
        'user_to',
        'title',
        'message',
        'type',
        'is_read',
        'action_url',
        'data'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class);
    }

    /**
     * Get the app that the notification belongs to.
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\CoreApp::class, 'app_id');
    }

    /**
     * Get the user that the notification is targeted to (sitehead user).
     */
    public function userTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class, 'user_to');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }
}
