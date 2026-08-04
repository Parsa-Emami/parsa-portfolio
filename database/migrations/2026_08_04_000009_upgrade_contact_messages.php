<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->string('ip_hash', 64)->nullable()->index()->after('ip_address');
            $table->longText('reply_message')->nullable()->after('user_agent');
            $table->timestamp('replied_at')->nullable()->index()->after('reply_message');
            $table->timestamp('archived_at')->nullable()->index()->after('replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropColumn(['ip_hash', 'reply_message', 'replied_at', 'archived_at']);
        });
    }
};
