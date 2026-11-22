<?php

namespace App\Services;

use App\Http\Resources\ListingResource;
use App\Http\Resources\ShopResource;
use App\Models\Category;
use App\Models\Listing;
use App\Models\MobileHomeDocument;
use App\Models\MobileHomeRevision;
use App\Models\Section;
use App\Models\Shop;
use App\Services\Catalog\PublicCatalog;
use App\Services\Catalog\PublicFields;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileHomeService
{
    public function __construct(
        private readonly PublicCatalog $catalog
    ) {}

    public function published(): array
    {
        $document = MobileHomeDocument::query()
            ->where('key', 'home')
            ->with('publishedRevision')
            ->first();

        $payload = $document?->publishedRevision?->payload ?? [
            'sections' => [],
        ];

        return $this->render($payload);
    }

    public function preview(): array
    {
        $document = $this->document();

        return $this->render(
            $document->draft_payload ?? ['sections' => []]
        );
    }

    public function saveDraft(array $payload, int $userId): MobileHomeDocument
    {
        return DB::transaction(function () use ($payload, $userId) {
            $document = $this->lockedDocument();

            $document->forceFill([
                'draft_payload' => $payload,
                'draft_updated_by' => $userId,
            ])->save();

            return $document->fresh();
        });
    }

    public function publish(int $userId): MobileHomeRevision
    {
        return DB::transaction(function () use ($userId) {
            $document = $this->lockedDocument();

            if (! is_array($document->draft_payload)) {
                throw ValidationException::withMessages([
                    'draft' => ['There is no saved draft to publish.'],
                ]);
            }

            $version = ((int) MobileHomeRevision::query()
                ->where('document_id', $document->id)
                ->max('version')) + 1;

            $revision = MobileHomeRevision::query()->create([
                'document_id' => $document->id,
                'version' => $version,
                'payload' => $document->draft_payload,
                'created_by' => $userId,
                'published_by' => $userId,
                'published_at' => now(),
            ]);

            $document->forceFill([
                'published_revision_id' => $revision->id,
            ])->save();

            return $revision;
        });
    }

    public function restore(int $revisionId, int $userId): MobileHomeDocument
    {
        return DB::transaction(function () use ($revisionId, $userId) {
            $document = $this->lockedDocument();

            $source = MobileHomeRevision::query()
                ->where('document_id', $document->id)
                ->whereKey($revisionId)
                ->firstOrFail();

            // Restore into the draft only. Publishing remains a separate action.
            $document->forceFill([
                'draft_payload' => $source->payload,
                'draft_updated_by' => $userId,
            ])->save();

            return $document->fresh();
        });
    }

    public function revisions()
    {
        $document = MobileHomeDocument::query()
            ->where('key', 'home')
            ->first();

        if (! $document) {
            return collect();
        }

        return MobileHomeRevision::query()
            ->where('document_id', $document->id)
            ->orderByDesc('version')
            ->limit(100)
            ->get([
                'id',
                'document_id',
                'version',
                'created_by',
                'published_by',
                'published_at',
                'restored_from_revision_id',
                'created_at',
            ]);
    }

    private function document(): MobileHomeDocument
    {
        return MobileHomeDocument::query()
            ->where('key', 'home')
            ->first() ?? new MobileHomeDocument([
                'key' => 'home',
                'draft_payload' => ['sections' => []],
            ]);
    }

    private function lockedDocument(): MobileHomeDocument
    {
        MobileHomeDocument::query()->firstOrCreate(
            ['key' => 'home'],
            ['draft_payload' => ['sections' => []]]
        );

        return MobileHomeDocument::query()
            ->where('key', 'home')
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function render(array $payload): array
    {
        $sections = collect($payload['sections'] ?? [])
            ->filter(fn ($section) => is_array($section)
                && (bool) ($section['enabled'] ?? true))
            ->sortBy(fn ($section) => (int) ($section['position'] ?? 0))
            ->values()
            ->map(fn ($section) => $this->renderSection($section))
            ->values()
            ->all();

        return [
            'schema_version' => 1,
            'sections' => $sections,
        ];
    }

    private function renderSection(array $section): array
    {
        $type = $section['type'] ?? '';
        $limit = min(20, max(1, (int) ($section['limit'] ?? 10)));

        $result = [
            'id' => $section['id'] ?? null,
            'type' => $type,
            'title' => $section['title'] ?? null,
            'title_ar' => $section['title_ar'] ?? null,
            'position' => (int) ($section['position'] ?? 0),
        ];

        if (in_array($type, [
            'products_latest',
            'products_featured',
            'products_selected',
        ], true)) {
            $query = $this->catalog->listings();

            if ($type === 'products_selected') {
                $selectedIds = array_values(array_filter(
                    array_map('intval', $section['ids'] ?? [])
                ));

                $items = $query
                    ->whereIn('listings.id', $selectedIds)
                    ->limit($limit)
                    ->get();

                $positions = array_flip($selectedIds);

                $items = $items
                    ->sortBy(fn ($item) => $positions[$item->id] ?? PHP_INT_MAX)
                    ->values();
            } else {
                if ($type === 'products_featured') {
                    $query->where('listings.featured', true)
                        ->orderByDesc('listings.featured_at');
                } else {
                    $query->orderByDesc('listings.id');
                }

                $items = $query->limit($limit)->get();
            }

            $result['items'] = ListingResource::collection($items)->resolve();

            return $result;
        }

        if (in_array($type, ['shops_latest', 'shops_selected'], true)) {
            $query = $this->catalog->shops()
                ->with(PublicCatalogRelations::SHOP_RELATIONS);

            $selectedIds = [];

            if ($type === 'shops_selected') {
                $selectedIds = array_values(array_filter(
                    array_map('intval', $section['ids'] ?? [])
                ));

                $query->whereIn('shops.id', $selectedIds);
            } else {
                $query->orderByDesc('shops.id');
            }

            $shops = $query->limit($limit)->get();

            // Keep the administrator's selected order without database-specific SQL.
            if ($type === 'shops_selected') {
                $positions = array_flip($selectedIds);

                $shops = $shops
                    ->sortBy(fn ($shop) => $positions[$shop->id] ?? PHP_INT_MAX)
                    ->values();
            }

            $result['items'] = ShopResource::collection($shops)->resolve();

            return $result;
        }

        if ($type === 'categories') {
            $query = Category::query()
                ->where('status', 'active')
                ->where('parent_id', 0)
                ->orderBy('id');

            if (! empty($section['ids'])) {
                $query->whereIn('id', array_map('intval', $section['ids']));
            }

            $result['items'] = $query->limit($limit)->get([
                'id',
                'category_name',
                'category_name_ar',
                'slug',
                'image',
                'section_id',
            ])->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->category_name,
                'name_ar' => $category->category_name_ar,
                'slug' => $category->slug,
                'image' => PublicFields::media($category->image),
                'section_id' => $category->section_id,
            ])->values()->all();

            return $result;
        }

        if ($type === 'catalog_sections') {
            $result['items'] = Section::query()
                ->where('status', 'active')
                ->orderBy('id')
                ->limit($limit)
                ->get(['id', 'name', 'name_ar', 'slug', 'image'])
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'slug' => $item->slug,
                    'image' => PublicFields::media($item->image),
                ])
                ->values()
                ->all();

            return $result;
        }

        if ($type === 'banner') {
            $image = $section['image'] ?? null;

            $result['image'] = is_string($image) && $image !== ''
                ? (preg_match('/^https?:\\/\\//i', $image)
                    ? $image
                    : url(Storage::disk('public')->url($image)))
                : null;

            $result['content'] = $section['content'] ?? null;
            $result['content_ar'] = $section['content_ar'] ?? null;
            $result['button_text'] = $section['button_text'] ?? null;
            $result['button_text_ar'] = $section['button_text_ar'] ?? null;
            $result['button_url'] = $section['button_url'] ?? null;

            return $result;
        }

        if ($type === 'editorial') {
            $result['content'] = $section['content'] ?? null;
            $result['content_ar'] = $section['content_ar'] ?? null;
            $result['button_text'] = $section['button_text'] ?? null;
            $result['button_text_ar'] = $section['button_text_ar'] ?? null;
            $result['button_url'] = $section['button_url'] ?? null;

            return $result;
        }

        return $result;
    }
}

/**
 * Keeps the resource eager-load list in one place.
 */
final class PublicCatalogRelations
{
    public const SHOP_RELATIONS = [
        'section',
        'country',
        'state',
        'city',
    ];
}
