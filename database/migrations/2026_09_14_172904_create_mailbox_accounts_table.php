<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // app username that owns this mailbox connection
            $table->string('email');

            $table->string('imap_host');
            $table->unsignedInteger('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl');
            $table->boolean('imap_validate_cert')->default(true);
            $table->string('imap_username');
            $table->text('imap_password'); // encrypted at the model level

            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->default(465);
            $table->string('smtp_encryption')->default('ssl');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_accounts');
    }
};
