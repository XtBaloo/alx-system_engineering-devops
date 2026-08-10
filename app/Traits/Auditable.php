<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::writeAuditLog('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $original = array_intersect_key($model->getOriginal(), $changes);
            static::writeAuditLog('updated', $model, $original, $changes);
        });

        static::deleted(function ($model) {
            static::writeAuditLog('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function writeAuditLog(string $action, $model, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_data' => $old,
            'new_data' => $new,
            'ip_address' => Request::ip(),
        ]);
    }
}
