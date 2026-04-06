<?php

namespace Database\Factories\Cms;

use App\Models\Cms\QuoteRequest;
use App\Models\Cms\CmsProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cms\QuoteRequest>
 */
class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => CmsProduct::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'message' => $this->faker->sentence(),
            'status' => 'new',
        ];
    }
}
