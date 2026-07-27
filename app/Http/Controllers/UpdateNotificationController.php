<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsNews;
use Illuminate\Http\Request;

class UpdateNotificationController extends Controller
{
    use HasAutonbr;

    public function json(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $notifications = MsNews::where('status', 'A')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get([
                'id',
                'news_title as title',
                'news_descr as description',
                'news_type as type',
                'created_by',
                'created_at',
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = $request->user();
        $username = $user->username ?? '';

        abort_unless($user && $user->isAdmin(), 403);

        $validated = $request->validate([
            'description' => 'required|string',
            'type' => 'required|string|in:new_feature,maintenance,information',
        ]);

        $typeTitles = [
            'new_feature' => 'New Feature',
            'maintenance' => 'Maintenance',
            'information' => 'Information',
        ];

        $year = (int) now()->format('Y');
        $month = now()->format('m');

        $auto = $this->nextAutonbr(
            'NEWS',
            $year,
            $month,
            $username,
            'Update Notification'
        );

        $newsId = 'NEWS'.substr((string) $year, 2).$month.sprintf('%04d', $auto['next']);

        $notification = MsNews::create([
            'news_id' => $newsId,
            'news_title' => $typeTitles[$validated['type']],
            'news_descr' => $validated['description'],
            'news_type' => $validated['type'],
            'status' => 'A',
            'created_by' => $username,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }
}
