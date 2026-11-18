<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Support\TenancySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/** Generic CRUD over the legacy tenancy master tables described in App\Support\TenancySettings. */
class TsSettingController extends Controller
{
    private function db()
    {
        return DB::connection('mysql5');
    }

    public function index()
    {
        abort_unless((bool) Auth::user()?->isPrimaryAdmin(), 403);

        $defs = collect(TenancySettings::all())->map(fn ($d, $k) => [
            'key' => $k, 'label' => $d['label'], 'group' => $d['group'], 'icon' => $d['icon'],
            'desc' => $d['desc'], 'fields' => $d['fields'],
        ])->values()->all();

        return view('pages.tenancy.settings', ['defs' => $defs]);
    }

    /** Dropdown sources + datalist suggestions for the form. */
    public function lookups()
    {
        $db = $this->db();

        $suggest = [];
        foreach (TenancySettings::all() as $key => $d) {
            foreach ($d['fields'] as $f) {
                if (! empty($f['suggest'])) {
                    $suggest[$key][$f['name']] = $db->table($d['table'])->whereNotNull($f['name'])->where($f['name'], '<>', '')
                        ->distinct()->orderBy($f['name'])->limit(50)->pluck($f['name']);
                }
            }
        }

        return response()->json([
            'sites'       => $db->table('mssite')->where('status', 'A')->orderBy('sitename')->get(['siteid', 'sitename']),
            'doctypes'    => $db->table('msdoctype')->where('status', 'A')->orderBy('doctype')->get(['doctype', 'documentname']),
            'departments' => $db->table('msdepartment')->where('status', 'A')->whereNotNull('departmentid')->distinct()->orderBy('departmentid')->pluck('departmentid'),
            'suggest'     => $suggest,
        ]);
    }

    public function json(string $key)
    {
        $d = TenancySettings::get($key);
        $cols = array_merge(['id'], array_column($d['fields'], 'name'), ['status']);

        return response()->json(['data' => $this->db()->table($d['table'])->orderByDesc('id')->get($cols)]);
    }

    public function edit(string $key, $id)
    {
        $d = TenancySettings::get($key);
        $row = $this->db()->table($d['table'])->where('id', $id)->first();
        abort_unless($row, 404);

        return response()->json($row);
    }

    public function store(Request $request, string $key)
    {
        $d = TenancySettings::get($key);
        $data = $request->validate($this->rules($d));
        $this->checkUnique($d, $data, null);

        $by = Auth::user()->username ?? 'system';
        $row = $this->clean($d, $data) + [
            'status'              => 'A',
            'created_user'        => $by,
            'created_datetime'    => now(),
            'lastupdate_user'     => $by,
            'lastupdate_datetime' => now(),
        ];

        $id = $this->db()->table($d['table'])->insertGetId($row);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, string $key, $id)
    {
        $d = TenancySettings::get($key);
        abort_unless($this->db()->table($d['table'])->where('id', $id)->exists(), 404);

        $data = $request->validate($this->rules($d));
        $this->checkUnique($d, $data, $id);

        $this->db()->table($d['table'])->where('id', $id)->update($this->clean($d, $data) + [
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Request $request, string $key, $id)
    {
        $d = TenancySettings::get($key);
        $request->validate(['status' => ['required', Rule::in(['A', 'X'])]]);

        $this->db()->table($d['table'])->where('id', $id)->update([
            'status'              => $request->status,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }

    private function rules(array $d): array
    {
        $rules = [];
        foreach ($d['fields'] as $f) {
            $r = [$f['required'] ? 'required' : 'nullable'];
            switch ($f['type']) {
                case 'integer': $r[] = 'integer'; break;
                case 'number':  $r[] = 'numeric'; break;
                case 'time':    $r[] = 'date_format:H:i,H:i:s'; break;
                case 'site':    $r[] = 'string'; $r[] = 'max:5'; $r[] = 'exists:mysql5.mssite,siteid'; break;
                case 'doctype': $r[] = 'string'; $r[] = 'max:5'; $r[] = 'exists:mysql5.msdoctype,doctype'; break;
                default:        $r[] = 'string'; if (! empty($f['max'])) $r[] = 'max:' . $f['max'];
            }
            $rules[$f['name']] = $r;
        }

        return $rules;
    }

    private function checkUnique(array $d, array $data, $ignoreId): void
    {
        foreach ($d['unique'] ?? [] as $col) {
            $q = $this->db()->table($d['table'])->where($col, $data[$col] ?? null);
            if ($ignoreId) $q->where('id', '<>', $ignoreId);
            if ($q->exists()) {
                abort(response()->json(['message' => 'The ' . $col . ' already exists.', 'errors' => [$col => ['Already exists']]], 422));
            }
        }
    }

    /** Only known fields; empty strings become NULL. */
    private function clean(array $d, array $data): array
    {
        $out = [];
        foreach ($d['fields'] as $f) {
            $v = $data[$f['name']] ?? null;
            $out[$f['name']] = ($v === '' ? null : $v);
        }

        return $out;
    }
}
