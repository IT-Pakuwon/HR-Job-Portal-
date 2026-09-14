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
use Webklex\PHPIMAP\Message as ImapMessage;

class MailboxService
{
    public const DEFAULT_FOLDER = 'INBOX';
    public const TRASH_FOLDER = 'Trash';
    public const SENT_FOLDER = 'Sent';
    public const DRAFTS_FOLDER = 'Drafts';
    public const ARCHIVE_FOLDER = 'Archive';

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
     * messages seen.
     */
    public static function fetchAll(MailboxAccount $account, int $lookbackDays = 14, int $limit = 200): int
    {
        // Refresh the folder list on every manual sync so a newly created
        // server-side folder (e.g. a new subfolder) shows up right away
        // instead of waiting for the hourly cache to expire.
        Cache::forget(static::folderCacheKey($account));

        $total = 0;
        foreach (static::listFolders($account) as $folder) {
            $total += static::fetchNew($account, $folder, $lookbackDays, $limit);
        }

        return $total;
    }

    /**
     * Connect to the account's mailbox, pull recent messages from one
     * folder, and upsert them into mailbox_emails. Returns the number of
     * messages seen.
     */
    public static function fetchNew(MailboxAccount $account, string $folderPath = self::DEFAULT_FOLDER, int $lookbackDays = 14, int $limit = 200): int
    {
        $client = static::imapClient($account);
        $client->connect();

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

        $client->disconnect();

        return $seen;
    }

    /**
     * Compose and send a new email as the account owner, then drop a copy
     * into the Sent folder so it shows up like it would in any other mail
     * client. If $existingDraft is given, that draft is removed afterward.
     * $attachments are ['path'=>tmp upload path,...] or ['content'=>raw
     * bytes,...] entries (each with 'name' and optional 'mime'); $keepFromDraftIndexes
     * re-fetches those attachment indexes from $existingDraft to carry them over.
     */
    public static function send(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], ?MailboxEmail $existingDraft = null, array $attachments = [], array $keepFromDraftIndexes = []): void
    {
        $attachments = static::mergeCarriedOverAttachments($account, $existingDraft, $keepFromDraftIndexes, $attachments);
        $mime = static::buildMimeEmail($account, $subject, $bodyHtml, $to, $cc, $bcc, $attachments);

        static::mailer($account)->getSymfonyTransport()->send($mime);

        $client = static::imapClient($account);
        $client->connect();

        try {
            $client->getFolder(self::SENT_FOLDER)->appendMessage($mime->toString(), ['\\Seen']);
        } catch (\Throwable $e) {
            // The send already succeeded; a missing Sent-folder copy isn't fatal.
        }

        if ($existingDraft && strcasecmp($existingDraft->folder, self::DRAFTS_FOLDER) === 0) {
            static::expungeRemoteMessage($client, $existingDraft->folder, $existingDraft->uid);
            $existingDraft->delete();
        }

        $client->disconnect();

        static::fetchNew($account, self::SENT_FOLDER);
    }

    /**
     * Save (or replace) a draft in the Drafts folder. IMAP has no in-place
     * edit, so updating a draft appends a new copy and removes the old one.
     */
    public static function saveDraft(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], ?MailboxEmail $existingDraft = null, array $attachments = [], array $keepFromDraftIndexes = []): void
    {
        $attachments = static::mergeCarriedOverAttachments($account, $existingDraft, $keepFromDraftIndexes, $attachments);
        $mime = static::buildMimeEmail($account, $subject, $bodyHtml, $to, $cc, $bcc, $attachments);

        $client = static::imapClient($account);
        $client->connect();

        $client->getFolder(self::DRAFTS_FOLDER)->appendMessage($mime->toString(), ['\\Draft']);

        if ($existingDraft && strcasecmp($existingDraft->folder, self::DRAFTS_FOLDER) === 0) {
            static::expungeRemoteMessage($client, $existingDraft->folder, $existingDraft->uid);
            $existingDraft->delete();
        }

        $client->disconnect();

        static::fetchNew($account, self::DRAFTS_FOLDER);
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
        $copied = $message->copy($targetFolder);
        if ($copied) {
            $message->delete();
        }
        $client->disconnect();

        $email->delete();

        if ($copied) {
            static::upsertMessage($account, $targetFolder, $copied);
        } else {
            // Fallback: couldn't confirm the new UID directly, resync the whole folder.
            static::fetchNew($account, $targetFolder);
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
    protected static function buildMimeEmail(MailboxAccount $account, string $subject, string $bodyHtml, array $to, array $cc = [], array $bcc = [], array $attachments = []): MimeEmail
    {
        $plainText = trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/div|\/li)\s*\/?>/i', "\n", $bodyHtml))));

        $mime = (new MimeEmail())
            ->from($account->email)
            ->subject($subject !== '' ? $subject : '(no subject)')
            ->text($plainText)
            ->html($bodyHtml)
            ->date(new \DateTimeImmutable());

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
     * Re-fetch specific attachments (by index) from an existing draft on the
     * server and merge them alongside freshly uploaded ones, so re-saving or
     * sending an edited draft doesn't silently drop its original attachments.
     */
    protected static function mergeCarriedOverAttachments(MailboxAccount $account, ?MailboxEmail $existingDraft, array $keepIndexes, array $newAttachments): array
    {
        if (!$existingDraft || empty($keepIndexes)) {
            return $newAttachments;
        }

        $carried = static::loadAttachments($account, $existingDraft->folder, $existingDraft->uid, $keepIndexes);

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
        $preview = trim(mb_substr(strip_tags($textBody ?: $htmlBody), 0, 300));

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
                'email_date'      => $message->date?->toDate(),
                'body_preview'    => $preview,
                'body_html'       => $htmlBody ?: null,
                'body_text'       => $textBody ?: null,
                'has_attachments' => $message->hasAttachments(),
            ]
        );

        return true;
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

        $client = static::imapClient($account);
        $client->connect();
        $client->createFolder($folderPath);
        $client->disconnect();

        Cache::forget(static::folderCacheKey($account));
    }
}
