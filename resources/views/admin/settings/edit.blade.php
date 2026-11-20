<x-admin>
    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.75 3.75h4.5M12 3.75v2.25m7.07 1.68-1.59 1.59M20.25 12h-2.25m-.93 5.32 1.59 1.59M12 18v2.25m-5.32-2.93-1.59 1.59M6 12H3.75m2.93-4.32L5.09 6.09" />
                                    <circle cx="12" cy="12" r="3.75" />
                                </svg>
                            </div>

                            <div>
                                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                                    Platform Settings
                                </h1>

                                <p class="mt-1 text-sm text-slate-500">
                                    Manage your platform identity, contact information, content and social links.
                                </p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back
                    </a>
                </div>
            </div>


            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18A2 2 0 003.53 21h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>

                        <div>
                            <h3 class="text-sm font-semibold text-red-800">
                                Please correct the following errors:
                            </h3>

                            <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif


            {{-- Success Message --}}
            @if (session('message'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-center gap-3 text-sm font-medium text-emerald-700">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>

                        {{ session('message') }}
                    </div>
                </div>
            @endif


            <form action="{{ route('settings.update', $setting->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                {{-- ===================================================== --}}
                {{-- BRANDING --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3l8.25 4.5v9L12 21l-8.25-4.5v-9L12 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 7.5L12 12l8.25-4.5M12 12v9" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Platform Branding
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Update your platform logo and favicon.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        {{-- Logo --}}
                        <div>
                            <label for="logo" class="mb-2 block text-sm font-semibold text-slate-700">
                                Platform Logo
                            </label>

                            @if ($setting->logo)
                                <div
                                    class="mb-3 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div
                                        class="flex h-20 w-32 items-center justify-center rounded-lg bg-white p-3 shadow-sm">
                                        <img src="{{ asset($setting->logo) }}" alt="Current logo"
                                            class="max-h-14 max-w-full object-contain">
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-slate-700">
                                            Current logo
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            Upload a new image to replace it.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <input type="file" id="logo" name="logo"
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">

                            <p class="mt-2 text-xs text-slate-400">
                                Recommended: PNG, JPG, WEBP or SVG.
                            </p>
                        </div>


                        {{-- Favicon --}}
                        <div>
                            <label for="favicon" class="mb-2 block text-sm font-semibold text-slate-700">
                                Favicon
                            </label>

                            @if ($setting->favicon)
                                <div
                                    class="mb-3 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div
                                        class="flex h-20 w-20 items-center justify-center rounded-lg bg-white p-3 shadow-sm">
                                        <img src="{{ asset($setting->favicon) }}" alt="Current favicon"
                                            class="h-12 w-12 object-contain">
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-slate-700">
                                            Current favicon
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            Recommended size: 32×32 or 64×64.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <input type="file" id="favicon" name="favicon"
                                accept="image/png,image/x-icon,image/jpeg,image/webp"
                                class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">

                            <p class="mt-2 text-xs text-slate-400">
                                Use a small square icon for best results.
                            </p>
                        </div>

                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- CONTACT --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l9 6 9-6" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Contact Information
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Configure the contact details shown across your platform.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">
                                Primary Phone
                            </label>

                            <input type="tel" id="phone" name="phone"
                                value="{{ old('phone', $setting->phone) }}"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                placeholder="+971 50 000 0000" required>
                        </div>


                        {{-- Second Phone --}}
                        <div>
                            <label for="phone2" class="mb-2 block text-sm font-semibold text-slate-700">
                                Secondary Phone
                            </label>

                            <input type="tel" id="phone2" name="phone2"
                                value="{{ old('phone2', $setting->phone2) }}"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                placeholder="+971 50 000 0000">
                        </div>


                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                                Email Address
                            </label>

                            <input type="email" id="email" name="email"
                                value="{{ old('email', $setting->email) }}"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                placeholder="support@example.com" required>
                        </div>


                        {{-- Address --}}
                        <div>
                            <label for="address" class="mb-2 block text-sm font-semibold text-slate-700">
                                Address
                            </label>

                            <input type="text" id="address" name="address"
                                value="{{ old('address', $setting->address) }}"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                placeholder="Enter platform address" required>
                        </div>

                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- CONTENT --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 5.25h15M4.5 9.75h15M4.5 14.25h10.5M4.5 18.75h7.5" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Platform Content
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Manage your platform description and multilingual content.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="space-y-6 p-6">

                        {{-- Short Description --}}
                        <div class="grid gap-6 md:grid-cols-2">

                            <div>
                                <label for="short_des" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Short Description
                                </label>

                                <input type="text" id="short_des" name="short_des"
                                    value="{{ old('short_des', $setting->short_des) }}"
                                    class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="Short platform description" required>
                            </div>


                            <div dir="rtl">
                                <label for="short_des_ar" class="mb-2 block text-sm font-semibold text-slate-700">
                                    الوصف المختصر
                                </label>

                                <input type="text" id="short_des_ar" name="short_des_ar"
                                    value="{{ old('short_des_ar', $setting->short_des_ar) }}"
                                    class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="أدخل الوصف المختصر">
                            </div>

                        </div>


                        {{-- Description --}}
                        <div class="grid gap-6 md:grid-cols-2">

                            <div>
                                <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Description
                                </label>

                                <textarea id="description" name="description" rows="6"
                                    class="block w-full resize-y rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="Enter platform description">{{ old('description', $setting->description) }}</textarea>
                            </div>


                            <div dir="rtl">
                                <label for="description_ar" class="mb-2 block text-sm font-semibold text-slate-700">
                                    الوصف
                                </label>

                                <textarea id="description_ar" name="description_ar" rows="6"
                                    class="block w-full resize-y rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="أدخل وصف المنصة">{{ old('description_ar', $setting->description_ar) }}</textarea>
                            </div>

                        </div>


                        {{-- Copyright --}}
                        <div class="grid gap-6 md:grid-cols-2">

                            <div>
                                <label for="copyright" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Copyright
                                </label>

                                <textarea id="copyright" name="copyright" rows="3"
                                    class="block w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="© 2026 Your Platform">{{ old('copyright', $setting->copyright) }}</textarea>
                            </div>


                            <div dir="rtl">
                                <label for="copyright_ar" class="mb-2 block text-sm font-semibold text-slate-700">
                                    حقوق النشر
                                </label>

                                <textarea id="copyright_ar" name="copyright_ar" rows="3"
                                    class="block w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="حقوق النشر">{{ old('copyright_ar', $setting->copyright_ar) }}</textarea>
                            </div>

                        </div>

                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- SOCIAL MEDIA --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-50 text-pink-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7a4 4 0 014-4z" />
                                    <circle cx="12" cy="12" r="3.5" />
                                    <path stroke-linecap="round" d="M17.5 6.5h.01" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Social Media
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Add links to your official social media accounts.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        {{-- Facebook --}}
                        <div>
                            <label for="facebook" class="mb-2 block text-sm font-semibold text-slate-700">
                                Facebook
                            </label>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-sm font-bold text-blue-600">f</span>
                                </div>

                                <input type="url" id="facebook" name="facebook"
                                    value="{{ old('facebook', $setting->facebook) }}"
                                    class="block w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    placeholder="https://facebook.com/yourpage">
                            </div>
                        </div>


                        {{-- Instagram --}}
                        <div>
                            <label for="instagram" class="mb-2 block text-sm font-semibold text-slate-700">
                                Instagram
                            </label>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-sm font-bold text-pink-500">@</span>
                                </div>

                                <input type="url" id="instagram" name="instagram"
                                    value="{{ old('instagram', $setting->instagram) }}"
                                    class="block w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-100"
                                    placeholder="https://instagram.com/yourpage">
                            </div>
                        </div>


                        {{-- Telegram --}}
                        <div>
                            <label for="telegram" class="mb-2 block text-sm font-semibold text-slate-700">
                                Telegram
                            </label>

                            <input type="url" id="telegram" name="telegram"
                                value="{{ old('telegram', $setting->telegram) }}"
                                class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                placeholder="https://t.me/yourchannel">
                        </div>


                        {{-- TikTok --}}
                        <div>
                            <label for="tiktok" class="mb-2 block text-sm font-semibold text-slate-700">
                                TikTok
                            </label>

                            <input type="url" id="tiktok" name="tiktok"
                                value="{{ old('tiktok', $setting->tiktok) }}"
                                class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-700 focus:ring-2 focus:ring-slate-100"
                                placeholder="https://tiktok.com/@youraccount">
                        </div>

                    </div>
                </div>

                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            ⚠
                        </div>

                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Maintenance Mode
                            </h3>

                            <p class="mt-1 text-sm text-gray-600">
                                Temporarily disable the public platform while you perform maintenance.
                                Administrators can still access the dashboard.
                            </p>

                            <label class="mt-4 flex cursor-pointer items-center gap-3">
                                <input type="hidden" name="maintenance_mode" value="0">

                                <input type="checkbox" name="maintenance_mode" value="1"
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ old('maintenance_mode', $setting->maintenance_mode) ? 'checked' : '' }}>

                                <span class="text-sm font-medium text-gray-700">
                                    Enable Maintenance Mode
                                </span>
                            </label>
                        </div>

                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- MAP --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 6.75L3.75 4.5v14.25L9 21l6-2.25 5.25 2.25V6.75L15 4.5 9 6.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 6.75V21m6-16.5v14.25" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Location & Map
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Configure the map embed URL displayed on your platform.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="p-6">

                        <label for="map" class="mb-2 block text-sm font-semibold text-slate-700">
                            Map Embed URL
                        </label>

                        <input type="url" id="map" name="map" value="{{ old('map', $setting->map) }}"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            placeholder="https://www.google.com/maps/embed?...">

                        <p class="mt-2 text-xs text-slate-400">
                            Paste the Google Maps embed URL rather than the normal Google Maps page URL.
                        </p>

                        @if ($setting->map)
                            <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                <iframe src="{{ $setting->map }}" class="h-72 w-full border-0" loading="lazy"
                                    allowfullscreen referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        @endif

                    </div>
                </div>


                {{-- ===================================================== --}}

                {{-- ===================================================== --}}
                {{-- PROMOTIONAL BANNER --}}
                {{-- ===================================================== --}}

                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                     stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 11.25v1.5a2.25 2.25 0 002.25 2.25H7.5l3.75 4.5v-13.5L7.5 10.5H5.25A2.25 2.25 0 003 12.75z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 9a4.5 4.5 0 010 6m2.25-8.25a7.5 7.5 0 010 10.5"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-semibold text-slate-900">
                                    Promotional Banner
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Configure the promotional strip displayed above the main website header.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">

                        {{-- Enable banner --}}
                        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                            <input type="hidden" name="promo_enabled" value="0">

                            <label class="flex cursor-pointer items-center gap-3">
                                <input type="checkbox" name="promo_enabled" value="1"
                                       class="h-5 w-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                       {{ old('promo_enabled', $setting->promo_enabled ?? false) ? 'checked' : '' }}>

                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">
                                        Enable promotional banner
                                    </span>
                                    <span class="mt-1 block text-xs text-slate-600">
                                        The banner remains hidden when disabled.
                                    </span>
                                </span>
                            </label>
                        </div>

                        {{-- Promotional text --}}
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="promo_text_en" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Promotional Text (English)
                                </label>
                                <input type="text" id="promo_text_en" name="promo_text_en"
                                       maxlength="255"
                                       value="{{ old('promo_text_en', $setting->promo_text_en ?? '') }}"
                                       class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                       placeholder="Discover our latest offers">
                            </div>

                            <div>
                                <label for="promo_text_ar" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Promotional Text (Arabic)
                                </label>
                                <input type="text" id="promo_text_ar" name="promo_text_ar"
                                       maxlength="255" dir="rtl"
                                       value="{{ old('promo_text_ar', $setting->promo_text_ar ?? '') }}"
                                       class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                       placeholder="اكتشف أحدث عروضنا">
                            </div>
                        </div>

                        <div>
                            <label for="promo_url" class="mb-2 block text-sm font-semibold text-slate-700">
                                Destination URL <span class="font-normal text-slate-400">(Optional)</span>
                            </label>
                            <input type="url" id="promo_url" name="promo_url"
                                   value="{{ old('promo_url', $setting->promo_url ?? '') }}"
                                   class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                   placeholder="https://example.com/offer">
                            <p class="mt-2 text-xs text-slate-400">
                                Visitors can click the banner to open this destination.
                            </p>
                        </div>

                        {{-- Appearance --}}
                        <div class="border-t border-slate-100 pt-6">
                            <h3 class="mb-4 text-sm font-semibold text-slate-900">
                                Appearance & Animation
                            </h3>

                            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <label for="promo_bg_color" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Background Color
                                    </label>
                                    <input type="color" id="promo_bg_color" name="promo_bg_color"
                                           value="{{ old('promo_bg_color', $setting->promo_bg_color ?? '#f97316') }}"
                                           class="h-11 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                </div>

                                <div>
                                    <label for="promo_text_color" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Text Color
                                    </label>
                                    <input type="color" id="promo_text_color" name="promo_text_color"
                                           value="{{ old('promo_text_color', $setting->promo_text_color ?? '#ffffff') }}"
                                           class="h-11 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                </div>

                                <div>
                                    <label for="promo_animation" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Animation
                                    </label>
                                    <select id="promo_animation" name="promo_animation"
                                            class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                                        <option value="marquee" {{ old('promo_animation', $setting->promo_animation ?? 'marquee') === 'marquee' ? 'selected' : '' }}>
                                            Moving text
                                        </option>
                                        <option value="fade" {{ old('promo_animation', $setting->promo_animation ?? 'marquee') === 'fade' ? 'selected' : '' }}>
                                            Fade
                                        </option>
                                        <option value="static" {{ old('promo_animation', $setting->promo_animation ?? 'marquee') === 'static' ? 'selected' : '' }}>
                                            Static
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="promo_duration" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Animation Duration (seconds)
                                    </label>
                                    <input type="number" id="promo_duration" name="promo_duration"
                                           min="5" max="120"
                                           value="{{ old('promo_duration', $setting->promo_duration ?? 20) }}"
                                           class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                                </div>
                            </div>
                        </div>

                        {{-- Visibility schedule --}}
                        <div class="border-t border-slate-100 pt-6">
                            <h3 class="mb-1 text-sm font-semibold text-slate-900">
                                Display Schedule
                            </h3>
                            <p class="mb-4 text-xs text-slate-500">
                                Leave dates empty to display the banner without a date restriction.
                                Times use the application timezone.
                            </p>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="promo_starts_at" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Start Date & Time
                                    </label>
                                    <input type="datetime-local" id="promo_starts_at" name="promo_starts_at"
                                           value="{{ old('promo_starts_at', $setting->promo_starts_at?->format('Y-m-d\TH:i')) }}"
                                           class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                                </div>

                                <div>
                                    <label for="promo_ends_at" class="mb-2 block text-sm font-semibold text-slate-700">
                                        End Date & Time
                                    </label>
                                    <input type="datetime-local" id="promo_ends_at" name="promo_ends_at"
                                           value="{{ old('promo_ends_at', $setting->promo_ends_at?->format('Y-m-d\TH:i')) }}"
                                           class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                                </div>
                            </div>
                        </div>

                        {{-- Dismiss option --}}
                        <div class="border-t border-slate-100 pt-5">
                            <input type="hidden" name="promo_dismissible" value="0">

                            <label class="flex cursor-pointer items-center gap-3">
                                <input type="checkbox" name="promo_dismissible" value="1"
                                       class="h-5 w-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                       {{ old('promo_dismissible', $setting->promo_dismissible ?? true) ? 'checked' : '' }}>

                                <span>
                                    <span class="block text-sm font-semibold text-slate-800">
                                        Allow visitors to dismiss the banner
                                    </span>
                                    <span class="mt-1 block text-xs text-slate-500">
                                        Displays a close button on the promotional banner.
                                    </span>
                                </span>
                            </label>
                        </div>

                    </div>
                </div>

                {{-- SAVE BAR --}}
                {{-- ===================================================== --}}

                <div class="sticky bottom-4 z-20">
                    <div
                        class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                Save platform settings
                            </p>

                            <p class="text-xs text-slate-500">
                                Your changes will be applied across the platform.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">

                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 4.5h11.25L19.5 7.75V19.5a1.5 1.5 0 01-1.5 1.5H6a1.5 1.5 0 01-1.5-1.5v-15z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 4.5v5.25h7.5V4.5M8.25 21v-6h7.5v6" />
                                </svg>

                                Save Changes
                            </button>

                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
</x-admin>
