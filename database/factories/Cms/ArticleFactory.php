<?php

namespace Database\Factories\Cms;

use App\Models\Cms\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cms\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        
        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'featured_image' => 'https://via.placeholder.com/640x480.png/00eeff?text=Article',
            'category' => $this->faker->randomElement(['Tech', 'Lifestyle', 'Business', 'Health']),
            'view_count' => $this->faker->numberBetween(0, 1000),
            'is_published' => $this->faker->boolean(80),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'meta_title' => $title,
            'meta_description' => $this->faker->sentence(),
        ];
    }
}
