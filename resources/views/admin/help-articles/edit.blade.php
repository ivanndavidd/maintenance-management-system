@extends('layouts.admin')

@section('page-title', 'Edit Help Article')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-edit"></i> Edit Help Article</h2>
            <p class="text-muted mb-0">{{ $helpArticle->title }}</p>
        </div>
        <a href="{{ route($routePrefix.'.help-articles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="edit-article-form" action="{{ route($routePrefix.'.help-articles.update', $helpArticle) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $helpArticle->title) }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (URL-friendly name)</label>
                            <input type="text"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug', $helpArticle->slug) }}">
                            <small class="text-muted">Current URL: /user/help/{{ $helpArticle->slug }}</small>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror"
                                    id="category"
                                    name="category"
                                    required>
                                <option value="faq" {{ old('category', $helpArticle->category) == 'faq' ? 'selected' : '' }}>FAQ</option>
                                <option value="sop" {{ old('category', $helpArticle->category) == 'sop' ? 'selected' : '' }}>SOP (Standard Operating Procedure)</option>
                                <option value="tutorial" {{ old('category', $helpArticle->category) == 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                                <option value="documentation" {{ old('category', $helpArticle->category) == 'documentation' ? 'selected' : '' }}>Documentation</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Icon -->
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (FontAwesome class)</label>
                            <div class="input-group">
                                @if($helpArticle->icon)
                                    <span class="input-group-text">
                                        <i class="fas {{ $helpArticle->icon }}"></i>
                                    </span>
                                @endif
                                <input type="text"
                                       class="form-control @error('icon') is-invalid @enderror"
                                       id="icon"
                                       name="icon"
                                       value="{{ old('icon', $helpArticle->icon) }}"
                                       placeholder="e.g., fa-question-circle">
                            </div>
                            <small class="text-muted">
                                <a href="https://fontawesome.com/icons" target="_blank">Browse FontAwesome icons</a>
                            </small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <div id="editor-container"></div>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      style="display: none;"
                                      required>{{ old('content', $helpArticle->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Order -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Display Order</label>
                            <input type="number"
                                   class="form-control @error('order') is-invalid @enderror"
                                   id="order"
                                   name="order"
                                   value="{{ old('order', $helpArticle->order) }}"
                                   min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Published Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_published"
                                       name="is_published"
                                       {{ old('is_published', $helpArticle->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    <strong>Published</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($helpArticle->is_published)
                                            Article is currently visible to users
                                        @else
                                            Article is currently saved as draft
                                        @endif
                                    </small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Article
                            </button>
                            <a href="{{ route($routePrefix.'.help-articles.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Article Stats -->
            <div class="card shadow-sm border-info mb-3">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-line"></i> Article Statistics
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
            <div class="card shadow-sm border-warning mb-3">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.help.show', $helpArticle) }}"
                           class="btn btn-outline-info btn-sm"
                           target="_blank">
                            <i class="fas fa-external-link-alt"></i> Preview Article
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

            <!-- Help -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Formatting Tips
                </div>
                <div class="card-body small">
                    <p><strong>Line breaks:</strong> Press Enter to create new lines</p>
                    <p><strong>Lists:</strong> Use numbers (1., 2.) or bullets (-)</p>
                    <p><strong>Headings:</strong> Use ALL CAPS or add dashes (---)</p>
                    <p class="mb-0"><strong>Emphasis:</strong> Use UPPERCASE for important text</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #editor-container {
        height: 400px;
        background-color: white;
    }
    .ql-editor {
        min-height: 400px;
        font-size: 16px;
    }
</style>
@endpush

@push('scripts')
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
// Initialize Quill Editor
var quill = new Quill('#editor-container', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'font': [] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean']
        ]
    },
    placeholder: 'Enter the article content here...'
});

// Load existing content into Quill
var existingContent = {!! json_encode($helpArticle->content) !!};

if (existingContent) {
    // Check if content contains HTML tags
    var hasHTMLTags = /<[^>]+>/.test(existingContent);

    if (hasHTMLTags) {
        // If it's HTML, use dangerouslyPasteHTML
        quill.clipboard.dangerouslyPasteHTML(0, existingContent);
    } else {
        // If it's plain text, convert line breaks to HTML
        var htmlContent = existingContent
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');
        htmlContent = '<p>' + htmlContent + '</p>';
        quill.clipboard.dangerouslyPasteHTML(0, htmlContent);
    }

    // Update textarea immediately
    document.querySelector('#content').value = quill.root.innerHTML;
}

// Handle image upload
quill.getModule('toolbar').addHandler('image', function() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = function() {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };
});

// Sync Quill content to textarea before form submission
const editForm = document.getElementById('edit-article-form');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        const content = document.querySelector('#content');
        const htmlContent = quill.root.innerHTML;
        content.value = htmlContent;
    });
}

// Sync content whenever Quill changes
quill.on('text-change', function(delta, oldDelta, source) {
    const content = document.querySelector('#content');
    if (content && quill) {
        content.value = quill.root.innerHTML;
    }
});
</script>
@endpush
@endsection
