{{-- Dashboard --}}
<div class="menu-item" id="tour-nav-dashboard">
    <a class="menu-link {{ request()->routeIs('tenant.admin.dashboard') ? 'active' : '' }}"
       href="{{ route('tenant.admin.dashboard') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-element-11 fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
            </i>
        </span>
        <span class="menu-title">Dashboard</span>
    </a>
</div>

{{-- Separator --}}
<div class="menu-item mt-2 mb-1">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Issues</span>
    </div>
</div>

{{-- All Issues (admin + branch_manager) --}}
@if(auth()->user()?->hasRole(['admin', 'branch_manager']))
<div class="menu-item" id="tour-nav-issues">
    <a class="menu-link {{ request()->routeIs('tenant.admin.issues.index') ? 'active' : '' }}"
       href="{{ route('tenant.admin.issues.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-burger-menu-2 fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
            </i>
        </span>
        <span class="menu-title">All Issues</span>
    </a>
</div>
@endif

{{-- My Issues (all roles) --}}
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.issues.assignedTome') ? 'active' : '' }}"
       href="{{ route('tenant.admin.issues.assignedTome') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-notepad fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
                <span class="path5"></span>
            </i>
        </span>
        <span class="menu-title">My Issues</span>
    </a>
</div>

{{-- Reports (admin + branch_manager) --}}
@if(auth()->user()?->hasRole(['admin', 'branch_manager']))
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.reports.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.reports.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-chart-simple fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
            </i>
        </span>
        <span class="menu-title">Reports</span>
    </a>
</div>
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.activity_log') ? 'active' : '' }}"
       href="{{ route('tenant.admin.activity_log') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-notepad-edit fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Activity Log</span>
    </a>
</div>
@endif

{{-- User Management (admin only) --}}
@if(auth()->user()?->hasRole('admin'))
<div class="menu-item mt-2 mb-1">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Management</span>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.announcements.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.announcements.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-notification-on fs-2">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                <span class="path4"></span><span class="path5"></span>
            </i>
        </span>
        <span class="menu-title">Announcements</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.kudos.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.kudos.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-heart fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Compliments</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.users.index') ? 'active' : '' }}"
       href="{{ route('tenant.admin.users.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-people fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
                <span class="path5"></span>
            </i>
        </span>
        <span class="menu-title">Users</span>
    </a>
</div>


<div class="menu-item mt-2 mb-1">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Contacts</span>
    </div>
</div>

<div class="menu-item" id="tour-nav-contacts">
    <a class="menu-link {{ request()->routeIs('tenant.admin.contacts.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.contacts.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-address-book fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span>
            </i>
        </span>
        <span class="menu-title">Roster Contacts</span>
    </a>
</div>

@php $sidebarPlan = \App\Services\PlanService::forCurrentTenant(); @endphp

{{-- Documents --}}
<div class="menu-item mt-2 mb-1">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Documents</span>
    </div>
</div>

@if($sidebarPlan->allows('document_library'))
<div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('tenant.admin.documents.*', 'tenant.admin.document_categories.*', 'tenant.admin.faqs.*') ? 'here show' : '' }}">
    <span class="menu-link">
        <span class="menu-icon">
            <i class="ki-duotone ki-folder fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Document Library</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion">
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('tenant.admin.documents.*') ? 'active' : '' }}"
               href="{{ route('tenant.admin.documents.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">All Documents</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('tenant.admin.document_categories.*') ? 'active' : '' }}"
               href="{{ route('tenant.admin.document_categories.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">Categories</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('tenant.admin.faqs.*') ? 'active' : '' }}"
               href="{{ route('tenant.admin.faqs.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">FAQs</span>
            </a>
        </div>
    </div>
</div>
@else
<div class="menu-item">
    <a class="menu-link opacity-50 pe-none" href="{{ route('tenant.admin.plan.index') }}"
       title="Upgrade to unlock Document Library">
        <span class="menu-icon">
            <i class="ki-duotone ki-folder fs-2"><span class="path1"></span><span class="path2"></span></i>
        </span>
        <span class="menu-title">Document Library</span>
        <span class="menu-badge">
            <i class="ki-solid ki-lock-2 fs-6 text-muted" title="Upgrade required"></i>
        </span>
    </a>
</div>
@endif

{{-- Chatbot Observability --}}
@if($sidebarPlan->allows('chatbot'))
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.chatbot.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.chatbot.logs') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-message-text-2 fs-2">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
        </span>
        <span class="menu-title">Chatbot Logs</span>
    </a>
</div>
@else
<div class="menu-item">
    <a class="menu-link opacity-50 pe-none" href="{{ route('tenant.admin.plan.index') }}"
       title="Upgrade to unlock Chatbot">
        <span class="menu-icon">
            <i class="ki-duotone ki-message-text-2 fs-2">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
        </span>
        <span class="menu-title">Chatbot Logs</span>
        <span class="menu-badge">
            <i class="ki-solid ki-lock-2 fs-6 text-muted"></i>
        </span>
    </a>
</div>
@endif

{{-- Bulk Notifications --}}
<div class="menu-item mt-2 mb-1">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Bulk Notifications</span>
    </div>
</div>

@if($sidebarPlan->allows('broadcasting'))
<div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('tenant.admin.contacts.broadcast*') ? 'here show' : '' }}">
    <span class="menu-link">
        <span class="menu-icon">
            <i class="ki-duotone ki-send fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Broadcasts</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion">
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('tenant.admin.contacts.broadcast') && !request()->routeIs('tenant.admin.contacts.broadcast.logs*') ? 'active' : '' }}"
               href="{{ route('tenant.admin.contacts.broadcast') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">Send Broadcast</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('tenant.admin.contacts.broadcast.logs*') ? 'active' : '' }}"
               href="{{ route('tenant.admin.contacts.broadcast.logs') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">Broadcast Logs</span>
            </a>
        </div>
    </div>
</div>
@else
<div class="menu-item">
    <a class="menu-link opacity-50 pe-none" href="{{ route('tenant.admin.plan.index') }}"
       title="Upgrade to unlock Bulk Notifications">
        <span class="menu-icon">
            <i class="ki-duotone ki-send fs-2"><span class="path1"></span><span class="path2"></span></i>
        </span>
        <span class="menu-title">Broadcasts</span>
        <span class="menu-badge">
            <i class="ki-solid ki-lock-2 fs-6 text-muted"></i>
        </span>
    </a>
</div>
@endif

{{-- Settings --}}
<div class="menu-item mt-2 mb-1" id="tour-nav-settings">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Settings</span>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.branches.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.branches.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-geolocation fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Branches</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.categories.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.categories.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-category fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
            </i>
        </span>
        <span class="menu-title">Issue Categories</span>
    </a>
</div>

@if(auth()->user()?->hasRole('admin'))
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.escalation_rules.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.escalation_rules.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-time fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Escalation Rules</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.issue_groups.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.issue_groups.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-abstract-26 fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Issue Groups</span>
    </a>
</div>
@endif

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.users.auto_assign') ? 'active' : '' }}"
       href="{{ route('tenant.admin.users.auto_assign') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-route fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Auto-assign Rules</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.settings.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.settings.edit') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-setting-2 fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">School Settings</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('tenant.admin.plan.*') ? 'active' : '' }}"
       href="{{ route('tenant.admin.plan.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-rocket fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Plan &amp; Features</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link" href="{{ route('tenant.manual') }}" target="_blank">
        <span class="menu-icon">
            <i class="ki-duotone ki-book-open fs-2">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span>
            </i>
        </span>
        <span class="menu-title">User Manual</span>
    </a>
</div>
@endif
