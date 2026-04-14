@extends('layouts.tenant_admin')

@section('page_title', 'My Support Tickets')

@push('page-title')
<div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
        Support Tickets
    </h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('tenant.admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">Support Tickets</li>
    </ul>
</div>
@endpush

@section('content')
<div class="container-fluid px-6 py-6">

    <div class="d-flex align-items-center justify-content-between mb-6">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">My Support Tickets</h2>
            <p class="text-muted fs-7 mb-0">Issues and requests you've submitted to the platform team.</p>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#support-modal">
            <i class="ki-duotone ki-message-text-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            New Ticket
        </button>
    </div>

    @if($tickets->isEmpty())
        <div class="card">
            <div class="card-body text-center py-16">
                <i class="ki-duotone ki-message-question fs-5x text-gray-300 mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <p class="text-muted fs-6 mb-4">You haven't submitted any support tickets yet.</p>
                <button class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#support-modal">
                    Submit your first ticket
                </button>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 mb-0">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase bg-light">
                                <th class="ps-6">#</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Notes from Team</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td class="ps-6">
                                    <span class="text-muted fw-semibold fs-7">#{{ $ticket->id }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-800 fs-7">{{ $ticket->subject }}</span>
                                    <div class="text-muted fs-8 mt-1 text-truncate" style="max-width:280px;">
                                        {{ Str::limit($ticket->message, 80) }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'bug'             => ['label' => 'Bug / Error',       'class' => 'badge-light-danger'],
                                            'question'        => ['label' => 'Question',           'class' => 'badge-light-primary'],
                                            'billing'         => ['label' => 'Billing',            'class' => 'badge-light-warning'],
                                            'feature_request' => ['label' => 'Feature Request',   'class' => 'badge-light-info'],
                                            'other'           => ['label' => 'Other',              'class' => 'badge-light-secondary'],
                                        ];
                                        $tl = $typeLabels[$ticket->type] ?? ['label' => ucfirst($ticket->type), 'class' => 'badge-light-secondary'];
                                    @endphp
                                    <span class="badge {{ $tl['class'] }} fs-8">{{ $tl['label'] }}</span>
                                </td>
                                <td>
                                    @php
                                        $priorityClasses = [
                                            'urgent' => 'badge-danger',
                                            'high'   => 'badge-light-danger',
                                            'medium' => 'badge-light-warning',
                                            'low'    => 'badge-light-success',
                                        ];
                                        $pc = $priorityClasses[$ticket->priority] ?? 'badge-light-secondary';
                                    @endphp
                                    <span class="badge {{ $pc }} fs-8">{{ ucfirst($ticket->priority) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusConf = [
                                            'open'        => ['label' => 'Open',        'class' => 'badge-light-warning', 'dot' => '#f59e0b'],
                                            'in_progress' => ['label' => 'In Progress', 'class' => 'badge-light-primary', 'dot' => '#4f46e5'],
                                            'resolved'    => ['label' => 'Resolved',    'class' => 'badge-light-success', 'dot' => '#22c55e'],
                                        ];
                                        $sc = $statusConf[$ticket->status] ?? ['label' => ucfirst($ticket->status), 'class' => 'badge-light-secondary', 'dot' => '#94a3b8'];
                                    @endphp
                                    <span class="badge {{ $sc['class'] }} fs-8 d-inline-flex align-items-center gap-1">
                                        <span class="rounded-circle d-inline-block" style="width:6px;height:6px;background:{{ $sc['dot'] }};flex-shrink:0;"></span>
                                        {{ $sc['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted fs-8">{{ $ticket->created_at->diffForHumans() }}</span>
                                    <div class="text-muted fs-9">{{ $ticket->created_at->format('d M Y') }}</div>
                                </td>
                                <td style="max-width:220px;">
                                    @if($ticket->admin_notes)
                                        <div class="text-gray-700 fs-8 bg-light-success rounded px-3 py-2" style="border-left:3px solid #22c55e;white-space:pre-wrap;">{{ $ticket->admin_notes }}</div>
                                    @else
                                        <span class="text-muted fs-8">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
