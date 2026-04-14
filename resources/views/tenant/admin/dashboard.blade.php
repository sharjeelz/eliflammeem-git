@extends('layouts.tenant_admin')
@section('page_title', 'Dashboard')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class=" container-xxl " id="kt_content_container">
         @include('partials.alerts')
        @push('page-title')
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">

            <!--begin::Heading-->
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">
              Dashboard - {{auth()->user()->name}}
            </h1>
            <!--end::Heading-->

        </div>
        <!--end::Page title--->
        {{-- Scope notice --}}
        
        @if($user->hasRole('branch_manager'))
         <div class="d-flex align-items-center rounded py-5 px-5 bg-light-primary ">
         <i class="ki-duotone ki-information-5 fs-3x text-primary me-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>  
         You are viewing statistics for &nbsp; <strong>your branch only</strong>.
        </div>
        @elseif($user->hasRole('staff'))
        <div class="d-flex align-items-center rounded py-5 px-5 bg-light-primary ">
            <i class="ki-duotone ki-information-5 fs-3x text-primary me-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> You are viewing statistics for &nbsp; <strong> issues assigned to you</strong>.
        </div>
        @elseif($user->hasRole('admin'))
        <div class="d-flex align-items-center rounded py-5 px-5 bg-light-primary ">
<i class="ki-duotone ki-information-5 fs-3x text-primary me-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> You are viewing statistics for  &nbsp;<strong>All branches and Members</strong>.
        </div>
        @endif
        @endpush

        {{-- Queue health warning (admin only) --}}
        @if(!empty($queueWarning))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-4" role="alert">
            <i class="ki-duotone ki-warning-2 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div>
                <h4 class="mb-1 text-danger">Queue Worker May Be Down</h4>
                <span class="fs-6">There are jobs pending for more than 10 minutes. AI analysis, emails, and notifications may be delayed. Please restart the queue worker: <code>php artisan queue:restart</code></span>
            </div>
        </div>
        @endif

        {{-- Date range filter --}}
        <form method="GET" action="{{ route('tenant.admin.dashboard') }}" class="d-flex align-items-center gap-3 flex-wrap mb-6 mt-4">
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted fw-semibold fs-7 mb-0">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm form-control-solid w-150px">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted fw-semibold fs-7 mb-0">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm form-control-solid w-150px">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted fw-semibold fs-7 mb-0">Grain</label>
                <select name="grain" class="form-select form-select-sm form-select-solid w-110px">
                    @foreach(['day','week','month'] as $g)
                        <option value="{{ $g }}" @selected($grain === $g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            <a href="{{ route('tenant.admin.dashboard') }}" class="btn btn-sm btn-light">Reset</a>
            <span class="text-muted fs-7 ms-2">
                Showing {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
            </span>
        </form>

        <!--begin::Row-->
        <div class="row g-5 mb-5 mb-xl-10 tour-kpi-row">
            <!--begin::Col-->
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #F8285A">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #F8285A">
                            <i class="ki-duotone ki-message-question text-white fs-2qx lh-0"><span
                                    class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                    class="path4"></span><span class="path5"></span><span class="path6"></span><span
                                    class="path7"></span><span class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx text-white fw-bold me-6">{{$kpis['new_period']}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Total</span>
                            <span class="opacity-50"></span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #7239EA">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #7239EA">
                            <i class="ki-duotone ki-call text-white fs-2qx lh-0"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                    class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                    class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx text-white fw-bold me-6">{{$kpis['open']}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Open</span>
                            <span class="opacity-50"></span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #17c653">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #17c653">
                            <i class="ki-duotone ki-like text-white fs-2qx lh-0"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                    class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                    class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx text-white fw-bold me-6">{{$kpis['resolved_period']}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Resolved</span>
                            <span class="opacity-50"></span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #1b84ff">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #1b84ff">
                            <i class="ki-duotone ki-verify text-white fs-2qx lh-0"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                    class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                    class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx text-white fw-bold me-6">{{$kpis['closed_period']}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Closed</span>
                            <span class="opacity-50"></span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #1a60c3ff">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #1a60c3ff">
                            <i class="ki-duotone ki-medal-star text-white fs-2qx lh-0"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                    class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                    class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx text-white fw-bold me-6">{{$kpis['avg_resolution_hours'] ?? '—'}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Average</span>
                            <span class="opacity-50"> Resolution(h)</span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <!--begin::Card widget 3-->
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
                    style="background-color: #f37812">
                    <!--begin::Header-->
                    <div class="card-header pt-5 mb-3">
                        <!--begin::Icon-->
                        <div class="d-flex flex-center rounded-circle h-80px w-80px"
                            style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #f37812">
                            <i class="ki-duotone ki-support-24 text-white fs-2qx lh-0"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                    class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                    class="path8"></span></i>
                        </div>
                        <!--end::Icon-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end mb-3">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <span
                                class="fs-2hx text-white fw-bold me-6">{{$kpis['avg_first_response_hours']?? '-'}}</span>

                            <div class="fw-bold fs-6 text-white">
                                <span class="d-block"></span>
                                <span class=""></span>
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
                        <!--begin::Progress-->
                        <div class="fw-bold text-white py-2">
                            <span class="fs-1 d-block">Average</span>
                            <span class="opacity-50"> First Resp(h)</span>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card widget 3-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        {{-- Live snapshot context banner --}}
        <div class="d-flex align-items-center justify-content-between bg-light rounded px-6 py-4 mb-6">
            <div class="d-flex align-items-center gap-2">
                <i class="ki-duotone ki-time fs-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
                <span class="text-gray-700 fw-semibold fs-7">
                    Live snapshot &mdash; {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}.
                    For historical trend analysis and category deep-dives, see Reports.
                </span>
            </div>
            <a href="{{ route('tenant.admin.reports.index') }}" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-chart-line fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                View full analysis
            </a>
        </div>

        <!--begin::Row-->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!--begin::Col-->
            <div class="col-lg-5 col-xl-4">

                <!--begin::List widget 23-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Categories</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6"></span>
                        </h3>
                        <!--end::Title-->

                        <!--begin::Toolbar-->
                        <div class="card-toolbar">

                        </div>
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body pt-5">
                        <!--begin::Items-->

                        <div class="">

                            <!--begin::Item-->
                            @foreach($categoryRows as $row)
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <div class="d-flex align-items-center me-5">
                                   
                                    
                                      <i class="ki-solid ki-category fs-2x text-success">&nbsp;</i>
                                  

                                   
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">
                                            {{ $row->issueCategory->name ?? '—' }}</a>

                                        <!--end::Title-->

                                    </div>
                                    <!--end::Content-->
                                </div>
                                <!--end::Section-->

                                <!--begin::Wrapper-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Number-->
                                    <span class="text-gray-800 fw-bold fs-4 me-3">{{ $row->c }}</span>
                                    <!--end::Number-->


                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Item-->

                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->

                            @endforeach



                        </div>
                        <!--end::Items-->
                    </div>
                    <!--end: Card Body-->
                </div>
                <!--end::List widget 23-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-lg-7 col-xl-8">

                <!--begin::Table widget 15-->
                <div class="card card-flush h-lg-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Assigned Issues</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">Top 5 staff by issue count</span>
                        </h3>
                        <!--end::Title-->

                        <!--begin::Toolbar-->
                        <!-- <div class="card-toolbar">
                           
                            <div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="left"
                                class="btn btn-sm btn-light d-flex align-items-center px-4">
                               
                                <div class="text-gray-600 fw-bold">
                                    Loading date range...
                                </div>
                               

                                <i class="ki-duotone ki-calendar-8 fs-1 ms-2 me-0"><span class="path1"></span><span
                                        class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                        class="path5"></span><span class="path6"></span></i>
                            </div>
                            
                        </div> -->
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body pt-6">
                        <!--begin::Table container-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                        <th class="p-0 pb-3 min-w-175px text-start">Name</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">Total</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">Open</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">Resolved</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">Closed</th>

                                    </tr>
                                </thead>
                                <!--end::Table head-->

                                <!--begin::Table body-->
                                <tbody>
                                    @foreach($staffRows as $row)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="{{asset('theme/media/avatars/300-31.jpg')}}" class=""
                                                        alt="" />
                                                        @if($row->assignedTo->id == Auth::user()->id)
                                                  
                                                   <span class="symbol-badge badge badge-circle bg-success start-100">Me</span>
                                                    @endif
                                                </div>

                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="{{ route('tenant.admin.users.edit', $row->assignedTo->id) }}"
                                                        class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">
                                                        {{ $row->assignedTo->name ?? '—' }}
                                                        
                                                    </a>

                                                    




                                                    <span
                                                        class="text-gray-500 fw-semibold d-block fs-7">{{$row->assignedTo->getRoleNames()->toArray()[0]}} &middot; {{ $row->assignedTo->branches->pluck('name')->join(', ') }}</span>
                                                        
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">{{ $row->total }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">{{ $row->open }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">{{ $row->resolved }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">{{ $row->closed }}</span>
                                        </td>







                                        <td class="text-end">
                                          @if($row->assignedTo->id == Auth::user()->id)
                                             <a href="{{ route('tenant.admin.issues.assignedTome') }}" 
                                             class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                                            </a>
                                            @else
                                            <a href="{{ route('tenant.admin.issues.assigned', $row->assignedTo->id) }}"
                                                class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                                            </a>
                                           
                                            @endif
                                        </td>
                                    </tr>

                                    @endforeach

                                </tbody>
                                <!--end::Table body-->
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end: Card Body-->
                </div>
                <!--end::Table widget 15-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row g-5 g-xl-10 g-xl-10">

            <!--begin::Col-->
            <div class="col-md-6 mb-6 mb-xl-12">
                <!--begin::Chart widget 14-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Priority Distribution</span>

                            <!-- <span class="text-gray-500 pt-2 fw-semibold fs-6">Performance &amp; achievements</span> -->
                        </h3>
                        <!--end::Title-->


                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body pt-5">
                        <!--begin::Chart container-->
                        <div id="issues_by_distribution" class="w-100 h-350px">
                        </div>
                        <!--end::Chart container-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Chart widget 14-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 mb-6 mb-xl-12">
                <!--begin::Chart widget 14-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Status Distribution</span>

                            <!-- <span class="text-gray-500 pt-2 fw-semibold fs-6">Performance &amp; achievements</span> -->
                        </h3>
                        <!--end::Title-->


                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body pt-5">
                        <!--begin::Chart container-->
                        <div id="issues_by_distribution_state" class="w-100 h-350px">
                        </div>
                        <!--end::Chart container-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Chart widget 14-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->



        {{-- ── AI Action Widgets ─────────────────────────────────────────────────── --}}
        @if(!$user->hasRole('staff'))
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">

            {{-- Widget 1: Needs Attention --}}
            <div class="col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-900">
                            <i class="ki-duotone ki-warning-2 fs-3 text-danger me-2">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            Needs Attention
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-light-danger fs-8">Urgent &amp; Unassigned</span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @if($urgentUnassigned->isEmpty())
                            <div class="d-flex flex-column align-items-center justify-content-center py-8 text-center">
                                <i class="ki-duotone ki-check-circle fs-3x text-success mb-3">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-gray-600 fw-semibold fs-7">No urgent unassigned issues</div>
                                <div class="text-muted fs-8 mt-1">All escalate-flagged issues have been assigned.</div>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($urgentUnassigned as $ui)
                                <div class="p-3 rounded bg-light-danger">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                        <a href="{{ route('tenant.admin.issue.show', $ui->id) }}"
                                           class="text-gray-800 text-hover-danger fw-bold fs-7 text-truncate"
                                           style="min-width:0">
                                            {{ $ui->title }}
                                        </a>
                                        <a href="{{ route('tenant.admin.issue.show', $ui->id) }}"
                                           class="btn btn-sm btn-light-danger flex-shrink-0">Assign</a>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        @if($ui->issueCategory->name ?? null)
                                            <span class="badge badge-light fs-9">{{ $ui->issueCategory->name }}</span>
                                        @endif
                                        @if($ui->roasterContact)
                                            <span class="text-muted fs-9">{{ $ui->roasterContact->name }}</span>
                                        @endif
                                        <span class="text-muted fs-9">{{ $ui->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Widget 2: Hot Topics --}}
            <div class="col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-header pt-6">
                        <h3 class="card-title fw-bold text-gray-900">
                            <i class="ki-duotone ki-graph-up fs-3 text-warning me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Hot Topics
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-light-warning fs-8">Last 7 days</span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @if($hotTopics->isEmpty())
                            <div class="d-flex flex-column align-items-center justify-content-center py-8 text-center">
                                <i class="ki-duotone ki-time fs-3x text-muted mb-3">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-gray-600 fw-semibold fs-7">No theme data yet</div>
                                <div class="text-muted fs-8 mt-1">Themes are extracted from full AI analysis.</div>
                            </div>
                        @else
                            @php $maxCount = $hotTopics->max(); @endphp
                            <div class="d-flex flex-column gap-3">
                                @foreach($hotTopics as $topic => $cnt)
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <a href="{{ route('tenant.admin.issues.index', ['theme' => $topic]) }}" 
                                           class="text-gray-700 text-hover-primary fw-semibold fs-7 text-decoration-none"
                                           title="Click to view {{ $cnt }} {{ Str::plural('issue', $cnt) }} with this theme">
                                            {{ ucfirst($topic) }}
                                            <i class="ki-duotone ki-arrow-right fs-8 ms-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a>
                                        <span class="badge badge-light-warning fs-8 fw-bold">{{ $cnt }}</span>
                                    </div>
                                    <div class="h-6px bg-light rounded" style="cursor: pointer;" 
                                         onclick="window.location='{{ route('tenant.admin.issues.index', ['theme' => $topic]) }}'">
                                        <div class="bg-warning rounded h-6px"
                                             style="width: {{ round($cnt / $maxCount * 100) }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Widget 3: AI Issue Groups --}}
        @if(auth()->user()->hasRole('admin') && $issueGroupCount > 0)
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-12">
                <div class="card card-flush border border-dashed border-primary bg-light-primary">
                    <div class="card-body py-5 d-flex align-items-center gap-5 flex-wrap">
                        <div class="symbol symbol-50px symbol-circle bg-primary flex-shrink-0">
                            <div class="symbol-label">
                                <i class="ki-duotone ki-abstract-26 fs-2 text-white">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5 text-gray-900">
                                AI detected <span class="text-primary">{{ $issueGroupCount }} issue {{ Str::plural('group', $issueGroupCount) }}</span> that may share the same root cause
                            </div>
                            <div class="text-muted fs-7">Review and bulk resolve them with a single message sent to all parents.</div>
                        </div>
                        <a href="{{ route('tenant.admin.issue_groups.index') }}" class="btn btn-primary flex-shrink-0">
                            Review Groups
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
        {{-- ── End AI Action Widgets ─────────────────────────────────────────────── --}}

        {{-- ── Positive Signals (Kudos) ───────────────────────────────────────────── --}}
        @if(auth()->user()->hasRole(['admin', 'branch_manager']) && $recentKudos->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold fs-4">
                    <i class="ki-duotone ki-heart text-danger fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Positive Signals
                </h3>
                <div class="card-toolbar text-muted fs-7">Recent compliments from parents &amp; teachers</div>
            </div>
            <div class="card-body pt-0">
                <div class="row g-4">
                    @foreach($recentKudos as $kudo)
                    <div class="col-md-6 col-xl-4">
                        <div class="bg-light-success rounded-3 p-5 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="symbol symbol-35px">
                                        <div class="symbol-label bg-success text-white fw-bold fs-7">
                                            {{ strtoupper(substr($kudo->contact?->name ?? 'A', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-gray-800 fs-7">{{ $kudo->contact?->name ?? 'Anonymous' }}</div>
                                        <div class="text-muted fs-8">{{ $kudo->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                @if($kudo->category)
                                <span class="badge badge-light-primary fs-9">{{ $kudo->category->name }}</span>
                                @endif
                            </div>
                            <p class="text-gray-700 fs-7 mb-0" style="line-height:1.6">
                                "{{ Str::limit($kudo->message, 120) }}"
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        {{-- ── End Positive Signals ────────────────────────────────────────────────── --}}

        {{-- ── AI Sentiment Analysis ───────────────────────────────────────────────── --}}
        @if(!$user->hasRole('staff'))
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-12">
                <div class="card card-flush">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">
                                <i class="ki-duotone ki-abstract-26 fs-3 text-primary me-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                AI Sentiment Analysis
                            </span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">Sentiment breakdown across issue categories</span>
                        </h3>
                    </div>
                    <div class="card-body pt-4">
                        @php
                            $pos     = (int) ($sentimentTotals['positive']?->total ?? 0);
                            $neu     = (int) ($sentimentTotals['neutral']?->total  ?? 0);
                            $neg     = (int) ($sentimentTotals['negative']?->total ?? 0);
                            $hasData = ($pos + $neu + $neg) > 0;
                        @endphp

                        @if($hasData)
                            {{-- Summary tiles --}}
                            <div class="row g-4 mb-6">
                                <div class="col-4">
                                    <a href="{{ route('tenant.admin.issues.index', ['sentiment' => 'positive']) }}" class="text-decoration-none d-block">
                                        <div class="bg-light-success rounded p-4 text-center" style="cursor:pointer;transition:transform .2s"
                                             onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <div class="fs-2x fw-bold text-success">{{ $pos }}</div>
                                            <div class="text-success fw-semibold fs-7">Positive</div>
                                            <div class="text-muted fs-8">{{ round($pos / $sentimentGrandTotal * 100) }}%</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('tenant.admin.issues.index', ['sentiment' => 'neutral']) }}" class="text-decoration-none d-block">
                                        <div class="bg-light-info rounded p-4 text-center" style="cursor:pointer;transition:transform .2s"
                                             onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <div class="fs-2x fw-bold text-info">{{ $neu }}</div>
                                            <div class="text-info fw-semibold fs-7">Neutral</div>
                                            <div class="text-muted fs-8">{{ round($neu / $sentimentGrandTotal * 100) }}%</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('tenant.admin.issues.index', ['sentiment' => 'negative']) }}" class="text-decoration-none d-block">
                                        <div class="bg-light-danger rounded p-4 text-center" style="cursor:pointer;transition:transform .2s"
                                             onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <div class="fs-2x fw-bold text-danger">{{ $neg }}</div>
                                            <div class="text-danger fw-semibold fs-7">Negative</div>
                                            <div class="text-muted fs-8">{{ round($neg / $sentimentGrandTotal * 100) }}%</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            {{-- Stacked bar chart --}}
                            <div id="ai_sentiment_chart" class="w-100" style="height:260px"></div>
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center py-10 text-center">
                                <i class="ki-duotone ki-time fs-3x text-muted mb-3">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-gray-700 fw-semibold fs-6 mb-1">No AI analysis yet</div>
                                <div class="text-muted fs-7">AI sentiment runs automatically after each issue is submitted.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        {{-- ── End AI Sentiment Analysis ────────────────────────────────────────────── --}}

    </div>
    <!--end::Container-->
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
(function () {
    var el = document.getElementById('ai_sentiment_chart');
    if (!el) return;
    var chart = echarts.init(el);
    var categories = @json($aiCategories);
    var positive   = @json($aiSeriesData['positive'] ?? []);
    var neutral    = @json($aiSeriesData['neutral']  ?? []);
    var negative   = @json($aiSeriesData['negative'] ?? []);
    chart.setOption({
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: function (params) {
                var cat = params[0].axisValue;
                var lines = '<div style="font-weight:600;margin-bottom:4px">' + cat + '</div>';
                params.forEach(function (p) {
                    if (p.value > 0) {
                        lines += '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + p.color + ';margin-right:5px"></span>'
                                + p.seriesName + ': <b>' + p.value + '</b><br>';
                    }
                });
                return lines;
            }
        },
        legend: { data: ['Positive', 'Neutral', 'Negative'], top: 'bottom', textStyle: { color: '#99a1b7', fontSize: 12 } },
        grid: { left: 16, right: 16, top: 10, bottom: 36, containLabel: true },
        xAxis: {
            type: 'category', data: categories,
            axisLabel: { show: false },
            axisLine: { lineStyle: { color: '#e9ecef' } }
        },
        yAxis: {
            type: 'value', minInterval: 1,
            axisLabel: { color: '#99a1b7', fontSize: 11 },
            splitLine: { lineStyle: { color: '#f1f1f4' } }
        },
        series: [
            { name: 'Positive', type: 'bar', stack: 'sentiment', data: positive, itemStyle: { color: '#17c653' } },
            { name: 'Neutral',  type: 'bar', stack: 'sentiment', data: neutral,  itemStyle: { color: '#7239ea' } },
            { name: 'Negative', type: 'bar', stack: 'sentiment', data: negative, itemStyle: { color: '#f8285a', borderRadius: [4,4,0,0] } },
        ]
    });
    window.addEventListener('resize', function () { chart.resize(); });
})();
</script>
<script>
    am5.ready(function() {


  const myTheme = am5.Theme.new(root);

myTheme.rule("LineSeries").setAll({
  fill: am5.color('#f90000ff'),
  fontSize: "1.5em",

});      
// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("issues_by_distribution");

// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
root.setThemes([
  am5themes_Animated.new(root),
  myTheme
  
]);

// Create chart
// https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
var chart = root.container.children.push(
  am5percent.PieChart.new(root, {
    endAngle: 270
  })
);

// Create series
// https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
var series = chart.series.push(
  am5percent.PieSeries.new(root, {
    valueField: "value",
    categoryField: "category",
    endAngle: 270,
    fill:'blue'
    //fill color
    
  })
);

series.states.create("hidden", {
  endAngle: -90
});

 const priorityLabels= @json($priorityRows->pluck('priority')->map(fn($p)=>ucfirst($p)));
  const priorityCounts= @json($priorityRows->pluck('c'));
  console.log(priorityLabels, priorityCounts);
// Set data
// https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
var data = priorityLabels.map((labels, index) => ({
            category: labels,
            value: parseInt(priorityCounts[index], 10)
        }));
        console.log(data);
series.data.setAll(data);

series.appear(1000, 100);

}); // end am5.ready()

am5.ready(function() {

    // Create root element
    // https://www.amcharts.com/docs/v5/getting-started/#Root_element
    var root = am5.Root.new("issues_by_distribution_state");
    // Set themes
    // https://www.amcharts.com/docs/v5/concepts/themes/
    root.setThemes([
      am5themes_Animated.new(root)
    ]);
    // Create chart
    // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
    var chart = root.container.children.push(
      am5percent.PieChart.new(root, {
        endAngle: 270
      })
    );
    // Create series
    // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
    var series = chart.series.push(
      am5percent.PieSeries.new(root, {
        valueField: "value",
        categoryField: "category",
        endAngle: 270
      })
    );
   const statusLabels  = @json($statusRows->pluck('status')->map(fn($s)=>ucwords(str_replace('_',' ', $s))));
  const statusCounts  = @json($statusRows->pluck('c'));

// Set data
// https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
var data = statusLabels.map((labels, index) => ({
            category: labels,
            value: parseInt(statusCounts[index], 10)
        }));
        console.log(data);
series.data.setAll(data);

series.appear(1000, 100);
});
</script>
@endpush

@include('partials.dashboard_tour')

@endsection