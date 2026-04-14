@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit FAQ</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.faqs.index') }}" class="text-muted text-hover-primary">FAQs</a></li>
                <li class="breadcrumb-item text-gray-900">Edit</li>
            </ul>
        </div>
        @endpush

        <form method="POST" action="{{ route('tenant.admin.faqs.update', $faq) }}">
            @csrf
            @method('PUT')
            <div class="row g-6">
                <div class="col-lg-8">
                    <div class="card mb-5">
                        <div class="card-header"><h3 class="card-title fw-bold">FAQ Details</h3></div>
                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Question</label>
                                    <textarea name="question" rows="2" class="form-control form-control-solid @error('question') is-invalid @enderror" placeholder="What is the school's refund policy?" required>{{ old('question', $faq->question) }}</textarea>
                                    <div class="text-muted fs-8 mt-1">Keep questions clear and concise (max 500 characters)</div>
                                    @error('question')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Answer</label>
                                    <textarea name="answer" rows="8" class="form-control form-control-solid @error('answer') is-invalid @enderror" placeholder="Provide a detailed answer here..." required>{{ old('answer', $faq->answer) }}</textarea>
                                    <div class="text-muted fs-8 mt-1">Provide a complete, helpful answer (max 5000 characters)</div>
                                    @error('answer')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Category</label>
                                    <select name="category_id" class="form-select form-select-solid">
                                        <option value="">— None —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id', $faq->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Display Order</label>
                                    <input type="number" name="display_order" value="{{ old('display_order', $faq->display_order) }}" class="form-control form-control-solid" min="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Related Documents <span class="text-muted fw-normal">(optional)</span></label>
                                    <select name="related_document_ids[]" class="form-select form-select-solid" multiple data-control="select2" data-placeholder="Select related documents">
                                        @foreach($documents as $doc)
                                            <option value="{{ $doc->id }}" {{ in_array($doc->id, old('related_document_ids', $faq->related_document_ids ?? [])) ? 'selected' : '' }}>{{ $doc->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-muted fs-8 mt-1">Link documents that provide additional information for this FAQ</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold d-block mb-3">Publish Status</label>
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', $faq->is_published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">
                                            Published <span class="text-muted fw-normal">(visible to parents)</span>
                                        </label>
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
                        <a href="{{ route('tenant.admin.faqs.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-5">
                        <div class="card-body py-5">
                            <div class="fw-bold text-gray-800 mb-4">FAQ Analytics</div>
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-shrink-0 me-4">
                                    <i class="ki-duotone ki-eye fs-2x text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">{{ number_format($faq->view_count) }}</div>
                                    <div class="text-muted fs-7">Total Views</div>
                                </div>
                            </div>
                            @if($faq->helpful_count + $faq->not_helpful_count > 0)
                                <div class="separator mb-4"></div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0 me-4">
                                        <i class="ki-duotone ki-like fs-2x text-success"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-gray-800 fw-bold fs-3">{{ $faq->helpfulPercentage }}%</div>
                                        <div class="text-muted fs-7">Found Helpful</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted fs-7">
                                    <span><i class="ki-duotone ki-like fs-5 me-1 text-success"><span class="path1"></span><span class="path2"></span></i> {{ number_format($faq->helpful_count) }} helpful</span>
                                    <span><i class="ki-duotone ki-dislike fs-5 me-1 text-danger"><span class="path1"></span><span class="path2"></span></i> {{ number_format($faq->not_helpful_count) }} not helpful</span>
                                </div>
                            @else
                                <div class="text-center text-muted fs-7 py-3">
                                    No feedback yet
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card mb-5">
                        <div class="card-body py-5">
                            <div class="fw-bold text-gray-800 mb-4">Status</div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Published</span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($faq->is_published)
                                        <span class="badge badge-light-success">Yes</span>
                                    @else
                                        <span class="badge badge-light-warning">No</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Created</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $faq->created_at->format('M j, Y') }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-7">Last Updated</span>
                                </div>
                                <div class="flex-shrink-0 text-gray-800 fs-7">
                                    {{ $faq->updated_at->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border border-dashed border-info">
                        <div class="card-body py-5">
                            <div class="fw-bold text-gray-800 mb-3">FAQ Best Practices</div>
                            <ul class="text-muted fs-7 ps-4 mb-0">
                                <li class="mb-2">Write questions from the parent's perspective</li>
                                <li class="mb-2">Keep answers concise but complete</li>
                                <li class="mb-2">Use simple, jargon-free language</li>
                                <li class="mb-2">Link related documents for AI context</li>
                                <li class="mb-2">Use analytics to identify improvements</li>
                                <li>Published FAQs are used by the AI chatbot</li>
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
    // Initialize Select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('[data-control="select2"]').select2({
            placeholder: $(this).data('placeholder'),
            allowClear: true
        });
    }
</script>
@endpush
@endsection
