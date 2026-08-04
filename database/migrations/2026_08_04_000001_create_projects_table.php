<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('eyebrow')->nullable();
            $table->text('summary');
            $table->longText('content')->nullable();
            $table->string('role')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->json('technologies')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('accent', 20)->default('#d7ff3f');
            $table->string('github_url')->nullable();
            $table->string('live_url')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
