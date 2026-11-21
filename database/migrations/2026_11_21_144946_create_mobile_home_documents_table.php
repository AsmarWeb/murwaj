<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_home_documents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->default('home')->unique();
            $table->json('draft_payload')->nullable();
            $table->unsignedBigInteger('published_revision_id')->nullable();

            $table->foreignId('draft_updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_home_documents');
    }
};
