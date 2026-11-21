<x-admin>
    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">
                        DRAFT PREVIEW
                    </span>
                    <h1 class="mt-3 text-3xl font-black text-slate-900">Homepage preview</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        This is a preview of the saved draft. It is not the live homepage.
                    </p>
                </div>

                <a href="{{ route('mobile-home.index') }}"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    ← Back to editor
                </a>
            </header>

            <div class="rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4 text-sm text-orange-900">
                Changes shown here remain unpublished until an administrator uses the Publish action.
            </div>

            @forelse (($preview['sections'] ?? []) as $section)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @if (($section['type'] ?? '') === 'banner')
                        @if (!empty($section['image']))
                            <img src="{{ $section['image'] }}" alt="{{ $section['title'] ?? 'Homepage banner' }}"
                                class="max-h-96 w-full object-cover">
                        @endif

                        <div class="p-6">
                            <h2 class="text-2xl font-black text-slate-900">
                                {{ $section['title'] ?? '' }}
                            </h2>
                            @if (!empty($section['title_ar']))
                                <h3 class="mt-1 text-xl font-bold text-slate-700" dir="rtl">
                                    {{ $section['title_ar'] }}
                                </h3>
                            @endif
                            @if (!empty($section['content']))
                                <p class="mt-3 text-slate-600">{{ $section['content'] }}</p>
                            @endif
                            @if (!empty($section['content_ar']))
                                <p class="mt-2 text-slate-600" dir="rtl">{{ $section['content_ar'] }}</p>
                            @endif
                            @if (!empty($section['button_text']))
                                <span class="mt-4 inline-flex rounded-xl px-4 py-2 text-sm font-bold text-white"
                                    style="background:#FF5517">
                                    {{ $section['button_text'] }}
                                </span>
                            @endif
                        </div>
                    @elseif (($section['type'] ?? '') === 'editorial')
                        <div class="p-6">
                            <h2 class="text-xl font-black text-slate-900">{{ $section['title'] ?? 'Editorial section' }}</h2>
                            <p class="mt-3 whitespace-pre-line text-slate-600">{{ $section['content'] ?? '' }}</p>
                            @if (!empty($section['title_ar']) || !empty($section['content_ar']))
                                <div class="mt-4 border-t border-slate-100 pt-4 text-right" dir="rtl">
                                    <h3 class="font-bold text-slate-800">{{ $section['title_ar'] ?? '' }}</h3>
                                    <p class="mt-2 whitespace-pre-line text-slate-600">{{ $section['content_ar'] ?? '' }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-xl font-black text-slate-900">
                                        {{ $section['title'] ?? ucfirst(str_replace('_', ' ', $section['type'] ?? 'Section')) }}
                                    </h2>
                                    @if (!empty($section['title_ar']))
                                        <p class="mt-1 text-sm text-slate-500" dir="rtl">{{ $section['title_ar'] }}</p>
                                    @endif
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    {{ count($section['items'] ?? []) }} items
                                </span>
                            </div>
                        </div>

                        @if (!empty($section['items']))
                            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-5">
                                @foreach ($section['items'] as $item)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                        <p class="line-clamp-2 text-sm font-semibold text-slate-800">
                                            {{ data_get($item, 'title') ?? data_get($item, 'name') ?? data_get($item, 'category_name') ?? 'Homepage item' }}
                                        </p>
                                        @if (!empty(data_get($item, 'title_ar')) || !empty(data_get($item, 'name_ar')))
                                            <p class="mt-1 line-clamp-2 text-xs text-slate-500" dir="rtl">
                                                {{ data_get($item, 'title_ar') ?? data_get($item, 'name_ar') }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="px-6 py-5 text-sm text-slate-500">No items are currently available for this section.</p>
                        @endif
                    @endif
                </section>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center text-slate-500">
                    The draft has no enabled homepage sections.
                </div>
            @endforelse
        </div>
    </div>
</x-admin>
