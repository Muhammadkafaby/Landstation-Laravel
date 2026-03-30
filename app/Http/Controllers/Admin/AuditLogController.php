<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Reports/Audit/Index', [
            'logs' => $this->auditQuery($request)
                ->paginate(15)
                ->withQueryString()
                ->through(fn (AuditLog $auditLog) => $this->mapAuditLog($auditLog)),
            'filters' => $this->filters($request),
            'actions' => [
                'booking.status_transitioned',
                'payment.verified',
                'service_session.started',
                'service_session.stopped',
            ],
            'auditableTypes' => [
                'App\Models\Booking',
                'App\Models\Invoice',
                'App\Models\ServiceSession',
            ],
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $logs = $this->auditQuery($request)
            ->get()
            ->map(fn (AuditLog $auditLog) => $this->mapAuditLog($auditLog));

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['created_at', 'actor_name', 'action', 'auditable_type', 'auditable_id', 'context_json']);

        foreach ($logs as $log) {
            fputcsv($stream, [
                $log['createdAt'],
                $log['actorName'],
                $log['action'],
                $log['auditableType'],
                $log['auditableId'],
                json_encode($log['context'], JSON_UNESCAPED_SLASHES),
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=audit-logs.csv',
        ]);
    }

    protected function auditQuery(Request $request): Builder
    {
        $filters = $this->filters($request);

        return AuditLog::query()
            ->with('actor:id,name')
            ->when($filters['action'] !== '', fn ($query) => $query->where('action', $filters['action']))
            ->when($filters['actor'] !== '', fn ($query) => $query->where('actor_user_id', (int) $filters['actor']))
            ->when($filters['auditable_type'] !== '', fn ($query) => $query->where('auditable_type', $filters['auditable_type']))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    protected function filters(Request $request): array
    {
        return [
            'action' => trim((string) $request->string('action')->toString()),
            'actor' => trim((string) $request->string('actor')->toString()),
            'auditable_type' => trim((string) $request->string('auditable_type')->toString()),
        ];
    }

    protected function mapAuditLog(AuditLog $auditLog): array
    {
        return [
            'id' => $auditLog->id,
            'createdAt' => optional($auditLog->created_at)->toIso8601String(),
            'actorId' => $auditLog->actor_user_id,
            'actorName' => $auditLog->actor?->name,
            'action' => $auditLog->action,
            'auditableType' => $auditLog->auditable_type,
            'auditableId' => $auditLog->auditable_id,
            'context' => $auditLog->context_json,
        ];
    }
}
