<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class IsbnSearchController extends Controller
{
    public function show(string $isbn): JsonResponse
    {
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json(['error' => 'ISBNは13桁で入力してください。'], 400);
        }

        try {
            $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
                'q' => "isbn:{$isbn}",
                'key' => config('services.google_books.key'),
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['error' => 'API通信エラーが発生しました。'], 500);
        }

        if ($response->status() === 429) {
            return response()->json([
                'error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。',
            ], 429);
        }

        if (! $response->successful()) {
            return response()->json(['error' => 'API通信エラーが発生しました。'], 500);
        }

        $items = $response->json('items');

        if (empty($items)) {
            return response()->json(['error' => '書籍が見つかりませんでした。'], 404);
        }

        $volumeInfo = $items[0]['volumeInfo'] ?? [];

        return response()->json([
            'title' => $volumeInfo['title'] ?? null,
            'author' => implode('、', $volumeInfo['authors'] ?? []),
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
