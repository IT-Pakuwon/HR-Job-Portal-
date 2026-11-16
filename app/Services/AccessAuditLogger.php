<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

// Audit trail for the RBAC chain (role / menu / role-menu / access-right changes).
// Table is created on first use. Writing never throws — a failed log line must
// not undo the admin's save.
class AccessAuditLogger
{
    const CONN = 'pgsql2';
    const TABLE = 'sys_access_audit';

    private static function ensureTable(): void
    {
        $schema = Schema::connection(self::CONN);
        if ($schema->hasTable(self::TABLE)) {
            return;
        }
        $schema->create(self::TABLE, function ($t) {
            $t->bigIncrements('id');
            $t->string('entity_type', 20)->index();   // role | menu | role_menu | access_right
            $t->string('entity_id', 100)->index();
            $t->string('action', 30);                  // create | update | enable | disable | clone | grant | revoke
            $t->text('description');
            $t->text('changes')->nullable();            // json
            $t->string('created_by', 100)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public static function log(string $entityType, string $entityId, string $action, string $description, array $changes = []): void
    {
        try {
            self::ensureTable();
            DB::connection(self::CONN)->table(self::TABLE)->insert([
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'action'      => $action,
                'description' => $description,
                'changes'     => $changes ? json_encode($changes) : null,
                'created_by'  => Auth::user()?->username ?? 'system',
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Access audit log failed', ['entity' => "$entityType:$entityId", 'error' => $e->getMessage()]);
        }
    }

    public static function recent(int $limit = 200, ?string $type = null, ?string $entityId = null): array
    {
        try {
            self::ensureTable();
            return DB::connection(self::CONN)->table(self::TABLE)
                ->when($type, fn ($q) => $q->where('entity_type', $type))
                ->when($entityId, fn ($q) => $q->where('entity_id', $entityId))
                ->orderByDesc('id')->limit($limit)->get()
                ->map(function ($r) {
                    $r->changes = $r->changes ? json_decode($r->changes, true) : null;
                    return $r;
                })->all();
        } catch (\Throwable $e) {
            Log::warning('Access audit read failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
