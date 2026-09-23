<?php

namespace App\Services;

use App\Models\MailboxAccount;
use App\Models\MailboxEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email as MimeEmail;
use Webklex\PHPIMAP\Client as ImapClient;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\MessageHeaderFetchingException;
use Webklex\PHPIMAP\Message as ImapMessage;

class MailboxService
{
    public const DEFAULT_FOLDER = 'INBOX';
    public const TRASH_FOLDER = 'Trash';
    public const SENT_FOLDER = 'Sent';
    public const DRAFTS_FOLDER = 'Drafts';
    public const ARCHIVE_FOLDER = 'Archive';

    // Each IMAP fetch pulls this many full message bodies (text+HTML, with
    // inline images base64-embedded) into memory at once before any of them
    // are saved — the library has no per-message lazy fetch, so this is the
    // one real lever on peak memory per sync/load-more request. Matches the
    // default list page size so one sync (or one "Load older" click) tracks
    // roughly one page of the UI instead of silently front-loading 200.
    public const DEFAULT_FETCH_LIMIT = 25;

    // Every mailbox this app can connect to is a @pakuwon.com address on the
    // same mail servers — the settings modal only asks for the local part of
    // the email and appends this domain, rather than letting a user type an
    // address the mail server would never accept anyway.
    public const DEFAULT_EMAIL_DOMAIN = 'pakuwon.com';

    // Every mailbox on this domain sits behind the same mail servers, so these
    // are pre-filled in the settings modal — only the user's own address,
    // username, and password actually differ per account.
    public const DEFAULT_IMAP_HOST = 'mail3.pakuwon.com';
    public const DEFAULT_IMAP_PORT = 993;
    public const DEFAULT_IMAP_ENCRYPTION = 'ssl';
    public const DEFAULT_SMTP_HOST = 'mx5.pakuwon.com';
    public const DEFAULT_SMTP_PORT = 465;
    public const DEFAULT_SMTP_ENCRYPTION = 'ssl';

    /**
     * Try connecting with the given IMAP settings. Returns null on success,
     * or a human-readable error message on failure. Used to validate a
     * mailbox account before saving it.
     */
    public static function testConnection(array $imapConfig): ?string
    {
        try {
            static::manager()->make($imapConfig)->connect();
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * Folder paths available in the account's mailbox, cached for an hour
     * since listing them requires an IMAP round trip.
     */
    public static function listFolders(MailboxAccount $account): array
    {
        return Cache::remember(static::folderCacheKey($account), now()->addHour(), function () use ($account) {
            $client = static::imapClient($account);
            $client->connect();

            $folders = static::flattenFolders($client->getFolders());

            $client->disconnect();

            return $folders;
        });
    }

    /**
     * getFolders() returns a hierarchy (each Folder's children nested inside
     * it), not a flat list — walk it so subfolders (e.g. "INBOX/APP System")
     * are included too, not just top-level folders.
     */
    protected static function flattenFolders(iterable $folders): array
    {
        $paths = [];

        foreach ($folders as $folder) {
            $paths[] = $folder->path;
            if ($folder->hasChildren()) {
                $paths = array_merge($paths, static::flattenFolders($folder->children));
            }
        }

        return $paths;
    }

    /**
     * Sync every known folder for this account. Returns the total number of
     * messages seen. Shares a single IMAP connection across every folder
     * (each connect() is a fresh TCP+TLS+login round trip) instead of
     * reconnecting per folder, which used to make this noticeably slower for
     * accounts with many folders.
     */
    public static function fetchAll(MailboxAccount $account, int $lookbackDays = 14, int $limit = self::DEFAULT_FETCH_LIMIT): int
    {
        $client = static::imapClient($account);
        $client->connect();

        // Refresh the folder list on every manual sync (over this same
        // connection) so a newly created server-side folder (e.g. a new
        // subfolder) shows up right away instead of waiting for the hourly
        // cache to expire.
        $folders = static::flattenFolders($client->getFolders());
        Cache::put(static::folderCacheKey($account), $folders, now()->addHour());

        $total = 0;
        foreach ($folders as $folder) {
            $total += static::fetchNew($account, $folder, $lookbackDays, $limit, $client);
        }

        $client->disconnect();

        return $total;
    }

    /**
     * Connect to the account's mailbox, pull recent messages from one
     * folder, and upsert them into mailbox_emails. Returns the number of
     * messages seen. Pass an already-connected $client (as fetchAll() does)
     * to reuse it instead of opening a new connection just for this folder.
     */
    public static function fetchNew(MailboxAccount $account, string $folderPath = self::DEFAULT_FOLDER, int $lookbackDays = 14, int $limit = self::DEFAULT_FETCH_LIMIT, ?ImapClient $client = null): int
    {
        static::raiseMemoryLimitForFetch();

        $ownsClient = $client === null;
        if ($ownsClient) {
            $client = static::imapClient($account);
            $client->connect();
        }

        $folder = $client->getFolder($folderPath);

        $lastSeenDate = MailboxEmail::where('username', $account->username)
            ->where('folder', $folderPath)
            ->max('email_date');

        // Re-check the last day even if we've synced before, in case messages
        // arrived slightly out of order; otherwise fall back to a wider window.
        $since = $lastSeenDate
            ? Carbon::parse($lastSeenDate)->subDay()
            : Carbon::now()->subDays($lookbackDays);

        $messages = $folder->messages()
            ->since($since)
            ->leaveUnread()
            ->limit($limit)
            ->fetchOrderDesc()
            ->get();

        $seen = 0;
        foreach ($messages as $message) {
            if (static::upsertMessage($account, $folderPath, $message)) {
                $seen++;
            }
        }

        if ($ownsClient) {
            $client->disconnect();
        }

        return $seen;
    }

    /**
     * Fetch a further page of older messages for one folder, for the "Load
     * older messages" button. fetchNew() only ever looks forward from the
     * newest synced message (bounded by $limit), so a folder with more than
     * $limit unsynced messages silently leaves the older ones behind
     * forever — this fetches backward from whatever is currently the oldest
     * synced message instead. Returns the number of messages seen and
     * whether the server likely still has more beyond that (a full page
     * came back).
     */
    public static function fetchOlder(MailboxAccount $account, string $folderPath, int $limit = self::DEFAULT_FETCH_LIMIT): array
    {
        static::raiseMemoryLimitForFetch();

        $client = static::imapClient($account);
        $client->connect();

        $folder = $client->getFolder($folderPath);

        $oldestSeenDate = MailboxEmail::where('username', $account->username)
            ->where('folder', $folderPath)
            ->min('email_date');

        $messages = $folder->messages()
            // IMAP's BEFORE is date-only precision, so add a day to still
            // catch messages earlier the same calendar day as the oldest one
            // already stored; re-upserting those is harmless (keyed on uid).
            ->when($oldestSeenDate, fn ($q) => $q->before(Carbon::parse($oldestSeenDate)->addDay()))
            ->leaveUnread()
            ->limit($limit)
            ->fetchOrderDesc()
            ->get();

        $seen = 0;
        foreach ($messages as $message) {
            if (static::upsertMessage($account, $folderPath, $message)) {
                $seen++;
            }
        }

        $client->disconnect();

        return ['fetched' => $seen, 'hasMore' => $messages->count() >= $limit];
    }

    /**
     * Compose and send a new email as the account owner, then drop a copy
     * into the Sent folder so it shows up like it would in any other mail
     * client. If $existingDraft is given, that draft is removed afterward.
     * $attachments are ['path'=>tmp upload path,...] or ['content'=>raw
     * bytes,...] entries (each with 'name' and optional 'mime'); $keepFromDraftIndexes
     * re-fetches those attachment indexes from $attachmentSource (the
     * message being edited/replied-to/forwarded — defaults to $existingDraft
     * when not given separately) to carry them over. $inReplyTo is the
     * original message's Message-ID header, for reply threading.
     */
    public static function send(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], ?MailboxEmail $existingDraft = null, array $attachments = [], array $keepFromDraftIndexes = [], ?MailboxEmail $attachmentSource = null, ?string $inReplyTo = null): void
    {
        $attachments = static::mergeCarriedOverAttachments($account, $attachmentSource ?? $existingDraft, $keepFromDraftIndexes, $attachments);
        $mime = static::buildMimeEmail($account, $subject, $bodyHtml, $to, $cc, $bcc, $attachments, $inReplyTo);

        static::mailer($account)->getSymfonyTransport()->send($mime);

        // The send above already succeeded — nothing from here on (appending
        // a Sent-folder copy, cleaning up the draft it replaced) may surface
        // as a "send failed" error, or the user could resend a duplicate
        // over what's really just a flaky IMAP connection. connect() itself
        // used to sit outside this guard, so a hiccup there alone was enough
        // to misreport a fully-delivered email as failed.
        try {
            $client = static::imapClient($account);
            $client->connect();

            try {
                try {
                    $client->getFolder(self::SENT_FOLDER)->appendMessage($mime->toString(), ['\\Seen']);
                } catch (\Throwable $e) {
                    // A missing Sent-folder copy isn't fatal.
                }

                if ($existingDraft && strcasecmp($existingDraft->folder, self::DRAFTS_FOLDER) === 0) {
                    static::expungeRemoteMessage($client, $existingDraft->folder, $existingDraft->uid);
                    $existingDraft->delete();
                }
            } finally {
                $client->disconnect();
            }
        } catch (\Throwable $e) {
            // Next sync will pick up the Sent copy; the old draft (if any)
            // gets cleaned up the next time it's resaved or sent.
        }

        // The send (and the Sent-folder append above) already succeeded —
        // this is only refreshing our local cache of the Sent folder, so a
        // hiccup here must not surface as a "send failed" error and risk the
        // user re-sending a duplicate.
        try {
            static::fetchNew($account, self::SENT_FOLDER);
        } catch (\Throwable $e) {
            // Next sync (manual or scheduled) will pick it up.
        }
    }

    /**
     * Save (or replace) a draft in the Drafts folder. IMAP has no in-place
     * edit, so updating a draft appends a new copy and removes the old one.
     */
    public static function saveDraft(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], ?MailboxEmail $existingDraft = null, array $attachments = [], array $keepFromDraftIndexes = [], ?MailboxEmail $attachmentSource = null): void
    {
        $attachments = static::mergeCarriedOverAttachments($account, $attachmentSource ?? $existingDraft, $keepFromDraftIndexes, $attachments);
        $mime = static::buildMimeEmail($account, $subject, $bodyHtml, $to, $cc, $bcc, $attachments);

        $client = static::imapClient($account);
        $client->connect();

        $client->getFolder(self::DRAFTS_FOLDER)->appendMessage($mime->toString(), ['\\Draft']);

        // The new draft copy above is already saved — cleaning up the old
        // copy it replaces must not surface as a "save failed" error, or a
        // retry after seeing that error would pile up further duplicate
        // drafts on top of the one that already saved fine.
        if ($existingDraft && strcasecmp($existingDraft->folder, self::DRAFTS_FOLDER) === 0) {
            try {
                static::expungeRemoteMessage($client, $existingDraft->folder, $existingDraft->uid);
                $existingDraft->delete();
            } catch (\Throwable $e) {
                // Next save/send of this draft will retry the cleanup.
            }
        }

        $client->disconnect();

        // The draft is already appended (and any previous copy already
        // removed) — this is only refreshing our local cache of the Drafts
        // folder, so a hiccup here must not surface as a "save failed" error
        // and risk the user re-saving a duplicate draft.
        try {
            static::fetchNew($account, self::DRAFTS_FOLDER);
        } catch (\Throwable $e) {
            // Next sync (manual or scheduled) will pick it up.
        }
    }

    /**
     * Move a message to another folder, on the server and locally. Returns
     * true on success.
     */
    public static function moveMessage(MailboxAccount $account, MailboxEmail $email, string $targetFolder): bool
    {
        $client = static::imapClient($account);
        $client->connect();

        $source = $client->getFolder($email->folder);
        $message = static::getMessageByUidWithRetry($source, $email->uid);

        if (!$message) {
            $client->disconnect();
            return false;
        }

        // UID MOVE isn't supported by every mail server ("command not
        // permitted with UID" on this one), so move is copy-then-delete.
        // copy() returns null (not an exception) on a handful of real
        // failure paths (e.g. a rejected copy) — treat that as a failure
        // rather than deleting the local record for a message that never
        // actually moved, which would otherwise make it vanish from the
        // app while still sitting untouched in its original server folder.
        //
        // copy() internally validates the IMAP COPY command *before* it
        // immediately reads back the new message's headers to hand us a
        // Message object — that read-back has no retry of its own (unlike
        // getMessageByUidWithRetry() above), so on a server slow to index a
        // just-copied message it throws "no headers found" even though the
        // copy itself already succeeded. Treat that specific exception as a
        // successful move with no object to cache locally — the next sync
        // of $targetFolder will pick the copy up normally.
        try {
            $copied = $message->copy($targetFolder);
            $copySucceeded = (bool) $copied;
        } catch (MessageHeaderFetchingException $e) {
            $copied = null;
            $copySucceeded = true;
        }
        if (!$copySucceeded) {
            $client->disconnect();
            return false;
        }

        $message->delete();
        $client->disconnect();

        $email->delete();
        if ($copied) {
            static::upsertMessage($account, $targetFolder, $copied);
        }

        return true;
    }

    /**
     * Delete a message. Anywhere except Trash this is a soft delete (move to
     * Trash); from Trash itself it's permanent.
     */
    public static function deleteMessage(MailboxAccount $account, MailboxEmail $email): bool
    {
        if (strcasecmp($email->folder, self::TRASH_FOLDER) === 0) {
            $client = static::imapClient($account);
            $client->connect();
            static::expungeRemoteMessage($client, $email->folder, $email->uid);
            $client->disconnect();

            $email->delete();

            return true;
        }

        return static::moveMessage($account, $email, self::TRASH_FOLDER);
    }

    /**
     * Archive a message, creating the Archive folder on first use.
     */
    public static function archiveMessage(MailboxAccount $account, MailboxEmail $email): bool
    {
        static::ensureFolderExists($account, self::ARCHIVE_FOLDER);

        return static::moveMessage($account, $email, self::ARCHIVE_FOLDER);
    }

    /**
     * Create a new folder (optionally nested, e.g. "INBOX/Clients") on the
     * server, and drop the cached folder list so it shows up right away.
     */
    public static function createFolder(MailboxAccount $account, string $folderPath): void
    {
        $client = static::imapClient($account);
        $client->connect();
        $client->createFolder($folderPath);
        $client->disconnect();

        Cache::forget(static::folderCacheKey($account));
    }

    /**
     * Delete a folder on the server. Callers are expected to have already
     * confirmed it has no subfolders (see MailboxController::deleteFolder) —
     * most IMAP servers reject deleting a folder that still has children.
     * Any locally cached messages under that folder are removed too, since
     * they'd otherwise linger in mailbox_emails pointing at a folder that no
     * longer exists.
     */
    public static function deleteFolder(MailboxAccount $account, string $folderPath): void
    {
        $client = static::imapClient($account);
        $client->connect();
        $client->deleteFolder($folderPath);
        $client->disconnect();

        Cache::forget(static::folderCacheKey($account));

        MailboxEmail::where('username', $account->username)->where('folder', $folderPath)->delete();
    }

    /**
     * fetchNew()/fetchOlder() pull up to DEFAULT_FETCH_LIMIT full message
     * bodies (inline images base64-embedded) into memory at once — the
     * web server's default memory_limit (commonly 128M) is tight enough
     * that a handful of image-heavy messages can still exhaust it even at
     * that page-sized batch, which used to surface as a raw PHP fatal
     * error on the Sync / Load older buttons instead of a clean failure
     * message. Only raises the limit, never lowers one already higher
     * (e.g. the CLI scheduler's own php.ini setting).
     */
    protected static function raiseMemoryLimitForFetch(): void
    {
        $target = 512 * 1024 * 1024;
        $current = static::iniBytes((string) ini_get('memory_limit'));

        if ($current !== -1 && $current < $target) {
            ini_set('memory_limit', '512M');
        }
    }

    /**
     * Parses a php.ini memory-size value ("128M", "1G", "-1" for unlimited)
     * into bytes.
     */
    protected static function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    protected static function manager(): ClientManager
    {
        return new ClientManager(config('imap'));
    }

    protected static function imapClient(MailboxAccount $account): ImapClient
    {
        return static::manager()->make([
            'host'            => $account->imap_host,
            'port'            => $account->imap_port,
            'protocol'        => 'imap',
            'encryption'      => $account->imap_encryption,
            'validate_cert'   => $account->imap_validate_cert,
            'username'        => $account->imap_username,
            'password'        => $account->imap_password,
            'authentication'  => null,
            'proxy' => [
                'socket'          => null,
                'request_fulluri' => false,
                'username'        => null,
                'password'        => null,
            ],
            'timeout'    => 30,
            'extensions' => [],
        ]);
    }

    protected static function mailer(MailboxAccount $account)
    {
        return Mail::build([
            'transport' => 'smtp',
            'host'      => $account->smtp_host,
            'port'      => $account->smtp_port,
            'encryption' => $account->smtp_encryption,
            'username'  => $account->imap_username,
            'password'  => $account->imap_password,
        ]);
    }

    protected static function folderCacheKey(MailboxAccount $account): string
    {
        return "mailbox_folders_{$account->id}";
    }

    /**
     * $bodyHtml is the rich-text (Quill) HTML the user composed; a plain-text
     * fallback part is derived from it automatically. $attachments entries are
     * either ['path' => tmp upload path, 'name', 'mime'] (fresh uploads) or
     * ['content' => raw bytes, 'name', 'mime'] (carried over from a draft).
     */
    protected static function buildMimeEmail(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], array $attachments = [], ?string $inReplyTo = null): MimeEmail
    {
        $plainText = trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/div|\/li)\s*\/?>/i', "\n", $bodyHtml))));

        $mime = (new MimeEmail())
            ->from($account->email)
            ->subject($subject !== '' ? $subject : '(no subject)')
            ->text($plainText)
            ->html($bodyHtml)
            ->date(new \DateTimeImmutable());

        if ($inReplyTo) {
            // addIdHeader wants the bare id (no surrounding <>), whereas the
            // stored message_id already has them from the original header.
            $bareId = trim($inReplyTo, '<> ');
            $mime->getHeaders()->addIdHeader('In-Reply-To', $bareId);
            $mime->getHeaders()->addIdHeader('References', $bareId);
        }

        foreach ($to as $address) {
            $mime->addTo($address);
        }
        foreach ($cc as $address) {
            $mime->addCc($address);
        }
        foreach ($bcc as $address) {
            $mime->addBcc($address);
        }

        foreach ($attachments as $attachment) {
            $name = $attachment['name'] ?? 'attachment';
            $attachmentMime = $attachment['mime'] ?? null;
            if (isset($attachment['path'])) {
                $mime->attachFromPath($attachment['path'], $name, $attachmentMime);
            } elseif (isset($attachment['content'])) {
                $mime->attach($attachment['content'], $name, $attachmentMime);
            }
        }

        return $mime;
    }

    /**
     * Re-fetch specific attachments (by index) from another message on the
     * server ($source — an existing draft being re-saved, or the original
     * message being forwarded) and merge them alongside freshly uploaded
     * ones, so re-saving a draft or forwarding a message doesn't silently
     * drop attachments the user chose to keep.
     */
    protected static function mergeCarriedOverAttachments(MailboxAccount $account, ?MailboxEmail $source, array $keepIndexes, array $newAttachments): array
    {
        if (!$source || empty($keepIndexes)) {
            return $newAttachments;
        }

        $carried = static::loadAttachments($account, $source->folder, $source->uid, $keepIndexes);

        foreach ($carried as $item) {
            $newAttachments[] = ['content' => $item['content'], 'name' => $item['name'], 'mime' => $item['mime']];
        }

        return $newAttachments;
    }

    /**
     * List (or fetch the raw bytes of) attachments on a live message, keyed
     * by their position among that message's attachments. Used both to show
     * "N attachment(s)" with download links when reading a message, and to
     * carry a draft's attachments forward when it's re-saved/sent.
     */
    public static function loadAttachments(MailboxAccount $account, string $folderPath, int $uid, ?array $onlyIndexes = null): array
    {
        $client = static::imapClient($account);
        $client->connect();
        $message = static::getMessageByUidWithRetry($client->getFolder($folderPath), $uid);
        $client->disconnect();

        if (!$message) {
            return [];
        }

        $items = collect($message->getAttachments())->values()->map(function ($att, $i) {
            return [
                'index'   => $i,
                'name'    => $att->name ?: "attachment-{$i}",
                'size'    => (int) ($att->size ?? strlen((string) $att->content)),
                'mime'    => $att->content_type ?: 'application/octet-stream',
                'content' => (string) $att->content,
            ];
        });

        if ($onlyIndexes !== null) {
            $items = $items->filter(fn ($item) => in_array($item['index'], $onlyIndexes, true));
        }

        return $items->values()->all();
    }

    protected static function upsertMessage(MailboxAccount $account, string $folderPath, ImapMessage $message): bool
    {
        $uid = (int) $message->uid;
        if ($uid <= 0) {
            return false;
        }

        $from = $message->from?->first();
        $textBody = (string) $message->getTextBody();
        $htmlBody = (string) $message->getHTMLBody();
        if ($htmlBody !== '') {
            $htmlBody = static::embedInlineImages($htmlBody, $message);
        }
        $preview = trim(mb_substr(static::textPreview($textBody, $htmlBody), 0, 300));

        MailboxEmail::updateOrCreate(
            [
                'mailbox' => $account->email,
                'folder'  => $folderPath,
                'uid'     => $uid,
            ],
            [
                'username'        => $account->username,
                'message_id'      => (string) ($message->message_id ?? ''),
                'subject'         => (string) ($message->subject ?: '(no subject)'),
                'from_address'    => $from->mail ?? null,
                'from_name'       => $from->personal ?? null,
                'to_address'      => (string) ($message->to ?? ''),
                'cc_address'      => (string) ($message->cc ?? ''),
                'bcc_address'     => (string) ($message->bcc ?? ''),
                'email_date'      => $message->date?->toDate(),
                'body_preview'    => $preview,
                'body_html'       => $htmlBody ?: null,
                'body_text'       => $textBody ?: null,
                'has_attachments' => $message->hasAttachments(),
            ]
        );

        return true;
    }

    /**
     * Plain-text snippet for the email list preview. Falls back to the HTML
     * body when there's no text part (common for marketing/newsletter mail),
     * but strip_tags() alone leaves <style>/<script> *content* behind (it
     * only removes the tags), which used to surface as raw CSS/JS garbage in
     * the preview for HTML-only emails — strip those blocks first.
     */
    protected static function textPreview(string $textBody, string $htmlBody): string
    {
        if ($textBody !== '') {
            return strip_tags($textBody);
        }

        $cleaned = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $htmlBody);

        return html_entity_decode(strip_tags($cleaned), ENT_QUOTES | ENT_HTML5);
    }

    /**
     * Rewrite cid: references (inline/embedded images, e.g. a logo attached
     * as a related part) to base64 data URIs using the message's own
     * attachments, since a browser has no way to resolve "cid:" itself and
     * would otherwise just show a broken image icon for every such image.
     */
    protected static function embedInlineImages(string $htmlBody, ImapMessage $message): string
    {
        if (!str_contains($htmlBody, 'cid:')) {
            return $htmlBody;
        }

        $attachments = collect($message->getAttachments())->filter(fn ($att) => !empty($att->id));
        if ($attachments->isEmpty()) {
            return $htmlBody;
        }

        return preg_replace_callback(
            '/cid:([^"\'\s)]+)/i',
            function ($matches) use ($attachments) {
                $contentId = trim($matches[1], '<>');
                $attachment = $attachments->first(fn ($att) => $att->id === $contentId);
                if (!$attachment) {
                    return $matches[0];
                }

                $mime = $attachment->content_type ?: 'application/octet-stream';
                return 'data:' . $mime . ';base64,' . base64_encode((string) $attachment->content);
            },
            $htmlBody
        );
    }

    protected static function expungeRemoteMessage(ImapClient $client, string $folderPath, int $uid): void
    {
        $message = static::getMessageByUidWithRetry($client->getFolder($folderPath), $uid);
        $message?->delete();
    }

    /**
     * Fetching a message immediately after it was appended (a fresh draft, a
     * just-sent copy) can occasionally race the server's own indexing and
     * throw "no headers found" — retry a few times before giving up.
     */
    protected static function getMessageByUidWithRetry(\Webklex\PHPIMAP\Folder $folder, int $uid, int $attempts = 6): ?ImapMessage
    {
        $lastException = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                return $folder->query()->getMessageByUid($uid);
            } catch (\Throwable $e) {
                $lastException = $e;
                usleep(700_000);
            }
        }

        throw $lastException;
    }

    protected static function ensureFolderExists(MailboxAccount $account, string $folderPath): void
    {
        if (in_array($folderPath, static::listFolders($account), true)) {
            return;
        }

        static::createFolder($account, $folderPath);
    }
}
