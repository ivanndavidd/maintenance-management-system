@extends('layouts.admin')

@section('page-title', 'Help Articles')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5><i class="fas fa-question-circle"></i> Help Articles Management</h5>
            <p class="text-muted mb-0">Manage FAQ, SOP, Tutorials, and Documentation</p>
        </div>
        <a href="{{ route($routePrefix.'.help-articles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i><span class="btn-text"> Create New Article</span>
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route($routePrefix.'.help-articles.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <div class="input-group" style="flex-wrap: nowrap; align-items: stretch;">
                            <span class="input-group-text d-flex align-items-center">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control" placeholder="Search articles..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="faq" {{ $category == 'faq' ? 'selected' : '' }}>FAQ</option>
                            <option value="sop" {{ $category == 'sop' ? 'selected' : '' }}>SOP</option>
                            <option value="tutorial" {{ $category == 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                            <option value="documentation" {{ $category == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-filter"></i><span class="btn-text"> Filter</span>
                        </button>
                        <a href="{{ route($routePrefix.'.help-articles.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center py-2">
                    <h5 class="text-primary mb-0">{{ $categoryCounts['all'] }}</h5>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-info shadow-sm">
                <div class="card-body text-center py-2">
                    <h5 class="text-info mb-0">{{ $categoryCounts['faq'] }}</h5>
                    <small class="text-muted">FAQ</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center py-2">
                    <h5 class="text-primary mb-0">{{ $categoryCounts['sop'] }}</h5>
                    <small class="text-muted">SOP</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center py-2">
                    <h5 class="text-success mb-0">{{ $categoryCounts['tutorial'] }}</h5>
                    <small class="text-muted">Tutorial</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-secondary shadow-sm">
                <div class="card-body text-center py-2">
                    <h5 class="text-secondary mb-0">{{ $categoryCounts['documentation'] }}</h5>
                    <small class="text-muted">Docs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($articles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="d-none d-md-table-cell" style="width:5%">#</th>
                                <th class="d-none d-md-table-cell" style="width:5%">Order</th>
                                <th>Title</th>
                                <th class="d-none d-md-table-cell" style="width:12%">Category</th>
                                <th style="width:10%">Status</th>
                                <th class="d-none d-lg-table-cell" style="width:8%">Views</th>
                                <th class="d-none d-lg-table-cell" style="width:12%">Updated</th>
                                <th class="text-center" style="width:13%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                            <tr>
                                <td class="d-none d-md-table-cell">{{ $article->id }}</td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-secondary">{{ $article->order }}</span>
                                </td>
                                <td>
                                    @if($article->icon)
                                        <i class="fas {{ $article->icon }} me-1"></i>
                                    @endif
                                    <strong>{{ $article->title }}</strong>
                                    <div class="d-md-none">
                                        {!! $article->category_badge !!}
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">{!! $article->category_badge !!}</td>
                                <td>
                                    @if($article->is_published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <i class="fas fa-eye text-muted"></i> {{ $article->view_count }}
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small>{{ $article->updated_at->format('d M Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route($routePrefix.'.help-articles.show', $article) }}"
                                           class="btn btn-sm btn-info"
                                           title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route($routePrefix.'.help-articles.edit', $article) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route($routePrefix.'.help-articles.toggle-publish', $article) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $article->is_published ? 'btn-secondary' : 'btn-success' }}"
                                                    title="{{ $article->is_published ? 'Unpublish' : 'Publish' }}">
                                                <i class="fas {{ $article->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route($routePrefix.'.help-articles.destroy', $article) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this article?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $articles->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Articles Found</h4>
                    <p class="text-muted">
                        @if($search || $category)
                            No articles match your search criteria.
                        @else
                            Start by creating your first help article.
                        @endif
                    </p>
                    @if($search || $category)
                        <a href="{{ route($routePrefix.'.help-articles.index') }}" class="btn btn-primary">
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route($routePrefix.'.help-articles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i><span class="btn-text"> Create New Article</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
