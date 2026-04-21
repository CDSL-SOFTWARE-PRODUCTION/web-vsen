<?php

namespace Database\Factories\Ops;

use App\Models\Ops\FounderWorkCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FounderWorkCard>
 */
class FounderWorkCardFactory extends Factory
{
    protected $model = FounderWorkCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'founder_user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'summary' => fake()->optional()->sentence(8),
            'assignee_label' => fake()->name(),
            'due_at' => fake()->optional()->dateTimeBetween('-1 week', '+2 weeks'),
            'status' => FounderWorkCard::STATUS_OPEN,
            'digest_lane' => fake()->randomElement([
                FounderWorkCard::LANE_GENERAL,
                FounderWorkCard::LANE_SIGNATURE,
                FounderWorkCard::LANE_REPLY,
            ]),
            'attachment_urls' => null,
        ];
    }
}
