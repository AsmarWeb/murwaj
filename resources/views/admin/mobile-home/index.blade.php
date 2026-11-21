<x-admin>
    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-7 px-4 sm:px-6 lg:px-8">

            <header class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">
                        <span class="h-2 w-2 rounded-full" style="background:#FF5517"></span>
                        MURWAJ CONTENT STUDIO
                    </div>

                    <h1 class="text-3xl font-black tracking-tight text-slate-900">
                        Homepage management
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Manage the mobile storefront for
                        <strong>murwaj.com</strong> · <strong>مُروَّج.كم</strong>
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('mobile-home.preview') }}" target="_blank"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Preview draft ↗
                    </a>

                    <button type="button" id="add-section"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                        style="background:#FF5517">
                        + Add section
                    </button>
                </div>
            </header>

            @if (session('message'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                    {{ session('message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="mb-2 font-bold">Please review the following:</p>
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Draft sections</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ count($sections) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Sections in the current working copy</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Published version</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">
                        {{ $publishedRevision ? 'v' . $publishedRevision->version : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        @if ($publishedRevision?->published_at)
                            Published {{ $publishedRevision->published_at->format('M j, Y · H:i') }}
                        @else
                            No homepage version has been published yet
                        @endif
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Draft status</p>
                    <p class="mt-2 text-xl font-black text-orange-600">Not published</p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $draftUpdatedAt ? 'Last saved ' . $draftUpdatedAt->format('M j, Y · H:i') : 'No saved draft yet' }}
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold text-slate-800">Safe publishing workflow</p>
                        <p class="mt-1 text-sm text-slate-600">
                            Save your changes first, review the preview, then publish manually.
                            Saving or restoring a revision never changes the live homepage.
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-orange-700">
                        Draft → Preview → Publish
                    </span>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Homepage sections</h2>
                        <p class="mt-1 text-sm text-slate-500">Move sections up or down to change their display order.</p>
                    </div>
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ count($sections) }} / 30
                    </span>
                </div>

                <form id="mobile-home-form" method="POST" action="{{ route('mobile-home.draft') }}"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="payload_json" id="payload-json"
                        value="{{ json_encode(['sections' => $sections], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">

                    <div id="sections-list" class="space-y-4">
                        @foreach ($sections as $section)
                            @include('admin.mobile-home._section', ['section' => $section])
                        @endforeach
                    </div>

                    <div id="empty-sections"
                        class="{{ count($sections) ? 'hidden' : '' }} rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-2xl text-orange-600">＋</div>
                        <h3 class="mt-4 font-bold text-slate-800">Your homepage is ready to be built</h3>
                        <p class="mt-1 text-sm text-slate-500">Add your first section to start arranging the storefront.</p>
                        <button type="button" id="empty-add-section"
                            class="mt-5 rounded-xl px-5 py-2.5 text-sm font-bold text-white"
                            style="background:#FF5517">Add first section</button>
                    </div>

                    <div class="flex flex-col justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center">
                        <p class="text-xs text-slate-500">
                            Saving updates the draft only. The live homepage stays unchanged.
                        </p>
                        <button type="submit"
                            class="rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                            style="background:#FF5517">
                            Save draft
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Publish homepage</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Publishing creates a new immutable revision and makes it live.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('mobile-home.publish') }}"
                        onsubmit="return confirm('Publish the currently saved draft to the live homepage?')">
                        @csrf
                        <button type="submit"
                            class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                            Publish saved draft
                        </button>
                    </form>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <h2 class="text-lg font-black text-slate-900">Revision history</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Restore any published version into the draft editor. It will not be published automatically.
                    </p>
                </div>

                @forelse ($revisions as $revision)
                    <div class="flex flex-col justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center sm:px-6">
                        <div>
                            <p class="font-bold text-slate-800">Homepage version {{ $revision->version }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $revision->published_at?->format('M j, Y · H:i') ?? 'Publication date unavailable' }}
                                · Revision #{{ $revision->id }}
                            </p>
                        </div>

                        <form method="POST"
                            action="{{ route('mobile-home.restore', $revision->id) }}"
                            onsubmit="return confirm('Restore this version into the draft? The live homepage will not change.')">
                            @csrf
                            <button type="submit"
                                class="rounded-lg border border-orange-200 px-4 py-2 text-sm font-bold text-orange-700 transition hover:bg-orange-50">
                                Restore to draft
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-slate-500">
                        No published revisions yet.
                    </div>
                @endforelse
            </section>
        </div>
    </div>

    <template id="section-template">
        @include('admin.mobile-home._section', [
            'section' => [
                'id' => '__SECTION_ID__',
                'type' => 'products_latest',
                'enabled' => true,
                'position' => count($sections),
                'limit' => 10,
            ],
        ])
    </template>

    <script>
        (() => {
            const form = document.getElementById('mobile-home-form');
            const list = document.getElementById('sections-list');
            const template = document.getElementById('section-template');
            const payloadInput = document.getElementById('payload-json');
            const emptyState = document.getElementById('empty-sections');
            const addButton = document.getElementById('add-section');
            const emptyAddButton = document.getElementById('empty-add-section');

            if (!form || !list || !template || !payloadInput) return;

            const value = (card, key) => {
                const field = card.querySelector(`[data-field="${key}"]`);
                return field ? field.value : '';
            };

            function updateCard(card) {
                const type = value(card, 'type');

                const visibility = {
                    'selection-options': ['products_selected', 'shops_selected', 'categories'],
                    content: ['editorial', 'banner'],
                    button: ['editorial', 'banner'],
                    image: ['banner']
                };

                card.querySelectorAll('[data-field-row]').forEach((row) => {
                    const field = row.dataset.fieldRow;
                    const allowedTypes = visibility[field] || [];
                    row.classList.toggle('hidden', !allowedTypes.includes(type));
                });

                const selected = card.querySelector('[data-field="type"]');
                const heading = card.querySelector('[data-section-heading]');
                if (heading && selected) {
                    heading.textContent = selected.options[selected.selectedIndex].text;
                }
            }

            function updateOrder() {
                [...list.querySelectorAll('[data-section-card]')].forEach((card, index) => {
                    const position = card.querySelector('[data-field="position"]');
                    if (position) position.value = index;
                });

                emptyState.classList.toggle(
                    'hidden',
                    list.querySelectorAll('[data-section-card]').length > 0
                );
            }

            function addSection() {
                const count = list.querySelectorAll('[data-section-card]').length;
                if (count >= 30) {
                    alert('A maximum of 30 sections is allowed.');
                    return;
                }

                const id = 'section_' + (
                    window.crypto?.randomUUID
                        ? window.crypto.randomUUID().replaceAll('-', '')
                        : Date.now().toString(36) + Math.random().toString(36).slice(2)
                );

                const markup = template.innerHTML.replaceAll('__SECTION_ID__', id);
                const holder = document.createElement('template');
                holder.innerHTML = markup.trim();

                const card = holder.content.firstElementChild;
                if (!card) return;

                list.appendChild(card);
                updateCard(card);
                updateOrder();
            }

            list.addEventListener('input', (event) => {
                const search = event.target.closest('[data-selection-search]');
                if (!search) return;

                const panel = search.closest('[data-selection-list]');
                const term = search.value.trim().toLocaleLowerCase();

                panel?.querySelectorAll('[data-option-item]').forEach((item) => {
                    item.classList.toggle(
                        'hidden',
                        !String(item.dataset.search || '').includes(term)
                    );
                });
            });

            list.addEventListener('change', (event) => {
                if (event.target.matches('[data-field="type"]')) {
                    updateCard(event.target.closest('[data-section-card]'));
                }

                if (event.target.matches('[data-banner-file]')) {
                    const card = event.target.closest('[data-section-card]');
                    const file = event.target.files?.[0];
                    if (!file) return;

                    const preview = card.querySelector('[data-image-preview]');
                    const empty = card.querySelector('[data-image-empty]');

                    if (preview) {
                        preview.src = URL.createObjectURL(file);
                        preview.classList.remove('hidden');
                    }

                    if (empty) empty.classList.add('hidden');
                }
            });

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-action]');
                if (!button) return;

                const card = button.closest('[data-section-card]');
                if (!card) return;

                const action = button.dataset.action;

                if (action === 'remove') {
                    if (!confirm('Remove this section from the draft?')) return;
                    card.remove();
                } else if (action === 'up' && card.previousElementSibling) {
                    list.insertBefore(card, card.previousElementSibling);
                } else if (action === 'down' && card.nextElementSibling) {
                    list.insertBefore(card.nextElementSibling, card);
                }

                updateOrder();
            });

            function addField(card, key) {
                const element = card.querySelector(`[data-field="${key}"]`);
                return element ? element.value.trim() : '';
            }

            form.addEventListener('submit', () => {
                const sections = [...list.querySelectorAll('[data-section-card]')].map((card, index) => {
                    const ids = [...card.querySelectorAll('[data-selected-id]:checked')]
                        .map((input) => Number.parseInt(input.value, 10))
                        .filter((item) => Number.isSafeInteger(item) && item > 0);

                    const enabled = card.querySelector('[data-field="enabled"]');

                    return {
                        id: addField(card, 'id'),
                        type: addField(card, 'type'),
                        enabled: enabled ? enabled.checked : true,
                        position: index,
                        limit: Number.parseInt(addField(card, 'limit'), 10) || 10,
                        title: addField(card, 'title') || null,
                        title_ar: addField(card, 'title_ar') || null,
                        ids,
                        content: addField(card, 'content') || null,
                        content_ar: addField(card, 'content_ar') || null,
                        button_text: addField(card, 'button_text') || null,
                        button_text_ar: addField(card, 'button_text_ar') || null,
                        button_url: addField(card, 'button_url') || null,
                        image: addField(card, 'image') || null
                    };
                });

                payloadInput.value = JSON.stringify({ sections });
            });

            addButton?.addEventListener('click', addSection);
            emptyAddButton?.addEventListener('click', addSection);

            list.querySelectorAll('[data-section-card]').forEach(updateCard);
            updateOrder();
        })();
    </script>
</x-admin>
