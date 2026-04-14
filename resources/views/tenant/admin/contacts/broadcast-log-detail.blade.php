@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Broadcast Details</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.broadcast.logs') }}" class="text-muted text-hover-primary">Broadcast Logs</a></li>
                <li class="breadcrumb-item text-gray-900">Details</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6 mb-6">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted fs-7 mb-2">Subject</div>
                        <div class="fw-semibold text-gray-800">{{ $batch->subject ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted fs-7 mb-2">Channel</div>
                        @if($batch->channel === 'email')
                            <span class="badge badge-light-primary">Email</span>
                        @elseif($batch->channel === 'sms')
                            <span class="badge badge-light-success">SMS</span>
                        @elseif($batch->channel === 'whatsapp')
                            <span class="badge badge-light-success">WhatsApp</span>
                        @else
                            <span class="badge badge-light-warning">Both</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted fs-7 mb-2">Sent</div>
                        <div class="fw-semibold text-gray-800">{{ $batch->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title fw-bold">Message</h3>
            </div>
            <div class="card-body">
                <div class="text-gray-700" style="white-space: pre-wrap;">{{ $batch->message }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $recipients->total() }} recipient{{ $recipients->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar d-flex gap-2">
                    <span class="badge badge-light-success">{{ $batch->sent_count }} sent</span>
                    @if($batch->failed_count > 0)
                        <span class="badge badge-light-danger">{{ $batch->failed_count }} failed</span>
                    @endif
                    @if($batch->pending_count > 0)
                        <span class="badge badge-light-warning">{{ $batch->pending_count }} pending</span>
                    @endif
                </div>
            </div>

            <div class="card-body py-4">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Recipient</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($recipients as $recipient)
                        <tr>
                            <td>
                                <div class="text-gray-800 fw-semibold">{{ $recipient->contact_name }}</div>
                            </td>
                            <td>
                                <div>{{ $recipient->contact_email ?? '—' }}</div>
                                @if($recipient->contact_phone)
                                    <div class="text-muted fs-7">{{ $recipient->contact_phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($recipient->status === 'sent')
                                    <span class="badge badge-light-success">Sent</span>
                                @elseif($recipient->status === 'failed')
                                    <span class="badge badge-light-danger">Failed</span>
                                @else
                                    <span class="badge badge-light-warning">Pending</span>
                                @endif
                                @if($recipient->status === 'failed' && $recipient->error_message)
                                    <div class="text-danger fs-7 mt-1" title="{{ $recipient->error_message }}">
                                        {{ Str::limit($recipient->error_message, 40) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($recipient->sent_at)
                                    <div class="text-gray-800">{{ $recipient->sent_at->format('M d, Y') }}</div>
                                    <div class="text-muted fs-7">{{ $recipient->sent_at->format('h:i A') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($recipient->status === 'failed')
                                    <form method="POST" action="{{ route('tenant.admin.contacts.broadcast.logs.retry', $recipient) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light-primary">
                                            <i class="ki-duotone ki-arrows-circle fs-5"><span class="path1"></span><span class="path2"></span></i>
                                            Retry
                                        </button>
                                    </form>
                                @elseif($recipient->status === 'pending')
                                    <span class="text-muted fs-7">Processing...</span>
                                @else
                                    <span class="text-success">
                                        <i class="ki-duotone ki-check fs-5"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-10">
                                No recipients found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $recipients->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
