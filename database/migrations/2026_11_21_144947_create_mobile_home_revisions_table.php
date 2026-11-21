<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_home_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained('mobile_home_documents')
                ->cascadeOnDelete();

            $table->unsignedInteger('version');
            $table->json('payload');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('published_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('restored_from_revision_id')->nullable();

            $table->timestamps();

            $table->unique(
                ['document_id', 'version'],
                'mobile_home_revisions_document_version_unique'
            );

            $table->index(
                ['document_id', 'published_at'],
                'mobile_home_revisions_document_published_index'
            );

            $table->foreign('restored_from_revision_id')
                ->references('id')
                ->on('mobile_home_revisions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_home_revisions');
    }
};
