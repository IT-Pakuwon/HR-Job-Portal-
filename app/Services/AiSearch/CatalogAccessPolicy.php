<?php

namespace App\Services\AiSearch;

use Illuminate\Support\Collection;

class CatalogAccessPolicy
{
    public function allows(Collection $permissions, int $sourceId, object $table): bool
    {
        $matching = $permissions->filter(fn ($p) => ($p->source_id !== null || $p->schema_table_id !== null || $p->module_name !== null)
            && ($p->source_id === null || (int) $p->source_id === $sourceId)
            && ($p->schema_table_id === null || (int) $p->schema_table_id === (int) $table->id)
            && ($p->module_name === null || $p->module_name === $table->module_name)
        );
        // Row-scoped policies cannot be widened into unrestricted table access.
        if ($matching->contains(fn ($p) => $p->company_id !== null || $p->department_id !== null)) {
            return false;
        }

        return ! $matching->contains('permission_type', 'DENY') && $matching->contains('permission_type', 'READ');
    }
}
