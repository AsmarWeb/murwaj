<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileHomeDocument;
use App\Services\MobileHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobileHomeController extends Controller
{
    public function __construct(
        private readonly MobileHomeService $home
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->home->published(),
        ]);
    }

    public function preview(): JsonResponse
    {
        return response()->json([
            'data' => $this->home->preview(),
            'preview' => true,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function draft(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $document = $this->home->saveDraft(
            $validated['payload'],
            (int) $request->user()->id
        );

        return response()->json([
            'message' => 'Draft saved.',
            'data' => [
                'key' => $document->key,
                'updated_at' => $document->updated_at,
            ],
        ]);
    }

    public function publish(Request $request): JsonResponse
    {
        $revision = $this->home->publish(
            (int) $request->user()->id
        );

        return response()->json([
            'message' => 'Homepage published.',
            'data' => $revision,
        ]);
    }

    public function revisions(): JsonResponse
    {
        return response()->json([
            'data' => $this->home->revisions(),
        ]);
    }

    public function restore(Request $request, int $revision): JsonResponse
    {
        $restored = $this->home->restore(
            $revision,
            (int) $request->user()->id
        );

        return response()->json([
            'message' => 'Previous revision copied to draft. Review and publish manually.',
            'data' => [
                'key' => $restored->key,
                'updated_at' => $restored->updated_at,
            ],
        ]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'payload' => ['required', 'array'],
            'payload.sections' => ['required', 'array', 'max:30'],

            'payload.sections.*.id' => [
                'required',
                'string',
                'max:80',
                'distinct',
            ],

            'payload.sections.*.type' => [
                'required',
                Rule::in([
                    'products_latest',
                    'products_featured',
                    'products_selected',
                    'shops_latest',
                    'shops_selected',
                    'categories',
                    'catalog_sections',
                    'editorial',
                    'banner',
                ]),
            ],

            'payload.sections.*.enabled' => ['sometimes', 'boolean'],
            'payload.sections.*.position' => ['required', 'integer', 'min:0', 'max:10000'],
            'payload.sections.*.limit' => ['sometimes', 'integer', 'min:1', 'max:20'],

            'payload.sections.*.title' => ['nullable', 'string', 'max:160'],
            'payload.sections.*.title_ar' => ['nullable', 'string', 'max:160'],
            'payload.sections.*.image' => ['nullable', 'string', 'max:2048'],

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
        ]);
    }
}
