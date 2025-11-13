<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display help & support page with all categories
     */
    public function index(Request $request)
    {
        $category = $request->get('category');

        $query = HelpArticle::published()->orderBy('order')->orderBy('created_at', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        $articles = $query->get()->groupBy('category');

        // Get counts for each category
        $categoryCounts = [
            'faq' => HelpArticle::published()->byCategory('faq')->count(),
            'sop' => HelpArticle::published()->byCategory('sop')->count(),
            'tutorial' => HelpArticle::published()->byCategory('tutorial')->count(),
            'documentation' => HelpArticle::published()->byCategory('documentation')->count(),
        ];

        return view('user.help.index', compact('articles', 'categoryCounts', 'category'));
    }

    /**
     * Display specific help article
     */
    public function show(HelpArticle $article)
    {
        // Increment view count
        $article->incrementViews();

        // Get related articles from same category
        $relatedArticles = HelpArticle::published()
            ->byCategory($article->category)
            ->where('id', '!=', $article->id)
            ->orderBy('order')
            ->limit(5)
            ->get();

        return view('user.help.show', compact('article', 'relatedArticles'));
    }

    /**
     * Search help articles
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('user.help.index');
        }

        $articles = HelpArticle::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderBy('order')
            ->get();

        return view('user.help.search', compact('articles', 'query'));
    }
}
