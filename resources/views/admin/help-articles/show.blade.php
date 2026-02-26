@extends('layouts.admin')

@section('page-title', 'View Help Article')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas {{ $helpArticle->icon ?? 'fa-file-alt' }}"></i> {{ $helpArticle->title }}</h2>
            <p class="text-muted mb-0">{!! $helpArticle->category_badge !!}</p>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.help-articles.edit', $helpArticle) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Article
            </a>
            <a href="{{ route($routePrefix.'.help-articles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Preview Banner -->
            <div class="alert alert-info alert-dismissible fade show py-2 mb-3" role="alert">
                <i class="fas fa-eye me-1"></i>
                <strong>Preview Mode</strong> — This is how the article appears to users.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Article Content -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="article-content">
                        {!! $helpArticle->content !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Article Stats -->
            <div class="card shadow-sm border-info mb-3">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-line"></i> Article Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Views:</strong>
                        <span class="float-end badge bg-primary">{{ $helpArticle->view_count }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Category:</strong>
                        <span class="float-end">{!! $helpArticle->category_badge !!}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="float-end">
                            @if($helpArticle->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Display Order:</strong>
                        <span class="float-end badge bg-secondary">{{ $helpArticle->order }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <span class="float-end">{{ $helpArticle->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="mb-0">
                        <strong>Last Updated:</strong>
                        <span class="float-end">{{ $helpArticle->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.help.show', $helpArticle) }}"
                           class="btn btn-outline-info btn-sm"
                           target="_blank">
                            <i class="fas fa-external-link-alt"></i> View as User
                        </a>
                        <a href="{{ route($routePrefix.'.help-articles.edit', $helpArticle) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Article
                        </a>
                        <form action="{{ route($routePrefix.'.help-articles.toggle-publish', $helpArticle) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-{{ $helpArticle->is_published ? 'secondary' : 'success' }} btn-sm w-100">
                                <i class="fas {{ $helpArticle->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                {{ $helpArticle->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        <form action="{{ route($routePrefix.'.help-articles.destroy', $helpArticle) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this article permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fas fa-trash"></i> Delete Article
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

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

    .article-content a {
        color: #0d6efd;
        text-decoration: underline;
        word-break: break-all;
    }

    .article-content a:hover {
        color: #0a58ca;
    }
</style>
@endpush

@push('scripts')
<script>
// Auto-linkify plain URLs in article content
(function() {
    const urlRegex = /(\bhttps?:\/\/[^\s<>"']+)/gi;
    const container = document.querySelector('.article-content');
    if (!container) return;

    function linkifyNode(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent;
            if (!urlRegex.test(text)) return;
            urlRegex.lastIndex = 0;

            const frag = document.createDocumentFragment();
            let last = 0, match;
            while ((match = urlRegex.exec(text)) !== null) {
                if (match.index > last) {
                    frag.appendChild(document.createTextNode(text.slice(last, match.index)));
                }
                const a = document.createElement('a');
                a.href = match[0];
                a.textContent = match[0];
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                frag.appendChild(a);
                last = match.index + match[0].length;
            }
            if (last < text.length) {
                frag.appendChild(document.createTextNode(text.slice(last)));
            }
            node.parentNode.replaceChild(frag, node);
        } else if (node.nodeType === Node.ELEMENT_NODE && node.tagName !== 'A' && node.tagName !== 'SCRIPT') {
            Array.from(node.childNodes).forEach(linkifyNode);
        }
    }

    linkifyNode(container);
})();
</script>
@endpush
