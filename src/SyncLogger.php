<?php

namespace HoheiselIT\Lexoffice;

use HoheiselIT\Lexoffice\Models\LexofficeSyncLog;
use Throwable;

class SyncLogger
{
    public static function success(
        object $model,
        string $syncType,
        array $payload,
        array $response,
    ): LexofficeSyncLog {
        return LexofficeSyncLog::create([
            'model_type'   => get_class($model),
            'model_id'     => $model->getKey(),
            'lexoffice_id' => $response['id'] ?? null,
            'sync_type'    => $syncType,
            'status'       => 'success',
            'payload'      => $payload,
            'error'        => null,
        ]);
    }

    public static function failure(
        object $model,
        string $syncType,
        array $payload,
        Throwable $exception,
    ): LexofficeSyncLog {
        return LexofficeSyncLog::create([
            'model_type'   => get_class($model),
            'model_id'     => $model->getKey(),
            'lexoffice_id' => null,
            'sync_type'    => $syncType,
            'status'       => 'failed',
            'payload'      => $payload,
            'error'        => $exception->getMessage(),
        ]);
    }
}
