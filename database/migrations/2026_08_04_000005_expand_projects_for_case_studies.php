<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('client')->nullable()->after('role');
            $table->string('cover_alt')->nullable()->after('cover_image');
            $table->string('video_url')->nullable()->after('live_url');
            $table->longText('challenge')->nullable()->after('content');
            $table->longText('solution')->nullable()->after('challenge');
            $table->longText('architecture')->nullable()->after('solution');
            $table->longText('results')->nullable()->after('architecture');
            $table->string('seo_title')->nullable()->after('results');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('og_image')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn([
                'client', 'cover_alt', 'video_url', 'challenge', 'solution',
                'architecture', 'results', 'seo_title', 'seo_description', 'og_image',
            ]);
        });
    }
};
