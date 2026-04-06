<?php

namespace Database\Factories\Cms;

use App\Models\Cms\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cms\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'parent_id' => null,
            'slug' => Str::slug($name),
            'name' => ucwords($name),
            'description' => $this->faker->sentence(),
            'image' => 'https://via.placeholder.com/150?text=Category',
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
