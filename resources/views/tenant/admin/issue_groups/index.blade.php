@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">AI Issue Groups</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Issue Groups</li>
            </ul>
        </div>
        @endpush

        @if($tab === 'open')
        <div class="alert alert-dismissible bg-light-primary d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-information-5 fs-2x text-primary me-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <span class="fw-bold">AI has detected issues that share the same root problem.</span>
                <span class="text-muted fs-7">Review each group carefully, remove outliers, then bulk resolve with a single message sent to all parents.</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
        @endif

        <div class="card">

            {{-- Tabs --}}
            <div class="card-header border-0 pt-5">
                <div class="card-title">
                    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                        <li class="nav-item">
                            <a class="nav-link text-active-primary {{ $tab === 'open' ? 'active' : '' }}"
                               href="{{ route('tenant.admin.issue_groups.index', ['tab' => 'open']) }}">
                                Open
                                @if(($counts['open'] ?? 0) > 0)
                                    <span class="badge badge-light-primary ms-1">{{ $counts['open'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary {{ $tab === 'dismissed' ? 'active' : '' }}"
                               href="{{ route('tenant.admin.issue_groups.index', ['tab' => 'dismissed']) }}">
                                Dismissed
                                @if(($counts['dismissed'] ?? 0) > 0)
                                    <span class="badge badge-light-secondary ms-1">{{ $counts['dismissed'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary {{ $tab === 'resolved' ? 'active' : '' }}"
                               href="{{ route('tenant.admin.issue_groups.index', ['tab' => 'resolved']) }}">
                                Resolved
                                @if(($counts['resolved'] ?? 0) > 0)
                                    <span class="badge badge-light-success ms-1">{{ $counts['resolved'] }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-toolbar">
                    @if($tab === 'open')
                    <form method="POST" action="{{ route('tenant.admin.issue_groups.refresh') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-light-primary">
                            <i class="ki-duotone ki-arrows-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Re-scan now
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card-body py-4">
                @forelse($groups as $group)
                <div class="border border-dashed rounded p-5 mb-4
                    {{ $tab === 'dismissed' ? 'border-gray-200 bg-light opacity-75' : '' }}
                    {{ $tab === 'open' && in_array($group->category?->name, \App\Jobs\DetectIssueGroups::SENSITIVE_CATEGORIES) ? 'border-warning bg-light-warning' : '' }}
                    {{ $tab === 'open' && !in_array($group->category?->name, \App\Jobs\DetectIssueGroups::SENSITIVE_CATEGORIES) ? 'border-gray-300' : '' }}
                    {{ $tab === 'resolved' ? 'border-gray-200' : '' }}">

                    <div class="d-flex align-items-start gap-4 flex-wrap">

                        {{-- Info --}}
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <span class="fw-bold fs-5 text-gray-{{ $tab === 'dismissed' ? '500' : '800' }}">
                                    {{ $group->label }}
                                </span>
                                <span class="badge {{ $group->confidence_badge }}">{{ ucfirst($group->confidence) }} confidence</span>

                                @if($tab === 'open' && in_array($group->category?->name, \App\Jobs\DetectIssueGroups::SENSITIVE_CATEGORIES))
                                    <span class="badge badge-light-warning">
                                        <i class="ki-duotone ki-warning-2 fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        Sensitive — review carefully
                                    </span>
                                @endif

                                @if($tab === 'dismissed')
                                    <span class="badge badge-light-secondary">Dismissed</span>
                                @endif

                                @if($tab === 'resolved')
                                    <span class="badge badge-light-success">Resolved</span>
                                @endif
                            </div>

                            <div class="d-flex gap-4 text-muted fs-7 flex-wrap">
                                @if($group->category)
                                    <span><i class="ki-duotone ki-category fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>{{ $group->category->name }}</span>
                                @endif
                                @if($group->branch)
                                    <span><i class="ki-duotone ki-geolocation fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $group->branch->name }}</span>
                                @endif
                                <span>
                                    <i class="ki-duotone ki-people fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    {{ $group->issue_count }} {{ Str::plural('issue', $group->issue_count) }}
                                </span>
                                <span>
                                    <i class="ki-duotone ki-time fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    Detected {{ $group->created_at->diffForHumans() }}
                                </span>
                                @if($tab === 'resolved' && $group->resolved_at)
                                    <span class="text-success">
                                        <i class="ki-duotone ki-check-circle fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                        Resolved {{ $group->resolved_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            @if($tab === 'resolved' && $group->resolved_message)
                                <div class="mt-2 p-3 rounded bg-light fs-8 text-muted border-start border-3 border-success">
                                    {{ Str::limit($group->resolved_message, 120) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 align-items-center flex-shrink-0">
                            @if($tab === 'open')
                                <a href="{{ route('tenant.admin.issue_groups.show', $group) }}"
                                   class="btn btn-primary btn-sm">
                                    Review &amp; Resolve
                                </a>
                                <form method="POST" action="{{ route('tenant.admin.issue_groups.dismiss', $group) }}"
                                      onsubmit="return confirm('Dismiss this group without resolving?')">
                                    @csrf
                                    <button class="btn btn-light btn-sm">Dismiss</button>
                                </form>

                            @elseif($tab === 'dismissed')
                                <form method="POST" action="{{ route('tenant.admin.issue_groups.reopen', $group) }}">
                                    @csrf
                                    <button class="btn btn-light-primary btn-sm">
                                        <i class="ki-duotone ki-arrows-circle fs-5"><span class="path1"></span><span class="path2"></span></i>
                                        Reopen
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
                @empty
                    <div class="text-center py-12">
                        @if($tab === 'open')
                            <i class="ki-duotone ki-check-circle fs-3x text-success mb-3"><span class="path1"></span><span class="path2"></span></i>
                            <div class="fw-bold text-gray-700">No open groups</div>
                            <div class="text-muted fs-7 mt-1">Groups appear when 2+ open issues share the same AI-detected theme, category, and branch.</div>
                        @elseif($tab === 'dismissed')
                            <i class="ki-duotone ki-minus-circle fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span></i>
                            <div class="fw-bold text-gray-700">No dismissed groups</div>
                            <div class="text-muted fs-7 mt-1">Dismissed groups appear here and can be reopened.</div>
                        @else
                            <i class="ki-duotone ki-verify fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span></i>
                            <div class="fw-bold text-gray-700">No resolved groups yet</div>
                        @endif
                    </div>
                @endforelse

                <div class="mt-4">{{ $groups->links() }}</div>
            </div>
        </div>

    </div>
</div>
@endsection
