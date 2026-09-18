<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => 'reading',
            'target_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'completed_at' => null,
        ];
    }
}
