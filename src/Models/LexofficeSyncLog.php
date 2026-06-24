<?php

namespace HoheiselIT\Lexoffice\Models;

use Illuminate\Database\Eloquent\Model;

class LexofficeSyncLog extends Model
{
    protected $table = 'lexoffice_sync_log';

    protected $fillable = [
        'model_type',
        'model_id',
        'lexoffice_id',
        'sync_type',
        'status',
        'payload',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }
}
