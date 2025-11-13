@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-search"></i> Search Results</h2>
        <p class="text-muted mb-0">
            Found <strong>{{ $articles->count() }}</strong> results for "<strong>{{ $query }}</strong>"
        </p>
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
                           value="{{ $query }}"
                           required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if($articles->count() > 0)
        <div class="row">
            @foreach($articles as $article)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 search-result-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            @if($article->icon)
                                <i class="fas {{ $article->icon }} fa-2x text-primary me-3"></i>
                            @else
                                <i class="fas {{ $article->category_icon }} fa-2x text-primary me-3"></i>
                            @endif
                            <div class="flex-grow-1">
                                {!! $article->category_badge !!}
                                <h5 class="mt-2 mb-0">
                                    <a href="{{ route('user.help.show', $article) }}" class="text-decoration-none">
                                        {{ $article->title }}
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <p class="text-muted">
                            {{ Str::limit(strip_tags($article->content), 150) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> {{ $article->view_count }} views
                            </small>
                            <a href="{{ route('user.help.show', $article) }}" class="btn btn-primary btn-sm">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- No Results -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Results Found</h4>
                <p class="text-muted mb-4">
                    We couldn't find any articles matching "<strong>{{ $query }}</strong>"
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('user.help.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Help Center
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="document.querySelector('input[name=q]').focus()">
                        <i class="fas fa-search"></i> Try Another Search
                    </button>
                </div>

                <!-- Search Tips -->
                <div class="mt-4 text-start" style="max-width: 600px; margin: 2rem auto 0;">
                    <h6 class="mb-3">Search Tips:</h6>
                    <ul class="text-muted small">
                        <li>Try using different keywords or phrases</li>
                        <li>Check your spelling</li>
                        <li>Use more general terms</li>
                        <li>Browse by category instead</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Browse Categories -->
        <div class="row mt-4">
            <div class="col-md-3 mb-3">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                        <h6>FAQ</h6>
                        <a href="{{ route('user.help.index', ['category' => 'faq']) }}" class="btn btn-info btn-sm">
                            Browse
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                        <h6>SOP</h6>
                        <a href="{{ route('user.help.index', ['category' => 'sop']) }}" class="btn btn-primary btn-sm">
                            Browse
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                        <h6>Tutorials</h6>
                        <a href="{{ route('user.help.index', ['category' => 'tutorial']) }}" class="btn btn-success btn-sm">
                            Browse
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-secondary shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x text-secondary mb-3"></i>
                        <h6>Documentation</h6>
                        <a href="{{ route('user.help.index', ['category' => 'documentation']) }}" class="btn btn-secondary btn-sm">
                            Browse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .search-result-card {
        transition: all 0.3s ease;
    }

    .search-result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }

    .search-result-card h5 a {
        color: #333;
        transition: color 0.3s ease;
    }

    .search-result-card:hover h5 a {
        color: #0d6efd;
    }
</style>
@endpush
@endsection
