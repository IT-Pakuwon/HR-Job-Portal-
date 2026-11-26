<?php

namespace App\Models;

// Chat messages of the Project module (db_das). Same columns and helpers as
// TrMessage, which keeps every other module's messages.
class TrProjectMessage extends TrMessage
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_message';

    // Project and Team "Message" tabs, Project Task and Team Task chats.
    public const DOCTYPES = ['PRJ', 'TEAM', 'TSK', 'TTK'];

    // The message model holding a doctype's thread, for code that serves
    // both the Project module and other documents.
    public static function modelFor(string $doctype): string
    {
        return in_array(strtoupper($doctype), self::DOCTYPES, true) ? self::class : TrMessage::class;
    }
}
