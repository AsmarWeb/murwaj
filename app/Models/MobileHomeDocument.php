<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileHomeDocument extends Model
{
    protected $fillable = [
        'key',
        'draft_payload',
        'published_revision_id',
        'draft_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'draft_payload' => 'array',
        ];
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(
            MobileHomeRevision::class,
            'published_revision_id'
        );
    }
}
