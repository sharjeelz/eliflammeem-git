@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Frequently Asked Questions</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">FAQs</li>
            </ul>
        </div>
        @endpush

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                        <form method="GET" class="d-flex gap-3">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid w-250px ps-13" placeholder="Search question or answer...">
                            <select name="category_id" class="form-select form-select-solid w-200px">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <select name="published" class="form-select form-select-solid w-150px">
                                <option value="">All Status</option>
                                <option value="yes" {{ request('published') === 'yes' ? 'selected' : '' }}>Published</option>
                                <option value="no" {{ request('published') === 'no' ? 'selected' : '' }}>Draft</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            @if(request()->hasAny(['search', 'category_id', 'published']))
                                <a href="{{ route('tenant.admin.faqs.index') }}" class="btn btn-sm btn-light">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.faqs.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Add FAQ
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                @if($faqs->count() > 0)
                    <div class="accordion" id="faqAccordion">
                        @foreach($faqs as $index => $faq)
                            <div class="accordion-item mb-3 border rounded">
                                <h2 class="accordion-header" id="heading{{ $faq->id }}">
                                    <button class="accordion-button collapsed fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $faq->id }}" aria-expanded="false">
                                        <div class="d-flex align-items-center w-100">
                                            <div class="flex-grow-1">
                                                {{ $faq->question }}
                                            </div>
                                            <div class="d-flex gap-2 me-3">
                                                @if($faq->category)
                                                    <span class="badge badge-light-primary">{{ $faq->category->name }}</span>
                                                @endif
                                                @if($faq->is_published)
                                                    <span class="badge badge-light-success">Published</span>
                                                @else
                                                    <span class="badge badge-light-warning">Draft</span>
                                                @endif
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <div class="mb-4">
                                            <div class="text-gray-800">{{ $faq->answer }}</div>
                                        </div>
                                        <div class="separator mb-4"></div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex gap-4 text-muted fs-7">
                                                <span>
                                                    <i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                    {{ number_format($faq->view_count) }} views
                                                </span>
                                                @if($faq->helpful_count + $faq->not_helpful_count > 0)
                                                    <span>
                                                        <i class="ki-duotone ki-like fs-5 me-1 text-success"><span class="path1"></span><span class="path2"></span></i>
                                                        {{ $faq->helpfulPercentage }}% helpful
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('tenant.admin.faqs.edit', $faq) }}" class="btn btn-sm btn-light-primary">
                                                    <i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('tenant.admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger">
                                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        {{ $faqs->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="ki-duotone ki-question-2 fs-5x text-gray-400 mb-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <h3 class="text-gray-800 fw-bold mb-3">No FAQs Found</h3>
                        <p class="text-muted mb-5">
                            @if(request()->hasAny(['search', 'category_id', 'published']))
                                No FAQs match your filters. Try adjusting your search criteria.
                            @else
                                Get started by adding your first FAQ to help answer common questions.
                            @endif
                        </p>
                        @if(request()->hasAny(['search', 'category_id', 'published']))
                            <a href="{{ route('tenant.admin.faqs.index') }}" class="btn btn-light me-3">Clear Filters</a>
                        @endif
                        <a href="{{ route('tenant.admin.faqs.create') }}" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            Add FAQ
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
