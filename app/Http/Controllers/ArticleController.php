<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->through(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'image' => $article->featured_image ? Storage::disk('public')->url($article->featured_image) : null,
                    'category' => $article->category,
                    'published_at' => $article->published_at ? $article->published_at->format('M d, Y') : null,
                ];
            });

        return Inertia::render('News/Index', [
            'articles' => $articles
        ]);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return Inertia::render('News/Show', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'image' => $article->featured_image ? Storage::disk('public')->url($article->featured_image) : null,
                'category' => $article->category,
                'published_at' => $article->published_at ? $article->published_at->format('M d, Y') : null,
            ]
        ]);
    }
}
