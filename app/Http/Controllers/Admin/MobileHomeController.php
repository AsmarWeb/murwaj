<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\MobileHomeDocument;
use App\Models\Shop;
use App\Services\MobileHomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use JsonException;

class MobileHomeController extends Controller
{
    public function index(MobileHomeService $home): View
    {
        $document = MobileHomeDocument::query()
            ->where('key', 'home')
            ->with('publishedRevision')
            ->first();

        $payload = $document?->draft_payload ?? ['sections' => []];

        $selectionOptions = [
            'products' => Listing::query()
                ->active()
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id', 'title', 'title_ar'])
                ->map(static fn ($item) => [
                    'id' => (int) $item->id,
                    'name' => $item->title,
                    'name_ar' => $item->title_ar,
                ])->all(),

            'shops' => Shop::query()
                ->publicCatalog()
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id', 'name', 'name_ar'])
                ->map(static fn ($item) => [
                    'id' => (int) $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                ])->all(),

            'categories' => Category::query()
                ->where('status', 'active')
                ->where('parent_id', 0)
                ->orderBy('category_name')
                ->limit(100)
                ->get(['id', 'category_name', 'category_name_ar'])
                ->map(static fn ($item) => [
                    'id' => (int) $item->id,
                    'name' => $item->category_name,
                    'name_ar' => $item->category_name_ar,
                ])->all(),
        ];

        return view('admin.mobile-home.index', [
            'sections' => is_array($payload['sections'] ?? null)
                ? $payload['sections']
                : [],
            'revisions' => $home->revisions(),
            'publishedRevision' => $document?->publishedRevision,
            'draftUpdatedAt' => $document?->updated_at,
            'selectionOptions' => $selectionOptions,
        ]);
    }

    public function saveDraft(
        Request $request,
        MobileHomeService $home
    ): RedirectResponse {
        try {
            $decoded = json_decode(
                (string) $request->input('payload_json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'payload_json' => 'The homepage configuration is not valid JSON.',
            ]);
        }

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'payload_json' => 'The homepage configuration must be an object.',
            ]);
        }

        $validated = Validator::make(
            ['payload' => $decoded],
            [
                'payload' => ['required', 'array'],
                'payload.sections' => ['required', 'array', 'max:30'],
                'payload.sections.*' => [
                    'required',
                    'array:id,type,enabled,position,limit,title,title_ar,ids,content,content_ar,button_text,button_text_ar,button_url,image',
                ],
                'payload.sections.*.id' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[A-Za-z0-9_-]+$/',
                    'distinct',
                ],
                'payload.sections.*.type' => [
                    'required',
                    Rule::in([
                        'products_latest',
                        'products_featured',
                        'shops_latest',
                        'shops_selected',
                        'categories',
                        'catalog_sections',
                        'editorial',
                        'banner',
                    ]),
                ],
                'payload.sections.*.enabled' => ['sometimes', 'boolean'],
                'payload.sections.*.position' => [
                    'required', 'integer', 'min:0', 'max:10000',
                ],
                'payload.sections.*.limit' => [
                    'sometimes', 'integer', 'min:1', 'max:20',
                ],
                'payload.sections.*.title' => ['nullable', 'string', 'max:160'],
                'payload.sections.*.title_ar' => ['nullable', 'string', 'max:160'],
                'payload.sections.*.ids' => ['sometimes', 'array', 'max:50'],
                'payload.sections.*.ids.*' => ['integer', 'min:1', 'distinct'],
                'payload.sections.*.content' => ['nullable', 'string', 'max:2000'],
                'payload.sections.*.content_ar' => ['nullable', 'string', 'max:2000'],
                'payload.sections.*.button_text' => ['nullable', 'string', 'max:80'],
                'payload.sections.*.button_text_ar' => ['nullable', 'string', 'max:80'],
                'payload.sections.*.button_url' => [
                    'nullable',
                    'url',
                    'regex:/^https:\/\//i',
                    'max:2048',
                ],
                'payload.sections.*.image' => ['nullable', 'string', 'max:2048'],
            ]
        )->validate();

        $request->validate([
            'banner_files' => ['sometimes', 'array'],
            'banner_files.*' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $payload = $validated['payload'];

        foreach ($payload['sections'] as &$section) {
            if ($section['type'] !== 'banner') {
                continue;
            }

            $upload = $request->file('banner_files.' . $section['id']);

            if ($upload) {
                $section['image'] = $upload->store(
                    'mobile-home/banners',
                    'public'
                );

                continue;
            }

            $existingImage = $section['image'] ?? null;

            if (
                ! is_string($existingImage)
                || ! Str::startsWith($existingImage, 'mobile-home/banners/')
            ) {
                throw ValidationException::withMessages([
                    'banner_files.' . $section['id'] =>
                        'Please upload an image for this banner.',
                ]);
            }
        }

        unset($section);

        $home->saveDraft(
            $payload,
            (int) $request->user()->id
        );

        return redirect()
            ->route('mobile-home.index')
            ->with('message', 'Homepage draft saved. It is not published yet.');
    }

    public function preview(MobileHomeService $home): View
    {
        return view('admin.mobile-home.preview', [
            'preview' => $home->preview(),
        ]);
    }

    public function publish(
        Request $request,
        MobileHomeService $home
    ): RedirectResponse {
        $home->publish((int) $request->user()->id);

        return redirect()
            ->route('mobile-home.index')
            ->with('message', 'Homepage published successfully.');
    }

    public function restore(
        Request $request,
        int $revision,
        MobileHomeService $home
    ): RedirectResponse {
        $home->restore(
            $revision,
            (int) $request->user()->id
        );

        return redirect()
            ->route('mobile-home.index')
            ->with('message', 'Revision restored to draft. Publish it separately when ready.');
    }
}
