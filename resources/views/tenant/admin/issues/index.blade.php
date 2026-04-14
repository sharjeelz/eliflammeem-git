@extends('layouts.tenant_admin')
@section('page_title', 'Issues')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')


        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Issues</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted">Issue Management</li>
                <li class="breadcrumb-item text-gray-900">Issues</li>
            </ul>
        </div>
        @endpush

        {{-- Quick Filter Presets --}}
        <div class="card card-body mb-4 py-4">
            <div class="d-flex align-items-center mb-2">
                <i class="ki-duotone ki-filter fs-3 text-primary me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <h5 class="fw-bold text-gray-900 mb-0">Quick Filters</h5>
                <span class="badge badge-light-primary fs-8 ms-2">Click to apply preset</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                {{-- Urgent & Unassigned --}}
                <a href="{{ route('tenant.admin.issues.index', ['priority' => 'urgent', 'assigned_user_id' => '']) }}" 
                   class="btn btn-sm btn-light-danger @if(request('priority') === 'urgent' && request('assigned_user_id') === '') active @endif">
                    <i class="ki-duotone ki-warning-2 fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Urgent & Unassigned
                </a>

                {{-- All Unassigned --}}
                <a href="{{ route('tenant.admin.issues.index', ['assigned_user_id' => 'none']) }}"
                   class="btn btn-sm btn-light-secondary @if(request('assigned_user_id') === 'none') active @endif">
                    <i class="ki-duotone ki-user-cross fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Unassigned
                </a>

                {{-- Auto-closed due to branch move --}}
                <a href="{{ route('tenant.admin.issues.index', ['assigned_user_id' => 'branch_moved']) }}"
                   class="btn btn-sm btn-light-warning @if(request('assigned_user_id') === 'branch_moved') active @endif">
                    <i class="ki-duotone ki-geolocation fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Branch Moved
                </a>

                {{-- New This Week --}}
                <a href="{{ route('tenant.admin.issues.index', ['status' => 'new', 'from' => now()->startOfWeek()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                   class="btn btn-sm btn-light-primary @if(request('status') === 'new' && request('from') === now()->startOfWeek()->format('Y-m-d')) active @endif">
                    <i class="ki-duotone ki-calendar-add fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    New This Week
                </a>

                {{-- Overdue (Past SLA) --}}
                <a href="{{ route('tenant.admin.issues.index', ['status' => 'in_progress']) }}" 
                   class="btn btn-sm btn-light-warning @if(request('status') === 'in_progress') active @endif">
                    <i class="ki-duotone ki-time fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    In Progress
                </a>

                {{-- Escalating Issues (AI Urgency) --}}
                <a href="{{ route('tenant.admin.issues.index', ['urgency' => 'escalate']) }}" 
                   class="btn btn-sm btn-light-danger @if(request('urgency') === 'escalate') active @endif">
                    <i class="ki-duotone ki-arrow-up fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    AI: Escalate
                </a>

                {{-- Negative Sentiment --}}
                <a href="{{ route('tenant.admin.issues.index', ['sentiment' => 'negative']) }}" 
                   class="btn btn-sm btn-light-danger @if(request('sentiment') === 'negative') active @endif">
                    <i class="ki-duotone ki-emoji-sad fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Negative Sentiment
                </a>

                @if(!auth()->user()->hasRole('staff'))
                {{-- My Branch (for branch managers) --}}
                @if(auth()->user()->hasRole('branch_manager'))
                    @php
                        $myBranchId = auth()->user()->branches()->first()?->id;
                    @endphp
                    @if($myBranchId)
                    <a href="{{ route('tenant.admin.issues.index', ['branch_id' => $myBranchId]) }}" 
                       class="btn btn-sm btn-light-info @if(request('branch_id') == $myBranchId) active @endif">
                        <i class="ki-duotone ki-home fs-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        My Branch
                    </a>
                    @endif
                @endif

                {{-- Resolved This Month --}}
                <a href="{{ route('tenant.admin.issues.index', ['status' => 'resolved', 'from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}" 
                   class="btn btn-sm btn-light-success @if(request('status') === 'resolved' && request('from') === now()->startOfMonth()->format('Y-m-d')) active @endif">
                    <i class="ki-duotone ki-check-circle fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Resolved This Month
                </a>
                @endif

                {{-- Clear Filters --}}
                @if(request()->hasAny(['search', 'status', 'priority', 'assigned_user_id', 'branch_id', 'category_id', 'from', 'to', 'urgency', 'theme', 'sentiment', 'spam', 'submission_type']))
                <a href="{{ route('tenant.admin.issues.index') }}"
                   class="btn btn-sm btn-light">
                    <i class="ki-duotone ki-cross-circle fs-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Clear All Filters
                </a>
                @endif
            </div>

            {{-- Saved Filters Row --}}
            @if($savedFilters->isNotEmpty())
            <div class="separator separator-dashed my-3"></div>
            <div class="d-flex align-items-center gap-2 flex-wrap" id="saved-filters-row">
                <span class="text-muted fs-8 fw-semibold d-flex align-items-center">
                    <i class="ki-duotone ki-bookmark fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Saved:
                </span>
                @foreach($savedFilters as $sf)
                <div class="d-flex align-items-center gap-1 saved-filter-item" data-id="{{ $sf->id }}">
                    <a href="{{ route('tenant.admin.issues.index', $sf->query_params) }}"
                       class="btn btn-sm btn-light-info py-1 px-3 fs-8">
                        {{ $sf->name }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-icon btn-light-danger w-20px h-20px"
                            onclick="deleteSavedFilter({{ $sf->id }}, this)"
                            title="Delete">
                        <i class="ki-duotone ki-cross fs-9"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>
                @endforeach
            </div>
            @else
            <div id="saved-filters-row" class="d-none"></div>
            @endif
        </div>

        {{-- Filter form --}}
        <form method="GET" action="{{ route('tenant.admin.issues.index') }}" class="card card-body mb-5 py-4">
            {{-- Active Filters Notice --}}
            @if(request()->hasAny(['search', 'status', 'priority', 'assigned_user_id', 'branch_id', 'category_id', 'from', 'to', 'urgency', 'theme', 'sentiment', 'spam']))
            <div class="alert alert-primary d-flex align-items-center p-3 mb-4">
                <i class="ki-duotone ki-information-5 fs-2hx text-primary me-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="flex-grow-1">
                    <div class="fw-semibold text-gray-900 fs-7">Active Filters Applied</div>
                    <div class="text-gray-600 fs-8">
                        @if(request('theme')) <span class="badge badge-light-warning me-1">Theme: {{ ucfirst(request('theme')) }}</span> @endif
                        @if(request('sentiment')) <span class="badge badge-light-info me-1">Sentiment: {{ ucfirst(request('sentiment')) }}</span> @endif
                        @if(request('urgency')) <span class="badge badge-light-danger me-1">Urgency: {{ ucfirst(request('urgency')) }}</span> @endif
                        @if(request('status')) <span class="badge badge-light-primary me-1">Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span> @endif
                        @if(request('priority')) <span class="badge badge-light-danger me-1">Priority: {{ ucfirst(request('priority')) }}</span> @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="d-flex flex-wrap gap-3 align-items-end">

                {{-- Search --}}
                <div class="flex-grow-1" style="min-width:200px">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-10" placeholder="Title / ID / description">
                    </div>
                </div>

                {{-- Status --}}
                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">Status</label>
                    <select name="status" class="form-select form-select-solid">
                        <option value="">All statuses</option>
                        @foreach(['new','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Priority --}}
                <div style="min-width:130px">
                    <label class="form-label fs-7 mb-1">Priority</label>
                    <select name="priority" class="form-select form-select-solid">
                        <option value="">All priorities</option>
                        @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" @selected(request('priority') === $p)>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Assigned to --}}
                <div style="min-width:160px">
                    <label class="form-label fs-7 mb-1">Assigned to</label>
                    <select name="assigned_user_id" class="form-select form-select-solid">
                        <option value="">Anyone</option>
                        @foreach($staffList as $s)
                            <option value="{{ $s->id }}" @selected(request('assigned_user_id') == $s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch (admin + branch_manager) --}}
                @if(!auth()->user()->hasRole('staff'))
                <div style="min-width:150px">
                    <label class="form-label fs-7 mb-1">Branch</label>
                    <select name="branch_id" class="form-select form-select-solid">
                        <option value="">All branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category (admin + branch_manager) --}}
                @if($categories->isNotEmpty())
                <div style="min-width:150px">
                    <label class="form-label fs-7 mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-solid">
                        <option value="">All categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                @endif

                {{-- Date from --}}
                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-solid">
                </div>

                {{-- Date to --}}
                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-solid">
                </div>

                {{-- Urgency --}}
                <div style="min-width:130px">
                    <label class="form-label fs-7 mb-1">AI Urgency</label>
                    <select name="urgency" class="form-select form-select-solid">
                        <option value="">Any urgency</option>
                        <option value="escalate" @selected(request('urgency') === 'escalate')>🔴 Escalate</option>
                        <option value="monitor"  @selected(request('urgency') === 'monitor')>🟡 Monitor</option>
                        <option value="normal"   @selected(request('urgency') === 'normal')>Normal</option>
                    </select>
                </div>

                {{-- Theme --}}
                @if($availableThemes->isNotEmpty())
                <div style="min-width:150px">
                    <label class="form-label fs-7 mb-1">AI Theme</label>
                    <select name="theme" class="form-select form-select-solid">
                        <option value="">All themes</option>
                        @foreach($availableThemes as $theme)
                            <option value="{{ $theme }}" @selected(request('theme') === $theme)>
                                {{ ucfirst(str_replace('_', ' ', $theme)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Sentiment --}}
                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">AI Sentiment</label>
                    <select name="sentiment" class="form-select form-select-solid">
                        <option value="">Any sentiment</option>
                        <option value="positive" @selected(request('sentiment') === 'positive')>😊 Positive</option>
                        <option value="neutral"  @selected(request('sentiment') === 'neutral')>😐 Neutral</option>
                        <option value="negative" @selected(request('sentiment') === 'negative')>😟 Negative</option>
                    </select>
                </div>

                {{-- Submission type --}}
                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">Type</label>
                    <select name="submission_type" class="form-select form-select-solid">
                        <option value="">All types</option>
                        <option value="complaint"  @selected(request('submission_type') === 'complaint')>Complaints</option>
                        <option value="suggestion" @selected(request('submission_type') === 'suggestion')>Suggestions</option>
                        <option value="compliment" @selected(request('submission_type') === 'compliment')>Compliments</option>
                    </select>
                </div>

                {{-- Spam toggle --}}
                <div class="align-self-end">
                    <label class="form-label fs-7 mb-1">View</label>
                    <select name="spam" class="form-select form-select-solid" style="min-width:150px">
                        <option value="" @selected(!in_array(request('spam'), ['only','anonymous']))>Normal Issues</option>
                        <option value="only" @selected(request('spam') === 'only')>Spam Only</option>
                        <option value="anonymous" @selected(request('spam') === 'anonymous')>Anonymous Only</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 align-self-end flex-wrap">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.issues.index') }}" class="btn btn-light">Reset</a>
                    {{-- Save this filter — only shown when at least 1 filter is active --}}
                    @if(request()->hasAny(['search', 'status', 'priority', 'assigned_user_id', 'branch_id', 'category_id', 'from', 'to', 'urgency', 'theme', 'sentiment', 'submission_type', 'spam']))
                    <div class="d-flex align-items-center gap-1">
                        <input type="text" id="save-filter-name"
                               class="form-control form-control-solid form-control-sm"
                               placeholder="Name this filter…" style="width:155px" maxlength="60">
                        <button type="button" class="btn btn-sm btn-light-primary" onclick="saveCurrentFilter()">
                            <i class="ki-duotone ki-bookmark fs-5"><span class="path1"></span><span class="path2"></span></i>
                            Save
                        </button>
                    </div>
                    @endif
                </div>

            </div>
        </form>

        {{-- Active filter chips --}}
        @php
            $activeFilters = collect([
                'search'   => request('search') ?: null,
                'status'   => request('status') ? ucfirst(str_replace('_', ' ', request('status'))) : null,
                'priority' => request('priority') ? ucfirst(request('priority')) : null,
                'assigned' => $staffList->firstWhere('id', request('assigned_user_id'))?->name,
                'branch'   => ($branches ?? collect())->firstWhere('id', request('branch_id'))?->name,
                'category' => ($categories ?? collect())->firstWhere('id', request('category_id'))?->name,
                'from'     => request('from') ?: null,
                'to'       => request('to') ?: null,
                'urgency'  => request('urgency') ? ucfirst(request('urgency')) : null,
                'theme'    => request('theme') ? ucfirst(str_replace('_', ' ', request('theme'))) : null,
                'view'     => match(request('spam')) { 'only' => 'Spam only', 'anonymous' => 'Anonymous only', default => null },
            ])->filter();
        @endphp
        @if($activeFilters->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
            <span class="text-muted fs-7 fw-semibold">Active filters:</span>
            @foreach($activeFilters as $label => $value)
            <span class="badge badge-light-primary py-2 px-3 d-flex align-items-center gap-1 fs-7">
                <span class="text-muted fw-normal text-uppercase fs-9">{{ $label }}</span>
                <span class="mx-1 text-muted">·</span>
                <span class="fw-semibold">{{ $value }}</span>
            </span>
            @endforeach
            <a href="{{ route('tenant.admin.issues.index') }}" class="btn btn-sm btn-light-danger py-1 px-3">
                <i class="ki-duotone ki-cross fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                Clear all
            </a>
        </div>
        @endif

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title d-flex align-items-center gap-3">
                    <span class="text-muted fs-7">Total {{ $issues->total() }} issue{{ $issues->total() === 1 ? '' : 's' }}</span>
                    @if($overdueIssues->isNotEmpty())
                    @if(request()->boolean('sla_overdue'))
                    <span class="badge badge-danger d-flex align-items-center gap-1 py-2 px-3">
                        <i class="ki-duotone ki-time fs-7"><span class="path1"></span><span class="path2"></span></i>
                        ({{ $overdueIssues->count() }}) past SLA
                        <a href="{{ route('tenant.admin.issues.index') }}"
                           class="text-white ms-1 text-decoration-none opacity-75 hover-opacity-100"
                           title="Show all issues">✕</a>
                    </span>
                    @else
                    <a href="{{ route('tenant.admin.issues.index', ['sla_overdue' => '1']) }}"
                       class="badge badge-light-danger d-flex align-items-center gap-1 text-decoration-none py-2 px-3"
                       title="Filter to overdue issues">
                        <i class="ki-duotone ki-time fs-7"><span class="path1"></span><span class="path2"></span></i>
                        ({{ $overdueIssues->count() }}) past SLA
                    </a>
                    @endif
                    @endif
                    @if($spamIssues->isNotEmpty())
                    @if(request('spam') === 'only')
                    <span class="badge badge-warning d-flex align-items-center gap-1 py-2 px-3">
                        <i class="ki-duotone ki-shield-cross fs-7"><span class="path1"></span><span class="path2"></span></i>
                        {{ $spamIssues->count() }} spam
                        <a href="{{ route('tenant.admin.issues.index') }}"
                           class="text-white ms-1 text-decoration-none opacity-75 hover-opacity-100"
                           title="Show all issues">✕</a>
                    </span>
                    @else
                    <a href="{{ route('tenant.admin.issues.index', ['spam' => 'only']) }}"
                       class="badge badge-light-warning d-flex align-items-center gap-1 text-decoration-none py-2 px-3"
                       title="Filter to spam issues">
                        <i class="ki-duotone ki-shield-cross fs-7"><span class="path1"></span><span class="path2"></span></i>
                        {{ $spamIssues->count() }} spam
                    </a>
                    @endif
                    @endif
                </div>
                <div class="card-toolbar">
                    @if($planAllowCsvExport ?? false)
                    <a href="{{ route('tenant.admin.issues.export', request()->query()) }}"
                       class="btn btn-light-primary btn-sm">
                        <i class="ki-duotone ki-file-down fs-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Export CSV
                    </a>
                    @else
                    <a href="{{ route('tenant.admin.plan.index') }}"
                       class="btn btn-light-secondary btn-sm" title="Upgrade to export CSV">
                        <i class="ki-solid ki-lock-2 fs-4 me-1"></i>
                        Export CSV
                    </a>
                    @endif
                </div>
            </div>

            {{-- Bulk action bar — appears between header and table when rows are selected --}}
            <div id="bulk-bar" class="d-none px-6 py-3 border-top border-dashed border-gray-300 bg-light-primary">
                <div class="d-flex align-items-center gap-3">
                    <span id="bulk-count" class="text-primary fw-semibold fs-7"></span>
                    @if(!auth()->user()->hasRole('staff'))
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Bulk Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="assign">
                                    <i class="ki-duotone ki-profile-user fs-5 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    Assign to…
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="status">
                                    <i class="ki-duotone ki-arrows-circle fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Change Status…
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="priority">
                                    <i class="ki-duotone ki-graph-up fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Change Priority…
                                </a>
                            </li>
                        </ul>
                    </div>
                    @else
                    {{-- Staff: only status change --}}
                    <button class="btn btn-sm btn-primary" data-bulk-action="status">Change Status…</button>
                    @endif
                    <button type="button" id="bulk-clear" class="btn btn-sm btn-light ms-2">Clear selection</button>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-25px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Message</th>
                            <th>Branch</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($issues as $i)
                        @php
                            $priorityColor = ['low'=>'success','medium'=>'info','high'=>'warning','urgent'=>'danger'][$i->priority] ?? 'secondary';
                            $statusColor   = ['new'=>'primary','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'][$i->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input issue-checkbox" type="checkbox" value="{{ $i->id }}">
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('tenant.admin.issue.show', $i->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                    {{ $i->public_id }}
                                </a>
                            </td>
                            <td>
                                @if($i->is_anonymous)
                                    <span class="badge badge-light-secondary">
                                        <i class="ki-duotone ki-lock-3 fs-9 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        Anonymous
                                    </span>
                                @elseif($i->roasterContact)
                                    @if(auth()->user()->can('manage-users'))
                                        <a href="{{ route('tenant.admin.contacts.edit', $i->roasterContact->id) }}"
                                           class="text-primary fw-semibold">{{ $i->roasterContact->name }}</a>
                                    @else
                                        {{ $i->roasterContact->name }}
                                    @endif
                                    @if($i->roasterContact->role)
                                    @php $roleColor = ['parent'=>'info','teacher'=>'primary','admin'=>'warning'][$i->roasterContact->role] ?? 'secondary'; @endphp
                                    <span class="badge badge-light-{{ $roleColor }} ms-1">{{ ucfirst($i->roasterContact->role) }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ Str::limit($i->title, 45) }}
                                @if($i->is_spam)
                                    <span class="badge badge-danger ms-1 fs-9">Spam</span>
                                @endif
                                @if(($i->submission_type ?? 'complaint') === 'suggestion')
                                    <span class="badge badge-light-info ms-1 fs-9">Suggestion</span>
                                @endif
                                @php $iUrgency = data_get($i->aiAnalysis?->result ?? [], 'urgency_flag'); @endphp
                                @if($iUrgency === 'escalate')
                                    <span class="badge badge-light-danger ms-1 fs-9" title="AI: Escalate">
                                        <i class="ki-duotone ki-warning-2 fs-9"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </span>
                                @elseif($iUrgency === 'monitor')
                                    <span class="badge badge-light-warning ms-1 fs-9" title="AI: Monitor">
                                        <i class="ki-duotone ki-eye fs-9"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </span>
                                @endif
                            </td>
                            <td>{{ $i->branch->name ?? '—' }}</td>
                            <td>
                                @if($i->issueCategory && $i->issueCategory->name)
                                    <span class="badge badge-light-info">{{ $i->issueCategory->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $priorityColor }} fw-bold">
                                    {{ ucfirst($i->priority ?? '—') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $statusColor }} fw-bold">
                                    {{ ucfirst(str_replace('_', ' ', $i->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($i->assignedTo)
                                    @if(auth()->user()->can('manage-users'))
                                        <a href="{{ route('tenant.admin.users.edit', $i->assignedTo->id) }}"
                                           class="text-primary fw-semibold">{{ $i->assignedTo->name }}</a>
                                    @else
                                        {{ $i->assignedTo->name }}
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                    @if(($i->meta['unassigned_reason'] ?? null) === 'contact_branch_changed')
                                        <span class="badge badge-light-warning d-block mt-1" title="Contact was moved to a different branch — please reassign">
                                            <i class="ki-duotone ki-geolocation fs-8"><span class="path1"></span><span class="path2"></span></i>
                                            Contact moved branch
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $i->created_at->diffForHumans() }}
                                @if($i->sla_due_at && $i->sla_due_at->isPast() && !in_array($i->status, ['resolved','closed']))
                                    <span class="badge badge-light-danger ms-1" title="Past SLA deadline: {{ $i->sla_due_at->format('d M Y H:i') }}">
                                        <i class="ki-duotone ki-time fs-8"><span class="path1"></span><span class="path2"></span></i>
                                        SLA
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tenant.admin.issue.show', $i->id) }}"
                                   class="btn btn-sm btn-light btn-active-light-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-10">No issues found matching your filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $issues->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Bulk Action Modal ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="bulkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="bulkModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-6 px-7">

                {{-- Assign panel --}}
                <div id="bulk-panel-assign" class="d-none">
                    <label class="form-label fw-semibold">Select staff member</label>
                    <select id="bulk-assign-val" class="form-select form-select-solid">
                        <option value="">— choose —</option>
                        @foreach($staffList as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status panel --}}
                <div id="bulk-panel-status" class="d-none">
                    <label class="form-label fw-semibold">Select new status</label>
                    <div class="d-flex flex-column gap-2 mt-2">
                        @foreach(['new' => ['primary','New'], 'in_progress' => ['warning','In Progress'], 'resolved' => ['success','Resolved'], 'closed' => ['secondary','Closed']] as $val => [$color, $label])
                        @if($val !== 'closed' || !auth()->user()->hasRole('staff'))
                        <label class="d-flex align-items-center gap-3 p-3 rounded border border-dashed border-gray-300 cursor-pointer status-option"
                               style="cursor:pointer">
                            <input type="radio" name="bulk_status" value="{{ $val }}" class="form-check-input mt-0">
                            <span class="badge badge-light-{{ $color }} fw-bold fs-7">{{ $label }}</span>
                        </label>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Priority panel --}}
                <div id="bulk-panel-priority" class="d-none">
                    <label class="form-label fw-semibold">Select new priority</label>
                    <div class="d-flex flex-column gap-2 mt-2">
                        @foreach(['low' => 'success', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'] as $val => $color)
                        <label class="d-flex align-items-center gap-3 p-3 rounded border border-dashed border-gray-300"
                               style="cursor:pointer">
                            <input type="radio" name="bulk_priority" value="{{ $val }}" class="form-check-input mt-0">
                            <span class="badge badge-light-{{ $color }} fw-bold fs-7">{{ ucfirst($val) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bulk-modal-apply">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var bulkBar   = document.getElementById('bulk-bar');
    var bulkCount = document.getElementById('bulk-count');
    var selectAll = document.getElementById('select-all');
    var modal     = new bootstrap.Modal(document.getElementById('bulkModal'));
    var currentAction = null;

    function getChecked() {
        return Array.from(document.querySelectorAll('.issue-checkbox:checked'));
    }

    function updateBar() {
        var checked = getChecked();
        var n = checked.length;
        if (n > 0) {
            bulkBar.classList.remove('d-none');
            bulkCount.textContent = n + ' selected';
        } else {
            bulkBar.classList.add('d-none');
            bulkCount.textContent = '';
        }
        var all = document.querySelectorAll('.issue-checkbox');
        selectAll.indeterminate = n > 0 && n < all.length;
        selectAll.checked = all.length > 0 && n === all.length;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.issue-checkbox').forEach(cb => cb.checked = this.checked);
        updateBar();
    });
    document.querySelectorAll('.issue-checkbox').forEach(cb => cb.addEventListener('change', updateBar));

    document.getElementById('bulk-clear').addEventListener('click', function () {
        document.querySelectorAll('.issue-checkbox, #select-all').forEach(cb => cb.checked = false);
        selectAll.indeterminate = false;
        updateBar();
    });

    // Open modal for a given action
    function openModal(action) {
        currentAction = action;
        var titles = { assign: 'Assign Issues', status: 'Change Status', priority: 'Change Priority' };
        document.getElementById('bulkModalTitle').textContent = titles[action];

        ['assign', 'status', 'priority'].forEach(function (p) {
            document.getElementById('bulk-panel-' + p).classList.add('d-none');
        });
        document.getElementById('bulk-panel-' + action).classList.remove('d-none');

        // Reset selections
        document.getElementById('bulk-assign-val').value = '';
        document.querySelectorAll('input[name="bulk_status"], input[name="bulk_priority"]').forEach(r => r.checked = false);

        modal.show();
    }

    document.querySelectorAll('[data-bulk-action]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (getChecked().length === 0) return;
            openModal(this.dataset.bulkAction);
        });
    });

    // Apply button inside modal
    document.getElementById('bulk-modal-apply').addEventListener('click', function () {
        var ids = getChecked().map(cb => parseInt(cb.value, 10));
        if (!ids.length) { modal.hide(); return; }

        var payload = { ids: ids, action: currentAction };

        if (currentAction === 'assign') {
            var v = document.getElementById('bulk-assign-val').value;
            if (!v) { alert('Please select a staff member.'); return; }
            payload.assigned_user_id = parseInt(v, 10);
        } else if (currentAction === 'status') {
            var r = document.querySelector('input[name="bulk_status"]:checked');
            if (!r) { alert('Please select a status.'); return; }
            payload.status = r.value;
        } else if (currentAction === 'priority') {
            var r = document.querySelector('input[name="bulk_priority"]:checked');
            if (!r) { alert('Please select a priority.'); return; }
            payload.priority = r.value;
        }

        var applyBtn = this;
        applyBtn.disabled = true;
        applyBtn.textContent = 'Applying…';

        fetch('{{ route('tenant.admin.issues.bulk') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(function (data) {
            sessionStorage.setItem('bulk_flash', data.message);
            window.location.reload();
        })
        .catch(function () {
            applyBtn.disabled = false;
            applyBtn.textContent = 'Apply';
            alert('Something went wrong. Please try again.');
        });
    });

    // Show bulk flash from previous action
    var bulkFlash = sessionStorage.getItem('bulk_flash');
    if (bulkFlash) {
        sessionStorage.removeItem('bulk_flash');
        var flash = document.createElement('div');
        flash.className = 'alert alert-success d-flex align-items-center p-4 mb-5';
        flash.innerHTML = '<i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i><span class="fw-semibold">' + bulkFlash + '</span>';
        var container = document.getElementById('kt_content_container');
        container.insertBefore(flash, container.firstChild);
        setTimeout(function () { flash.remove(); }, 5000);
    }
})();

// ── Saved Filters ────────────────────────────────────────────────────────────
function saveCurrentFilter() {
    const nameInput = document.getElementById('save-filter-name');
    const name = nameInput.value.trim();
    if (!name) { nameInput.focus(); return; }

    const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
    params.name = name;

    fetch('{{ route("tenant.admin.saved-filters.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(params),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        nameInput.value = '';
        addSavedFilterChip(data);
    });
}

function addSavedFilterChip(filter) {
    const row = document.getElementById('saved-filters-row');
    if (!row) return;

    // Build URL from query_params
    const qs = new URLSearchParams(filter.query_params).toString();
    const url = '{{ route("tenant.admin.issues.index") }}' + (qs ? '?' + qs : '');

    const item = document.createElement('div');
    item.className = 'd-flex align-items-center gap-1 saved-filter-item';
    item.dataset.id = filter.id;
    item.innerHTML = `
        <a href="${url}" class="btn btn-sm btn-light-info py-1 px-3 fs-8">${filter.name}</a>
        <button type="button" class="btn btn-sm btn-icon btn-light-danger w-20px h-20px"
                onclick="deleteSavedFilter(${filter.id}, this)" title="Delete">
            <i class="ki-duotone ki-cross fs-9"><span class="path1"></span><span class="path2"></span></i>
        </button>`;

    // Show the row if it was hidden (no previous saved filters)
    if (row.classList.contains('d-none')) {
        // Add separator + label before the row
        const card = row.closest('.card-body') || row.parentElement;
        const sep = document.createElement('div');
        sep.className = 'separator separator-dashed my-3';
        row.before(sep);

        const label = document.createElement('span');
        label.className = 'text-muted fs-8 fw-semibold d-flex align-items-center';
        label.innerHTML = '<i class="ki-duotone ki-bookmark fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>Saved:';
        row.classList.remove('d-none');
        row.appendChild(label);
    }

    row.appendChild(item);
}

function deleteSavedFilter(id, btn) {
    if (!confirm('Delete this saved filter?')) return;

    fetch(`/admin/saved-filters/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        const item = btn.closest('.saved-filter-item');
        item?.remove();
        // If no chips remain, hide the row + separator
        const row = document.getElementById('saved-filters-row');
        if (row && !row.querySelector('.saved-filter-item')) {
            row.previousElementSibling?.remove(); // separator
            row.classList.add('d-none');
        }
    });
}
</script>
@endpush

@endsection
