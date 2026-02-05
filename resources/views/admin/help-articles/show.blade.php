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

<style>
.article-content {
    font-size: 16px;
    line-height: 1.6;
}

.article-content h1, .article-content h2, .article-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.article-content img {
    max-width: 100%;
    height: auto;
}

.article-content pre {
    background: #f4f4f4;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
}
</style>
@endsection
