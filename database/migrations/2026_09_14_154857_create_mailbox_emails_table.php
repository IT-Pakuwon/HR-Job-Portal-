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
        Schema::create('mailbox_emails', function (Blueprint $table) {
            $table->id();
            $table->string('mailbox')->index(); // which account this came from, e.g. bedriamaail@pakuwon.com
            $table->string('folder')->default('INBOX');
            $table->unsignedInteger('uid'); // IMAP UID within the folder
            $table->string('message_id')->nullable(); // RFC message-id, for dedup across UID changes
            $table->string('subject')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->text('to_address')->nullable();
            $table->timestamp('email_date')->nullable();
            $table->text('body_preview')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->unique(['mailbox', 'folder', 'uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_emails');
    }
};
