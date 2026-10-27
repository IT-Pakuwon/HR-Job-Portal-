<?php

namespace App\Services\DataHub\Om;

/**
 * OM Dashboard mall registry + access rules. Malls/companies come from config/datahub.php.
 *
 *  - DIRECTORACCESS / DASH2ACCESS (config datahub.om.all_mall_roles) and primary admins
 *    see every mall and switch between them with the dropdown.
 *  - Everyone else (OMACCESS) only gets the malls of the companies assigned to them.
 */
class OmMalls
{
    /** @return array<string,array{name:string,label:string,cpny_id:string}> */
    public function config(): array
    {
        return config('datahub.om.malls', []);
    }

    /** @return array<string,string> mall code => mall name (as stored in BigQuery) */
    public function names(): array
    {
        return array_map(fn ($mall) => $mall['name'], $this->config());
    }

    public function exists(string $code): bool
    {
        return array_key_exists($code, $this->config());
    }

    public function canSwitchAll(): bool
    {
        $user = auth()->user();

        return $user->isPrimaryAdmin()
            || collect(config('datahub.om.all_mall_roles', []))->contains(fn ($role) => $user->hasRole($role));
    }

    /** @return array<string,string> mall code => mall name, limited to what the user may open */
    public function allowed(): array
    {
        if ($this->canSwitchAll()) {
            return $this->names();
        }

        $companies = array_map('strtoupper', auth()->user()->scopedCompanyIds());

        return collect($this->names())
            ->filter(fn ($name, $code) => in_array(strtoupper($this->config()[$code]['cpny_id']), $companies, true))
            ->all();
    }

    /** @return array<string,string> mall code => dropdown label, limited to what the user may open */
    public function options(): array
    {
        return collect($this->allowed())
            ->mapWithKeys(fn ($name, $code) => [$code => $this->config()[$code]['label'] ?? $name])
            ->all();
    }

    /** The mall opened when none is asked for: the first one the user may see (AW - Gandaria City for all-company roles). */
    public function default(): string
    {
        $allowed = $this->allowed();
        abort_if(empty($allowed), 403, 'Tidak ada mall yang dapat diakses.');

        return array_key_first($allowed);
    }

    /** For saved state: a mall the user can still open, otherwise their default (never an error). */
    public function resolveOrDefault(?string $mall): string
    {
        $code = strtoupper(trim((string) $mall));

        return array_key_exists($code, $this->allowed()) ? $code : $this->default();
    }

    /** No mall given -> the user's default mall; otherwise 404 for an unknown mall, 403 when their companies don't cover it. */
    public function resolve(?string $mall): string
    {
        if ($mall === null || trim($mall) === '') {
            return $this->default();
        }

        $code = strtoupper(trim($mall));

        abort_unless($this->exists($code), 404);
        abort_unless(array_key_exists($code, $this->allowed()), 403);

        return $code;
    }

    // SQL CASE that maps a BigQuery mall name to its mall code (ELSE keeps the raw value).
    public function codeCaseSql(): string
    {
        $when = '';
        foreach ($this->config() as $code => $mall) {
            $when .= " WHEN mall = '".str_replace("'", "\\'", $mall['name'])."' THEN '{$code}'";
        }

        return "CASE{$when} ELSE mall END";
    }
}
