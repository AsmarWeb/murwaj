<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileHomeRevision extends Model
{
    protected $fillable = [
        'document_id',
        'version',
        'payload',
        'created_by',
        'published_by',
        'published_at',
        'restored_from_revision_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MobileHomeDocument::class, 'document_id');
    }
}
