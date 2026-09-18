<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
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

        $books = Book::pluck('id');

        foreach ($users as $user) {
            $favoriteCount = rand(3, 5);
            $favoriteBookIds = $books->random($favoriteCount);

            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}
