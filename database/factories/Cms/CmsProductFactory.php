<?php

namespace Database\Factories\Cms;

use App\Models\Cms\CmsProduct;
use App\Models\Cms\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cms\CmsProduct>
 */
class CmsProductFactory extends Factory
{
    protected $model = CmsProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('PROD-####-????')),
            'slug' => Str::slug($name),
            'name' => ucwords($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(2, true),
            'category_id' => Category::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'show_price' => true,
            'status' => 'active',
            'brand' => $this->faker->company(),
            'model' => $this->faker->word(),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'meta_title' => $name,
            'meta_description' => $this->faker->sentence(),
            'is_featured' => $this->faker->boolean(20),
            'is_active' => true,
            'view_count' => $this->faker->numberBetween(0, 500),
            'images' => [
                'https://via.placeholder.com/640x480.png?text=Product1',
                'https://via.placeholder.com/640x480.png?text=Product2',
            ],
        ];
    }
}
