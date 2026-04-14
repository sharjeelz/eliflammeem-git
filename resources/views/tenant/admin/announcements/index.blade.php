@extends('layouts.tenant_admin')
@section('page_title', 'Announcements')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Announcements</h1>
        </div>
        @endpush

        @include('partials.alerts')

        <div class="card">
            <div class="card-header border-0 pt-6 d-flex align-items-center justify-content-between">
                <div class="card-title fs-3 fw-bold">School Announcements</div>
                <div>
                    <a href="{{ route('tenant.admin.announcements.create') }}" class="btn btn-primary btn-sm">
                        <i class="ki-duotone ki-plus fs-3"></i> New Announcement
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                @if($announcements->isEmpty())
                <div class="text-center py-12 text-muted fs-6">
                    No announcements yet. Create one to let parents know what you've improved.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th>Author</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $a)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-gray-800">{{ $a->title }}</span>
                                    <div class="text-muted fs-7 mt-1">{{ Str::limit($a->body, 80) }}</div>
                                </td>
                                <td>
                                    @if($a->category)
                                    <span class="badge badge-light-primary">{{ $a->category->name }}</span>
                                    @else
                                    <span class="text-muted fs-7">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->is_published)
                                    <span class="badge badge-light-success">Published</span>
                                    @else
                                    <span class="badge badge-light-warning">Draft</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-7">
                                    {{ $a->published_at ? $a->published_at->format('d M Y') : '—' }}
                                </td>
                                <td class="text-muted fs-7">{{ $a->author->name }}</td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.announcements.edit', $a) }}" class="btn btn-icon btn-sm btn-light btn-active-light-primary me-1" title="Edit">
                                        <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                    @if($a->is_published)
                                    <form method="POST" action="{{ route('tenant.admin.announcements.draft', $a) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-sm btn-light btn-active-light-warning me-1" title="Move to Draft">
                                            <i class="ki-duotone ki-eye-slash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('tenant.admin.announcements.publish', $a) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-sm btn-light btn-active-light-success me-1" title="Publish Now">
                                            <i class="ki-duotone ki-check-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('tenant.admin.announcements.destroy', $a) }}" class="d-inline"
                                        onsubmit="return confirm('Delete this announcement?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-light btn-active-light-danger" title="Delete">
                                            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $announcements->links() }}
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
