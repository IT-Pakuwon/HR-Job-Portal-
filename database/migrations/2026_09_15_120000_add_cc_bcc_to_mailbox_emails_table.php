<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mailbox_emails', function (Blueprint $table) {
            $table->text('cc_address')->nullable()->after('to_address');
            $table->text('bcc_address')->nullable()->after('cc_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mailbox_emails', function (Blueprint $table) {
            $table->dropColumn(['cc_address', 'bcc_address']);
        });
    }
};
