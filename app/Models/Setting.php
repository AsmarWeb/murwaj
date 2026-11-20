<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'description_ar',
        'short_des',
        'short_des_ar',
        'logo',
        'favicon',
        'email',
        'phone',
        'phone2',
        'address',
        'map',
        'facebook',
        'telegram',
        'instagram',
        'tiktok',
        'copyright',
        'copyright_ar',
        'maintenance_mode',
        'promo_enabled',
        'promo_text_en',
        'promo_text_ar',
        'promo_url',
        'promo_bg_color',
        'promo_text_color',
        'promo_animation',
        'promo_duration',
        'promo_dismissible',
        'promo_starts_at',
        'promo_ends_at'
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'promo_enabled' => 'boolean',
        'promo_dismissible' => 'boolean',
        'promo_duration' => 'integer',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
    ];
}
