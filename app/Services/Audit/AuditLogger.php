<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function log(?User $actor, string $action, Model $auditable, array $context = []): AuditLog
    {
        return AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'context_json' => $context,
        ]);
    }
}
