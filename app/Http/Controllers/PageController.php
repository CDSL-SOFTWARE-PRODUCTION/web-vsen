<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function home()
    {
        // Fetch featured products for the home page
        $featuredProducts = Product::with(['category'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->take(4)
            ->get()
            ->map(function ($product) {
                $imagePath = $product->primary_image_url;
                $imageUrl = '';
                if ($imagePath) {
                    $imageUrl = filter_var($imagePath, FILTER_VALIDATE_URL) || str_starts_with($imagePath, 'http')
                        ? $imagePath
                        : Storage::disk('public')->url($imagePath);
                }

                return [
                    'id' => (string) $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? __('Uncategorized'),
                    'image' => $imageUrl,
                    'description' => $product->short_description ?? $product->description,
                ];
            });

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts
        ]);
    }

    public function solutions()
    {
        return Inertia::render('Solutions/Index');
    }

    public function services()
    {
        return Inertia::render('Services/Index');
    }

    public function education()
    {
        return Inertia::render('Education/Index');
    }

    public function contact()
    {
        return Inertia::render('Contact/Index');
    }

    public function serviceShow($slug)
    {
        return Inertia::render('Services/Show', [
            'slug' => $slug
        ]);
    }
}
