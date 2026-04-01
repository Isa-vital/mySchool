<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::log('created', $model, class_basename($model) . ' created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changed = $model->getChanges();
            unset($changed['updated_at']);
            if (!empty($changed)) {
                $oldValues = array_intersect_key($model->getOriginal(), $changed);
                ActivityLog::log('updated', $model, class_basename($model) . ' updated', $oldValues, $changed);
            }
        });

        static::deleted(function ($model) {
            ActivityLog::log('deleted', $model, class_basename($model) . ' deleted', $model->getAttributes());
        });
    }
}
