<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = Setting::find(1);

        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return view('admin.settings.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

        /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'promo_text_en' => ['nullable', 'string', 'max:255'],
            'promo_text_ar' => ['nullable', 'string', 'max:255'],
            'promo_url' => ['nullable', 'url', 'max:255'],
            'promo_bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'promo_text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'promo_animation' => ['nullable', 'in:marquee,fade,static'],
            'promo_duration' => ['nullable', 'integer', 'min:5', 'max:120'],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => [
                'nullable',
                'date',
                \Illuminate\Validation\Rule::when(
                    $request->filled('promo_starts_at'),
                    ['after:promo_starts_at']
                ),
            ],
        ]);

        $setting->phone = $request->phone;
        $setting->phone2 = $request->phone2;
        $setting->email = $request->email;
        $setting->address = $request->address;
        $setting->short_des = $request->short_des;
        $setting->short_des_ar = $request->short_des_ar;
        $setting->description = $request->description;
        $setting->description_ar = $request->description_ar;

        $setting->facebook = $request->facebook;
        $setting->instagram = $request->instagram;
        $setting->telegram = $request->telegram;
        $setting->tiktok = $request->tiktok;

        $setting->copyright = $request->copyright;
        $setting->copyright_ar = $request->copyright_ar;

        $setting->maintenance_mode = $request->boolean('maintenance_mode');
        $setting->map = $request->has('map')
            ? (string) ($request->input('map') ?? '')
            : (string) ($setting->map ?? '');

        // Promotional banner settings.
        $setting->promo_enabled = $request->boolean('promo_enabled');
        $setting->promo_text_en = $validated['promo_text_en'] ?? null;
        $setting->promo_text_ar = $validated['promo_text_ar'] ?? null;
        $setting->promo_url = $validated['promo_url'] ?? null;
        $setting->promo_bg_color = $validated['promo_bg_color'] ?? '#f97316';
        $setting->promo_text_color = $validated['promo_text_color'] ?? '#ffffff';
        $setting->promo_animation = $validated['promo_animation'] ?? 'marquee';
        $setting->promo_duration = $validated['promo_duration'] ?? 20;
        $setting->promo_dismissible = $request->boolean('promo_dismissible');
        $setting->promo_starts_at = $validated['promo_starts_at'] ?? null;
        $setting->promo_ends_at = $validated['promo_ends_at'] ?? null;

        if ($request->hasFile('logo')) {
            $uploadPath = 'storage/setting-suggest/'.$setting->logo;
            if (File::exists($uploadPath)) {
                unlink($uploadPath);
            }

            $image = $request->file('logo');
            $imageName = $image->getClientOriginalName();
            $image->move('storage/setting-suggest/', $imageName);
            $setting['logo'] = $imageName;
        }

        if ($request->hasFile('favicon')) {
            $uploadPath = 'storage/setting-suggest/'.$setting->favicon;
            if (File::exists($uploadPath)) {
                unlink($uploadPath);
            }

            $image = $request->file('favicon');
            $imageName = $image->getClientOriginalName();
            $image->move('storage/setting-suggest/', $imageName);
            $setting['favicon'] = $imageName;
        }

        $setting->save();

        return redirect()->route('settings.index')
            ->with('message', 'Setting updated successfully.');
    }

/**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
