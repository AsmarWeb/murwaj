<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Catalog\PublicFields;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->orderBy('id')
            ->get([
                'id',
                'category_name',
                'category_name_ar',
                'slug',
                'image',
                'section_id',
            ])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->category_name,
                'name_ar' => $category->category_name_ar,
                'slug' => $category->slug,
                'image' => PublicFields::media($category->image, 'category-suggest/'),
                'section_id' => $category->section_id,
            ])
            ->values();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
