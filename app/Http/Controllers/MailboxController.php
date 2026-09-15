<?php

namespace App\Http\Controllers;

use App\Models\MailboxAccount;
use App\Models\MailboxEmail;
use App\Services\MailboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailboxController extends Controller
{
    protected const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request)
    {
        return view('pages.mailbox.index', $this->buildViewData($request));
    }

    /**
     * Same data as index(), rendered as just the folder-sidebar + email-list
     * markup (no layout). Used for AJAX folder switches, search, pagination,
     * and per-page changes so the URL still updates via pushState but the
     * page doesn't do a full reload.
     */
    public function panel(Request $request)
    {
        $data = $this->buildViewData($request);
        abort_if(!$data['account'], 422, 'Connect a mailbox first.');

        return view('pages.mailbox._panel', $data);
    }

    protected function buildViewData(Request $request): array
    {
        $account = $this->currentAccount($request);

        if (!$account) {
            return [
                'account'      => null,
                'emails'       => null,
                'folders'      => [],
                'folder'       => MailboxService::DEFAULT_FOLDER,
                'folderCounts' => collect(),
                'search'       => '',
                'perPage'      => 10,
            ];
        }

        $folders = MailboxService::listFolders($account);
        $folder = $request->get('folder', MailboxService::DEFAULT_FOLDER);
        if (!in_array($folder, $folders, true)) {
            $folder = $folders[0] ?? MailboxService::DEFAULT_FOLDER;
        }

        $search = trim((string) $request->get('q'));

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $emails = MailboxEmail::query()
            ->where('username', $account->username)
            ->where('folder', $folder)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhere('from_address', 'like', "%{$search}%")
                        ->orWhere('from_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('email_date')
            // Only the columns the list view actually renders — body_html/body_text
            // are long-text blobs (a full email's HTML) that were otherwise getting
            // pulled for every row just to show a list, even though the list never
            // displays them.
            ->select(['id', 'subject', 'from_address', 'from_name', 'email_date', 'body_preview', 'is_read'])
            ->paginate($perPage)
            // Force the resolved state (not just whatever happened to be in the
            // incoming URL) onto every pagination link, and always point them at
            // /mailbox — this endpoint is also hit as /mailbox/panel for the AJAX
            // partial, whose own request URL must never leak into these links.
            ->appends(array_filter(['folder' => $folder, 'per_page' => $perPage, 'q' => $search !== '' ? $search : null]))
            ->withPath(route('mailbox.index'));

        $folderCounts = MailboxEmail::query()
            ->where('username', $account->username)
            ->select('folder', DB::raw('count(*) as total'), DB::raw('sum(case when is_read = false then 1 else 0 end) as unread'))
            ->groupBy('folder')
            ->get()
            ->keyBy('folder');

        return compact('account', 'emails', 'search', 'folders', 'folder', 'folderCounts', 'perPage');
    }

    /**
     * Current account's connection settings (no password) for the settings modal.
     */
    public function accountSettings(Request $request)
    {
        $account = $this->currentAccount($request);

        return response()->json([
            'connected' => (bool) $account,
            'email'          => $account->email ?? '',
            'imap_host'      => $account->imap_host ?? '',
            'imap_port'      => $account->imap_port ?? 993,
            'imap_encryption' => $account->imap_encryption ?? 'ssl',
            'imap_username'  => $account->imap_username ?? '',
            'smtp_host'      => $account->smtp_host ?? '',
            'smtp_port'      => $account->smtp_port ?? 465,
            'smtp_encryption' => $account->smtp_encryption ?? 'ssl',
        ]);
    }

    public function saveAccountSettings(Request $request)
    {
        $data = $request->validate([
            'email'           => 'required|email',
            'imap_host'       => 'required|string|max:255',
            'imap_port'       => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,notls,starttls',
            'imap_username'   => 'required|string|max:255',
            'imap_password'   => 'nullable|string|max:255',
            'smtp_host'       => 'required|string|max:255',
            'smtp_port'       => 'required|integer|min:1|max:65535',
            'smtp_encryption' => 'required|in:ssl,tls,notls,starttls',
        ]);

        $username = $request->user()->username;
        $existing = MailboxAccount::where('username', $username)->first();

        if (empty($data['imap_password']) && !$existing) {
            return response()->json(['success' => false, 'message' => 'A mailbox password is required to connect.'], 422);
        }

        $testConfig = [
            'host'          => $data['imap_host'],
            'port'          => $data['imap_port'],
            'encryption'    => $data['imap_encryption'],
            'validate_cert' => true,
            'username'      => $data['imap_username'],
            'password'      => $data['imap_password'] ?: $existing?->imap_password,
            'authentication' => null,
            'proxy' => ['socket' => null, 'request_fulluri' => false, 'username' => null, 'password' => null],
            'timeout' => 30,
            'extensions' => [],
        ];

        $error = MailboxService::testConnection($testConfig);
        if ($error) {
            return response()->json(['success' => false, 'message' => 'Could not connect: ' . $error], 422);
        }

        $payload = [
            'email'           => $data['email'],
            'imap_host'       => $data['imap_host'],
            'imap_port'       => $data['imap_port'],
            'imap_encryption' => $data['imap_encryption'],
            'imap_validate_cert' => true,
            'imap_username'   => $data['imap_username'],
            'smtp_host'       => $data['smtp_host'],
            'smtp_port'       => $data['smtp_port'],
            'smtp_encryption' => $data['smtp_encryption'],
            'is_active'       => true,
        ];
        if (!empty($data['imap_password'])) {
            $payload['imap_password'] = $data['imap_password'];
        }

        MailboxAccount::updateOrCreate(['username' => $username], $payload);

        return response()->json(['success' => true, 'message' => 'Mailbox connected.']);
    }

    /**
     * Email content for the read/edit-draft modal (AJAX). Marks the message
     * as read, unless it's a draft (drafts don't have a read state).
     */
    public function content(Request $request, MailboxEmail $email)
    {
        $this->authorizeOwner($request, $email);

        if (!$email->is_read && strcasecmp($email->folder, MailboxService::DRAFTS_FOLDER) !== 0) {
            $email->update(['is_read' => true]);
        }

        return response()->json([
            'id'           => $email->id,
            'folder'       => $email->folder,
            'subject'      => $email->subject ?: '(no subject)',
            'from_name'    => $email->from_name,
            'from_address' => $email->from_address,
            'to_address'   => $email->to_address,
            'date'         => optional($email->email_date)->format('d M Y H:i'),
            'body_html'    => $email->body_html,
            'body_text'    => $email->body_text,
            'has_attachments' => $email->has_attachments,
        ]);
    }

    /**
     * Attachment metadata (name/size/mime) for a live message — fetched from
     * IMAP on demand, not stored locally, since attachment bytes aren't
     * persisted in mailbox_emails.
     */
    public function attachments(Request $request, MailboxEmail $email)
    {
        $account = $this->requireAccount($request);
        $this->authorizeOwner($request, $email);

        $items = MailboxService::loadAttachments($account, $email->folder, $email->uid);

        return response()->json([
            'attachments' => collect($items)->map(fn ($a) => [
                'index' => $a['index'],
                'name'  => $a['name'],
                'size'  => $a['size'],
                'mime'  => $a['mime'],
            ])->values(),
        ]);
    }

    public function downloadAttachment(Request $request, MailboxEmail $email, int $index)
    {
        $account = $this->requireAccount($request);
        $this->authorizeOwner($request, $email);

        $items = MailboxService::loadAttachments($account, $email->folder, $email->uid, [$index]);
        $attachment = collect($items)->first();
        abort_if(!$attachment, 404);

        return response($attachment['content'], 200, [
            'Content-Type'        => $attachment['mime'],
            'Content-Disposition' => 'attachment; filename="' . str_replace('"', '', $attachment['name']) . '"',
        ]);
    }

    public function send(Request $request)
    {
        $account = $this->requireAccount($request);
        $data = $this->validateCompose($request, requireTo: true, account: $account);

        try {
            MailboxService::send($account, $data['subject'], $data['body'], $data['to'], $data['cc'], $data['bcc'], $data['existingDraft'], $data['attachments'], $data['keepFromDraftIndexes']);
            return response()->json(['success' => true, 'message' => 'Email sent.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Send failed: ' . $e->getMessage()], 422);
        }
    }

    public function saveDraft(Request $request)
    {
        $account = $this->requireAccount($request);
        $data = $this->validateCompose($request, requireTo: false, account: $account);

        try {
            MailboxService::saveDraft($account, $data['subject'], $data['body'], $data['to'], $data['cc'], $data['bcc'], $data['existingDraft'], $data['attachments'], $data['keepFromDraftIndexes']);
            return response()->json(['success' => true, 'message' => 'Draft saved.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Save draft failed: ' . $e->getMessage()], 422);
        }
    }

    public function archive(Request $request, MailboxEmail $email)
    {
        $account = $this->requireAccount($request);
        $this->authorizeOwner($request, $email);

        try {
            MailboxService::archiveMessage($account, $email);
            return response()->json(['success' => true, 'message' => 'Email archived.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Archive failed: ' . $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, MailboxEmail $email)
    {
        $account = $this->requireAccount($request);
        $this->authorizeOwner($request, $email);

        $wasInTrash = strcasecmp($email->folder, MailboxService::TRASH_FOLDER) === 0;

        try {
            MailboxService::deleteMessage($account, $email);
            return response()->json([
                'success' => true,
                'message' => $wasInTrash ? 'Email permanently deleted.' : 'Email moved to Trash.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 422);
        }
    }

    public function sync(Request $request)
    {
        $account = $this->currentAccount($request);
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Connect a mailbox first.'], 422);
        }

        try {
            $count = MailboxService::fetchAll($account);
            return response()->json(['success' => true, 'message' => "Synced {$count} message(s)."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 422);
        }
    }

    public function unreadCount(Request $request)
    {
        $account = $this->currentAccount($request);
        if (!$account) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => MailboxEmail::where('username', $account->username)->where('is_read', false)->count(),
        ]);
    }

    protected function currentAccount(Request $request): ?MailboxAccount
    {
        $username = $request->user()?->username;
        if (!$username) {
            return null;
        }

        return MailboxAccount::where('username', $username)->where('is_active', true)->first();
    }

    protected function requireAccount(Request $request): MailboxAccount
    {
        $account = $this->currentAccount($request);
        abort_if(!$account, 422, 'Connect a mailbox first.');

        return $account;
    }

    protected function authorizeOwner(Request $request, MailboxEmail $email): void
    {
        abort_unless($email->username === $request->user()?->username, 403);
    }

    protected function validateCompose(Request $request, bool $requireTo, MailboxAccount $account): array
    {
        $request->validate([
            'to'                          => $requireTo ? 'required|string' : 'nullable|string',
            'cc'                          => 'nullable|string',
            'bcc'                         => 'nullable|string',
            'subject'                     => 'nullable|string|max:500',
            'body'                        => 'nullable|string|max:200000',
            'draft_id'                    => 'nullable|integer',
            'attachments'                 => 'nullable|array|max:10',
            'attachments.*'               => 'file|max:5120', // 5MB per file
            'keep_attachment_indexes'     => 'nullable|array',
            'keep_attachment_indexes.*'   => 'integer|min:0',
        ]);

        $parseAddresses = function (?string $raw) {
            if (!$raw) {
                return [];
            }

            return collect(preg_split('/[,;]+/', $raw))
                ->map(fn ($a) => trim($a))
                ->filter(fn ($a) => $a !== '' && filter_var($a, FILTER_VALIDATE_EMAIL))
                ->values()
                ->all();
        };

        $to = $parseAddresses($request->input('to'));
        if ($requireTo && empty($to)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'to' => 'Enter at least one valid recipient email address.',
            ]);
        }

        $existingDraft = null;
        if ($request->filled('draft_id')) {
            $existingDraft = MailboxEmail::where('id', $request->integer('draft_id'))
                ->where('username', $account->username)
                ->first();
        }

        $attachments = collect($request->file('attachments', []))
            ->filter()
            ->map(fn ($f) => [
                'path' => $f->getRealPath(),
                'name' => $f->getClientOriginalName(),
                'mime' => $f->getClientMimeType(),
            ])
            ->values()
            ->all();

        return [
            'to'                    => $to,
            'cc'                    => $parseAddresses($request->input('cc')),
            'bcc'                   => $parseAddresses($request->input('bcc')),
            'subject'               => (string) $request->input('subject', ''),
            'body'                  => (string) $request->input('body', ''),
            'existingDraft'         => $existingDraft,
            'attachments'           => $attachments,
            'keepFromDraftIndexes'  => array_map('intval', $request->input('keep_attachment_indexes', [])),
        ];
    }
}
