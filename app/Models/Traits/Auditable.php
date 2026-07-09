<?php

namespace App\Models\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $module = $model->getAuditModule();
            app(AuditLogService::class)->logCreate($module, $model);
        });

        static::updated(function (Model $model) {
            if (!$model->getAuditIgnoreFields()) {
                $changes = $model->getAuditableChanges();
                if (!empty($changes['old']) && !empty($changes['new'])) {
                    $module = $model->getAuditModule();
                    app(AuditLogService::class)->logUpdate(
                        $module,
                        $model,
                        $changes['old'],
                        $changes['new'],
                    );
                }
            }
        });

        static::deleted(function (Model $model) {
            $module = $model->getAuditModule();
            app(AuditLogService::class)->logDelete($module, $model);
        });
    }

    protected function getAuditModule(): string
    {
        return $this->auditModule ?? $this->getTable();
    }

    protected function getAuditIgnoreFields(): array
    {
        return $this->auditIgnore ?? ['updated_at', 'created_at', 'remember_token'];
    }

    protected function getAuditableChanges(): array
    {
        $ignore = $this->getAuditIgnoreFields();
        $old = [];
        $new = [];

        foreach ($this->getDirty() as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }
            $oldValue = $this->getOriginal($field);
            if ($oldValue !== $newValue) {
                $old[$field] = $oldValue;
                $new[$field] = $newValue;
            }
        }

        return ['old' => $old, 'new' => $new];
    }
}
