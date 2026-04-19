<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Helper to verify permissions and satisfy IDE type-hinting.
     */
    private function authorizeKBManagement(): void
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->hasRole('admin') && !$currentUser->can('manage_kb_articles')) {
            abort(403, 'You do not have permission to manage knowledge base articles.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeKBManagement();

        $query = Article::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        // Fetch latest articles and paginate
        $articles = $query->latest()->paginate(15)->withQueryString();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $this->authorizeKBManagement();
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $this->authorizeKBManagement();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // HTML Checkboxes don't send false if unchecked
        $validated['is_published'] = $request->has('is_published');

        $article = Article::create($validated);

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created KB Article',
            'target_type' => 'Article',
            'target_id' => $article->id,
            'new_value' => $article->title,
        ]);

        return redirect()->route('articles.index')->with('success', 'Article published successfully.');
    }

    public function edit(Article $article)
    {
        $this->authorizeKBManagement();
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeKBManagement();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $article->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated KB Article',
            'target_type' => 'Article',
            'target_id' => $article->id,
            'new_value' => $article->title,
        ]);

        return redirect()->route('articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeKBManagement();

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted KB Article',
            'target_type' => 'Article',
            'target_id' => $article->id,
            'old_value' => $article->title,
        ]);

        $article->delete();

        return back()->with('success', 'Article deleted successfully.');
    }
}
