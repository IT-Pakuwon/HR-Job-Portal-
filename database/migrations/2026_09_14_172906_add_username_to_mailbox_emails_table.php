<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mailbox_emails', function (Blueprint $table) {
            $table->string('username')->nullable()->after('mailbox')->index();
        });

        // Backfill: every row synced so far belongs to the one mailbox
        // account that existed before per-user accounts (bedriamaail).
        DB::table('mailbox_emails')->whereNull('username')->update(['username' => 'bedriamaail']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mailbox_emails', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
