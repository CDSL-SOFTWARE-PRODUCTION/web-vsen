<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->where('is_active', true);

        // Search
        if ($request->has('q') && $request->q) {
            $terms = explode(' ', $request->q);
            // Detect driver to choose operator (ilike for pgsql, like for others)
            $operator = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($terms, $operator) {
                foreach ($terms as $term) {
                    $term = trim($term);
                    if (!empty($term)) {
                        $q->where(function ($subQ) use ($term, $operator) {
                            $subQ->where('name', $operator, '%' . $term . '%')
                                 ->orWhere('description', $operator, '%' . $term . '%')
                                 ->orWhere('short_description', $operator, '%' . $term . '%')
                                 ->orWhere('sku', $operator, '%' . $term . '%')
                                 ->orWhereHas('category', function ($catQ) use ($term, $operator) {
                                     $catQ->where('name', $operator, '%' . $term . '%');
                                 });
                        });
                    }
                }
            });
        }

        // Filter by Category
        if ($request->has('category') && $request->category !== 'All') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->get()->map(function ($product) {
            $imagePath = $product->images->where('is_primary', true)->first()->path ?? $product->images->first()->path ?? '';
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
                'description' => $product->short_description ?? strip_tags($product->description),
            ];
        });

        $categories = ['All', ...Category::where('is_active', true)->pluck('name')->toArray()];

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filterCategories' => $categories,
            'filters' => $request->only(['category', 'q']),
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'specs'])
            ->where('id', $id) // Ideally use slug, but maintaining ID for now as per previous frontend
            ->where('is_active', true)
            ->firstOrFail();

        $imagePath = $product->images->where('is_primary', true)->first()->path ?? $product->images->first()->path ?? '';
        $imageUrl = '';
        if ($imagePath) {
            $imageUrl = filter_var($imagePath, FILTER_VALIDATE_URL) || str_starts_with($imagePath, 'http')
                ? $imagePath
                : Storage::disk('public')->url($imagePath);
        }
        
        $transformedProduct = [
            'id' => (string) $product->id,
            'name' => $product->name,
            'category' => $product->category->name ?? 'Uncategorized',
            'image' => $imageUrl,
            'description' => $product->description,
            'features' => $product->specs->pluck('spec_value')->toArray(),
        ];

        return Inertia::render('Products/Show', [
            'product' => $transformedProduct
        ]);
    }
}
