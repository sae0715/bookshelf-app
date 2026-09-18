<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewはlikedByUsersでいいねしたユーザーを取得できる(): void
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();
        $user->likedReviews()->attach($review);

        $this->assertTrue($review->likedByUsers->contains($user));
    }
}
