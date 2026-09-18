<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('email', [
            'yamada@example.com',
            'suzuki@example.com',
            'tanaka@example.com',
            'sato@example.com',
            'takahashi@example.com',
        ])->get();

        $reviews = Review::whereIn('user_id', $users->pluck('id'))->get();

        foreach ($reviews as $review) {
            $likerCandidates = $users->reject(fn($user) => $user->id === $review->user_id);

            $likeCount = rand(0, 3);
            $likeCount = min($likeCount, $likerCandidates->count());

            $likers = $likerCandidates->random($likeCount);

            foreach ($likers as $liker) {
                $liker->likedReviews()->syncWithoutDetaching($review->id);
            }
        }
    }
}
