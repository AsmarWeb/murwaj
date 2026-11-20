<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('promo_enabled')->default(false);
            $table->string('promo_text_en')->nullable();
            $table->string('promo_text_ar')->nullable();
            $table->string('promo_url')->nullable();

            $table->string('promo_bg_color', 7)->default('#f97316');
            $table->string('promo_text_color', 7)->default('#ffffff');

            $table->string('promo_animation', 20)->default('marquee');
            $table->unsignedInteger('promo_duration')->default(20);

            $table->boolean('promo_dismissible')->default(true);

            $table->timestamp('promo_starts_at')->nullable();
            $table->timestamp('promo_ends_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
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
                'promo_ends_at',
            ]);
        });
    }
};
