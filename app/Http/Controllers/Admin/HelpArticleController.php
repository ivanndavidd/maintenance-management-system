<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HelpArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('search');

        $query = HelpArticle::query()->orderBy('order')->orderBy('created_at', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere(
                    'content',
                    'like',
                    "%{$search}%",
                );
            });
        }

        $articles = $query->paginate(15);

        // Get counts for each category
        $categoryCounts = [
            'all' => HelpArticle::count(),
            'faq' => HelpArticle::byCategory('faq')->count(),
            'sop' => HelpArticle::byCategory('sop')->count(),
            'tutorial' => HelpArticle::byCategory('tutorial')->count(),
            'documentation' => HelpArticle::byCategory('documentation')->count(),
        ];

        return view(
            'admin.help-articles.index',
            compact('articles', 'categoryCounts', 'category', 'search'),
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.help-articles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log incoming content
        \Log::info('Store Request - Content:', ['content' => $request->input('content')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:help_articles,slug',
            'category' => 'required|in:faq,sop,tutorial,documentation',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            // Remove is_published from validation - we'll handle it manually
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set defaults
        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = $validated['order'] ?? 0;

        \Log::info('Validated Content:', ['content' => $validated['content']]);

        HelpArticle::create($validated);

        return redirect()
            ->route('admin.help-articles.index')
            ->with('success', 'Help article created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(HelpArticle $helpArticle)
    {
        return view('admin.help-articles.show', compact('helpArticle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HelpArticle $helpArticle)
    {
        return view('admin.help-articles.edit', compact('helpArticle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HelpArticle $helpArticle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:help_articles,slug,' . $helpArticle->id,
            'category' => 'required|in:faq,sop,tutorial,documentation',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            // Remove is_published from validation - we'll handle it manually
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set defaults
        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = $validated['order'] ?? 0;

        // Update the article
        $helpArticle->update($validated);

        return redirect()
            ->route('admin.help-articles.index')
            ->with('success', 'Help article updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelpArticle $helpArticle)
    {
        $helpArticle->delete();

        return redirect()
            ->route('admin.help-articles.index')
            ->with('success', 'Help article deleted successfully!');
    }

    /**
     * Toggle published status
     */
    public function togglePublish(HelpArticle $helpArticle)
    {
        $helpArticle->update([
            'is_published' => !$helpArticle->is_published,
        ]);

        $status = $helpArticle->is_published ? 'published' : 'unpublished';

        return redirect()
            ->back()
            ->with('success', "Article has been {$status} successfully!");
    }
}
