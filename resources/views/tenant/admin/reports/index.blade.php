@extends('layouts.tenant_admin')
@section('page_title', 'Reports')

@push('styles')
<style>
    @media print {
        #kt_aside, #kt_header, .no-print { display: none !important; }
        #kt_wrapper { margin: 0 !important; }
        #kt_content { padding: 0 !important; }
        .card { break-inside: avoid; box-shadow: none !important; border: 1px solid #e5e7eb !important; }
        body { background: white !important; }
    }
</style>
@endpush

@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Reports</h1>
        </div>
        @endpush

        {{-- ── Filter form ─────────────────────────────────────────────── --}}
        <div class="card mb-6">
            <div class="card-body py-4">
                <form method="GET" action="{{ route('tenant.admin.reports.index') }}" class="d-flex flex-wrap align-items-end gap-4">
                    <div>
                        <label class="form-label fw-semibold text-gray-700 fs-7 mb-1">From</label>
                        <input type="date" name="from" class="form-control form-control-sm w-150px"
                               value="{{ $from }}">
                    </div>
                    <div>
                        <label class="form-label fw-semibold text-gray-700 fs-7 mb-1">To</label>
                        <input type="date" name="to" class="form-control form-control-sm w-150px"
                               value="{{ $to }}">
                    </div>
                    <div>
                        <label class="form-label fw-semibold text-gray-700 fs-7 mb-1">Grain</label>
                        <select name="grain" class="form-select form-select-sm w-120px">
                            <option value="day"   @selected($grain === 'day')>Day</option>
                            <option value="week"  @selected($grain === 'week')>Week</option>
                            <option value="month" @selected($grain === 'month')>Month</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        <a href="{{ route('tenant.admin.reports.index') }}" class="btn btn-light btn-sm ms-2">Reset</a>
                    </div>
                    <div class="ms-auto no-print">
                        <button type="button" onclick="window.print()" class="btn btn-light-primary btn-sm">
                            <i class="ki-duotone ki-printer fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            Print Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── KPI cards ───────────────────────────────────────────────── --}}
        <div class="row g-5 gx-xl-10 mb-6">
            {{-- Total --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100" style="--kc:#F8285A;background-color:var(--kc)">
                    <div class="card-header pt-5 mb-3">
                        <div class="d-flex flex-center rounded-circle h-70px w-70px"
                             style="border:1px dashed rgba(255,255,255,.4);background-color:var(--kc)">
                            <i class="ki-duotone ki-message-question text-white fs-2qx lh-0">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end mb-3">
                        <span class="fs-2hx text-white fw-bold me-4">{{ $total }}</span>
                    </div>
                    <div class="card-footer" style="border-top:1px solid rgba(255,255,255,.3);background:rgba(0,0,0,.15)">
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Total Issues</span>
                            <span class="opacity-50 fs-7">{{ $from }} → {{ $to }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Open --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100" style="--kc:#7239EA;background-color:var(--kc)">
                    <div class="card-header pt-5 mb-3">
                        <div class="d-flex flex-center rounded-circle h-70px w-70px"
                             style="border:1px dashed rgba(255,255,255,.4);background-color:var(--kc)">
                            <i class="ki-duotone ki-call text-white fs-2qx lh-0">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end mb-3">
                        <span class="fs-2hx text-white fw-bold me-4">{{ $open }}</span>
                    </div>
                    <div class="card-footer" style="border-top:1px solid rgba(255,255,255,.3);background:rgba(0,0,0,.15)">
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Open</span>
                            <span class="opacity-50 fs-7">New + In Progress</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resolved --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100" style="--kc:#17c653;background-color:var(--kc)">
                    <div class="card-header pt-5 mb-3">
                        <div class="d-flex flex-center rounded-circle h-70px w-70px"
                             style="border:1px dashed rgba(255,255,255,.4);background-color:var(--kc)">
                            <i class="ki-duotone ki-like text-white fs-2qx lh-0">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end mb-3">
                        <span class="fs-2hx text-white fw-bold me-4">{{ $resolved }}</span>
                    </div>
                    <div class="card-footer" style="border-top:1px solid rgba(255,255,255,.3);background:rgba(0,0,0,.15)">
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Resolved</span>
                            <span class="opacity-50 fs-7">{{ $total > 0 ? round($resolved / $total * 100) : 0 }}% of total</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Avg Resolution --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100" style="--kc:#1a60c3;background-color:var(--kc)">
                    <div class="card-header pt-5 mb-3">
                        <div class="d-flex flex-center rounded-circle h-70px w-70px"
                             style="border:1px dashed rgba(255,255,255,.4);background-color:var(--kc)">
                            <i class="ki-duotone ki-medal-star text-white fs-2qx lh-0">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                            </i>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end mb-3">
                        <span class="fs-2hx text-white fw-bold me-4">{{ $avgResolutionHours ?? '—' }}</span>
                    </div>
                    <div class="card-footer" style="border-top:1px solid rgba(255,255,255,.3);background:rgba(0,0,0,.15)">
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Avg Resolution</span>
                            <span class="opacity-50 fs-7">Hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Trend chart ──────────────────────────────────────────────── --}}
        <div class="card mb-6">
            <div class="card-header pt-6">
                <h3 class="card-title fw-bold text-gray-800">Issues Over Time</h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary">{{ ucfirst($grain) }}ly</span>
                </div>
            </div>
            <div class="card-body pt-4">
                @if($trendRows->isEmpty())
                    <div class="text-center text-muted py-10">No issues in this date range.</div>
                @else
                    <div id="trend_chart" class="w-100" style="height:320px"></div>
                @endif
            </div>
        </div>

        {{-- ── Category Recurrence Trend ────────────────────────────────── --}}
        @if(!empty($catTrendMonths))
        <div class="card mb-6">
            <div class="card-header border-0 pt-6 d-flex align-items-center justify-content-between">
                <h3 class="card-title fw-bold text-gray-800">Issue Volume by Category (Monthly)</h3>
                <span class="text-muted fs-7">Falling lines = improvement</span>
            </div>
            <div class="card-body pt-0">
                <div id="cat_trend_chart" class="w-100" style="height:320px"></div>
            </div>
        </div>
        @endif

        {{-- ── SLA + Branch table ───────────────────────────────────────── --}}
        <div class="row g-5 mb-6">
            {{-- SLA card --}}
            <div class="col-lg-5 col-xl-4">
                <div class="card h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-800">SLA Compliance</h3>
                    </div>
                    <div class="card-body">

                        {{-- Overdue (unresolved) — always visible --}}
                        <div class="d-flex align-items-center justify-content-between p-4 rounded mb-5"
                             style="background:rgba(var(--bs-danger-rgb),.06); border:1px dashed rgba(var(--bs-danger-rgb),.3)">
                            <div>
                                <div class="text-danger fw-bold fs-6">Overdue &amp; Unresolved</div>
                                <div class="text-muted fs-7">New or in-progress, past SLA deadline</div>
                            </div>
                            <span class="fs-2x fw-bold text-danger">{{ $sla->overdue_unresolved ?? 0 }}</span>
                        </div>

                        {{-- Rate + resolved breakdown — only when resolved data exists --}}
                        @if($sla && $sla->total_resolved > 0)
                            <div class="mb-5 text-center">
                                <span class="fs-3x fw-bold {{ $slaRate >= 80 ? 'text-success' : ($slaRate >= 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ $slaRate }}%
                                </span>
                                <div class="text-gray-500 fs-6 mt-1">SLA Met Rate (resolved issues)</div>
                            </div>
                            <div class="separator mb-4"></div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-gray-600 fw-semibold">Resolved (total)</span>
                                <span class="fw-bold">{{ $sla->total_resolved }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-success fw-semibold">Met SLA</span>
                                <span class="fw-bold text-success">{{ $sla->met ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-warning fw-semibold">Missed SLA (resolved late)</span>
                                <span class="fw-bold text-warning">{{ $sla->missed ?? 0 }}</span>
                            </div>
                        @else
                            <div class="text-center text-muted fs-7 py-3">
                                No resolved issues yet — SLA met rate will appear once issues are resolved.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Branch breakdown table --}}
            <div class="col-lg-7 col-xl-8">
                <div class="card h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-800">Branch Breakdown</h3>
                    </div>
                    <div class="card-body pt-4">
                        @if($branchRows->isEmpty())
                            <div class="text-center text-muted py-6">No data for this period.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-2">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="min-w-150px">Branch</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">New</th>
                                            <th class="text-end">In Progress</th>
                                            <th class="text-end">Resolved</th>
                                            <th class="text-end">Closed</th>
                                            <th class="text-end">Resolution %</th>
                                            <th class="text-end">Spam</th>
                                            <th class="text-end">Spam Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branchRows as $row)
                                        <tr>
                                            <td class="fw-semibold text-gray-800">{{ $row->branch_name }}</td>
                                            <td class="text-end fw-bold">{{ $row->total }}</td>
                                            <td class="text-end text-muted">{{ $row->new }}</td>
                                            <td class="text-end text-muted">{{ $row->in_progress }}</td>
                                            <td class="text-end text-success fw-semibold">{{ $row->resolved }}</td>
                                            <td class="text-end text-primary fw-semibold">{{ $row->closed }}</td>
                                            <td class="text-end">
                                                <span class="badge badge-light-{{ $row->resolution_rate >= 70 ? 'success' : ($row->resolution_rate >= 40 ? 'warning' : 'danger') }}">
                                                    {{ $row->resolution_rate }}%
                                                </span>
                                            </td>
                                            <td class="text-end text-muted">{{ $row->spam_count }}</td>
                                            <td class="text-end">
                                                @if($row->spam_count > 0)
                                                    <span class="badge badge-light-{{ $row->spam_rate >= 20 ? 'danger' : ($row->spam_rate >= 10 ? 'warning' : 'secondary') }}">
                                                        {{ $row->spam_rate }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CSAT ─────────────────────────────────────────────────────── --}}
        <div class="card mb-6">
            <div class="card-header pt-6">
                <h3 class="card-title fw-bold text-gray-800">
                    Customer Satisfaction (CSAT)
                    <span class="ms-2" data-bs-toggle="tooltip"
                          title="Surveys are emailed to contacts when their issue is closed. Scoped to issues created in this date range.">
                        <i class="ki-duotone ki-information-5 fs-5 text-gray-400">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                    </span>
                </h3>
                <div class="card-toolbar">
                    @if($csatAvg !== null)
                        <span class="badge fs-6 px-4 py-2 badge-light-{{ $csatAvg >= 4 ? 'success' : ($csatAvg >= 3 ? 'warning' : 'danger') }}">
                            Avg {{ $csatAvg }} / 5
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($csatTotal === 0)
                    <div class="text-center text-muted py-8">
                        No CSAT surveys sent for this period — surveys are triggered when an issue with a contact email is closed.
                    </div>
                @else
                    <div class="row g-6">
                        {{-- Left: key metrics --}}
                        <div class="col-md-5 col-xl-4">
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <span class="text-muted fs-7">Surveys sent</span>
                                <span class="fw-bold">{{ $csatTotal }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <span class="text-muted fs-7">Responses received</span>
                                <span class="fw-bold text-success">{{ $csatReceived }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <span class="text-muted fs-7">Response rate</span>
                                <span class="fw-bold">
                                    {{ $csatResponseRate !== null ? $csatResponseRate.'%' : '—' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fs-7">Average rating</span>
                                <span class="fw-bold fs-3 {{ $csatAvg >= 4 ? 'text-success' : ($csatAvg >= 3 ? 'text-warning' : ($csatAvg !== null ? 'text-danger' : '')) }}">
                                    {{ $csatAvg !== null ? $csatAvg.' / 5' : '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: rating distribution bars --}}
                        <div class="col-md-7 col-xl-8">
                            <div class="fw-semibold text-gray-600 fs-7 mb-5">Rating Distribution</div>
                            @if($csatReceived === 0)
                                <div class="text-muted fs-7">No ratings submitted yet.</div>
                            @else
                                @php
                                    $barColors = [1 => 'danger', 2 => 'warning', 3 => 'info', 4 => 'primary', 5 => 'success'];
                                    $barLabels = [1 => 'Not satisfied', 2 => 'Unsatisfied', 3 => 'Neutral', 4 => 'Satisfied', 5 => 'Very satisfied'];
                                @endphp
                                @for($r = 5; $r >= 1; $r--)
                                    @php $cnt = $csatDist[$r] ?? 0; $pct = round($cnt / $csatReceived * 100); @endphp
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="w-20px text-end fw-bold fs-7 text-gray-800">{{ $r }}</div>
                                        <div class="text-muted fs-8 w-110px">{{ $barLabels[$r] }}</div>
                                        <div class="flex-grow-1 bg-light rounded" style="height:12px">
                                            <div class="bg-{{ $barColors[$r] }} rounded"
                                                 style="height:12px;width:{{ $pct }}%;transition:width .4s ease"></div>
                                        </div>
                                        <div class="w-25px text-end text-gray-700 fs-7 fw-semibold">{{ $cnt }}</div>
                                        <div class="w-35px text-end text-muted fs-8">{{ $pct }}%</div>
                                    </div>
                                @endfor
                            @endif
                        </div>
                    </div>

                    {{-- Branch-wise CSAT breakdown --}}
                    @if($csatBranchRows->isNotEmpty())
                    <div class="separator my-6"></div>
                    <div class="fw-bold text-gray-700 mb-4">CSAT by Branch</div>
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-2">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                    <th class="min-w-150px">Branch</th>
                                    <th class="text-end">Surveys Sent</th>
                                    <th class="text-end">Responses</th>
                                    <th class="text-end">Response Rate</th>
                                    <th class="text-end">Avg Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($csatBranchRows as $br)
                                <tr>
                                    <td class="fw-semibold text-gray-800">{{ $br->branch_name }}</td>
                                    <td class="text-end text-muted">{{ $br->total }}</td>
                                    <td class="text-end text-muted">{{ $br->received }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-light-{{ ($br->response_rate ?? 0) >= 60 ? 'success' : (($br->response_rate ?? 0) >= 30 ? 'warning' : 'danger') }}">
                                            {{ $br->response_rate !== null ? $br->response_rate.'%' : '—' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($br->avg_rating !== null)
                                            <span class="fw-bold {{ $br->avg_rating >= 4 ? 'text-success' : ($br->avg_rating >= 3 ? 'text-warning' : 'text-danger') }}">
                                                {{ $br->avg_rating }} / 5
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ── Staff + Category tables ─────────────────────────────────── --}}
        <div class="row g-5 mb-6">
            {{-- Staff performance --}}
            <div class="col-lg-7 col-xl-7">
                <div class="card h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-800">
                            Staff Performance
                            <span class="ms-2" data-bs-toggle="tooltip"
                                  title="'Resolved by them' counts issues this staff member personally moved to Resolved status, regardless of whether admin/parent later closed them.">
                                <i class="ki-duotone ki-information-5 fs-5 text-gray-400">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                        </h3>
                    </div>
                    <div class="card-body pt-4">
                        @if($staffRows->isEmpty())
                            <div class="text-center text-muted py-6">No assigned issues for this period.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-2">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="min-w-150px">Staff</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Open</th>
                                            <th class="text-end">Resolved by them</th>
                                            <th class="text-end">Closed</th>
                                            <th class="text-end">Resolution %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staffRows as $row)
                                        @if($row->assignedTo)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <img src="{{ asset('theme/media/avatars/300-31.jpg') }}" alt="" />
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-gray-800 d-block">{{ $row->assignedTo->name }}</span>
                                                        <span class="text-gray-500 fs-7">
                                                            {{ $row->assignedTo->getRoleNames()->first() }}
                                                            @if($row->assignedTo->branches->isNotEmpty())
                                                                &middot; {{ $row->assignedTo->branches->pluck('name')->join(', ') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold">{{ $row->total }}</td>
                                            <td class="text-end text-muted">{{ $row->open }}</td>
                                            <td class="text-end text-success fw-semibold">{{ $row->resolved_by_actor }}</td>
                                            <td class="text-end text-primary fw-semibold">{{ $row->closed }}</td>
                                            <td class="text-end">
                                                <span class="badge badge-light-{{ $row->resolution_rate >= 70 ? 'success' : ($row->resolution_rate >= 40 ? 'warning' : 'danger') }}">
                                                    {{ $row->resolution_rate }}%
                                                </span>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            <div class="col-lg-5 col-xl-5">
                <div class="card h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-800">Categories</h3>
                    </div>
                    <div class="card-body pt-4">
                        @if($categoryRows->isEmpty())
                            <div class="text-center text-muted py-6">No data for this period.</div>
                        @else
                            @foreach($categoryRows as $row)
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ki-solid ki-category fs-3 text-success me-3"></i>
                                    <span class="fw-semibold text-gray-800">{{ $row->issueCategory->name ?? '—' }}</span>
                                </div>
                                <span class="badge badge-light-primary fw-bold">{{ $row->c }}</span>
                            </div>
                            <div class="separator separator-dashed my-1"></div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Most Improved Categories ──────────────────────────────────── --}}
        @if($improvedCategories->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold fs-4">Most Improved Areas</h3>
                <div class="card-toolbar text-muted fs-7">First half vs second half of selected period</div>
            </div>
            <div class="card-body pt-4">
                <div class="row g-5">
                    @foreach($improvedCategories as $imp)
                    <div class="col-md-4">
                        <div class="bg-light-success rounded-3 p-6 h-100">
                            <div class="fw-bold fs-5 text-gray-800 mb-4">{{ $imp['name'] }}</div>
                            <div class="d-flex align-items-end gap-5 mb-3">
                                <div>
                                    <div class="fs-8 text-muted fw-semibold text-uppercase mb-1">First Half</div>
                                    <div class="fs-1 fw-bold text-gray-500">{{ $imp['first'] }}</div>
                                </div>
                                <div class="pb-2 text-muted">→</div>
                                <div>
                                    <div class="fs-8 text-muted fw-semibold text-uppercase mb-1">Second Half</div>
                                    <div class="fs-1 fw-bold text-gray-800">{{ $imp['second'] }}</div>
                                </div>
                            </div>
                            <div class="fs-2hx fw-bold text-success">↓ {{ $imp['drop'] }}</div>
                            <div class="text-success fw-semibold fs-7">fewer issues</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── Repeat Complaint Rate ─────────────────────────────────────── --}}
        @if($totalUniqueContacts > 0)
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold fs-4">Repeat Complaints</h3>
                <div class="card-toolbar text-muted fs-7">Contacts who submitted more than one issue in this period</div>
            </div>
            <div class="card-body pt-4">
                <div class="d-flex align-items-center gap-8">
                    <div class="text-center">
                        <div class="fs-2hx fw-bold text-{{ $repeatRate > 20 ? 'danger' : ($repeatRate > 10 ? 'warning' : 'success') }}">
                            {{ $repeatRate }}%
                        </div>
                        <div class="text-muted fs-7 mt-1">Repeat rate</div>
                    </div>
                    <div class="border-start ps-8">
                        <div class="fs-5 fw-semibold text-gray-700">
                            {{ $repeatContacts }} out of {{ $totalUniqueContacts }} contacts
                            submitted more than one issue.
                        </div>
                        <div class="text-muted fs-7 mt-2">
                            @if($repeatRate <= 10)
                                <span class="text-success fw-semibold">Healthy</span> — low recurrence suggests issues are being resolved effectively.
                            @elseif($repeatRate <= 20)
                                <span class="text-warning fw-semibold">Watch</span> — some contacts are facing recurring problems.
                            @else
                                <span class="text-danger fw-semibold">Attention needed</span> — high recurrence may indicate systemic issues not being fully resolved.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
(function () {
    var el = document.getElementById('trend_chart');
    if (!el) return;

    var trendRows = @json($trendRows);
    if (!trendRows.length) return;

    var buckets     = trendRows.map(function (r) { return r.bucket; });
    var newData      = trendRows.map(function (r) { return parseInt(r.new, 10) || 0; });
    var inProgData   = trendRows.map(function (r) { return parseInt(r.in_progress, 10) || 0; });
    var resolvedData = trendRows.map(function (r) { return parseInt(r.resolved, 10) || 0; });
    var closedData   = trendRows.map(function (r) { return parseInt(r.closed, 10) || 0; });

    var chart = echarts.init(el);
    chart.setOption({
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross', label: { backgroundColor: '#6a7985' } }
        },
        legend: {
            data: ['New', 'In Progress', 'Resolved', 'Closed'],
            bottom: 0,
            textStyle: { color: '#99a1b7', fontSize: 12 }
        },
        grid: { left: 16, right: 16, top: 16, bottom: 40, containLabel: true },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: buckets,
            axisLabel: { color: '#99a1b7', fontSize: 11 }
        },
        yAxis: {
            type: 'value',
            minInterval: 1,
            axisLabel: { color: '#99a1b7', fontSize: 11 },
            splitLine: { lineStyle: { color: '#f1f1f4' } }
        },
        series: [
            {
                name: 'New',
                type: 'line',
                stack: 'total',
                areaStyle: { opacity: 0.3 },
                smooth: true,
                data: newData,
                itemStyle: { color: '#F8285A' },
                lineStyle: { width: 2 }
            },
            {
                name: 'In Progress',
                type: 'line',
                stack: 'total',
                areaStyle: { opacity: 0.3 },
                smooth: true,
                data: inProgData,
                itemStyle: { color: '#7239EA' },
                lineStyle: { width: 2 }
            },
            {
                name: 'Resolved',
                type: 'line',
                stack: 'total',
                areaStyle: { opacity: 0.3 },
                smooth: true,
                data: resolvedData,
                itemStyle: { color: '#17c653' },
                lineStyle: { width: 2 }
            },
            {
                name: 'Closed',
                type: 'line',
                stack: 'total',
                areaStyle: { opacity: 0.3 },
                smooth: true,
                data: closedData,
                itemStyle: { color: '#1b84ff' },
                lineStyle: { width: 2 }
            },
        ]
    });

    window.addEventListener('resize', function () { chart.resize(); });
})();

// ── Category recurrence trend chart ──────────────────────────────────
(function () {
    var el = document.getElementById('cat_trend_chart');
    if (!el) return;

    var months  = @json($catTrendMonths);
    var series  = @json($catTrendSeries);
    if (!months.length || !series.length) return;

    var palette = [
        '#3e97ff','#f1416c','#50cd89','#ffc700','#7239ea',
        '#00c6bf','#ff8f00','#2d9ef5','#e879f9','#84cc16'
    ];

    var chartSeries = series.map(function (s, i) {
        return {
            name: s.name,
            type: 'line',
            smooth: true,
            data: s.data,
            itemStyle: { color: palette[i % palette.length] },
            lineStyle: { width: 2 },
            symbol: 'circle',
            symbolSize: 6,
        };
    });

    var catChart = echarts.init(el);
    catChart.setOption({
        tooltip: { trigger: 'axis' },
        legend: { bottom: 0, type: 'scroll', pageIconSize: 12 },
        xAxis: { type: 'category', data: months, boundaryGap: false },
        yAxis: { type: 'value', minInterval: 1 },
        series: chartSeries,
        grid: { left: 40, right: 20, top: 20, bottom: 60 },
    });
    window.addEventListener('resize', function () { catChart.resize(); });
})();
</script>
@endpush

@endsection
