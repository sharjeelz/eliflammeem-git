<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>
    <title>@yield('page_title', 'Admin') — {{ config('app.name', 'ElifLammeem') }}</title>
    <meta charset="utf-8" />
    <meta name="description" content="
            The most advanced Tailwind CSS & Bootstrap 5 Admin Theme with 40 unique prebuilt layouts on Themeforest trusted by 100,000 beginners and professionals. Multi-demo,
            Dark Mode, RTL support and complete React, Angular, Vue, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel versions.
            Grab your copy now and get life-time updates for free.
        " />
    <meta name="keywords" content="
            tailwind, tailwindcss, metronic, bootstrap, bootstrap 5, angular, VueJs, React, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js,
            Node.js, Flask, Symfony & Laravel starter kits, admin themes, web design, figma, web development, free templates,
            free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button,
            bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon
        " />
        <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title"
        content="Metronic - The World's #1 Selling Tailwind CSS & Bootstrap Admin Template by KeenThemes" />
    <meta property="og:url" content="https://keenthemes.com/metronic" />
    <meta property="og:site_name" content="Metronic by Keenthemes" />
    <link rel="canonical" href="call-center.html" />
    <link rel="shortcut icon" href="../assets/media/logos/favicon.ico" />

    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{asset('theme/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('theme/plugins/custom/vis-timeline/vis-timeline.bundle.css')}}" rel="stylesheet"
        type="text/css" />
    <!--end::Vendor Stylesheets-->


    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{asset('theme/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('theme/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    @stack('styles')

    <style>
        /* ── Zoom / Responsive Layout Overrides ────────────────────────────────────
           KT Metronic hard-codes the sidebar at 300px and the wrapper at
           padding-left:350px from min-width:992px. At 125–150% browser zoom on a
           1366px laptop the logical viewport drops to ~910–1093px — still above
           992px so the sidebar stays fixed and crushes the content.

           Fix: keep KT's JS drawer logic untouched (lg breakpoint). Instead, shrink
           the sidebar to 240px between 992–1199px so it takes less horizontal space,
           and adjust the wrapper padding to match. This is pure CSS — no JS changes.
        ───────────────────────────────────────────────────────────────────────── */

        /* 992–1199px: sidebar visible but narrower to free up content space at zoom.
           KT positions .aside at left:50px, so sidebar occupies 50px→290px (240px wide).
           Wrapper must clear 290px — use 295px so there's a small gap. */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .aside { width: 240px !important; }
            .wrapper { padding-left: 295px !important; }
            .aside-logo,
            .aside-footer { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
            .aside-menu { padding-left: 0.5rem !important; padding-right: 0.25rem !important; }
        }
        /* 1200px+: .aside is left:50px, width:300px → occupies 50–350px.
           Keep original 350px wrapper padding so nothing is obscured. */
        @media (min-width: 1200px) {
            .wrapper { padding-left: 350px !important; }
        }

        /* Content / header containers: full width, sensible side padding */
        #kt_header_container,
        #kt_content_container {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 24px !important;
            padding-right: 24px !important;
        }

        /* Footer */
        #kt_footer {
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
        }
    </style>

    <!-- Google tag (gtag.js) -->

    <script>
    // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
    if (window.top != window.self) {
        window.top.location.replace(window.self.location.href);
    }
    </script>
</head>
<!--end::Head-->

<!--begin::Body-->

<body id="kt_body" class="header-fixed">
    <!--begin::Theme mode setup on page load-->
    <script>
    var defaultThemeMode = "light";
    var themeMode;

    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }

        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
    </script>
    <!--end::Theme mode setup on page load-->

    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">
            <!--begin::Aside-->
            <div id="kt_aside" class="aside py-9 " data-kt-drawer="true" data-kt-drawer-name="aside"
                data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
                data-kt-drawer-toggle="#kt_aside_toggle">

                <!--begin::Brand-->
                <div class="aside-logo flex-column-auto px-9 mb-3" id="kt_aside_logo">
                    <!--begin::Logo-->
                    @php
                        $school = \App\Models\School::where('tenant_id', tenant('id'))->first();
                        $logoUrl = $school?->logo_url;
                    @endphp
                    @if($school?->status === 'inactive')
                    <div class="w-100 bg-danger text-white text-center py-2 fw-semibold fs-7 mb-3" style="position:sticky;top:0;z-index:9999;border-radius:6px">
                        &#9888; This school is currently <strong>suspended</strong>. Staff and parent portal access is restricted. Reactivate in Nova to restore full access.
                    </div>
                    @endif
                    <a href="{{url('admin')}}">
                        @if($logoUrl)
                            <img alt="{{ $school->name }}" src="{{ $logoUrl }}"
                                class="h-70px logo theme-light-show"
                                style="max-width: 160px; object-fit: contain;" />
                            <img alt="{{ $school->name }}" src="{{ $logoUrl }}"
                                class="h-70px logo theme-dark-show"
                                style="max-width: 160px; object-fit: contain;" />
                        @else
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-40px h-40px rounded-2 d-flex align-items-center justify-content-center fw-bold fs-4 text-white"
                                     style="background: linear-gradient(135deg,#4338ca,#6366f1)">
                                    {{ strtoupper(substr($school?->name ?? tenant('id'), 0, 1)) }}
                                </div>
                                <span class="text-gray-800 fw-bold fs-6">{{ $school?->name ?? 'School' }}</span>
                            </div>
                        @endif
                    </a>
                    <!--end::Logo-->
                </div>
                <!--end::Brand-->

                <!--begin::Aside menu-->
                <div class="aside-menu flex-column-fluid ps-5 pe-3 mb-3" id="kt_aside_menu">
                    <!--begin::Aside Menu-->
                    <div class="w-100 hover-scroll-overlay-y d-flex pe-3" id="kt_aside_menu_wrapper"
                        data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                        data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer"
                        data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu, #kt_aside_menu_wrapper"
                        data-kt-scroll-offset="100">
                        <!--begin::Menu-->
                        <div class="menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold mt-0"
                            id="#kt_aside_menu" data-kt-menu="true">
                            @include('partials.sidebar_menu')
                        </div>
                        <!--end::Menu-->
                    </div>
                    <!--end::Aside Menu-->
                </div>
                <!--end::Aside menu-->

                <!--begin::Footer-->
                <div class="aside-footer flex-column-auto px-9 pb-2" id="kt_aside_footer">
                    <button id="replay-tour-btn" onclick="replayTour()" class="btn btn-sm btn-light-primary w-100 mb-3 fs-8 py-2">
                        <i class="ki-duotone ki-map fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                        Take a Tour
                    </button>
                    @php
                        $tenantPlan = tenancy()->tenant?->plan ?? 'starter';
                        $planLabels = ['starter' => 'Starter', 'growth' => 'Growth', 'pro' => 'Pro', 'enterprise' => 'Enterprise'];
                        $planColors = ['starter' => 'secondary', 'growth' => 'info', 'pro' => 'primary', 'enterprise' => 'warning'];
                        $planLabel  = $planLabels[$tenantPlan] ?? ucfirst($tenantPlan);
                        $planColor  = $planColors[$tenantPlan] ?? 'secondary';
                    @endphp
                    <a href="{{ route('tenant.admin.plan.index') }}"
                       class="d-flex align-items-center justify-content-between mb-3 text-decoration-none">
                        <span class="text-muted fs-8 fw-semibold">Current Plan</span>
                        <span class="badge badge-light-{{ $planColor }} fs-8 fw-bold">
                            {{ $planLabel }}
                            <i class="ki-solid ki-arrow-right fs-9 ms-1 opacity-75"></i>
                        </span>
                    </a>
                    <!--begin::User panel-->
                    <div class="d-flex flex-stack">
                        <!--begin::Wrapper-->
                        <div class="d-flex align-items-center">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-circle symbol-40px">
                                <div class="symbol-label fs-6 fw-bold text-white"
                                     style="background:linear-gradient(135deg,#4338ca,#6366f1)">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            </div>
                            <!--end::Avatar-->

                            <!--begin::User info-->
                            <div class="ms-2">
                                <!--begin::Name-->
                                <a href="{{ route('tenant.admin.profile.edit') }}" class="text-gray-800 text-hover-primary fs-6 fw-bold lh-1 d-block mb-1"> {{Auth::user()->name}}</a>
                                <!--end::Name-->

                                <!--begin::Email-->
                                <span class="text-muted fw-semibold d-block fs-7 lh-1 mb-1 text-break" style="word-break: break-word;">{{Auth::user()->email}}</span>
                                <!--end::Email-->

                                <!--begin::Role-->
                                @php
                                    $userRole = Auth::user()->getRoleNames()->toArray()[0] ?? '';
                                    $roleColor = ['admin' => 'danger', 'branch_manager' => 'warning', 'staff' => 'primary'][$userRole] ?? 'secondary';
                                @endphp
                                <span class="badge badge-light-{{ $roleColor }} fs-8 fw-bold">{{ ucfirst(str_replace('_', ' ', $userRole)) }}</span>
                                <!--end::Role-->
                            </div>
                            <!--end::User info-->
                        </div>
                        <!--end::Wrapper-->

                        <!--begin::User menu-->
                        <div class="ms-1">
                            <div class="btn btn-sm btn-icon btn-active-color-primary position-relative me-n2"
                                data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-overflow="true"
                                data-kt-menu-placement="top-end">
                                <i class="ki-duotone ki-setting-2 fs-1"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </div>

                            <!--begin::User account menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                data-kt-menu="true">

                                <!--begin::Menu item-->
                                <div class="menu-item px-5">
                                    <a href="{{ route('tenant.admin.profile.edit') }}" class="menu-link px-5">
                                        My Profile
                                    </a>
                                </div>
                                <!--end::Menu item-->

                                <!--begin::Menu item-->
                                 @if(auth()->user()->hasRole('admin'))
                                
                               
                                {{-- <div class="menu-item px-5">
                                    
                                    <a href="{{ route('tenant.admin.issues.index') }}" class="menu-link px-5">
                                        <span class="menu-text">All Issues</span>
                                        <!-- <span class="menu-badge">
                                            <span class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>
                                        </span> -->
                                    </a>
                                </div> --}}
                                @endif
                                <!--end::Menu item-->

                                <!--begin::Menu item-->
                                
                                <!--end::Menu item-->

                                <!--begin::Menu item-->
                                {{-- <div class="menu-item px-5">
                                    <a href="../account/statements.html" class="menu-link px-5">
                                        My Statements
                                    </a>
                                </div> --}}
                                <!--end::Menu item-->

                                <!--begin::Menu separator-->
                                {{-- <div class="separator my-2"></div> --}}
                                <!--end::Menu separator-->


                                <!--begin::Menu item-->
                                {{-- <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                    data-kt-menu-placement="right-end" data-kt-menu-offset="-15px, 0">
                                    <a href="#" class="menu-link px-5">
                                        <span class="menu-title position-relative">
                                            Language

                                            <span
                                                class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0">
                                                English <img class="w-15px h-15px rounded-1 ms-2"
                                                    src="{{asset('theme/media/flags/united-states.svg')}}" alt="" />
                                            </span>
                                        </span>
                                    </a>

                                    <!--begin::Menu sub-->

                                    <!--end::Menu sub-->
                                </div> --}}
                                <!--end::Menu item-->

                                <!--begin::Menu item-->
                                {{-- <div class="menu-item px-5 my-1">
                                    <a href="../account/settings.html" class="menu-link px-5">
                                        Account Settings
                                    </a>
                                </div> --}}
                                <!--end::Menu item-->


                                <!--begin::Menu item-->
                                <div class="menu-item px-5">
                                    <form method="POST" action="{{ route('tenant.logout') }}">
                                        @csrf
                                        <button type="submit" class="menu-link px-5"
                                            style="border:none; background:none;">
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::User account menu-->
                        </div>
                        <!--end::User menu-->
                    </div>
                    <!--end::User panel-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Aside-->
            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <!--begin::Header-->
                <div id="kt_header" class="header mt-0 mt-lg-0 pt-lg-0 " data-kt-sticky="true"
                    data-kt-sticky-name="header" data-kt-sticky-offset="{lg: '300px'}">

                    <!--begin::Container-->
                    <div class=" container  d-flex flex-stack flex-wrap gap-4" id="kt_header_container">

                        <!--begin::Page title-->
                       @stack('page-title')
                        <!--end::Page title--->

                        <!--begin::Wrapper-->
                        <div class="d-flex d-lg-none align-items-center ms-n3 me-2">
                            <!--begin::Aside mobile toggle-->
                            <div class="btn btn-icon btn-active-icon-primary" id="kt_aside_toggle">
                                <i class="ki-duotone ki-abstract-14 fs-1 mt-1"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </div>
                            <!--end::Aside mobile toggle-->

                            <!--begin::Logo-->
                            <a href="../index.html" class="d-flex align-items-center">
                                <img alt="Logo" src="{{asset('theme/media/logos/demo3.svg')}}"
                                    class="theme-light-show h-20px" />
                                <img alt="Logo" src="{{asset('theme/media/logos/demo3-dark.svg')}}"
                                    class="theme-dark-show h-20px" />
                            </a>
                            <!--end::Logo-->
                        </div>
                        <!--end::Wrapper-->


                        <!--begin::Topbar-->
                        <div class="d-flex align-items-center flex-shrink-0 mb-0 mb-lg-0">

                            <!--begin::Search-->
                            {{-- Search feature disabled - KTSearch component causes errors without required form elements --}}
                            <div class="header-search d-flex align-items-center w-lg-250px d-none">

                                <!--begin::Tablet and mobile search toggle-->
                                {{-- <div data-kt-search-element="toggle"
                                    class="search-toggle-mobile d-flex d-lg-none align-items-center">
                                    <div
                                        class="d-flex btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px">
                                        <i class="ki-duotone ki-magnifier fs-1 "><span class="path1"></span><span
                                                class="path2"></span></i>
                                    </div>
                                </div> --}}
                                <!--end::Tablet and mobile search toggle-->

                                <!--begin::Form(use d-none d-lg-block classes for responsive search)-->
                       
                                <!--end::Form-->
                                <!--begin::Menu-->
                                <div data-kt-search-element="content"
                                    class="menu menu-sub menu-sub-dropdown py-7 px-7 overflow-hidden w-300px w-md-350px">
                                    <!--begin::Wrapper-->
                                    <div data-kt-search-element="wrapper">
                                        <!--begin::Recently viewed-->
                                        <div data-kt-search-element="results" class="d-none">
                                            <!--begin::Items-->
                                            <div class="scroll-y mh-200px mh-lg-350px">
                                                <!--begin::Category title-->
                                                <h3 class="fs-5 text-muted m-0  pb-5"
                                                    data-kt-search-element="category-title">
                                                    Users </h3>
                                                <!--end::Category title-->




                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <img src="{{asset('theme/media/avatars/300-6.jpg')}}" alt="" />
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Karina Clark</span>
                                                        <span class="fs-7 fw-semibold text-muted">Marketing
                                                            Manager</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <img src="{{asset('theme/media/avatars/300-2.jpg')}}" alt="" />
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Olivia Bold</span>
                                                        <span class="fs-7 fw-semibold text-muted">Software
                                                            Engineer</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <img src="{{asset('theme/media/avatars/300-9.jpg')}}" alt="" />
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Ana Clark</span>
                                                        <span class="fs-7 fw-semibold text-muted">UI/UX Designer</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <img src="{{asset('theme/media/avatars/300-14.jpg')}}" alt="" />
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Nick Pitola</span>
                                                        <span class="fs-7 fw-semibold text-muted">Art Director</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <img src="{{asset('theme/media/avatars/300-11.jpg')}}" alt="" />
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Edward Kulnic</span>
                                                        <span class="fs-7 fw-semibold text-muted">System
                                                            Administrator</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->
                                                <!--begin::Category title-->
                                                <h3 class="fs-5 text-muted m-0 pt-5 pb-5"
                                                    data-kt-search-element="category-title">
                                                    Customers </h3>
                                                <!--end::Category title-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <img class="w-20px h-20px"
                                                                src="{{asset('theme/media/svg/brand-logos/volicity-9.svg')}}"
                                                                alt="" />
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Company Rbranding</span>
                                                        <span class="fs-7 fw-semibold text-muted">UI Design</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <img class="w-20px h-20px"
                                                                src="{{asset('theme/media/svg/brand-logos/tvit.svg')}}"
                                                                alt="" />
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Company Re-branding</span>
                                                        <span class="fs-7 fw-semibold text-muted">Web Development</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <img class="w-20px h-20px"
                                                                src="{{asset('theme/media/svg/misc/infography.svg')}}"
                                                                alt="" />
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Business Analytics App</span>
                                                        <span class="fs-7 fw-semibold text-muted">Administration</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <img class="w-20px h-20px"
                                                                src="{{asset('theme/media/svg/brand-logos/leaf.svg')}}"
                                                                alt="" />
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">EcoLeaf App Launch</span>
                                                        <span class="fs-7 fw-semibold text-muted">Marketing</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <img class="w-20px h-20px"
                                                                src="{{asset('theme/media/svg/brand-logos/tower.svg')}}"
                                                                alt="" />
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column justify-content-start fw-semibold">
                                                        <span class="fs-6 fw-semibold">Tower Group Website</span>
                                                        <span class="fs-7 fw-semibold text-muted">Google Adwords</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->

                                                <!--begin::Category title-->
                                                <h3 class="fs-5 text-muted m-0 pt-5 pb-5"
                                                    data-kt-search-element="category-title">
                                                    Projects </h3>
                                                <!--end::Category title-->


                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-notepad fs-2 text-primary"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span
                                                                    class="path3"></span><span
                                                                    class="path4"></span><span class="path5"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <span class="fs-6 fw-semibold">Si-Fi Project by AU Themes</span>
                                                        <span class="fs-7 fw-semibold text-muted">#45670</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-frame fs-2 text-primary"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span
                                                                    class="path3"></span><span class="path4"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <span class="fs-6 fw-semibold">Shopix Mobile App Planning</span>
                                                        <span class="fs-7 fw-semibold text-muted">#45690</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-message-text-2 fs-2 text-primary"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span class="path3"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <span class="fs-6 fw-semibold">Finance Monitoring SAAS
                                                            Discussion</span>
                                                        <span class="fs-7 fw-semibold text-muted">#21090</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->



                                                <!--begin::Item-->
                                                <a href="#"
                                                    class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-profile-circle fs-2 text-primary"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span class="path3"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <span class="fs-6 fw-semibold">Dashboard Analitics Launch</span>
                                                        <span class="fs-7 fw-semibold text-muted">#34560</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </a>
                                                <!--end::Item-->


                                            </div>
                                            <!--end::Items-->
                                        </div>
                                        <!--end::Recently viewed-->
                                        <!--begin::Recently viewed-->
                                        <div class="" data-kt-search-element="main">
                                            <!--begin::Heading-->
                                            <div class="d-flex flex-stack fw-semibold mb-4">
                                                <!--begin::Label-->
                                                <span class="text-muted fs-6 me-2">Recently Searched:</span>
                                                <!--end::Label-->

                                                <!--begin::Toolbar-->
                                                <div class="d-flex" data-kt-search-element="toolbar">
                                                    <!--begin::Preferences toggle-->
                                                    <div data-kt-search-element="preferences-show"
                                                        class="btn btn-icon w-20px btn-sm btn-active-color-primary me-2 data-bs-toggle="
                                                        tooltip" title="Show search preferences">
                                                        <i class="ki-duotone ki-setting-2 fs-2"><span
                                                                class="path1"></span><span class="path2"></span></i>
                                                    </div>
                                                    <!--end::Preferences toggle-->

                                                    <!--begin::Advanced search toggle-->
                                                    <div data-kt-search-element="advanced-options-form-show"
                                                        class="btn btn-icon w-20px btn-sm btn-active-color-primary me-n1"
                                                        data-bs-toggle="tooltip" title="Show more search options">
                                                        <i class="ki-duotone ki-down fs-2"></i>
                                                    </div>
                                                    <!--end::Advanced search toggle-->
                                                </div>
                                                <!--end::Toolbar-->
                                            </div>
                                            <!--end::Heading-->

                                            <!--begin::Items-->
                                            <div class="scroll-y mh-200px mh-lg-325px">
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-laptop fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">BoomApp
                                                            by Keenthemes</a>
                                                        <span class="fs-7 text-muted fw-semibold">#45789</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-chart-simple fs-2 text-primary"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span
                                                                    class="path3"></span><span class="path4"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">"Kept
                                                            API Project Meeting</a>
                                                        <span class="fs-7 text-muted fw-semibold">#84050</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-chart fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">"KPI
                                                            Monitoring App Launch</a>
                                                        <span class="fs-7 text-muted fw-semibold">#84250</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-chart-line-down fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">Project
                                                            Reference FAQ</a>
                                                        <span class="fs-7 text-muted fw-semibold">#67945</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-sms fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">"FitPro
                                                            App Development</a>
                                                        <span class="fs-7 text-muted fw-semibold">#84250</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-bank fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">Shopix
                                                            Mobile App</a>
                                                        <span class="fs-7 text-muted fw-semibold">#45690</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="d-flex align-items-center mb-5">
                                                    <!--begin::Symbol-->
                                                    <div class="symbol symbol-40px me-4">
                                                        <span class="symbol-label bg-light">
                                                            <i class="ki-duotone ki-chart-line-down fs-2 text-primary"><span
                                                                    class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Symbol-->

                                                    <!--begin::Title-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#"
                                                            class="fs-6 text-gray-800 text-hover-primary fw-semibold">"Landing
                                                            UI Design" Launch</a>
                                                        <span class="fs-7 text-muted fw-semibold">#24005</span>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                                <!--end::Item-->
                                            </div>
                                            <!--end::Items-->
                                        </div>
                                        <!--end::Recently viewed-->
                                        <!--begin::Empty-->
                                        <div data-kt-search-element="empty" class="text-center d-none">
                                            <!--begin::Icon-->
                                            <div class="pt-10 pb-10">
                                                <i class="ki-duotone ki-search-list fs-4x opacity-50"><span
                                                        class="path1"></span><span class="path2"></span><span
                                                        class="path3"></span></i>
                                            </div>
                                            <!--end::Icon-->

                                            <!--begin::Message-->
                                            <div class="pb-15 fw-semibold">
                                                <h3 class="text-gray-600 fs-5 mb-2">No result found</h3>
                                                <div class="text-muted fs-7">Please try again with a different query
                                                </div>
                                            </div>
                                            <!--end::Message-->
                                        </div>
                                        <!--end::Empty-->
                                    </div>
                                    <!--end::Wrapper-->

                                    <!--begin::Preferences-->
                                    <form data-kt-search-element="advanced-options-form" class="pt-1 d-none">
                                        <!--begin::Heading-->
                                        <h3 class="fw-semibold text-gray-900 mb-7">Advanced Search</h3>
                                        <!--end::Heading-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="text" class="form-control form-control-sm form-control-solid"
                                                placeholder="Contains the word" name="query" />
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <!--begin::Radio group-->
                                            <div class="nav-group nav-group-fluid">
                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="type" value="has"
                                                        checked="checked" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary">
                                                        All
                                                    </span>
                                                </label>
                                                <!--end::Option-->

                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="type" value="users" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">
                                                        Users
                                                    </span>
                                                </label>
                                                <!--end::Option-->

                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="type" value="orders" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">
                                                        Orders
                                                    </span>
                                                </label>
                                                <!--end::Option-->

                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="type"
                                                        value="projects" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">
                                                        Projects
                                                    </span>
                                                </label>
                                                <!--end::Option-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="text" name="assignedto"
                                                class="form-control form-control-sm form-control-solid"
                                                placeholder="Assigned to" value="" />
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="text" name="collaborators"
                                                class="form-control form-control-sm form-control-solid"
                                                placeholder="Collaborators" value="" />
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <!--begin::Radio group-->
                                            <div class="nav-group nav-group-fluid">
                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="attachment" value="has"
                                                        checked="checked" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary">
                                                        Has attachment
                                                    </span>
                                                </label>
                                                <!--end::Option-->

                                                <!--begin::Option-->
                                                <label>
                                                    <input type="radio" class="btn-check" name="attachment"
                                                        value="any" />
                                                    <span
                                                        class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">
                                                        Any
                                                    </span>
                                                </label>
                                                <!--end::Option-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <select name="timezone" aria-label="Select a Timezone"
                                                data-control="select2" data-dropdown-parent="#kt_header_search"
                                                data-placeholder="date_period"
                                                class="form-select form-select-sm form-select-solid">
                                                <option value="next">Within the next</option>
                                                <option value="last">Within the last</option>
                                                <option value="between">Between</option>
                                                <option value="on">On</option>
                                            </select>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="row mb-8">
                                            <!--begin::Col-->
                                            <div class="col-6">
                                                <input type="number" name="date_number"
                                                    class="form-control form-control-sm form-control-solid"
                                                    placeholder="Lenght" value="" />
                                            </div>
                                            <!--end::Col-->

                                            <!--begin::Col-->
                                            <div class="col-6">
                                                <select name="date_typer" aria-label="Select a Timezone"
                                                    data-control="select2" data-dropdown-parent="#kt_header_search"
                                                    data-placeholder="Period"
                                                    class="form-select form-select-sm form-select-solid">
                                                    <option value="days">Days</option>
                                                    <option value="weeks">Weeks</option>
                                                    <option value="months">Months</option>
                                                    <option value="years">Years</option>
                                                </select>
                                            </div>
                                            <!--end::Col-->
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Actions-->
                                        <div class="d-flex justify-content-end">
                                            <button type="reset"
                                                class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                                data-kt-search-element="advanced-options-form-cancel">Cancel</button>

                                            <a href="../utilities/search/horizontal.html"
                                                class="btn btn-sm fw-bold btn-primary"
                                                data-kt-search-element="advanced-options-form-search">Search</a>
                                        </div>
                                        <!--end::Actions-->
                                    </form>
                                    <!--end::Preferences-->
                                    <!--begin::Preferences-->
                                    <form data-kt-search-element="preferences" class="pt-1 d-none">
                                        <!--begin::Heading-->
                                        <h3 class="fw-semibold text-gray-900 mb-7">Search Preferences</h3>
                                        <!--end::Heading-->

                                        <!--begin::Input group-->
                                        <div class="pb-4 border-bottom">
                                            <label
                                                class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">
                                                    Projects
                                                </span>

                                                <input class="form-check-input" type="checkbox" value="1"
                                                    checked="checked" />
                                            </label>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="py-4 border-bottom">
                                            <label
                                                class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">
                                                    Targets
                                                </span>
                                                <input class="form-check-input" type="checkbox" value="1"
                                                    checked="checked" />
                                            </label>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="py-4 border-bottom">
                                            <label
                                                class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">
                                                    Affiliate Programs
                                                </span>
                                                <input class="form-check-input" type="checkbox" value="1" />
                                            </label>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="py-4 border-bottom">
                                            <label
                                                class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">
                                                    Referrals
                                                </span>
                                                <input class="form-check-input" type="checkbox" value="1"
                                                    checked="checked" />
                                            </label>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="py-4 border-bottom">
                                            <label
                                                class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">
                                                    Users
                                                </span>
                                                <input class="form-check-input" type="checkbox" value="1" />
                                            </label>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Actions-->
                                        <div class="d-flex justify-content-end pt-7">
                                            <button type="reset"
                                                class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                                data-kt-search-element="preferences-dismiss">Cancel</button>
                                            <button type="submit" class="btn btn-sm fw-bold btn-primary">Save
                                                Changes</button>
                                        </div>
                                        <!--end::Actions-->
                                    </form>
                                    <!--end::Preferences-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Search-->
                            <!--begin::Notifications-->
                            <div class="d-flex align-items-center ms-3 ms-lg-4">
                                @php
                                    $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(15)->get();
                                    $unreadCount = $unreadNotifications->count();
                                @endphp
                                <div class="position-relative"
                                     data-kt-menu-trigger="click"
                                     data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    <!--begin::Bell button-->
                                    <div class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px position-relative">
                                        <i class="ki-duotone ki-notification-bing fs-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        @if($unreadCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-9"
                                              style="min-width:18px;height:18px;font-size:10px!important;padding:0 4px;">
                                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                        </span>
                                        @endif
                                    </div>
                                    <!--end::Bell button-->

                                    <!--begin::Notification dropdown-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
                                         data-kt-menu="true">
                                        <!--begin::Header-->
                                        <div class="d-flex flex-column bgi-no-repeat rounded-top"
                                             style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%);">
                                            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                                                Notifications
                                                @if($unreadCount > 0)
                                                <span class="fs-8 opacity-75 ps-3">{{ $unreadCount }} unread</span>
                                                @endif
                                            </h3>
                                        </div>
                                        <!--end::Header-->

                                        <!--begin::Body-->
                                        <div class="scroll-y mh-325px my-5 px-8">
                                            @forelse($unreadNotifications as $n)
                                            @php $d = $n->data; @endphp
                                            <div class="d-flex flex-stack py-4 notification-item" data-id="{{ $n->id }}">
                                                <div class="d-flex align-items-center me-2">
                                                    <div class="symbol symbol-35px me-3">
                                                        <span class="symbol-label bg-light-primary">
                                                            <i class="ki-duotone ki-abstract-28 fs-2 text-primary">
                                                                <span class="path1"></span><span class="path2"></span>
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="mb-0 me-2">
                                                        <a href="{{ $d['url'] ?? '#' }}"
                                                           class="fs-6 text-gray-800 text-hover-primary fw-bolder notification-link"
                                                           data-id="{{ $n->id }}">
                                                            {{ Str::limit($d['message'] ?? '', 60) }}
                                                        </a>
                                                        <div class="text-gray-400 fs-7">
                                                            {{ $d['issue_public_id'] ?? '' }}
                                                            &middot;
                                                            {{ $n->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge badge-light fs-8 fw-bold">New</span>
                                            </div>
                                            <div class="separator separator-dashed"></div>
                                            @empty
                                            <div class="py-10 text-center text-gray-500 fs-7">
                                                You're all caught up!
                                            </div>
                                            @endforelse
                                        </div>
                                        <!--end::Body-->

                                        <!--begin::Footer-->
                                        @if($unreadCount > 0)
                                        <div class="py-3 text-center border-top" id="notif-footer">
                                            <button type="button" id="btn-mark-all-read"
                                                class="btn btn-color-gray-600 btn-active-color-primary">
                                                Mark all as read
                                            </button>
                                        </div>
                                        @endif
                                        <!--end::Footer-->
                                    </div>
                                    <!--end::Notification dropdown-->
                                </div>
                            </div>
                            <!--end::Notifications-->

                            {{-- Chat button removed — real-time chat planned for future --}}

                            <!--begin::Theme mode-->
                            <div class="d-flex align-items-center ms-3 ms-lg-4">

                                <!--begin::Menu toggle-->
                                <a href="#"
                                    class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px"
                                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-night-day theme-light-show fs-1"><span
                                            class="path1"></span><span class="path2"></span><span
                                            class="path3"></span><span class="path4"></span><span
                                            class="path5"></span><span class="path6"></span><span
                                            class="path7"></span><span class="path8"></span><span
                                            class="path9"></span><span class="path10"></span></i> <i
                                        class="ki-duotone ki-moon theme-dark-show fs-1"><span class="path1"></span><span
                                            class="path2"></span></i></a>
                                <!--begin::Menu toggle-->

                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="light">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-night-day fs-2"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span><span class="path5"></span><span
                                                        class="path6"></span><span class="path7"></span><span
                                                        class="path8"></span><span class="path9"></span><span
                                                        class="path10"></span></i> </span>
                                            <span class="menu-title">
                                                Light
                                            </span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->

                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="dark">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-moon fs-2"><span class="path1"></span><span
                                                        class="path2"></span></i> </span>
                                            <span class="menu-title">
                                                Dark
                                            </span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->

                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="system">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-screen fs-2"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span></i> </span>
                                            <span class="menu-title">
                                                System
                                            </span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->

                            </div>
                            <!--end::Theme mode-->

                        </div>
                        <!--end::Topbar-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
                <!--begin::Content-->

                {{-- T&C acceptance banner for existing tenants who haven't yet accepted --}}
                @if(auth()->check() && auth()->user()->hasRole('admin') && tenancy()->tenant && ! tenancy()->tenant->terms_accepted_at)
                <div class="alert alert-warning d-flex align-items-center gap-4 mx-6 mt-4 mb-0 rounded-2 border border-warning-subtle" role="alert" style="background:#fffbeb;">
                    <i class="ki-duotone ki-shield-cross fs-2x text-warning"><span class="path1"></span><span class="path2"></span></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-gray-900 mb-1">Action Required: Please accept our Terms &amp; Conditions</div>
                        <div class="text-muted fs-7">To keep using {{ config('app.name') }}, your school must accept the Service Agreement. This takes less than a minute.</div>
                    </div>
                    <a href="{{ route('tenant.admin.terms') }}" class="btn btn-warning btn-sm text-nowrap">
                        Review &amp; Accept &rarr;
                    </a>
                </div>
                @endif

                @yield('content')
                <!--end::Content-->

                <!--begin::Footer-->
                <div class="footer py-4 d-flex flex-lg-column mt-auto" id="kt_footer">
                    <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                        <span class="text-muted fw-semibold fs-7">
                            &copy; {{ date('Y') }}
                            <span class="text-gray-700 fw-bold ms-1">ElifLammeem</span>
                        </span>
                        <span class="text-muted fw-semibold fs-7">School Issue Tracking Platform</span>
                    </div>
                </div>
                <!--end::Footer-->

            </div>
            <!--end::Wrapper-->

        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->
    <!--end::Main-->




    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up"><span class="path1"></span><span class="path2"></span></i>
    </div>
    <!--end::Scrolltop-->

    <!--begin::Javascript-->


    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{asset('theme/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{asset('theme/js/scripts.bundle.js')}}"></script>
    <!--end::Global Javascript Bundle-->

    <!--begin::Vendors Javascript(used for this page only)-->
    <script src="{{asset('theme/plugins/custom/datatables/datatables.bundle.js')}}"></script>
    <script src="{{asset('theme/plugins/custom/vis-timeline/vis-timeline.bundle.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/index.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/xy.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/percent.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/radar.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/themes/Animated.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/map.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/geodata/worldLow.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/geodata/continentsLow.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/geodata/usaLow.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js')}}"></script>
    <script src="{{asset('theme/cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js')}}"></script>
    <!--end::Vendors Javascript-->

    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{asset('theme/js/widgets.bundle.js')}}"></script>
    <script src="{{asset('theme/js/custom/widgets.js')}}"></script>
    <script src="{{asset('theme/js/custom/apps/chat/chat.js')}}"></script>
    <script src="{{asset('theme/js/custom/utilities/modals/upgrade-plan.js')}}"></script>
    <script src="{{asset('theme/js/custom/utilities/modals/users-search.js')}}"></script>
    <!--end::Custom Javascript-->
    @stack('scripts')
    <script>
    // Fallback for pages that don't define replayTour() — redirect to dashboard to start the tour there
    if (typeof replayTour === 'undefined') {
        window.replayTour = function () {
            window.location.href = '{{ url("admin") }}?tour=1';
        };
    }
    </script>

    {{-- Notification: mark as read on link click --}}
    <script>
    document.querySelectorAll('.notification-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var id = this.dataset.id;
            var href = this.href;
            e.preventDefault();
            fetch('{{ url("admin/notifications") }}/' + id + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).finally(function() {
                window.location.href = href;
            });
        });
    });

    {{-- Notification: mark all as read --}}
    var btnMarkAll = document.getElementById('btn-mark-all-read');
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', function() {
            fetch('{{ route("tenant.admin.notifications.read-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).finally(function() {
                window.location.reload();
            });
        });
    }
    </script>
    <!--end::Javascript-->

    {{-- ───── Support Ticket Floating Button & Modal (admin only) ───── --}}
    @if(auth()->user()?->hasRole('admin'))
    <style>
        #support-fab {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9000;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 0 20px 0 14px;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(79,70,229,.40);
            transition: background .2s, box-shadow .2s, transform .15s;
        }
        #support-fab:hover {
            background: #4338ca;
            box-shadow: 0 6px 24px rgba(79,70,229,.55);
            transform: translateY(-2px);
        }
        #support-fab svg { flex-shrink: 0; }
        #support-modal .modal-header { background: linear-gradient(135deg,#4f46e5,#6366f1); color:#fff; border-radius: .75rem .75rem 0 0; }
        #support-modal .modal-header .btn-close { filter: invert(1) grayscale(1); }
        #support-modal .modal-content { border-radius: .75rem; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.18); }
        #support-ticket-form .form-label { font-weight: 600; font-size: .82rem; color: #374151; margin-bottom: 4px; }
        #support-form-success { display:none; text-align:center; padding: 2rem 1rem; }
        #support-form-success svg { color: #22c55e; }
    </style>

    <!-- Floating Button -->
    <button id="support-fab" data-bs-toggle="modal" data-bs-target="#support-modal" title="Get help from the platform team">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Support
    </button>

    <!-- Modal -->
    <div class="modal fade" id="support-modal" tabindex="-1" aria-labelledby="support-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div style="flex:1;min-width:0;">
                        <h5 class="modal-title mb-0" id="support-modal-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:7px;">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Contact Platform Support
                        </h5>
                        <p class="mb-0 mt-1" style="font-size:.8rem;opacity:.8;">Your message goes directly to the Eliflameem team — usually responded to within a few hours.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                        <a href="{{ route('tenant.admin.support-tickets.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);font-size:.78rem;padding:.3rem .75rem;white-space:nowrap;" title="View all my tickets">
                            My Tickets
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">

                    <!-- Success state -->
                    <div id="support-form-success">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <h5 class="mt-3 mb-1" style="color:#15803d;">Ticket Submitted!</h5>
                        <p class="text-muted" style="font-size:.9rem;">We've received your message and will get back to you by email.</p>
                        <div class="d-flex gap-2 justify-content-center mt-3">
                            <button class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                            <a href="{{ route('tenant.admin.support-tickets.index') }}" class="btn btn-light-primary btn-sm">View my tickets →</a>
                        </div>
                    </div>

                    <!-- Form -->
                    <form id="support-ticket-form" novalidate>
                        <div id="support-form-error" class="alert alert-danger py-2 px-3 mb-3" style="display:none;font-size:.85rem;"></div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="question" selected>Question</option>
                                    <option value="bug">Bug / Error</option>
                                    <option value="billing">Billing</option>
                                    <option value="feature_request">Feature Request</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select form-select-sm" required>
                                    <option value="low">Low — general enquiry</option>
                                    <option value="medium" selected>Medium — needs attention</option>
                                    <option value="high">High — impacting work</option>
                                    <option value="urgent">Urgent — system down / data issue</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control form-control-sm" placeholder="Brief description of your issue" required minlength="5" maxlength="150">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control form-control-sm" rows="5" placeholder="Please describe your issue in as much detail as possible — steps to reproduce, screenshots if relevant, and any error messages you see." required minlength="10" maxlength="3000"></textarea>
                                <div class="text-end text-muted mt-1" style="font-size:.75rem;">
                                    <span id="support-msg-count">0</span> / 3000
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <span class="text-muted" style="font-size:.78rem;">Submitted as: <strong>{{ auth()->user()->name ?? '' }}</strong></span>
                            <button type="submit" class="btn btn-primary btn-sm px-4" id="support-submit-btn">
                                <span id="support-btn-text">Send Ticket</span>
                                <span id="support-btn-spinner" class="spinner-border spinner-border-sm ms-2" style="display:none;" role="status"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var form      = document.getElementById('support-ticket-form');
        var errBox    = document.getElementById('support-form-error');
        var success   = document.getElementById('support-form-success');
        var btnText   = document.getElementById('support-btn-text');
        var btnSpinner= document.getElementById('support-btn-spinner');
        var submitBtn = document.getElementById('support-submit-btn');
        var msgArea   = form ? form.querySelector('[name="message"]') : null;
        var msgCount  = document.getElementById('support-msg-count');

        if (msgArea && msgCount) {
            msgArea.addEventListener('input', function () {
                msgCount.textContent = this.value.length;
            });
        }

        // Reset form when modal is closed
        var modal = document.getElementById('support-modal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                form.reset();
                errBox.style.display = 'none';
                success.style.display = 'none';
                form.style.display = '';
                if (msgCount) msgCount.textContent = '0';
                submitBtn.disabled = false;
                btnText.textContent = 'Send Ticket';
                btnSpinner.style.display = 'none';
            });
        }

        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errBox.style.display = 'none';

            var fd = new FormData(form);
            var payload = {};
            fd.forEach(function (v, k) { payload[k] = v; });

            // Basic client-side length guards
            if (!payload.subject || payload.subject.trim().length < 5) {
                showError('Subject must be at least 5 characters.'); return;
            }
            if (!payload.message || payload.message.trim().length < 10) {
                showError('Message must be at least 10 characters.'); return;
            }

            submitBtn.disabled = true;
            btnText.textContent = 'Sending…';
            btnSpinner.style.display = 'inline-block';

            fetch('{{ route("tenant.admin.support-ticket.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (result.ok) {
                    form.style.display = 'none';
                    success.style.display = 'block';
                } else {
                    var msg = 'Something went wrong. Please try again.';
                    if (result.data && result.data.errors) {
                        msg = Object.values(result.data.errors).flat().join(' ');
                    } else if (result.data && result.data.message) {
                        msg = result.data.message;
                    }
                    showError(msg);
                    submitBtn.disabled = false;
                    btnText.textContent = 'Send Ticket';
                    btnSpinner.style.display = 'none';
                }
            })
            .catch(function () {
                showError('Network error. Please check your connection and try again.');
                submitBtn.disabled = false;
                btnText.textContent = 'Send Ticket';
                btnSpinner.style.display = 'none';
            });
        });

        function showError(msg) {
            errBox.textContent = msg;
            errBox.style.display = 'block';
        }
    })();
    </script>
    @endif
    {{-- ───── End Support Ticket ───── --}}

</body>
<!--end::Body-->

</html>