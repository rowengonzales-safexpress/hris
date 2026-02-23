<?php

namespace App\Models\FRMS;

use App\Models\MainModel;
use App\Models\Core\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrmsDocument extends MainModel
{
    protected $table = 'frms_documents';

    protected $fillable = [
        'documentable_id',
        'documentable_type',
        'document_name',
        'original_filename',
        'file_path',
        'file_extension',
        'mime_type',
        'file_size',
        'description',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the FRMS form that owns this document
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(FrmsLiquidationdetail::class, 'liquidation_id', 'id');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    /**
     * Get the file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full file URL
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
