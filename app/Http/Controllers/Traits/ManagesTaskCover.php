<?php

namespace App\Http\Controllers\Traits;

use App\Http\Controllers\TrAttachmentController;
use App\Models\TrAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Task cover image (the banner on a Task's detail header + its board card),
// shared by PmTaskController (TSK) and TeamTaskController (TTK). The image
// is an ordinary tr_attachment row, but under its own doctype (TSKCOVER /
// TTKCOVER) so it never shows up in the task's File tab; the task points at
// it via cover_attachment_id. Served through attachments.stream, which
// applies the same lock check as a TSK's own files (see
// TrProjectTask::abortUnlessAccessible()).
// Expects the using controller to define logTask() and noun().
trait ManagesTaskCover
{
    public static function coverUrl($attachmentId): ?string
    {
        return $attachmentId ? route('attachments.stream', $attachmentId) : null;
    }

    // Add or replace — the previous cover (if any) is soft-deleted.
    private function saveCover(Request $request, Model $task, string $doctype)
    {
        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $uploaded = app(TrAttachmentController::class)->uploadInternal([
            'refnbr' => $task->task_id,
            'doctype' => $doctype,
            'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
            'created_by' => Auth::user()->username,
        ], [$request->file('cover')]);

        $newId = $uploaded['items'][0]['id'] ?? null;
        abort_unless($newId, 500, 'Could not upload the cover image.');

        $replacing = (bool) $task->cover_attachment_id;
        $this->retireCoverAttachment($task->cover_attachment_id);

        $task->update([
            'cover_attachment_id' => $newId,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $this->logTask($task, 'cover', ($replacing ? 'replaced the cover of the ' : 'added a cover to the ') . $this->noun($task));

        return response()->json([
            'success' => true,
            'cover_url' => self::coverUrl($newId),
            'message' => $replacing ? 'Cover replaced.' : 'Cover added.',
        ]);
    }

    private function removeCover(Model $task)
    {
        abort_unless($task->cover_attachment_id, 404, 'This task has no cover.');

        $this->retireCoverAttachment($task->cover_attachment_id);

        $task->update([
            'cover_attachment_id' => null,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $this->logTask($task, 'cover', 'removed the cover of the ' . $this->noun($task));

        return response()->json(['success' => true, 'cover_url' => null, 'message' => 'Cover removed.']);
    }

    private function retireCoverAttachment($attachmentId): void
    {
        if ($attachmentId) {
            TrAttachment::where('id', $attachmentId)->update(['status' => 'X']);
        }
    }
}
