@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Review Group</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.issue_groups.index') }}" class="text-muted text-hover-primary">Issue Groups</a></li>
                <li class="breadcrumb-item text-gray-900">Review</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Left: issue list --}}
            <div class="col-lg-7">

                {{-- Group header --}}
                <div class="card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <div class="fw-bold fs-4 text-gray-800 mb-1">{{ $issueGroup->label }}</div>
                                <div class="d-flex gap-3 text-muted fs-7">
                                    <span><span class="badge {{ $issueGroup->confidence_badge }}">{{ ucfirst($issueGroup->confidence) }}</span></span>
                                    @if($issueGroup->category)
                                        <span>{{ $issueGroup->category->name }}</span>
                                    @endif
                                    @if($issueGroup->branch)
                                        <span>{{ $issueGroup->branch->name }}</span>
                                    @endif
                                    <span>{{ $issues->count() }} issues</span>
                                </div>
                            </div>
                        </div>

                        @if(in_array($issueGroup->category?->name, \App\Jobs\DetectIssueGroups::SENSITIVE_CATEGORIES))
                        <div class="alert alert-warning d-flex align-items-center mt-4 mb-0 p-3">
                            <i class="ki-duotone ki-warning-2 fs-3 text-warning me-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fs-7">
                                <strong>Sensitive category.</strong> These issues may require individual attention. Review each one carefully before bulk resolving.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Issues list --}}
                <div class="card">
                    <div class="card-header border-0 pt-5 pb-0">
                        <h3 class="card-title fw-bold fs-6 text-muted text-uppercase">Issues in this group</h3>
                        <div class="card-toolbar">
                            <span class="text-muted fs-8">Remove any that don't belong before resolving</span>
                        </div>
                    </div>
                    <div class="card-body py-4" id="issues-list">
                        @forelse($issues as $issue)
                        <div class="d-flex align-items-start gap-3 p-4 rounded bg-light mb-3" id="issue-row-{{ $issue->id }}">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <a href="{{ route('tenant.admin.issue.show', $issue->id) }}" target="_blank"
                                       class="fw-bold text-gray-800 text-hover-primary fs-7">
                                        #{{ $issue->public_id }} — {{ Str::limit($issue->title, 60) }}
                                    </a>
                                    <span class="badge badge-light-{{ $issue->priority === 'urgent' ? 'danger' : ($issue->priority === 'high' ? 'warning' : 'secondary') }} fs-9">
                                        {{ $issue->priority }}
                                    </span>
                                </div>
                                <div class="text-muted fs-8 d-flex gap-3 flex-wrap">
                                    @if($issue->roasterContact)
                                        <span>{{ $issue->roasterContact->name }} ({{ ucfirst($issue->roasterContact->role) }})</span>
                                    @endif
                                    @if($issue->branch)
                                        <span>{{ $issue->branch->name }}</span>
                                    @endif
                                    <span>{{ $issue->status }}</span>
                                    <span>{{ $issue->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <form method="POST"
                                  action="{{ route('tenant.admin.issue_groups.remove_issue', [$issueGroup, $issue]) }}"
                                  onsubmit="return confirm('Remove this issue from the group? It will not be resolved with the others.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger flex-shrink-0" title="Remove from group">
                                    <i class="ki-duotone ki-cross fs-5"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                            </form>
                        </div>
                        @empty
                            <div class="text-center py-8 text-muted">No issues remaining in this group.</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Right: resolve form --}}
            <div class="col-lg-5">

                <div class="card sticky-top" style="top: 100px">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Bulk Resolution</h3>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('tenant.admin.issue_groups.bulk_resolve', $issueGroup) }}"
                              id="bulk-resolve-form">
                            @csrf

                            <div class="mb-5">
                                <label class="form-label required fw-semibold">Resolution message to parents</label>
                                <textarea name="message" rows="6"
                                          class="form-control form-control-solid @error('message') is-invalid @enderror"
                                          placeholder="e.g. We have resolved the issue with the water supply in Block C. The plumbing was repaired on Monday morning and is now fully operational. We apologise for the inconvenience."
                                          required>{{ old('message') }}</textarea>
                                <div class="text-muted fs-8 mt-1">This message will be posted as a reply on every issue and emailed to each parent.</div>
                                @error('message')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="close_issues" value="1">
                                    <span class="form-check-label">
                                        <span class="fw-semibold d-block">Close issues (not just resolve)</span>
                                        <span class="text-muted fs-8">Sends CSAT survey to parents. Leave unchecked to set status to "Resolved" only.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="separator my-5"></div>

                            <div class="d-flex flex-column gap-3">
                                <button type="submit" class="btn btn-primary w-100"
                                        onclick="return confirm('Bulk resolve {{ $issues->count() }} issues and notify all parents? This cannot be undone.')">
                                    <i class="ki-duotone ki-check-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Resolve {{ $issues->count() }} Issues &amp; Notify Parents
                                </button>
                                <form method="POST" action="{{ route('tenant.admin.issue_groups.dismiss', $issueGroup) }}"
                                      onsubmit="return confirm('Dismiss this group without resolving?')">
                                    @csrf
                                    <button class="btn btn-light w-100">Dismiss group</button>
                                </form>
                                <a href="{{ route('tenant.admin.issue_groups.index') }}" class="btn btn-light-secondary w-100">
                                    Back to groups
                                </a>
                            </div>

                        </form>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
