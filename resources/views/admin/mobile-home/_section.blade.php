@php
    $section = (array) $section;
    $sectionId = (string) ($section['id'] ?? '__SECTION_ID__');
    $sectionType = (string) ($section['type'] ?? 'products_latest');
    $imageValue = (string) ($section['image'] ?? '');
    $imageUrl = '';

    if ($imageValue !== '') {
        $imageUrl = preg_match('~^https?://~i', $imageValue)
            ? $imageValue
            : url(\Illuminate\Support\Facades\Storage::disk('public')->url($imageValue));
    }
@endphp

<article
    data-section-card
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-5 py-4">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 font-bold text-orange-600">M</span>
            <div>
                <p data-section-heading class="font-semibold text-slate-800">Homepage section</p>
                <p class="text-xs text-slate-500">Configure the content shown in the app</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" data-action="up"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
                title="Move up">↑</button>
            <button type="button" data-action="down"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
                title="Move down">↓</button>
            <button type="button" data-action="remove"
                class="rounded-lg border border-red-100 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                Remove
            </button>
        </div>
    </div>

    <div class="space-y-5 p-5">
        <input type="hidden" data-field="id" value="{{ $sectionId }}">
        <input type="hidden" data-field="image" value="{{ $imageValue }}">

        <div class="grid gap-4 md:grid-cols-3">
            <label class="block md:col-span-2">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Section type</span>
                <select data-field="type"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300">
                    @foreach ([
                        'products_latest' => 'Latest products',
                        'products_featured' => 'Featured products',
                        'shops_latest' => 'Latest shops',
                        'shops_selected' => 'Selected shops',
                        'categories' => 'Categories',
                        'catalog_sections' => 'Catalog sections',
                        'editorial' => 'Editorial content',
                        'banner' => 'Image banner',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected($sectionType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex items-end gap-3 rounded-xl bg-slate-50 px-4 py-3">
                <input type="checkbox" data-field="enabled"
                    class="mb-0.5 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    @checked($section['enabled'] ?? true)>
                <span>
                    <span class="block text-sm font-semibold text-slate-700">Section enabled</span>
                    <span class="block text-xs text-slate-500">Show this section in the app</span>
                </span>
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Title (English)</span>
                <input data-field="title" type="text" maxlength="160"
                    value="{{ $section['title'] ?? '' }}"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300"
                    placeholder="For example: Discover new arrivals">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">العنوان بالعربية</span>
                <input data-field="title_ar" type="text" maxlength="160"
                    value="{{ $section['title_ar'] ?? '' }}" dir="rtl"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300"
                    placeholder="اكتشف أحدث المنتجات">
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Display order</span>
                <input data-field="position" type="number" min="0" max="10000"
                    value="{{ $section['position'] ?? 0 }}"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Items limit</span>
                <input data-field="limit" type="number" min="1" max="20"
                    value="{{ $section['limit'] ?? 10 }}"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300">
            </label>
        </div>

        @php
            $selectedIds = array_values(array_unique(array_filter(
                array_map('intval', (array) ($section['ids'] ?? []))
            )));
        @endphp

        <div data-field-row="selection-options" class="hidden space-y-4">
            <div>
                <p class="text-sm font-semibold text-slate-700">Choose items to display</p>
                <p class="mt-1 text-xs text-slate-500">
                    Search and select records. The selected order controls their order in the mobile app.
                </p>
            </div>

            @foreach ([
                'products_selected' => ['key' => 'products', 'label' => 'Products'],
                'shops_selected' => ['key' => 'shops', 'label' => 'Shops'],
                'categories' => ['key' => 'categories', 'label' => 'Categories'],
            ] as $selectionType => $selection)
                @php
                    $options = $selectionOptions[$selection['key']] ?? [];
                    $availableIds = array_map('intval', array_column($options, 'id'));
                @endphp

                <div data-selection-list="{{ $selectionType }}"
                    class="hidden rounded-xl border border-slate-200 p-4">
                    <label class="mb-3 block">
                        <span class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Search {{ strtolower($selection['label']) }}
                        </span>
                        <input type="search" data-selection-search
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300"
                            placeholder="Search by English or Arabic name">
                    </label>

                    <div class="max-h-64 space-y-2 overflow-y-auto">
                        @forelse ($options as $option)
                            <label data-option-item
                                data-search="{{ mb_strtolower(($option['name'] ?? '') . ' ' . ($option['name_ar'] ?? '')) }}"
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 hover:bg-orange-50">
                                <input type="checkbox" data-selected-id value="{{ $option['id'] }}"
                                    @checked(in_array((int) $option['id'], $selectedIds, true))
                                    class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-800">
                                        {{ $option['name'] ?: 'Unnamed item' }}
                                    </span>
                                    @if (!empty($option['name_ar']))
                                        <span class="block truncate text-xs text-slate-500" dir="rtl">
                                            {{ $option['name_ar'] }}
                                        </span>
                                    @endif
                                </span>
                                <span class="text-xs text-slate-400">#{{ $option['id'] }}</span>
                            </label>
                        @empty
                            <p class="py-4 text-center text-sm text-slate-500">
                                No {{ strtolower($selection['label']) }} available.
                            </p>
                        @endforelse

                        @foreach (array_diff($selectedIds, $availableIds) as $missingId)
                            <label data-option-item data-search=""
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2.5">
                                <input type="checkbox" data-selected-id value="{{ $missingId }}" checked
                                    class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                <span class="flex-1 text-sm text-amber-800">
                                    Unavailable record #{{ $missingId }} — retained in draft
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <p class="mt-3 text-xs text-slate-500">
                        Up to 100 recent public records are listed here. Previously selected IDs are retained.
                    </p>
                </div>
            @endforeach
        </div>

        <div data-field-row="content" class="hidden grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Content (English)</span>
                <textarea data-field="content" rows="3" maxlength="2000"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300">{{ $section['content'] ?? '' }}</textarea>
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">المحتوى بالعربية</span>
                <textarea data-field="content_ar" rows="3" maxlength="2000" dir="rtl"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-300">{{ $section['content_ar'] ?? '' }}</textarea>
            </label>
        </div>

        <div data-field-row="button" class="hidden grid gap-4 md:grid-cols-3">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Button text</span>
                <input data-field="button_text" type="text" maxlength="80"
                    value="{{ $section['button_text'] ?? '' }}"
                    class="w-full rounded-xl border-slate-200 text-sm">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">نص الزر</span>
                <input data-field="button_text_ar" type="text" maxlength="80" dir="rtl"
                    value="{{ $section['button_text_ar'] ?? '' }}"
                    class="w-full rounded-xl border-slate-200 text-sm">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">Button URL (HTTPS)</span>
                <input data-field="button_url" type="url"
                    value="{{ $section['button_url'] ?? '' }}"
                    class="w-full rounded-xl border-slate-200 text-sm"
                    placeholder="https://example.com">
            </label>
        </div>

        <div data-field-row="image" class="hidden rounded-xl border border-dashed border-orange-200 bg-orange-50/50 p-4">
            <div class="grid gap-4 md:grid-cols-[180px_1fr] md:items-center">
                <div class="overflow-hidden rounded-xl bg-slate-100">
                    <img data-image-preview src="{{ $imageUrl }}"
                        class="{{ $imageUrl ? '' : 'hidden' }} h-28 w-full object-cover"
                        alt="Banner preview">
                    <div data-image-empty class="{{ $imageUrl ? 'hidden' : '' }} flex h-28 items-center justify-center text-xs text-slate-400">
                        No banner image yet
                    </div>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">Banner image</span>
                    <input type="file" name="banner_files[{{ $sectionId }}]"
                        data-banner-file accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-orange-100 file:px-4 file:py-2 file:font-semibold file:text-orange-700 hover:file:bg-orange-200">
                    <span class="mt-2 block text-xs text-slate-500">JPG, PNG or WebP. Maximum size: 5 MB.</span>
                </label>
            </div>
        </div>
    </div>
</article>
