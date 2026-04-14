@extends('layouts.tenant_admin')
@section('page_title', 'Activity Log')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Activity Log</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Activity Log</li>
            </ul>
        </div>
        @endpush

        <div class="card">
            {{-- Filters --}}
            <div class="card-header border-0 pt-6 pb-4 d-block">
                @php $col = $branches->isNotEmpty() ? 'col-sm-6 col-md-3' : 'col-sm-6 col-md-4'; @endphp
                <form method="GET">

                    {{-- Row 1: search fields --}}
                    <div class="row g-3 mb-3">

                        <div class="{{ $col }}">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">Issue ID</label>
                            <input type="text" name="issue" value="{{ request('issue') }}"
                                placeholder="e.g. ISS-001"
                                class="form-control form-control-solid">
                        </div>

                        <div class="{{ $col }}">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">Action</label>
                            <select name="type" class="form-select form-select-solid">
                                <option value="">All actions</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" @selected(request('type') === $t)>
                                        {{ ucfirst(str_replace('_', ' ', $t)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($branches->isNotEmpty())
                        <div class="{{ $col }}">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">Branch</label>
                            <select name="branch_id" class="form-select form-select-solid">
                                <option value="">All branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="{{ $col }}">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">User</label>
                            <select name="actor_id" class="form-select form-select-solid">
                                <option value="">All users</option>
                                @foreach($actors as $a)
                                    <option value="{{ $a->id }}" @selected(request('actor_id') == $a->id)>
                                        {{ $a->name }}@if($a->branches->isNotEmpty()) — {{ $a->branches->pluck('name')->join(', ') }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- Row 2: date range + buttons --}}
                    <div class="row g-3 align-items-end">

                        <div class="col-sm-6 col-md-3">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">From</label>
                            <input type="date" name="from" value="{{ request('from') }}"
                                class="form-control form-control-solid">
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-1">To</label>
                            <input type="date" name="to" value="{{ request('to') }}"
                                class="form-control form-control-solid">
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-end justify-content-md-end gap-2 pt-1">
                            <button type="submit" class="btn btn-primary btn-sm px-5">Apply</button>
                            <a href="{{ route('tenant.admin.activity_log') }}" class="btn btn-light btn-sm px-5">Reset</a>
                        </div>

                    </div>

                </form>
            </div>

            {{-- Table --}}
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                <th class="min-w-160px">When</th>
                                <th class="min-w-110px">Issue</th>
                                <th class="min-w-130px">User</th>
                                <th class="min-w-130px">Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($activities as $a)
                            @php
                                $badgeColor = match($a->type) {
                                    'assigned'        => 'info',
                                    'status_changed'  => 'primary',
                                    'priority_changed'=> 'warning',
                                    'commented'       => 'success',
                                    'message_deleted' => 'danger',
                                    'contact_moved'   => 'dark',
                                    default           => 'secondary',
                                };

                                $description = match($a->type) {
                                    'assigned' => (function() use ($a, $userMap) {
                                        $from = $a->data['from'] ? ($userMap[$a->data['from']] ?? 'User #'.$a->data['from']) : 'System';
                                        $to   = $a->data['to']   ? ($userMap[$a->data['to']]   ?? 'User #'.$a->data['to'])   : 'System';
                                        return 'From <strong>'.e($from).'</strong> → <strong>'.e($to).'</strong>';
                                    })(),
                                    'status_changed'   => 'Status: <strong>'.ucwords(str_replace('_',' ',$a->data['from'] ?? '—')).'</strong> → <strong>'.ucwords(str_replace('_',' ',$a->data['to'] ?? '—')).'</strong>'.(!empty($a->data['note']) ? ' <span class="text-muted fst-italic fs-7">— '.e($a->data['note']).'</span>' : ''),
                                    'priority_changed' => 'Priority: <strong>'.ucfirst($a->data['from'] ?? '—').'</strong> → <strong>'.ucfirst($a->data['to'] ?? '—').'</strong>',
                                    'commented'        => 'Comment: <span class="text-muted fst-italic">'.e($a->data['preview'] ?? '').'</span>',
                                    'message_deleted'  => 'Deleted message by <strong>'.e($a->data['author_name'] ?? '?').'</strong>: <span class="text-muted fst-italic">'.e($a->data['preview'] ?? '').'</span>',
                                    'contact_moved'    => (function() use ($a) {
                                        $html = 'Contact <strong>'.e($a->data['contact_name'] ?? '?').'</strong> moved';
                                        if (!empty($a->data['from_branch'])) {
                                            $html .= ' from <strong>'.e($a->data['from_branch']).'</strong>';
                                        }
                                        $html .= ' to <strong>'.e($a->data['to_branch'] ?? '?').'</strong>';
                                        return $html;
                                    })(),
                                    default            => e(json_encode($a->data)),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span title="{{ $a->created_at->format('Y-m-d H:i:s') }}">
                                        {{ $a->created_at->format('M d, Y H:i') }}
                                    </span>
                                    <div class="text-muted fs-7">{{ $a->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if($a->issue)
                                        <a href="{{ route('tenant.admin.issue.show', $a->issue->id) }}"
                                           class="fw-bold text-dark text-hover-primary">
                                            {{ $a->issue->public_id }}
                                        </a>
                                        <div class="text-muted fs-7 text-truncate" style="max-width:180px">
                                            {{ $a->issue->title }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->actor)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="symbol symbol-30px symbol-circle">
                                                <div class="symbol-label fw-bold text-white fs-8"
                                                     style="background:linear-gradient(135deg,#4338ca,#6366f1)">
                                                    {{ strtoupper(substr($a->actor->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <span>{{ $a->actor->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $badgeColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $a->type)) }}
                                    </span>
                                </td>
                                <td>{!! $description !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">No activity found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
