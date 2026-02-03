@extends('layouts.admin')

@section('page-title', 'Create Help Article')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-plus-circle"></i> Create New Help Article</h2>
            <p class="text-muted mb-0">Add a new FAQ, SOP, Tutorial, or Documentation</p>
        </div>
        <a href="{{ route('admin.help-articles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="create-article-form" action="{{ route('admin.help-articles.store') }}" method="POST">
                        @csrf

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   placeholder="e.g., How do I create a work report?">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug (Optional) -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (URL-friendly name)</label>
                            <input type="text"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug') }}"
                                   placeholder="Leave empty to auto-generate">
                            <small class="text-muted">Leave empty to auto-generate from title</small>
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
                                <option value="">-- Select Category --</option>
                                <option value="faq" {{ old('category') == 'faq' ? 'selected' : '' }}>FAQ</option>
                                <option value="sop" {{ old('category') == 'sop' ? 'selected' : '' }}>SOP (Standard Operating Procedure)</option>
                                <option value="tutorial" {{ old('category') == 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                                <option value="documentation" {{ old('category') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Icon -->
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (FontAwesome class)</label>
                            <input type="text"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   id="icon"
                                   name="icon"
                                   value="{{ old('icon') }}"
                                   placeholder="e.g., fa-question-circle">
                            <small class="text-muted">
                                Example: fa-question-circle, fa-clipboard-list, fa-tools
                                (<a href="https://fontawesome.com/icons" target="_blank">Browse icons</a>)
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
                                      style="height: 100px; margin-top: 10px; font-family: monospace; font-size: 11px;"
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Use the rich text editor above. The textarea below shows the HTML that will be saved (for debugging).
                            </small>
                        </div>

                        <!-- Order -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Display Order</label>
                            <input type="number"
                                   class="form-control @error('order') is-invalid @enderror"
                                   id="order"
                                   name="order"
                                   value="{{ old('order', 0) }}"
                                   min="0"
                                   placeholder="0">
                            <small class="text-muted">Lower numbers appear first (0 = highest priority)</small>
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
                                       {{ old('is_published', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    <strong>Publish immediately</strong>
                                    <br>
                                    <small class="text-muted">Uncheck to save as draft</small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Article
                            </button>
                            <a href="{{ route('admin.help-articles.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle"></i> Writing Tips
                </div>
                <div class="card-body">
                    <h6>FAQ Articles</h6>
                    <ul class="small mb-3">
                        <li>Start with a clear question as the title</li>
                        <li>Provide step-by-step answers</li>
                        <li>Use numbered lists for procedures</li>
                        <li>Keep answers concise and direct</li>
                    </ul>

                    <h6>SOP Articles</h6>
                    <ul class="small mb-3">
                        <li>Include objectives and scope</li>
                        <li>List required tools and materials</li>
                        <li>Break down into clear steps</li>
                        <li>Add safety warnings if applicable</li>
                        <li>Include approval requirements</li>
                    </ul>

                    <h6>Tutorial Articles</h6>
                    <ul class="small mb-3">
                        <li>Write in beginner-friendly language</li>
                        <li>Include screenshots or examples</li>
                        <li>Provide common troubleshooting tips</li>
                        <li>End with next steps or related tutorials</li>
                    </ul>

                    <h6>Documentation</h6>
                    <ul class="small mb-3">
                        <li>Be comprehensive and detailed</li>
                        <li>Use proper headings and sections</li>
                        <li>Include technical specifications</li>
                        <li>Keep updated with system changes</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-warning mt-3">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-lightbulb"></i> Icon Examples
                </div>
                <div class="card-body small">
                    <p class="mb-2"><i class="fas fa-question-circle text-info"></i> <code>fa-question-circle</code> - FAQ</p>
                    <p class="mb-2"><i class="fas fa-clipboard-list text-primary"></i> <code>fa-clipboard-list</code> - Checklist</p>
                    <p class="mb-2"><i class="fas fa-tools text-success"></i> <code>fa-tools</code> - Maintenance</p>
                    <p class="mb-2"><i class="fas fa-cogs text-secondary"></i> <code>fa-cogs</code> - Settings</p>
                    <p class="mb-2"><i class="fas fa-exclamation-triangle text-warning"></i> <code>fa-exclamation-triangle</code> - Warning</p>
                    <p class="mb-2"><i class="fas fa-book text-info"></i> <code>fa-book</code> - Documentation</p>
                    <p class="mb-0"><i class="fas fa-graduation-cap text-success"></i> <code>fa-graduation-cap</code> - Tutorial</p>
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

// Sync content whenever Quill changes
quill.on('text-change', function(delta, oldDelta, source) {
    const content = document.querySelector('#content');
    if (content && quill) {
        var htmlContent = quill.root.innerHTML;
        content.value = htmlContent;
        console.log('Content synced to textarea (change event)');
        console.log('Current HTML:', htmlContent.substring(0, 150) + '...');
    }
});

// Sync Quill content to textarea before form submission
const createForm = document.getElementById('create-article-form');
if (createForm) {
    createForm.addEventListener('submit', function(e) {
        const content = document.querySelector('#content');
        const htmlContent = quill.root.innerHTML;
        content.value = htmlContent;
        console.log('Form submitting...');
        console.log('Quill HTML:', htmlContent);
        console.log('Textarea value:', content.value);
    });
} else {
    console.error('Form not found!');
}

// Also sync content periodically (backup)
setInterval(function() {
    const content = document.querySelector('#content');
    if (content && quill) {
        var htmlContent = quill.root.innerHTML;
        if (content.value !== htmlContent) {
            content.value = htmlContent;
            console.log('Content synced to textarea (interval)');
        }
    }
}, 2000); // Check every 2 seconds

// Auto-generate slug from title
document.getElementById('title').addEventListener('blur', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        const title = this.value;
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        slugInput.value = slug;
    }
});
</script>
@endpush
@endsection
