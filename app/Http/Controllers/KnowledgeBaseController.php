<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function search(Request $request)
    {
        // FIX: Using input() instead of the deprecated get()
        $query = $request->input('q');

        // Safety check: Ensure query exists and is at least 3 characters
        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $articles = Article::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->take(3)
            ->get(['id', 'title', 'content']); // Note: This get() is for Eloquent, which is perfectly fine!

        return response()->json($articles);
    }
}
