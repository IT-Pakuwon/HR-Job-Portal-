<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MailboxAccount;
use App\Services\MailboxService;

class FetchMailboxEmails extends Command
{
    protected $signature   = 'mailbox:fetch';
    protected $description = 'Fetch new emails for every connected mailbox account into mailbox_emails';

    public function handle(): void
    {
        $accounts = MailboxAccount::where('is_active', true)->get();

        foreach ($accounts as $account) {
            try {
                $count = MailboxService::fetchAll($account);
                $this->info("[{$account->username}] Synced {$count} message(s).");
            } catch (\Throwable $e) {
                $this->error("[{$account->username}] Mailbox fetch failed: " . $e->getMessage());
            }
        }
    }
}
