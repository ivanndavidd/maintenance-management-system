@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('user.help.index') }}">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('user.help.index', ['category' => $article->category]) }}">
                    {{ ucfirst($article->category) }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $article->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Article Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <!-- Article Header -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            @if($article->icon)
                                <i class="fas {{ $article->icon }} fa-2x text-primary me-3"></i>
                            @endif
                            <div>
                                {!! $article->category_badge !!}
                                <h3 class="mb-1">{{ $article->title }}</h3>
                            </div>
                        </div>
                        <div class="d-flex text-muted small">
                            <span class="me-3">
                                <i class="fas fa-eye"></i> {{ $article->view_count }} views
                            </span>
                            <span>
                                <i class="fas fa-calendar"></i> Updated {{ $article->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <!-- Article Content -->
                    <div class="article-content">
                        {!! $article->content !!}
                    </div>
                </div>
            </div>

            <!-- Helpful Feedback -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Was this article helpful?</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success" onclick="provideFeedback('yes')">
                            <i class="fas fa-thumbs-up"></i> Yes, it was helpful
                        </button>
                        <button class="btn btn-outline-danger" onclick="provideFeedback('no')">
                            <i class="fas fa-thumbs-down"></i> No, I need more help
                        </button>
                    </div>
                    <div id="feedback-message" class="mt-3" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Thank you for your feedback!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Related Articles -->
            @if($relatedArticles->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i> Related Articles
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($relatedArticles as $related)
                        <a href="{{ route('user.help.show', $related) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-start">
                                @if($related->icon)
                                    <i class="fas {{ $related->icon }} text-primary me-2 mt-1"></i>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">{{ $related->title }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> {{ $related->view_count }} views
                                    </small>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Navigation -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-compass"></i> Quick Navigation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.help.index', ['category' => 'faq']) }}"
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-question-circle"></i> Browse FAQs
                        </a>
                        <a href="{{ route('user.help.index', ['category' => 'sop']) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-clipboard-list"></i> View SOPs
                        </a>
                        <a href="{{ route('user.help.index', ['category' => 'tutorial']) }}"
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-graduation-cap"></i> Watch Tutorials
                        </a>
                        <a href="{{ route('user.help.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Help Center
                        </a>
                    </div>
                </div>
            </div>

            <!-- Need More Help -->
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-life-ring fa-3x text-warning mb-3"></i>
                    <h6>Still Need Help?</h6>
                    <p class="text-muted small mb-3">
                        Can't find what you're looking for? Browse more articles or contact support.
                    </p>
                    <a href="{{ route('user.help.index') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-search"></i> Search Help Center
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .article-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #333;
    }

    .article-content h1,
    .article-content h2,
    .article-content h3,
    .article-content h4,
    .article-content h5,
    .article-content h6 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .article-content h1 { font-size: 2.5rem; }
    .article-content h2 { font-size: 2rem; }
    .article-content h3 { font-size: 1.75rem; }
    .article-content h4 { font-size: 1.5rem; }
    .article-content h5 { font-size: 1.25rem; }
    .article-content h6 { font-size: 1rem; }

    .article-content p {
        margin-bottom: 1rem;
    }

    .article-content ul,
    .article-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }

    .article-content li {
        margin-bottom: 0.5rem;
    }

    .article-content img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 1rem auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .article-content table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }

    .article-content table th,
    .article-content table td {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
    }

    .article-content table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .article-content code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        color: #e83e8c;
    }

    .article-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
        overflow-x: auto;
    }

    .article-content blockquote {
        padding: 1rem 1.5rem;
        margin: 1rem 0;
        border-left: 4px solid #0d6efd;
        background-color: #f8f9fa;
        font-style: italic;
    }

    .article-content strong {
        font-weight: 700;
    }

    .article-content em {
        font-style: italic;
    }

    .article-content u {
        text-decoration: underline;
    }

    .article-content s {
        text-decoration: line-through;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0;
    }

    .breadcrumb-item a {
        text-decoration: none;
        color: #0d6efd;
    }

    .breadcrumb-item a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@push('scripts')
<script>
function provideFeedback(type) {
    // Here you can add AJAX call to save feedback to database
    document.getElementById('feedback-message').style.display = 'block';

    // Hide buttons after feedback
    event.target.parentElement.style.display = 'none';

    // Optional: Send to server via AJAX
    // fetch('/api/help/feedback', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //     },
    //     body: JSON.stringify({
    //         article_id: {{ $article->id }},
    //         feedback: type
    //     })
    // });
}
</script>
@endpush
@endsection
