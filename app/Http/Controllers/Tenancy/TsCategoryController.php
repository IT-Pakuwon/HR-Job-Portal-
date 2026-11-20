<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TsCategoryController extends Controller
{
    public function json()
    {
        $categories = TsCategory::select(['id', 'name', 'type', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function options()
    {
        $categories = TsCategory::where('status', 'A')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return response()->json(['data' => $categories]);
    }

    public function types()
    {
        $types = TsCategory::whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
        ]);

        $user = Auth::user()->username ?? 'system';

        $category = TsCategory::create([
            'name'                => $data['name'],
            'type'                => $data['type'] ?? null,
            'status'              => 'A',
            'created_user'        => $user,
            'created_datetime'    => now(),
            'lastupdate_user'     => $user,
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function edit($id)
    {
        $category = TsCategory::findOrFail($id);

        return response()->json([
            'id'     => $category->id,
            'name'   => $category->name,
            'type'   => $category->type,
            'status' => $category->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = TsCategory::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
        ]);

        $category->update([
            'name'                => $data['name'],
            'type'                => $data['type'] ?? null,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $category = TsCategory::findOrFail($id);

        $category->update([
            'status'              => request('status') === 'A' ? 'A' : 'X',
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
