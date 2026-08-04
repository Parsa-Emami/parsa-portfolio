<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('organization')->nullable();
            $table->string('location')->nullable();
            $table->date('started_at')->nullable()->index();
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(false)->index();
            $table->text('description')->nullable();
            $table->json('achievements')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
