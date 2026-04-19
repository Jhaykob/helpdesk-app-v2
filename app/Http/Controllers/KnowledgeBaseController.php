<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    // The Public Knowledge Base Portal
    public function index(Request $request)
    {
        $query = Article::where('is_published', true);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('content', 'like', "%{$request->search}%");
        }

        // Fetch paginated articles for the public grid
        $articles = $query->latest('updated_at')->paginate(12)->withQueryString();

        return view('kb.index', compact('articles'));
    }

    // Read a specific Article
    public function show(Article $article)
    {
        // Security check: Don't let regular users read unpublished drafts via direct URL!
        // But DO let KB Editors preview their drafts.
        if (!$article->is_published) {
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();

            if (!$currentUser->hasRole('admin') && !$currentUser->can('manage_kb_articles')) {
                abort(404, 'Article not found or not published.');
            }
        }

        return view('kb.show', compact('article'));
    }

    // Your existing AJAX Deflection API
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $articles = Article::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->take(3)
            ->get(['id', 'title', 'content']);

        return response()->json($articles);
    }
}
