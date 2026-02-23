<?php

namespace App\Models\Tracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingDocument extends Model
{
    use HasFactory;

    protected $table = 'tms_tracking_documents';

    protected $fillable = [
        'warehouse_id',
        'tracking_header_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'content_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_date' => 'datetime',
    ];



    /**
     * Get the tracking header that owns the tracking document.
     */
    public function trackingHeader(): BelongsTo
    {
        return $this->belongsTo(TrackingHeader::class, 'tracking_header_id');
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by tracking header.
     */
    public function scopeByTrackingHeader($query, $trackingHeaderId)
    {
        return $query->where('tracking_header_id', $trackingHeaderId);
    }

    /**
     * Scope a query to filter by document type.
     */
    public function scopeByDocumentType($query, $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
