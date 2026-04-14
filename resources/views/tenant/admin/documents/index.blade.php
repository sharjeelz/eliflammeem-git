@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Documents</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Documents</li>
            </ul>
        </div>
        @endpush

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.admin.documents.index') }}" class="card card-body mb-5 py-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid" placeholder="Document title…">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-solid">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->full_path }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 mb-1">Type</label>
                    <select name="type" class="form-select form-select-solid">
                        <option value="">All Types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 mb-1">Chatbot Access</label>
                    <select name="chatbot" class="form-select form-select-solid">
                        <option value="">All</option>
                        <option value="enabled" {{ request('chatbot') == 'enabled' ? 'selected' : '' }}>Enabled</option>
                        <option value="disabled" {{ request('chatbot') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 mb-1">Text Status</label>
                    <select name="extraction_status" class="form-select form-select-solid">
                        <option value="">All</option>
                        <option value="completed" {{ request('extraction_status') == 'completed' ? 'selected' : '' }}>Extracted</option>
                        <option value="processing" {{ request('extraction_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="failed" {{ request('extraction_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('extraction_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 mb-1">Embedding Status</label>
                    <select name="embedding_status" class="form-select form-select-solid">
                        <option value="">All</option>
                        <option value="completed" {{ request('embedding_status') == 'completed' ? 'selected' : '' }}>Ready</option>
                        <option value="processing" {{ request('embedding_status') == 'processing' ? 'selected' : '' }}>Generating</option>
                        <option value="failed" {{ request('embedding_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('embedding_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $documents->total() }} document{{ $documents->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.documents.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Upload Document
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Document</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Chatbot</th>
                            <th>Text Status</th>
                            <th>Embeddings</th>
                            <th>Uploaded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($documents as $doc)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-duotone {{ $doc->file_icon }} fs-2x"></i>
                                        <div>
                                            <a href="{{ route('tenant.admin.documents.edit', $doc) }}" class="text-gray-800 text-hover-primary fw-bold">{{ $doc->title }}</a>
                                            <div class="text-muted fs-8">{{ $doc->file_extension }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($doc->category)
                                        <span class="badge badge-light-secondary">{{ $doc->category->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-light-primary">{{ ucfirst($doc->type) }}</span></td>
                                <td>{{ $doc->file_size_formatted }}</td>
                                <td>
                                    @if($doc->include_in_chatbot)
                                        <span class="badge badge-success">Enabled</span>
                                    @else
                                        <span class="badge badge-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $doc->extraction_status_badge }}" title="{{ $doc->text_extraction_error ?? '' }}">
                                        {{ $doc->extraction_status_label }}
                                    </span>
                                    @if($doc->text_extraction_status === 'failed' && $doc->text_extraction_error)
                                        <i class="ki-duotone ki-information-2 fs-6 text-danger ms-1" data-bs-toggle="tooltip" title="{{ $doc->text_extraction_error }}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    @endif
                                </td>
                                <td>
                                    @if($doc->include_in_chatbot)
                                        <span class="badge badge-{{ $doc->embedding_status_badge }}" title="{{ $doc->embedding_error ?? '' }}">
                                            {{ $doc->embedding_status_label }}
                                        </span>
                                        @if($doc->embedding_status === 'completed' && $doc->chunk_count > 0)
                                            <div class="text-muted fs-8 mt-1">{{ $doc->chunk_count }} chunks</div>
                                        @endif
                                        @if($doc->embedding_status === 'failed' && $doc->embedding_error)
                                            <i class="ki-duotone ki-information-2 fs-6 text-danger ms-1" data-bs-toggle="tooltip" title="{{ $doc->embedding_error }}">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">—</span>
                                    @endif
                                </td>
                                <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.documents.download', $doc) }}" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="Download">
                                        <i class="ki-duotone ki-cloud-download fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                    <a href="{{ route('tenant.admin.documents.edit', $doc) }}" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="Edit">
                                        <i class="ki-duotone ki-notepad-edit fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenant.admin.documents.destroy', $doc) }}" class="d-inline" onsubmit="return confirm('Delete document {{ addslashes($doc->title) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-light-danger" title="Delete">
                                            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-10">
                                    No documents yet. <a href="{{ route('tenant.admin.documents.create') }}">Upload your first document</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
