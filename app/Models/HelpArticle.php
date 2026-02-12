<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HelpArticle extends TenantModels
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'content',
        'icon',
        'order',
        'is_published',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * Automatically generate slug from title
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scope to get published articles
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->increment('view_count');
    }

    /**
     * Get category badge HTML
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'faq' => '<span class="badge bg-info">FAQ</span>',
            'sop' => '<span class="badge bg-primary">SOP</span>',
            'tutorial' => '<span class="badge bg-success">Tutorial</span>',
            'documentation' => '<span class="badge bg-secondary">Documentation</span>',
        ];

        return $badges[$this->category] ??
            '<span class="badge bg-secondary">' . ucfirst($this->category) . '</span>';
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'faq' => 'fa-question-circle',
            'sop' => 'fa-clipboard-list',
            'tutorial' => 'fa-graduation-cap',
            'documentation' => 'fa-book',
        ];

        return $icons[$this->category] ?? 'fa-file-alt';
    }
}
