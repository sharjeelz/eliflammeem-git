@extends('layouts.tenant_admin')
@section('page_title', 'Chatbot Logs')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
<div class="container-xxl" id="kt_content_container">

    @push('page-title')
    <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
        data-kt-swapper="true" data-kt-swapper-mode="prepend"
        data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
        <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Chatbot Logs</h1>
    </div>
    @endpush

    @include('partials.alerts')

    {{-- KPI cards --}}
    <div class="row g-5 mb-7">
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100">
                <div class="card-body py-5 px-6">
                    <div class="text-muted fs-7 fw-semibold mb-1">Today</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($stats['total_today']) }}</div>
                    <div class="text-muted fs-8">questions</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100">
                <div class="card-body py-5 px-6">
                    <div class="text-muted fs-7 fw-semibold mb-1">Last 7 days</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($stats['total_week']) }}</div>
                    <div class="text-muted fs-8">questions</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100">
                <div class="card-body py-5 px-6">
                    <div class="text-muted fs-7 fw-semibold mb-1">No-answer rate</div>
                    <div class="fs-2 fw-bold {{ ($stats['no_answer_pct'] ?? 0) > 30 ? 'text-danger' : 'text-gray-900' }}">
                        {{ $stats['no_answer_pct'] !== null ? $stats['no_answer_pct'] . '%' : '—' }}
                    </div>
                    <div class="text-muted fs-8">last 30 days</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100">
                <div class="card-body py-5 px-6">
                    <div class="text-muted fs-7 fw-semibold mb-1">Avg confidence</div>
                    <div class="fs-2 fw-bold text-gray-900">
                        {{ $stats['avg_confidence'] !== null ? $stats['avg_confidence'] . '%' : '—' }}
                    </div>
                    <div class="text-muted fs-8">answered questions · last 30d</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Retention Policy notice --}}
    <div class="card card-flush mb-6 border border-dashed border-info">
        <div class="card-body py-4 px-6">
            <div class="d-flex align-items-start gap-4">
                <i class="ki-duotone ki-shield-tick fs-2x text-info mt-1 flex-shrink-0">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div>
                    <div class="fw-bold text-gray-800 mb-1">Data Retention &amp; Privacy Policy</div>
                    <div class="text-muted fs-7 lh-lg">
                        Chatbot logs are retained in accordance with GDPR guidelines:
                        <span class="fw-semibold text-gray-700">IP addresses are anonymised after 30 days</span> and
                        <span class="fw-semibold text-gray-700">full records are deleted after 90 days.</span>
                        Questions and answers are stored solely for quality and observability purposes and are
                        never shared with third parties. Logs are purged automatically every night at 02:00.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card card-flush mb-6">
        <div class="card-body py-5">
            <form method="GET" class="d-flex flex-wrap gap-3 align-items-end">
                <div>
                    <label class="form-label fs-7 mb-1">Search question</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="keyword…"
                           class="form-control form-control-sm w-200px">
                </div>
                <div>
                    <label class="form-label fs-7 mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="form-control form-control-sm w-150px">
                </div>
                <div>
                    <label class="form-label fs-7 mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="form-control form-control-sm w-150px">
                </div>
                <div>
                    <label class="form-label fs-7 mb-1">Confidence</label>
                    <select name="confidence" class="form-select form-select-sm w-130px">
                        <option value="">All</option>
                        <option value="high"   {{ request('confidence') === 'high'   ? 'selected' : '' }}>High (≥80%)</option>
                        <option value="medium" {{ request('confidence') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low"    {{ request('confidence') === 'low'    ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2 mt-4">
                    <input type="checkbox" name="no_answer" value="1" id="no_answer"
                           {{ request('no_answer') ? 'checked' : '' }}
                           class="form-check-input mt-0">
                    <label for="no_answer" class="form-label mb-0 fs-7">No-answer only</label>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.chatbot.logs') }}" class="btn btn-sm btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs table --}}
    <div class="card card-flush">
        <div class="card-header pt-6">
            <div class="card-title">
                <span class="text-muted fw-semibold fs-7">{{ number_format($logs->total()) }} interactions</span>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-200px">Question</th>
                            <th class="min-w-200px">Answer</th>
                            <th>Confidence</th>
                            <th>Sources</th>
                            <th>Response</th>
                            <th>Filters</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $label = $log->confidence_label;
                            $badgeClass = match($label) {
                                'high'      => 'badge-light-success',
                                'medium'    => 'badge-light-warning',
                                'low'       => 'badge-light-danger',
                                'no-answer' => 'badge-light-dark',
                                default     => 'badge-light',
                            };
                            $badgeText = match($label) {
                                'high'      => 'High',
                                'medium'    => 'Medium',
                                'low'       => 'Low',
                                'no-answer' => 'No answer',
                                default     => '—',
                            };
                        @endphp
                        <tr>
                            {{-- Question --}}
                            <td>
                                <span class="text-gray-900 fw-semibold fs-7 d-block"
                                      title="{{ $log->question }}">
                                    {{ Str::limit($log->question, 80) }}
                                </span>
                                @if($log->ip_address)
                                    <span class="text-muted fs-8">{{ $log->ip_address }}</span>
                                @endif
                            </td>

                            {{-- Answer --}}
                            <td>
                                @if($log->answer)
                                    <span class="text-gray-700 fs-7" title="{{ $log->answer }}">
                                        {{ Str::limit($log->answer, 100) }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic fs-7">No answer returned</span>
                                @endif
                            </td>

                            {{-- Confidence badge --}}
                            <td>
                                <span class="badge {{ $badgeClass }} fs-8">{{ $badgeText }}</span>
                                @if($label !== 'no-answer')
                                    <div class="text-muted fs-8">{{ round($log->confidence * 100) }}%</div>
                                @endif
                            </td>

                            {{-- Sources --}}
                            <td>
                                @if($log->sources)
                                    @foreach($log->sources as $src)
                                        @php $isFaq = ($src['type'] ?? 'document') === 'faq'; @endphp
                                        <span class="badge {{ $isFaq ? 'badge-light-info' : 'badge-light' }} fs-8 mb-1 d-block text-start"
                                              title="{{ $src['document_title'] ?? '?' }}">
                                            {{ Str::limit($src['document_title'] ?? '?', 35) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted fs-8">—</span>
                                @endif
                            </td>

                            {{-- Response time --}}
                            <td>
                                <span class="text-gray-700 fs-7">
                                    {{ $log->response_ms !== null ? $log->response_ms . ' ms' : '—' }}
                                </span>
                                @if($log->used_fallback)
                                    <div>
                                        <span class="badge badge-light-warning fs-8">fallback</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Metadata filters --}}
                            <td>
                                @if($log->metadata_filters)
                                    @foreach($log->metadata_filters as $key => $val)
                                        <span class="badge badge-light-primary fs-8 me-1">
                                            {{ $key }}:{{ is_array($val) ? implode(',', $val) : $val }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted fs-8">none</span>
                                @endif
                            </td>

                            {{-- Time --}}
                            <td class="text-muted fs-8 text-nowrap">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-10">No chatbot interactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
