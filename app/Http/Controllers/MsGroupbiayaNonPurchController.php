<?php

namespace App\Http\Controllers;

use App\Models\MsGroupbiayaNonPurch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MsGroupbiayaNonPurchController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $lastId = MsGroupbiayaNonPurch::query()
            ->where('groupbiaya_id', 'like', 'GB%')
            ->orderByRaw("CAST(REGEXP_REPLACE(groupbiaya_id, '[^0-9]', '', 'g') AS INTEGER) DESC")
            ->value('groupbiaya_id');

        $nextNumber = 1;

        if ($lastId) {
            $num = (int) preg_replace('/[^0-9]/', '', $lastId);
            $nextNumber = $num + 1;
        }

        $nextGroupbiayaId = 'GB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('pages.groupbiayanonpurch.groupbiayanonpurch', compact('nextGroupbiayaId'));
    }
   

    public function json()
    {
        $rows = MsGroupbiayaNonPurch::query()
            ->select([
                'id',
                'groupbiaya_id',
                'groupbiayadescr',
                'is_budget',
                'is_deposit',
                'status',
                'created_by',
                'created_at',
                'updated_by',
                'updated_at',
            ])
            ->orderBy('groupbiaya_id', 'asc')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $request->validate([
            'groupbiayadescr' => ['required', 'string', 'max:255'],
            'is_budget' => ['nullable', 'in:true,false,1,0,Y,N'],
            'is_deposit' => ['nullable', 'in:true,false,1,0,Y,N'],
        ]);

        $toBool = function ($v): bool {
            return in_array($v, [true, 1, '1', 'true', 'TRUE', 'Y'], true);
        };

        $lastId = MsGroupbiayaNonPurch::query()
            ->where('groupbiaya_id', 'like', 'GB%')
            ->orderByRaw("CAST(REGEXP_REPLACE(groupbiaya_id, '[^0-9]', '', 'g') AS INTEGER) DESC")
            ->value('groupbiaya_id');

        $nextNumber = 1;

        if ($lastId) {
            $num = (int) preg_replace('/[^0-9]/', '', $lastId);
            $nextNumber = $num + 1;
        }

        $groupbiayaId = 'GB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        MsGroupbiayaNonPurch::create([
            'groupbiaya_id' => $groupbiayaId,
            'groupbiayadescr' => $request->groupbiayadescr,
            'is_budget' => $toBool($request->input('is_budget', 'false')),
            'is_deposit' => $toBool($request->input('is_deposit', 'false')),
            'status' => 'A',
            'created_by' => $username,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Group Biaya Non Purchase saved successfully',
            'groupbiaya_id' => $groupbiayaId,
        ]);
    }

    public function edit($id)
    {
        $row = MsGroupbiayaNonPurch::findOrFail($id);

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurch::findOrFail($id);

        $request->validate([
            'groupbiayadescr' => ['required', 'string', 'max:255'],
            'is_budget' => ['nullable', 'in:true,false,1,0'],
            'is_deposit' => ['nullable', 'in:true,false,1,0'],
        ]);

        $toBool = function ($v): bool {
            return in_array($v, [true, 1, '1', 'true', 'TRUE'], true);
        };

        $row->update([
            // groupbiaya_id tidak diupdate
            'groupbiayadescr' => $request->groupbiayadescr,
            'is_budget' => $toBool($request->input('is_budget', 'false')),
            'is_deposit' => $toBool($request->input('is_deposit', 'false')),
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Group Biaya Non Purchase updated successfully',
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:A,X'],
        ]);

        $user = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurch::findOrFail($id);

        $row->update([
            'status' => $request->status,
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}