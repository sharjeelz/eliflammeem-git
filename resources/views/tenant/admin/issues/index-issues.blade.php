@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')
        @push('page-title')
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">

            <!--begin::Heading-->
            @php
                $pageSubject = isset($contact) ? $contact->name : $user->name;
                $backUrl = isset($contact)
                    ? route('tenant.admin.contacts.edit', $contact)
                    : route('tenant.admin.users.edit', $user);
            @endphp
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">
               {{ $pageSubject }}'s Issues
            </h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ $backUrl }}" class="text-muted text-hover-primary">{{ $pageSubject }}</a>
                </li>
                <li class="breadcrumb-item text-gray-900">Issues</li>
            </ul>

            <!--end::Heading-->
        </div>
        <!--end::Page title-->
        @endpush

        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span
                                class="path2"></span></i> <input type="text" data-kt-issues-table-filter="search"
                            class="form-control form-control-solid w-250px ps-13" placeholder="Search issues">
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-issues-table-toolbar="base">
                        <!--begin::Filter-->
                        <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span
                                    class="path2"></span></i>
                            Filter
                        </button>
                        <!--begin::Menu 1-->
                        <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                            <!--begin::Header-->
                            <!-- <div class="px-7 py-5">
                            <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                        </div> -->
                            <!--end::Header-->

                            <!--begin::Separator-->
                            <div class="separator border-gray-200"></div>
                            <!--end::Separator-->

                            <!--begin::Content-->
                            <div class="px-7 py-5" data-kt-issues-table-filter="form">
                                <!--begin::Input group-->
                                 
                                <div class="mb-10">
                                    <label class="form-label fs-6 fw-semibold">Status</label>
                                    <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                        data-placeholder="Select option" data-allow-clear="true"
                                        data-kt-issues-table-filter="role" data-hide-search="true">
                                        <option data-select2-id="select2-data-9-3asv"></option>
                                        @foreach (['new','in_progress','resolved','closed'] as $s)
                                        <option value="{{ $s }}" @selected(request('status')===$s)>
                                            {{ ucfirst(str_replace('_',' ',$s)) }}
                                        </option>
                                        @endforeach

                                    </select>

                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-6 fw-semibold">Priority</label>
                                    <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                        data-placeholder="Select option" data-allow-clear="true"
                                        data-kt-issues-table-filter="role" data-hide-search="true">
                                        <option data-select2-id="select2-data-9-3asv"></option>
                                        @foreach (['high','low','medium','urgent'] as $p)
                                        <option value="{{ $p }}" @selected(request('priority')===$p)>
                                            {{ ucfirst(str_replace('_',' ',$p)) }}
                                        </option>
                                        @endforeach

                                    </select>

                                </div>

                                <!--end::Input group-->



                                <!--begin::Actions-->
                                <div class="d-flex justify-content-end">
                                    <button type="reset"
                                        class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6"
                                        data-kt-menu-dismiss="true" data-kt-issues-table-filter="reset">Reset</button>
                                    <button type="submit" class="btn btn-primary fw-semibold px-6"
                                        data-kt-menu-dismiss="true" data-kt-issues-table-filter="filter">Apply</button>
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Menu 1-->
                        <!--end::Filter-->

                        <!--begin::Export-->
                        <!-- <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_export_users">
                        <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Export
                    </button> -->
                        <!--end::Export-->

                        <!--begin::Add user-->
                        <!-- <a href="{{route('tenant.admin.users.create')}}" class="btn btn-primary"> <i
                                class="ki-duotone ki-plus fs-2"></i> Add User</a> -->

                        <!--end::Add user-->
                    </div>
                    <!--end::Toolbar-->



                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">

                <!--begin::Table-->
                <div id="kt_table_issues_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                    <div id="" class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_issues">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Code</th>
                                    @unless(isset($hideContact) && $hideContact)
                                    <th class="min-w-125px">Contact</th>
                                    @endunless
                                    <th class="min-w-125px">Title</th>
                                    <th class="min-w-125px">Priority</th>
                                    <th class="min-w-125px">Status</th>
                                    <th class="min-w-125px">Assigned</th>

                                    <th class="min-w-125px">Created</th>
                                    <th class="text-end min-w-100px">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse ($issues as $i)
                                <tr data-id="{{ $i->id }}">
                                    <td>
                                        <a href="{{route('tenant.admin.issue.show', $i->id)}}" class="text-dark">{{$i->public_id }}</a>
                                        

                                    </td>
                                    @unless(isset($hideContact) && $hideContact)
                                    <td>
                                        {{ $i->roasterContact->name ?? '—' }}
                                        @if($i->roasterContact?->role)
                                        @php $roleColor = ['parent'=>'info','teacher'=>'primary','admin'=>'warning'][$i->roasterContact->role] ?? 'secondary'; @endphp
                                        <span class="badge badge-light-{{ $roleColor }} ms-1">{{ ucfirst($i->roasterContact->role) }}</span>
                                        @endif
                                    </td>
                                    @endunless

                                    <td>
                                        {{ $i->title }}
                                        @if($i->is_spam)
                                            <span class="badge badge-light-danger ms-1">Spam</span>
                                        @endif
                                    </td>
                                    @php
                                        $priorityColor = ['low'=>'success','medium'=>'info','high'=>'warning','urgent'=>'danger'][$i->priority] ?? 'secondary';
                                        $statusColor   = ['new'=>'primary','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'][$i->status] ?? 'secondary';
                                    @endphp

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

                                        {{$i->assignedTo->name?? 'Not Assigned' }}
                                    </td>


                                    <td>

                                        {{ $i->created_at->format('Y-m-d H:i') }}
                                    </td>



                                    <td class="text-end">
                                        <a href="{{ route('tenant.admin.issue.show', $i->id) }}"
                                           class="btn btn-sm btn-light btn-active-light-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                        
                    </div>
                    <div id="" class="row">
                        <div id=""
                            class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar">
                        </div>

                    </div>
                    <div class="dt-autosize" style="width: 100%; height: 0px;"></div>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
        @push('scripts')
        <script src="{{asset('theme/js/custom/users/table.js')}}" defer></script>
        @endpush
        @endsection