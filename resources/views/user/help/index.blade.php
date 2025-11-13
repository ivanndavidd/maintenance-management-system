@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-question-circle"></i> Help & Support</h2>
            <p class="text-muted mb-0">Find answers, tutorials, and documentation</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('user.help.search') }}" method="GET">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text"
                           name="q"
                           class="form-control border-start-0"
                           placeholder="Search for help articles, FAQs, tutorials..."
                           value="{{ request('q') }}"
                           required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('category') ? 'active' : '' }}"
               href="{{ route('user.help.index') }}">
                <i class="fas fa-th-large"></i> All Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('category') == 'faq' ? 'active' : '' }}"
               href="{{ route('user.help.index', ['category' => 'faq']) }}">
                <i class="fas fa-question-circle"></i> FAQ
                <span class="badge bg-info">{{ $categoryCounts['faq'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('category') == 'sop' ? 'active' : '' }}"
               href="{{ route('user.help.index', ['category' => 'sop']) }}">
                <i class="fas fa-clipboard-list"></i> SOP
                <span class="badge bg-primary">{{ $categoryCounts['sop'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('category') == 'tutorial' ? 'active' : '' }}"
               href="{{ route('user.help.index', ['category' => 'tutorial']) }}">
                <i class="fas fa-graduation-cap"></i> Tutorials
                <span class="badge bg-success">{{ $categoryCounts['tutorial'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('category') == 'documentation' ? 'active' : '' }}"
               href="{{ route('user.help.index', ['category' => 'documentation']) }}">
                <i class="fas fa-book"></i> Documentation
                <span class="badge bg-secondary">{{ $categoryCounts['documentation'] }}</span>
            </a>
        </li>
    </ul>

    <!-- Category Cards -->
    <div class="row">
        @if($articles->count() > 0)
            @foreach(['faq' => ['title' => 'Frequently Asked Questions', 'icon' => 'fa-question-circle', 'color' => 'info'],
                      'sop' => ['title' => 'Standard Operating Procedures', 'icon' => 'fa-clipboard-list', 'color' => 'primary'],
                      'tutorial' => ['title' => 'Tutorials & Guides', 'icon' => 'fa-graduation-cap', 'color' => 'success'],
                      'documentation' => ['title' => 'Documentation', 'icon' => 'fa-book', 'color' => 'secondary']] as $cat => $info)

                @if($articles->has($cat) && $articles->get($cat)->count() > 0)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-{{ $info['color'] }} text-white">
                            <h5 class="mb-0">
                                <i class="fas {{ $info['icon'] }}"></i> {{ $info['title'] }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach($articles->get($cat) as $article)
                                <a href="{{ route('user.help.show', $article) }}"
                                   class="list-group-item list-group-item-action border-0 help-article-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                @if($article->icon)
                                                    <i class="fas {{ $article->icon }} text-{{ $info['color'] }} me-2"></i>
                                                @endif
                                                <h6 class="mb-0">{{ $article->title }}</h6>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                {{ Str::limit(strip_tags($article->content), 120) }}
                                            </p>
                                        </div>
                                        <div class="text-end ms-3">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-eye"></i> {{ $article->view_count }} views
                                            </small>
                                            <small class="text-{{ $info['color'] }}">
                                                <i class="fas fa-chevron-right"></i>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        @else
            <!-- Empty State -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Help Articles Available</h4>
                        <p class="text-muted">
                            @if(request('category'))
                                No articles found in this category.
                            @else
                                Help articles will appear here once they are published.
                            @endif
                        </p>
                        @if(request('category'))
                            <a href="{{ route('user.help.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> View All Categories
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                    <h5>Need Quick Help?</h5>
                    <p class="text-muted small">Browse our FAQ section for quick answers</p>
                    <a href="{{ route('user.help.index', ['category' => 'faq']) }}" class="btn btn-info btn-sm">
                        View FAQs
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                    <h5>Learn SOPs</h5>
                    <p class="text-muted small">Follow standard procedures for maintenance</p>
                    <a href="{{ route('user.help.index', ['category' => 'sop']) }}" class="btn btn-primary btn-sm">
                        View SOPs
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                    <h5>Watch Tutorials</h5>
                    <p class="text-muted small">Step-by-step guides for common tasks</p>
                    <a href="{{ route('user.help.index', ['category' => 'tutorial']) }}" class="btn btn-success btn-sm">
                        View Tutorials
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .help-article-item {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 8px;
        padding: 16px !important;
        text-decoration: none;
        color: inherit;
    }

    .help-article-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .help-article-item:hover h6 {
        color: #0d6efd;
    }

    .help-article-item:hover .fa-chevron-right {
        transform: translateX(3px);
        transition: transform 0.3s ease;
    }

    .nav-pills .nav-link {
        color: #6c757d;
        margin-right: 8px;
        border-radius: 20px;
    }

    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
    }

    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: white;
    }
</style>
@endpush
@endsection
