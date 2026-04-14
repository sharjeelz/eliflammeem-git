@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Escalation Rules</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Escalation Rules</li>
            </ul>
        </div>
        @endpush

        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $rules->total() }} rule{{ $rules->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.escalation_rules.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Rule
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Rule</th>
                            <th>Trigger</th>
                            <th>Threshold</th>
                            <th>Action</th>
                            <th>Scope</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($rules as $rule)
                            <tr>
                                <td>
                                    <a href="{{ route('tenant.admin.escalation_rules.edit', $rule) }}"
                                       class="text-gray-800 text-hover-primary fw-bold">
                                        {{ $rule->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $rule->trigger_status }}</span>
                                    @if($rule->priority_filter)
                                        <span class="badge badge-light-warning ms-1">{{ $rule->priority_filter }} only</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $rule->hours_threshold }}h</span>
                                    <span class="text-muted fs-8">in status</span>
                                </td>
                                <td>
                                    @if($rule->action_notify_role)
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ki-duotone ki-notification-bing fs-5 text-primary">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            <span>Notify {{ $rule->action_notify_role }}</span>
                                        </div>
                                    @endif
                                    @if($rule->action_bump_priority)
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <i class="ki-duotone ki-arrow-up fs-5 text-danger">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            <span>Bump priority</span>
                                        </div>
                                    @endif
                                    @if(!$rule->action_notify_role && !$rule->action_bump_priority)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->scope_type === 'global')
                                        <span class="text-muted">All branches</span>
                                    @elseif($rule->scope_type === 'branch')
                                        <span class="badge badge-light-info">Branch #{{ $rule->scope_id }}</span>
                                    @else
                                        <span class="badge badge-light-success">Category #{{ $rule->scope_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.escalation_rules.edit', $rule) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-1">Edit</a>
                                    <form method="POST" action="{{ route('tenant.admin.escalation_rules.destroy', $rule) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete rule \'{{ addslashes($rule->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-10">
                                    No escalation rules yet.
                                    <a href="{{ route('tenant.admin.escalation_rules.create') }}">Add your first rule</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
