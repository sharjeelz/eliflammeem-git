@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Document</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.documents.index') }}" class="text-muted text-hover-primary">Documents</a></li>
                <li class="breadcrumb-item text-gray-900">Edit</li>
            </ul>
        </div>
        @endpush

        <form method="POST" action="{{ route('tenant.admin.documents.update', $document) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-6">
                <div class="col-lg-8">
                    <div class="card mb-5">
                        <div class="card-header"><h3 class="card-title fw-bold">Current File</h3></div>
                        <div class="card-body">
                            <div class="d-flex align-items-center bg-light rounded p-5 mb-5">
                                <div class="flex-shrink-0 me-4">
                                    {!! $document->fileIcon !!}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-gray-800 fs-5 mb-1">{{ $document->filename }}</div>
                                    <div class="text-muted fs-7">
                                        <span>{{ $document->formattedSize }}</span>
                                        <span class="mx-2">•</span>
                                        <span>Uploaded {{ $document->created_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('tenant.admin.documents.download', $document) }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-duotone ki-file-down fs-4"><span class="path1"></span><span class="path2"></span></i>
                                        Download
                                    </a>
                                </div>
                            </div>

                            <div class="alert alert-warning d-flex align-items-center mb-5">
                                <i class="ki-duotone ki-information-5 fs-2x text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-column">
                                    <h5 class="mb-1">File Replacement</h5>
                                    <span>To replace this document, upload a new file below. <strong>The old file will be permanently deleted.</strong></span>
                                </div>
                            </div>

                            <div class="row g-5">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Replace File <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept="application/pdf,.doc,.docx,.txt">
                                    <div class="text-muted fs-8 mt-1">Supported: PDF, DOC, DOCX, TXT. Maximum size: {{ config('app.max_document_size_kb', 25600) / 1024 }} MB</div>
                                    @error('file')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Document Title</label>
                                    <input name="title" value="{{ old('title', $document->title) }}" class="form-control form-control-solid @error('title') is-invalid @enderror" placeholder="e.g. Student Handbook 2024-25" required>
                                    @error('title')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea name="description" rows="3" class="form-control form-control-solid" placeholder="Brief description...">{{ old('description', $document->description) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Category</label>
                                    <select name="category_id" class="form-select form-select-solid">
                                        <option value="">— None —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id', $document->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->full_path }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold">Type</label>
                                    <select name="type" id="doc_type" class="form-select form-select-solid" required>
                                        @foreach($types as $value => $label)
                                            <option value="{{ $value }}" {{ old('type', $document->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Display Order</label>
                                    <input type="number" name="display_order" value="{{ old('display_order', $document->display_order) }}" class="form-control form-control-solid" min="0">
                                </div>
                                {{-- Shown only for form / handbook --}}
                                <div class="col-md-6" id="download_section"
                                     style="{{ in_array(old('type', $document->type), ['form','handbook']) ? '' : 'display:none' }}">
                                    <label class="form-label fw-semibold d-block mb-2">Parent Access</label>
                                    <div class="bg-light-success rounded p-4 border border-dashed border-success">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-success">
                                                    <i class="ki-duotone ki-people fs-2 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </span>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-gray-800 fs-7">Parent Download</div>
                                                <div class="text-muted fs-8">Forms &amp; handbooks parents can save</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid form-check-success">
                                            <input class="form-check-input" type="checkbox" name="allow_public_download" value="1" id="allow_public_download"
                                                   {{ old('allow_public_download', $document->allow_public_download) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-gray-700" for="allow_public_download">Allow parents to download</label>
                                        </div>
                                    </div>
                                </div>
                                {{-- Shown for all other types --}}
                                <div class="col-md-6" id="chatbot_section"
                                     style="{{ in_array(old('type', $document->type), ['form','handbook']) ? 'display:none' : '' }}">
                                    <label class="form-label fw-semibold d-block mb-2">Chatbot Access</label>
                                    <div class="bg-light-primary rounded p-4 border border-dashed border-primary">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-primary">
                                                    <i class="ki-duotone ki-message-text-2 fs-2 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </span>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-gray-800 fs-7">AI Chatbot</div>
                                                <div class="text-muted fs-8">Used to answer parent questions</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid form-check-primary">
                                            <input class="form-check-input" type="checkbox" name="include_in_chatbot" value="1" id="include_in_chatbot"
                                                   {{ old('include_in_chatbot', $document->include_in_chatbot) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-gray-700" for="include_in_chatbot">Include in chatbot responses</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Save Changes
                        </button>
                        <a href="{{ route('tenant.admin.documents.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-5">
                        <div class="card-body py-5">
                            <div class="fw-bold text-gray-800 mb-4">Document Statistics</div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Status</span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($document->is_published)
                                        <span class="badge badge-light-success">Published</span>
                                    @else
                                        <span class="badge badge-light-warning">Draft</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Chatbot Access</span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($document->include_in_chatbot)
                                        <span class="badge badge-light-success">Enabled</span>
                                    @else
                                        <span class="badge badge-light-secondary">Disabled</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Text Extraction</span>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge badge-light-{{ $document->extraction_status_badge }}" title="{{ $document->text_extraction_error ?? '' }}">
                                        {{ $document->extraction_status_label }}
                                    </span>
                                </div>
                            </div>
                            @if($document->text_extraction_status === 'failed' && $document->text_extraction_error)
                            <div class="alert alert-danger d-flex align-items-start p-3 mb-3">
                                <i class="ki-duotone ki-information-2 fs-4 text-danger me-2 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="fs-8">
                                    <strong>Extraction Failed:</strong><br>
                                    {{ $document->text_extraction_error }}
                                </div>
                            </div>
                            @endif
                            @if($document->text_extraction_status === 'completed' && $document->text_extracted_at)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Extracted</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $document->text_extracted_at->format('M j, Y') }}
                                </div>
                            </div>
                            @endif
                            <div class="separator separator-dashed my-4"></div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Embedding Status</span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($document->include_in_chatbot)
                                        <span class="badge badge-light-{{ $document->embedding_status_badge }}" title="{{ $document->embedding_error ?? '' }}">
                                            {{ $document->embedding_status_label }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-secondary">Not Enabled</span>
                                    @endif
                                </div>
                            </div>
                            @if($document->include_in_chatbot && $document->embedding_status === 'failed' && $document->embedding_error)
                            <div class="alert alert-danger d-flex align-items-start p-3 mb-3">
                                <i class="ki-duotone ki-information-2 fs-4 text-danger me-2 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="fs-8">
                                    <strong>Embedding Failed:</strong><br>
                                    {{ $document->embedding_error }}
                                </div>
                            </div>
                            @endif
                            @if($document->embedding_status === 'completed' && $document->chunk_count > 0)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Chunks Created</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $document->chunk_count }}
                                </div>
                            </div>
                            @endif
                            @if($document->embedding_status === 'completed' && $document->embeddings_generated_at)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Embeddings Generated</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $document->embeddings_generated_at->format('M j, Y') }}
                                </div>
                            </div>
                            @endif
                            <div class="separator separator-dashed my-4"></div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Created</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $document->created_at->format('M j, Y') }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Last Updated</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $document->updated_at->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border border-dashed border-info">
                        <div class="card-body py-5">
                            <div class="fw-bold text-gray-800 mb-3">Edit Tips</div>
                            <ul class="text-muted fs-7 ps-4 mb-0">
                                <li class="mb-2">Leave the file input empty to keep the current file</li>
                                <li class="mb-2">Uploading a new file will permanently delete the old one</li>
                                <li class="mb-2">Enable <strong>Chatbot Access</strong> to let the AI use this document</li>
                                <li>Documents are never shown directly to parents - only used by AI</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function () {
    const typeSelect      = document.getElementById('doc_type');
    const downloadSection = document.getElementById('download_section');
    const chatbotSection  = document.getElementById('chatbot_section');
    const downloadChk     = document.getElementById('allow_public_download');
    const chatbotChk      = document.getElementById('include_in_chatbot');
    const downloadTypes   = ['form', 'handbook'];

    function updateSections(type, isInit) {
        const isDownload = downloadTypes.includes(type);
        downloadSection.style.display = isDownload ? '' : 'none';
        chatbotSection.style.display  = isDownload ? 'none' : '';
        if (!isInit) {
            downloadChk.checked = isDownload;
            chatbotChk.checked  = !isDownload;
        }
    }

    typeSelect.addEventListener('change', function () { updateSections(this.value, false); });
    updateSections(typeSelect.value, true);
})();
</script>
@endpush

@endsection
