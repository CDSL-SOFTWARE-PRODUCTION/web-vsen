<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@vsen.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'Admin_PM',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ProductSeeder::class,
            ArticleSeeder::class,
            OpsV1Seeder::class,
        ]);

        if (filter_var(env('OPS_SEED_DEMO_TENDER', false), FILTER_VALIDATE_BOOL)) {
            $this->call(OpsHueMilkAwardedDemoSeeder::class);
        }
    }
}
