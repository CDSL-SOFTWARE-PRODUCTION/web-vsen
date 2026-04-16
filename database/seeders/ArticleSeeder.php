<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cms\Article;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Article::truncate();

        $articles = [
            [
                'title' => 'VSEN Medical Announces New AI-Powered MRI Technology',
                'category' => 'Press Release',
                'image' => 'https://picsum.photos/seed/art1/800/600',
                'excerpt' => 'Revolutionary new deep learning reconstruction algorithms reduce scan times by up to 50%.',
                'content' => '<p><strong>New York, NY</strong> — VSEN Medical today unveiled its latest innovation in magnetic resonance imaging...</p><p>The new system leverages AIR™ Recon DL technology...</p>',
            ],
            [
                'title' => 'The Future of Precision Oncology',
                'category' => 'Insights',
                'image' => 'https://picsum.photos/seed/art2/800/600',
                'excerpt' => 'How integrated data solutions are helping clinicians make more confident decisions in cancer care.',
                'content' => '<p>Cancer care is becoming increasingly complex. With more data points than ever before...</p>',
            ],
            [
                'title' => 'Improving Patient Experience in Radiology',
                'category' => 'Blog',
                'image' => 'https://picsum.photos/seed/art3/800/600',
                'excerpt' => 'Tips for reducing patient anxiety and improving throughput in busy imaging centers.',
                'content' => '<p>Patient anxiety can lead to motion artifacts and the need for rescan...</p>',
            ]
        ];

        foreach ($articles as $art) {
            Article::create([
                'title' => $art['title'],
                'slug' => Str::slug($art['title']),
                'category' => $art['category'],
                'excerpt' => $art['excerpt'],
                'content' => $art['content'],
                'featured_image' => $art['image'],
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
