<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
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

        $commentsByRating = [
            1 => 'あまり自分には合いませんでした。',
            2 => '期待していた内容とは少し違いました。',
            3 => '普通に読める内容でした。',
            4 => '読みやすく、内容も充実していました。',
            5 => 'とても勉強になりました。おすすめです。',
        ];

        $isbns = ['9784101010014', '9784422100524', '9784873115658', '9784863940246', '9784101010021', '9784309226712', '9784048930598', '9784478025819', '9784163902302', '9784822289607', '9784822251468'];
        $books = Book::whereIn('isbn', $isbns)->get();

        foreach ($books as $book) {
            $reviewerCount = rand(2, 4);
            $reviewers = $users->random($reviewerCount);

            foreach ($reviewers as $user) {
                $rating = rand(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $commentsByRating[$rating],
                ]);
            }
        }
    }
}
