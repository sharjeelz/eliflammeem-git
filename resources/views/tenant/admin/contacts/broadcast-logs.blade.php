@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Broadcast Logs</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-gray-900">Broadcast Logs</li>
            </ul>
        </div>
        @endpush

        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $batches->total() }} broadcast{{ $batches->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.contacts.broadcast') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-send fs-2"><span class="path1"></span><span class="path2"></span></i>
                        New Broadcast
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Channel</th>
                            <th>Audience</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <div class="text-gray-800">{{ $batch->created_at->format('M d, Y') }}</div>
                                <div class="text-muted fs-7">{{ $batch->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="text-gray-800 fw-semibold">{{ $batch->subject ?? '—' }}</div>
                                <div class="text-muted fs-7">{{ Str::limit($batch->message, 50) }}</div>
                            </td>
                            <td>
                                @if($batch->channel === 'email')
                                    <span class="badge badge-light-primary">Email</span>
                                @elseif($batch->channel === 'sms')
                                    <span class="badge badge-light-success">SMS</span>
                                @elseif($batch->channel === 'whatsapp')
                                    <span class="badge badge-light-success">WhatsApp</span>
                                @else
                                    <span class="badge badge-light-warning">Both</span>
                                @endif
                            </td>
                            <td>
                                @if($batch->audience_type === 'all')
                                    <span class="text-muted">All contacts</span>
                                @elseif($batch->audience_type === 'filter')
                                    @php $filter = $batch->audience_filter; @endphp
                                    <span class="text-muted">
                                        {{ $filter['role'] ?? 'All roles' }}
                                        @if($filter['branch_id'])
                                            - {{ $branchNames[$filter['branch_id']] ?? 'Unknown branch' }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">{{ $batch->total_count }} selected</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <span class="badge badge-light-success">{{ $batch->sent_count }} sent</span>
                                    @if($batch->failed_count > 0)
                                        <span class="badge badge-light-danger">{{ $batch->failed_count }} failed</span>
                                    @endif
                                    @if($batch->pending_count > 0)
                                        <span class="badge badge-light-warning">{{ $batch->pending_count }} pending</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tenant.admin.contacts.broadcast.logs.detail', $batch) }}" class="btn btn-sm btn-light btn-active-light-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-10">
                                No broadcast logs yet.
                                <a href="{{ route('tenant.admin.contacts.broadcast') }}">Send your first announcement</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $batches->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
