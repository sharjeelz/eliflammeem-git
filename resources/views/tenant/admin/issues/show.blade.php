@section('page_title', 'Issue #' . ($issue->public_id ?? 'Detail'))

@push('styles')
<style>
.msg-delete-btn { opacity: 0; transition: opacity .15s; }
.msg-row:hover .msg-delete-btn { opacity: 1; }
</style>
@endpush

@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Issue Detail</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.issues.index') }}" class="text-muted text-hover-primary">Issues</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $issue->public_id }}</li>
            </ul>
        </div>
        @endpush

        @php
            $statusColor   = ['new'=>'primary','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'];
            $priorityColor = ['low'=>'success','medium'=>'info','high'=>'warning','urgent'=>'danger'];
            $sc = $statusColor[$issue->status]   ?? 'secondary';
            $pc = $priorityColor[$issue->priority] ?? 'secondary';

            // Pre-extract AI suggested category so Category card (rendered before AI block) can use it
            $aiResult     = $issue->aiAnalysis?->result ?? [];
            $suggestedCat = data_get($aiResult, 'suggested_category');
        @endphp

        <div class="row g-6">

            {{-- ══════════════════════════════════════════════
                 LEFT COLUMN — issue body + conversation
            ══════════════════════════════════════════════ --}}
            <div class="col-xl-7">

                {{-- Issue header card --}}
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="text-muted fs-7 fw-semibold font-monospace">{{ $issue->public_id }}</span>
                                    <span class="badge badge-light-{{ $sc }} fw-bold">{{ ucwords(str_replace('_',' ', $issue->status)) }}</span>
                                    <span class="badge badge-light-{{ $pc }} fw-bold">{{ ucfirst($issue->priority) }}</span>
                                    @if($issue->is_spam)
                                        <span class="badge badge-danger fw-bold">
                                            <i class="ki-duotone ki-shield-cross fs-8 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            Spam
                                        </span>
                                    @endif
                                    @if(($issue->submission_type ?? 'complaint') === 'suggestion')
                                        <span class="badge badge-light-info fw-bold">Suggestion</span>
                                    @endif
                                </div>
                                <h2 class="text-gray-900 fw-bold fs-2 mb-1">{{ $issue->title }}</h2>
                                <div class="d-flex flex-wrap gap-4 text-muted fs-7 mt-2">
                                    @if($issue->branch)
                                        <span><i class="ki-duotone ki-geolocation fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $issue->branch->name }}</span>
                                    @endif
                                    @if($issue->issueCategory?->name)
                                        <span><i class="ki-duotone ki-category fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>{{ $issue->issueCategory->name }}</span>
                                    @endif
                                    <span><i class="ki-duotone ki-clock fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $issue->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('tenant.admin.issues.index') }}" class="btn btn-sm btn-light">
                                <i class="ki-duotone ki-arrow-left fs-4"><span class="path1"></span><span class="path2"></span></i>
                                Back
                            </a>
                        </div>

                        @if($issue->attachments && count($issue->attachments))
                            <div class="separator my-5"></div>
                            <div class="fs-7 fw-semibold text-muted text-uppercase mb-3">Attachments</div>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($issue->attachments as $att)
                                    @php
                                        $attUrl  = $att->storage_url;
                                        $isImage = str_starts_with($att->mime ?? '', 'image/');
                                        $filename = basename($att->path);
                                        $ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
                                        $sizeLabel = $att->size ? round($att->size / 1024, 1) . ' KB' : '';
                                    @endphp

                                    @if($isImage)
                                        {{-- Image thumbnail --}}
                                        <a href="{{ $attUrl }}" target="_blank" rel="noopener"
                                           class="d-block border rounded overflow-hidden"
                                           style="width:110px;" title="{{ $filename }}">
                                            <img src="{{ $attUrl }}" alt="{{ $filename }}"
                                                 style="width:110px;height:90px;object-fit:cover;display:block;">
                                            <div class="px-2 py-1 bg-light text-truncate fs-8 text-muted" style="max-width:110px;">
                                                {{ $filename }}
                                            </div>
                                        </a>
                                    @else
                                        {{-- Non-image file --}}
                                        <a href="{{ $attUrl }}" target="_blank" rel="noopener" download
                                           class="d-flex flex-column align-items-center justify-content-center border rounded p-3 text-center text-hover-primary bg-hover-light"
                                           style="width:110px;min-height:90px;text-decoration:none;" title="{{ $filename }}">
                                            <i class="ki-duotone ki-file-down fs-2x text-primary mb-2">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            <span class="badge badge-light-primary fs-9 mb-1">{{ $ext }}</span>
                                            <span class="fs-8 text-muted text-truncate w-100">{{ $filename }}</span>
                                            @if($sizeLabel)
                                                <span class="fs-9 text-muted">{{ $sizeLabel }}</span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Meta bar (single line above conversation) ── --}}
                <div class="card mb-5">
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap gap-x-6 gap-y-2 align-items-center" style="gap:0.6rem 1.4rem;">
                            {{-- Contact --}}
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-8">Contact:</span>
                                @if($issue->is_anonymous)
                                    <span class="badge badge-light-secondary fs-9">
                                        <i class="ki-duotone ki-lock-3 fs-10 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Anonymous
                                    </span>
                                @elseif($issue->roasterContact)
                                    @can('manage-users')
                                    <a href="{{ route('tenant.admin.contacts.edit', $issue->roasterContact->id) }}" class="fw-semibold text-primary fs-8">{{ $issue->roasterContact->name }}</a>
                                    @else
                                    <span class="fw-semibold fs-8 text-gray-800">{{ $issue->roasterContact->name }}</span>
                                    @endcan
                                    @if($issue->roasterContact->role)
                                    @php $_rc = ['parent'=>'info','teacher'=>'primary','admin'=>'warning'][$issue->roasterContact->role] ?? 'secondary'; @endphp
                                    <span class="badge badge-light-{{ $_rc }} fs-9">{{ ucfirst($issue->roasterContact->role) }}</span>
                                    @endif
                                @else
                                    <span class="fs-8 text-gray-600">—</span>
                                @endif
                            </div>
                            {{-- Branch --}}
                            @if($issue->branch)
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-8">Branch:</span>
                                <span class="fw-semibold fs-8 text-gray-800">{{ $issue->branch->name }}</span>
                            </div>
                            @endif
                            {{-- SLA Due --}}
                            @if($issue->sla_due_at)
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-8">SLA:</span>
                                <span class="fw-semibold fs-8 {{ $issue->sla_due_at < now() && !in_array($issue->status, ['resolved','closed']) ? 'text-danger' : 'text-gray-800' }}">
                                    {{ $issue->sla_due_at->format('d M, H:i') }}
                                </span>
                            </div>
                            @endif
                            {{-- First response --}}
                            @if($issue->first_response_at)
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-8">1st Reply:</span>
                                <span class="fw-semibold fs-8 text-gray-800">{{ $issue->first_response_at->format('d M, H:i') }}</span>
                            </div>
                            @endif
                            {{-- Resolved --}}
                            @if($issue->resolved_at)
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-8">Resolved:</span>
                                <span class="fw-semibold fs-8 text-success">{{ $issue->resolved_at->format('d M, H:i') }}</span>
                            </div>
                            @endif
                            {{-- Last activity --}}
                            <div class="d-flex align-items-center gap-1 ms-auto">
                                <i class="ki-duotone ki-time fs-8 text-muted me-1"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-8 text-muted">{{ $issue->last_activity_at?->diffForHumans() ?? $issue->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conversation --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Conversation</h3>
                    </div>
                    <div class="card-body" id="chat-body" style="max-height:500px;overflow-y:auto;">
                        @php
                            $msgDisplayName = function($msg) {
                                if (!empty($msg->author->name)) return $msg->author->name;
                                if (!empty($msg->meta['actor_name'])) return $msg->meta['actor_name'];
                                return match($msg->sender) {
                                    'admin'   => 'Admin',
                                    'teacher' => 'Staff',
                                    'parent'  => 'Parent',
                                    'system'  => 'System',
                                    default   => ucfirst($msg->sender ?? 'Unknown'),
                                };
                            };

                            $msgRoleLabel = fn($msg) => $msg->sender_label;

                            $isMyMsg = fn($msg) => $msg->author_type === get_class(auth()->user())
                                                    && $msg->author_id === auth()->id();

                            $contactName = $issue->is_anonymous ? 'Anonymous' : ($issue->roasterContact->name ?? 'Contact');
                            $contactRole = $issue->is_anonymous ? 'Anonymous' : ucfirst($issue->roasterContact->role ?? 'Contact');
                            $contactInitial = $issue->is_anonymous ? '?' : strtoupper(substr($contactName, 0, 1));
                        @endphp

                        {{-- ── Original submission (always left / contact side) ── --}}
                        @if($issue->description)
                        <div class="d-flex mb-5">
                            <div class="symbol symbol-35px me-3 flex-shrink-0">
                                <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                    {{ $contactInitial }}
                                </div>
                            </div>
                            <div style="max-width:75%">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if(!$issue->is_anonymous && $issue->roasterContact && auth()->user()->can('manage-users'))
                                        <a href="{{ route('tenant.admin.contacts.edit', $issue->roasterContact->id) }}"
                                           class="fw-bold text-primary fs-7">{{ $contactName }}</a>
                                    @else
                                        <span class="fw-bold text-gray-800 fs-7">{{ $contactName }}</span>
                                    @endif
                                    @if($issue->is_anonymous)
                                        <span class="badge badge-light-secondary fs-8">
                                            <i class="ki-duotone ki-lock-3 fs-9 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            Anonymous
                                        </span>
                                    @else
                                        <span class="badge badge-light-primary fs-8">{{ $contactRole }}</span>
                                    @endif
                                    <span class="text-muted fs-8">{{ $issue->created_at->format('d M, H:i') }}</span>
                                </div>
                                <div class="p-4 rounded bg-light text-gray-700 fs-6 lh-lg">
                                    {{ $issue->description }}
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- ── Replies ── --}}
                        @foreach($issue->messages as $msg)
                            @php
                                $isMe       = $isMyMsg($msg);
                                $isInternal = $msg->is_internal;
                                $canDelete  = auth()->user()->hasRole('admin')
                                    || ($msg->author_type === get_class(auth()->user()) && $msg->author_id === auth()->id());
                            @endphp
                            <div class="d-flex mb-5 {{ $isMe ? 'justify-content-end' : '' }} msg-row">

                                {{-- Avatar left (others) --}}
                                @if(!$isMe)
                                    <div class="symbol symbol-35px me-3 flex-shrink-0">
                                        <div class="symbol-label fs-6 fw-bold {{ $isInternal ? 'bg-light-warning text-warning' : 'bg-light-primary text-primary' }}">
                                            {{ strtoupper(substr($msgDisplayName($msg), 0, 1)) }}
                                        </div>
                                    </div>
                                @endif

                                <div style="max-width:75%">
                                    <div class="d-flex align-items-center gap-2 mb-1 {{ $isMe ? 'justify-content-end' : '' }}">
                                        @if(!$isMe)
                                            @php
                                                $canManage = auth()->user()->can('manage-users');
                                                $senderUrl = null;
                                                if ($canManage && $msg->author_id) {
                                                    if ($msg->author_type === \App\Models\User::class) {
                                                        $senderUrl = route('tenant.admin.users.edit', $msg->author_id);
                                                    } elseif ($msg->author_type === \App\Models\RosterContact::class) {
                                                        $senderUrl = route('tenant.admin.contacts.edit', $msg->author_id);
                                                    }
                                                }
                                            @endphp
                                            @if($senderUrl)
                                                <a href="{{ $senderUrl }}" class="fw-bold text-primary fs-7">{{ $msgDisplayName($msg) }}</a>
                                            @else
                                                <span class="fw-bold text-gray-800 fs-7">{{ $msgDisplayName($msg) }}</span>
                                            @endif
                                            <span class="badge badge-light fs-8">{{ $msgRoleLabel($msg) }}</span>
                                        @endif
                                        @if($isInternal)
                                            <span class="badge badge-light-warning fs-8">
                                                <i class="ki-duotone ki-lock-3 fs-9"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                Internal
                                            </span>
                                        @endif
                                        <span class="text-muted fs-8">{{ $msg->created_at->format('d M, H:i') }}</span>
                                        @if($isMe)
                                            <span class="badge badge-light fs-8">You</span>
                                        @endif
                                        @if($canDelete)
                                            <form method="POST"
                                                  action="{{ route('tenant.admin.issue.message.delete', [$issue->id, $msg->id]) }}"
                                                  class="msg-delete-form"
                                                  onsubmit="return confirm('Delete this message? This action will be logged.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-icon btn-sm btn-active-light-danger msg-delete-btn"
                                                        title="Delete message">
                                                    <i class="ki-duotone ki-trash fs-5 text-danger">
                                                        <span class="path1"></span><span class="path2"></span>
                                                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                                    </i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="p-4 rounded fs-6 lh-lg
                                        @if($isMe)
                                            {{ $isInternal ? 'bg-warning bg-opacity-25 text-gray-800 border border-warning border-dashed' : 'bg-primary text-white' }}
                                        @else
                                            {{ $isInternal ? 'bg-warning bg-opacity-10 text-gray-700 border border-warning border-dashed' : 'bg-light text-gray-700' }}
                                        @endif">
                                        {{ $msg->message }}
                                    </div>
                                </div>

                                {{-- Avatar right (me) --}}
                                @if($isMe)
                                    <div class="symbol symbol-35px ms-3 flex-shrink-0">
                                        <div class="symbol-label fs-6 fw-bold {{ $isInternal ? 'bg-light-warning text-warning' : 'bg-primary text-white' }}">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif

                            </div>
                        @endforeach

                        @if(!$issue->description && $issue->messages->isEmpty())
                            <div class="text-center text-muted py-6">
                                <i class="ki-duotone ki-message-text-2 fs-2x mb-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div>No messages yet.</div>
                            </div>
                        @endif

                        @can('comment', $issue)
                            <div class="separator my-5"></div>
                            <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/comment') }}" id="comment-form">
                                @csrf

                                {{-- Internal / External toggle --}}
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" id="btn-internal"
                                            class="btn btn-sm btn-light-warning fw-bold active-toggle"
                                            onclick="setInternal(true)">
                                        <i class="ki-duotone ki-lock-3 fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        Internal Note
                                    </button>
                                    @if(!auth()->user()->hasRole('staff'))
                                    <button type="button" id="btn-external"
                                            class="btn btn-sm btn-light fw-bold"
                                            onclick="setInternal(false)">
                                        <i class="ki-duotone ki-message-text-2 fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        Reply to Contact
                                    </button>
                                    @endif
                                </div>

                                <input type="hidden" name="is_internal" id="is_internal_input" value="1">

                                <textarea name="message" id="comment-textarea"
                                          class="form-control form-control-solid mb-3" rows="3"
                                          placeholder="Internal note — not visible to contact..." required></textarea>

                                <button class="btn btn-primary">
                                    <i class="ki-duotone ki-send fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Send
                                </button>
                            </form>
                            <script>
                            function setInternal(internal) {
                                document.getElementById('is_internal_input').value = internal ? '1' : '0';
                                var textarea = document.getElementById('comment-textarea');
                                var btnInt   = document.getElementById('btn-internal');
                                var btnExt   = document.getElementById('btn-external');
                                if (internal) {
                                    btnInt.classList.add('btn-light-warning');
                                    btnInt.classList.remove('btn-light');
                                    btnExt.classList.add('btn-light');
                                    btnExt.classList.remove('btn-light-primary');
                                    textarea.placeholder = 'Internal note — not visible to contact...';
                                } else {
                                    btnExt.classList.add('btn-light-primary');
                                    btnExt.classList.remove('btn-light');
                                    btnInt.classList.add('btn-light');
                                    btnInt.classList.remove('btn-light-warning');
                                    textarea.placeholder = 'Reply to contact — they will see this...';
                                }
                            }
                            </script>
                        @endcan
                    </div>
                </div>

                {{-- Activity Timeline (admin only) --}}
                @if(auth()->user()->hasRole('admin'))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Activity Log</h3>
                    </div>
                    <div class="card-body" style="max-height:400px;overflow-y:auto;">
                        @php
                            $statusBadge   = ['new'=>'primary','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'];
                            $priorityBadge = ['low'=>'success','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                        @endphp

                        @forelse($issue->activities as $a)
                            @php
                                $actorName  = $a->actor->name ?? 'System';
                                $actorUrl   = $a->actor?->id ? route('tenant.admin.users.edit', $a->actor->id) : null;
                                $icon  = match($a->type) {
                                    'assigned'        => 'ki-people',
                                    'status_changed'  => 'ki-arrows-circle',
                                    'priority_changed'=> 'ki-flag',
                                    'commented'       => 'ki-message-text-2',
                                    'message_deleted' => 'ki-trash',
                                    'contact_moved'   => 'ki-transfer-horizontal',
                                    default           => 'ki-information-5',
                                };
                                $iconColor = match($a->type) {
                                    'assigned'        => 'primary',
                                    'status_changed'  => 'warning',
                                    'priority_changed'=> 'info',
                                    'commented'       => 'success',
                                    'message_deleted' => 'danger',
                                    'contact_moved'   => 'dark',
                                    default           => 'secondary',
                                };
                            @endphp

                            <div class="d-flex gap-4 mb-5">
                                <div class="symbol symbol-35px">
                                    <div class="symbol-label bg-light-{{ $iconColor }}">
                                        <i class="ki-duotone {{ $icon }} fs-3 text-{{ $iconColor }}">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    @switch($a->type)
                                        @case('status_changed')
                                            @php
                                                $from        = data_get($a->data,'from');
                                                $to          = data_get($a->data,'to');
                                                $byContact   = data_get($a->data,'by_contact', false);
                                                $statusActor = $byContact
                                                    ? (data_get($a->data,'contact_name') ?? 'Contact')
                                                    : $actorName;
                                            @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if(!$byContact && $actorUrl)
                                                    <a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $statusActor }}</a>
                                                @else
                                                    <span class="fw-bold">{{ $statusActor }}</span>
                                                @endif
                                                @if($byContact)
                                                    <span class="badge badge-light-secondary fs-9 me-1">{{ ucfirst(data_get($a->data,'contact_role','contact')) }}</span>
                                                @endif
                                                changed status
                                                <span class="badge badge-light-{{ $statusBadge[$from] ?? 'secondary' }} ms-1">{{ ucwords(str_replace('_',' ',$from)) }}</span>
                                                <i class="ki-duotone ki-arrow-right fs-6 mx-1"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="badge badge-light-{{ $statusBadge[$to] ?? 'secondary' }}">{{ ucwords(str_replace('_',' ',$to)) }}</span>
                                                @if(data_get($a->data,'note'))
                                                    <div class="text-muted fs-7 mt-1">{{ data_get($a->data,'note') }}</div>
                                                @endif
                                            </div>
                                        @break

                                        @case('assigned')
                                            @php
                                                $fromId   = data_get($a->data,'from');
                                                $toId     = data_get($a->data,'to');
                                                $fromUser = $fromId ? \App\Models\User::find($fromId) : null;
                                                $toUser   = $toId   ? \App\Models\User::find($toId)   : null;
                                            @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)
                                                    <a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>
                                                @else
                                                    <span class="fw-bold">{{ $actorName }}</span>
                                                @endif
                                                @if($toUser)
                                                    reassigned issue
                                                    @if($fromUser)
                                                        from <a href="{{ route('tenant.admin.users.edit', $fromUser->id) }}"
                                                               class="fw-semibold text-primary">{{ $fromUser->name }}</a>
                                                    @endif
                                                    to <a href="{{ route('tenant.admin.users.edit', $toUser->id) }}"
                                                           class="fw-semibold text-primary">{{ $toUser->name }}</a>
                                                @else
                                                    unassigned issue
                                                    @if($fromUser)
                                                        from <a href="{{ route('tenant.admin.users.edit', $fromUser->id) }}"
                                                               class="fw-semibold text-primary">{{ $fromUser->name }}</a>
                                                    @endif
                                                @endif
                                            </div>
                                        @break

                                        @case('commented')
                                            @php
                                                $commentActor = data_get($a->data, 'by_contact')
                                                    ? (data_get($a->data, 'contact_name') ?? 'Contact')
                                                    : $actorName;
                                                $commentUrl = data_get($a->data, 'by_contact') ? null : $actorUrl;
                                            @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if($commentUrl)<a href="{{ $commentUrl }}" class="fw-bold text-primary">{{ $commentActor }}</a>@else<span class="fw-bold">{{ $commentActor }}</span>@endif added a comment
                                            </div>
                                        @break

                                        @case('message_deleted')
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif deleted
                                                @if(data_get($a->data, 'author_name'))
                                                    <span class="fw-semibold">{{ data_get($a->data, 'author_name') }}</span>'s
                                                @else
                                                    a
                                                @endif
                                                message
                                            </div>
                                            @if(data_get($a->data, 'preview'))
                                                <div class="text-muted fs-8 mt-1 fst-italic">
                                                    "{{ data_get($a->data, 'preview') }}"
                                                </div>
                                            @endif
                                        @break

                                        @case('priority_changed')
                                            @php $from = data_get($a->data,'from'); $to = data_get($a->data,'to'); @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif changed priority
                                                <span class="badge badge-light-{{ $priorityBadge[$from] ?? 'secondary' }} ms-1">{{ ucfirst($from) }}</span>
                                                <i class="ki-duotone ki-arrow-right fs-6 mx-1"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="badge badge-light-{{ $priorityBadge[$to] ?? 'secondary' }}">{{ ucfirst($to) }}</span>
                                            </div>
                                        @break

                                        @case('category_changed')
                                            @php $from = data_get($a->data,'from'); $to = data_get($a->data,'to'); @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif changed category
                                                <span class="badge badge-light-secondary ms-1">{{ $from }}</span>
                                                <i class="ki-duotone ki-arrow-right fs-6 mx-1"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="badge badge-light-primary">{{ $to }}</span>
                                            </div>
                                        @break

                                        @case('type_changed')
                                            @php $from = data_get($a->data,'from'); $to = data_get($a->data,'to'); @endphp
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif changed type
                                                <span class="badge badge-light-secondary ms-1">{{ ucfirst($from) }}</span>
                                                <i class="ki-duotone ki-arrow-right fs-6 mx-1"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="badge badge-light-info">{{ ucfirst($to) }}</span>
                                            </div>
                                        @break

                                        @case('contact_moved')
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif moved contact
                                                <span class="fw-semibold">{{ data_get($a->data,'contact_name') }}</span>
                                                @if(data_get($a->data,'from_branch'))
                                                    from <span class="badge badge-light-secondary">{{ data_get($a->data,'from_branch') }}</span>
                                                @endif
                                                to <span class="badge badge-light-primary">{{ data_get($a->data,'to_branch') }}</span>
                                            </div>
                                        @break

                                        @default
                                            <div class="text-gray-800 fs-6">
                                                @if($actorUrl)<a href="{{ $actorUrl }}" class="fw-bold text-primary">{{ $actorName }}</a>@else<span class="fw-bold">{{ $actorName }}</span>@endif · <code class="fs-8">{{ $a->type }}</code>
                                            </div>
                                    @endswitch
                                    <div class="text-muted fs-8 mt-1">{{ $a->created_at?->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">No activity yet.</div>
                        @endforelse
                    </div>
                </div>
                @endif

            </div>

            {{-- ══════════════════════════════════════════════
                 RIGHT SIDEBAR — actions + meta
            ══════════════════════════════════════════════ --}}
            <div class="col-xl-5">

                {{-- ── Status + Priority (side by side) ── --}}
                <div class="row g-3 mb-5">
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Status</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="mb-3">
                                    <span class="badge badge-light-{{ $sc }} fs-8 fw-semibold px-3 py-2">
                                        {{ ucwords(str_replace('_',' ', $issue->status)) }}
                                    </span>
                                </div>
                                @if(!empty($allowedTransitions))
                                    <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/status') }}" id="statusForm">
                                        @csrf
                                        <select name="status" id="statusSelect" class="form-select form-select-solid form-select-sm mb-2"
                                                onchange="document.getElementById('closeNoteWrap').style.display = this.value === 'closed' ? 'block' : 'none'">
                                            @foreach($allowedTransitions as $next)
                                                <option value="{{ $next }}">{{ ucwords(str_replace('_',' ',$next)) }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Close note — shown only when "Closed" is selected --}}
                                        <div id="closeNoteWrap" style="display:none" class="mb-2">
                                            <textarea name="close_note" rows="3"
                                                      class="form-control form-control-sm @error('close_note') is-invalid @enderror"
                                                      placeholder="How was this issue resolved? (required)">{{ old('close_note') }}</textarea>
                                            @error('close_note')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button class="btn btn-sm btn-warning w-100">Update</button>
                                    </form>
                                    @if($issue->close_note)
                                        <div class="mt-3 p-3 bg-light rounded border border-dashed">
                                            <div class="text-muted fs-8 fw-semibold mb-1">Resolution Note</div>
                                            <div class="fs-7">{{ $issue->close_note }}</div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted fs-8">No transitions.</div>
                                    @if($issue->close_note)
                                        <div class="mt-3 p-3 bg-light rounded border border-dashed">
                                            <div class="text-muted fs-8 fw-semibold mb-1">Resolution Note</div>
                                            <div class="fs-7">{{ $issue->close_note }}</div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Priority</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="mb-3">
                                    <span class="badge badge-light-{{ $pc }} fs-8 fw-semibold px-3 py-2">
                                        {{ ucfirst($issue->priority) }}
                                    </span>
                                </div>
                                @can('updatePriority', $issue)
                                    <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/priority') }}">
                                        @csrf
                                        <select name="priority" class="form-select form-select-solid form-select-sm mb-2">
                                            @foreach(['low','medium','high','urgent'] as $p)
                                                <option value="{{ $p }}" @selected($issue->priority === $p)>{{ ucfirst($p) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-light-primary w-100">Update</button>
                                    </form>
                                @else
                                    <div class="text-muted fs-8">{{ ucfirst($issue->priority) }}</div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Submission Type + Category (side by side) ── --}}
                @can('updatePriority', $issue)
                <div class="row g-3 mb-5">
                    <div class="col-6">
                        @php
                            $typeColor = match($issue->submission_type ?? 'complaint') {
                                'suggestion' => 'info',
                                'compliment' => 'success',
                                default      => 'warning',
                            };
                        @endphp
                        <div class="card h-100">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Type</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="mb-3">
                                    <span class="badge badge-light-{{ $typeColor }} fs-8 fw-semibold px-3 py-2">
                                        {{ ucfirst($issue->submission_type ?? 'complaint') }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/submission-type') }}">
                                    @csrf
                                    <select name="submission_type" class="form-select form-select-solid form-select-sm mb-2">
                                        @foreach(['complaint','suggestion','compliment'] as $t)
                                            <option value="{{ $t }}" @selected(($issue->submission_type ?? 'complaint') === $t)>{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-light-{{ $typeColor }} w-100">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        @php
                            $suggestedCatForCard = isset($suggestedCat) && $suggestedCat
                                ? $categories->first(fn ($c) => strcasecmp($c->name, $suggestedCat) === 0)
                                : null;
                        @endphp
                        <div class="card h-100">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Category</h3>
                                @if($suggestedCatForCard && $suggestedCatForCard->id !== $issue->issue_category_id)
                                    <div class="card-toolbar">
                                        <span class="badge badge-light-primary fs-9" title="AI suggestion">AI ✦</span>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body py-3">
                                <div class="mb-3">
                                    <span class="badge badge-light-secondary fs-8 fw-semibold px-3 py-2">
                                        {{ $issue->issueCategory?->name ?? '—' }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/category') }}">
                                    @csrf
                                    <select name="issue_category_id" class="form-select form-select-solid form-select-sm mb-2">
                                        <option value="">— None —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected($issue->issue_category_id === $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-light-primary w-100">Update</button>
                                </form>
                                @if($suggestedCatForCard && $suggestedCatForCard->id !== $issue->issue_category_id)
                                    <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/category') }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="issue_category_id" value="{{ $suggestedCatForCard->id }}">
                                        <button type="submit" class="btn btn-sm btn-light-info w-100 py-2">
                                            <i class="ki-duotone ki-abstract-26 fs-9 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            Apply: {{ $suggestedCat }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- ── Spam + Assignment (side by side) ── --}}
                <div class="row g-3 mb-5">
                    {{-- Spam --}}
                    <div class="col-6">
                        <div class="card h-100 {{ $issue->is_spam ? 'border border-danger' : '' }}">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Spam</h3>
                                @if($issue->is_spam)
                                    <div class="card-toolbar"><span class="badge badge-danger fs-9">Flagged</span></div>
                                @endif
                            </div>
                            <div class="card-body py-3">
                                @if($issue->is_spam)
                                    <div class="text-danger fs-8 fw-semibold mb-2">Marked as Spam</div>
                                    @if($issue->spam_reason)
                                        <div class="text-gray-600 fs-9 mb-3 lh-lg">{{ Str::limit($issue->spam_reason, 70) }}</div>
                                    @endif
                                    @if(auth()->user()->hasRole(['admin', 'branch_manager']))
                                        <form method="POST" action="{{ route('tenant.admin.issue.spam.unmark', $issue->id) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger w-100"
                                                    onclick="return confirm('Remove spam flag?')">
                                                <i class="ki-duotone ki-shield-tick fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                Not Spam
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <div class="text-muted fs-8 mb-3">Not flagged.</div>
                                    @if(auth()->user()->hasRole(['admin', 'branch_manager']))
                                        <button type="button" class="btn btn-sm btn-light-danger w-100"
                                                data-bs-toggle="modal" data-bs-target="#markSpamModal">
                                            <i class="ki-duotone ki-shield-cross fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            Mark Spam
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Assignment --}}
                    @can('assign', $issue)
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-header py-3 min-h-auto">
                                <h3 class="card-title fw-bold fs-7">Assigned To</h3>
                            </div>
                            <div class="card-body py-3">
                                @if($issue->assignedTo->name ?? null)
                                    @php
                                        $assignedTo   = $issue->assignedTo;
                                        $assigneeRole = $assignedTo->hasRole('branch_manager') ? 'BM' : 'Staff';
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="symbol symbol-30px flex-shrink-0">
                                            <div class="symbol-label bg-light-success text-success fw-bold fs-7">
                                                {{ strtoupper(substr($assignedTo->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            @can('manage-users')
                                            <a href="{{ route('tenant.admin.users.edit', $assignedTo->id) }}"
                                               class="fw-bold text-primary fs-8 d-block text-truncate" style="max-width:90px;">{{ $assignedTo->name }}</a>
                                            @else
                                            <div class="fw-bold text-gray-800 fs-8 text-truncate" style="max-width:90px;">{{ $assignedTo->name }}</div>
                                            @endcan
                                            <span class="badge badge-light-{{ $assigneeRole === 'BM' ? 'warning' : 'info' }} fs-9">{{ $assigneeRole }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted fs-8 mb-3">Not assigned.</div>
                                @endif
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-primary flex-grow-1"
                                            data-bs-toggle="modal" data-bs-target="#assignModal">
                                        <i class="ki-duotone ki-people fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        {{ $issue->assigned_user_id ? 'Reassign' : 'Assign' }}
                                    </button>
                                    @if($issue->assigned_user_id && auth()->user()->hasRole('admin'))
                                        <button type="button" class="btn btn-sm btn-light-danger"
                                                data-bs-toggle="modal" data-bs-target="#unassignModal" title="Unassign">
                                            <i class="ki-duotone ki-disconnect fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>

                {{-- AI Analysis --}}
                <div class="card mb-5">
                    <div class="card-header">
                        <h3 class="card-title fw-bold d-flex align-items-center gap-2">
                            <span class="d-flex align-items-center justify-content-center rounded"
                                  style="width:28px;height:28px;background:linear-gradient(135deg,#6366f1,#8b5cf6);flex-shrink:0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" fill="white"/>
                                </svg>
                            </span>
                            AI Analysis
                        </h3>
                        @if($ai ?? null)
                            <div class="card-toolbar">
                                <span class="badge fs-9 text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                    {{ strtoupper($ai->model_version ?? 'AI') }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $ai             = $issue->aiAnalysis;
                            $result         = $ai?->result ?? [];
                            $isFull         = ($ai?->analysis_type === 'full');
                            $label          = data_get($result, 'sentiment') ?? data_get($result, 'label');
                            $score          = $ai?->confidence ?? data_get($result, 'sentiment_score') ?? data_get($result, 'score');
                            $summary        = data_get($result, 'admin_summary') ?? data_get($result, 'summary') ?? data_get($result, 'explanation');
                            $urgencyFlag    = data_get($result, 'urgency_flag');
                            $urgencyVal     = data_get($result, 'urgency');
                            $themes             = data_get($result, 'themes', []);
                            $parentTone         = data_get($result, 'parent_tone');
                            $suggestedCat       = data_get($result, 'suggested_category');
                            $acknowledgment     = data_get($result, 'acknowledgment');
                            $acknowledgmentUr   = data_get($result, 'acknowledgment_ur');
                            $suggestedActions   = data_get($result, 'suggested_actions', []);
                            $suggestedActionsUr = data_get($result, 'suggested_actions_ur', []);
                            $hasUrdu            = !empty($suggestedActionsUr) || !empty($acknowledgmentUr);

                            $sentColor = match(strtolower((string) $label)) {
                                'positive' => 'success',
                                'negative' => 'danger',
                                'neutral'  => 'info',
                                default    => 'secondary',
                            };
                            $sentIcon = match(strtolower((string) $label)) {
                                'positive' => 'ki-like',
                                'negative' => 'ki-dislike',
                                'neutral'  => 'ki-minus-circle',
                                default    => 'ki-question',
                            };
                            $urgencyColor = match($urgencyFlag) {
                                'escalate' => 'danger',
                                'monitor'  => 'warning',
                                default    => 'secondary',
                            };
                            $toneColor = in_array($parentTone, ['distressed', 'frustrated']) ? 'warning' : 'info';
                        @endphp

                        @if($ai && $label)
                            {{-- Row 1: Sentiment + Urgency badges --}}
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-{{ $sentColor }}">
                                        <i class="ki-duotone {{ $sentIcon }} fs-2 text-{{ $sentColor }}">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-gray-800 fs-6">{{ ucfirst($label) }}</div>
                                    <div class="text-muted fs-8">Sentiment</div>
                                </div>
                                @if($score !== null)
                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        <span class="badge badge-light-{{ $sentColor }} fs-8 fw-bold">
                                            {{ max(0, min(100, round((float) $score * 100))) }}%
                                        </span>
                                        @if($urgencyFlag)
                                            <span class="badge badge-light-{{ $urgencyColor }} fs-8 fw-bold">
                                                @if($urgencyFlag === 'escalate')
                                                    <i class="ki-duotone ki-warning-2 fs-8 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                @elseif($urgencyFlag === 'monitor')
                                                    <i class="ki-duotone ki-eye fs-8 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                @endif
                                                {{ ucfirst($urgencyFlag) }}
                                                @if($urgencyVal) ({{ $urgencyVal }}/10) @endif
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Confidence bar --}}
                            @if($score !== null)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between fs-8 text-muted mb-1">
                                        <span>Confidence</span>
                                        <span>{{ max(0, min(100, round((float) $score * 100))) }}%</span>
                                    </div>
                                    <div class="h-6px bg-light rounded">
                                        <div class="bg-{{ $sentColor }} rounded h-6px"
                                             style="width: {{ max(0, min(100, round((float) $score * 100))) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Parent tone chip --}}
                            @if($parentTone)
                                <div class="mb-3">
                                    <span class="text-muted fs-8 me-2">Parent tone:</span>
                                    <span class="badge badge-light-{{ $toneColor }} fs-8">{{ ucfirst($parentTone) }}</span>
                                </div>
                            @endif

                            {{-- Theme pills --}}
                            @if(!empty($themes))
                                <div class="mb-3 d-flex flex-wrap gap-1 align-items-center">
                                    <span class="text-muted fs-8 me-1">Themes:</span>
                                    @foreach($themes as $theme)
                                        <span class="badge badge-light fs-8 fw-normal">{{ $theme }}</span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Suggested category --}}
                            @if($suggestedCat && $suggestedCat !== ($issue->issueCategory->name ?? ''))
                                <div class="mb-3">
                                    <span class="text-muted fs-8 me-1">Suggested category:</span>
                                    <span class="badge badge-light-primary fs-8">{{ $suggestedCat }}</span>
                                </div>
                            @endif

                            {{-- AI summary --}}
                            @if($summary)
                                <div class="separator mb-3"></div>
                                <div class="text-gray-700 fs-7 lh-lg">{{ $summary }}</div>
                            @endif

                            {{-- Positive sentiment notice --}}
                            @if(strtolower((string) $label) === 'positive')
                                <div class="alert alert-dismissible bg-light-success d-flex align-items-start gap-3 p-4 mb-3 rounded">
                                    <i class="ki-duotone ki-like fs-2x text-success mt-1">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-success mb-1">This looks like positive feedback</div>
                                        <div class="text-gray-700 fs-7">
                                            The AI detected a positive tone — this may be a
                                            @if(($issue->submission_type ?? '') === 'compliment')
                                                <strong>compliment</strong> submitted through the issue form instead of the compliments portal.
                                            @elseif(($issue->submission_type ?? '') === 'suggestion')
                                                <strong>constructive suggestion</strong> rather than a complaint.
                                            @else
                                                misrouted compliment or suggestion rather than a complaint.
                                            @endif
                                            Consider whether this needs resolution or just acknowledgment.
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            {{-- Suggested Actions + Draft Reply with EN/UR toggle --}}
                            @if(!empty($suggestedActions) || $acknowledgment)
                                <div class="separator mt-3 mb-2"></div>

                                {{-- Language toggle (only if Urdu data available) --}}
                                @if($hasUrdu)
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="text-muted fs-8">Lang:</span>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-light-primary py-1 px-3 fs-8 fw-bold ai-lang-btn active" data-lang="en">EN</button>
                                            <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-8 ai-lang-btn" data-lang="ur" style="font-family:serif;">اردو</button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Suggested Actions — English --}}
                                @if(!empty($suggestedActions))
                                    <div class="bg-light-warning rounded p-3 mb-3 ai-lang-en">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ki-duotone ki-abstract-26 fs-6 text-warning me-2">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            <span class="text-warning fw-bold fs-8">Suggested Actions</span>
                                        </div>
                                        <ol class="mb-0 ps-4">
                                            @foreach($suggestedActions as $action)
                                                <li class="text-gray-700 fs-7 lh-lg mb-2">{{ $action }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endif

                                {{-- Suggested Actions — Urdu --}}
                                @if(!empty($suggestedActionsUr))
                                    <div class="bg-light-warning rounded p-3 mb-3 ai-lang-ur" style="display:none;direction:rtl;text-align:right;">
                                        <div class="d-flex align-items-center mb-2 flex-row-reverse">
                                            <i class="ki-duotone ki-abstract-26 fs-6 text-warning ms-2">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            <span class="text-warning fw-bold fs-8">تجویز کردہ اقدامات</span>
                                        </div>
                                        <ol class="mb-0 pe-2 ps-0" style="direction:rtl;">
                                            @foreach($suggestedActionsUr as $action)
                                                <li class="text-gray-700 fs-7 lh-lg mb-2">{{ $action }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endif

                                {{-- Draft Reply — English --}}
                                @if($acknowledgment)
                                    <div class="bg-light-primary rounded p-3 ai-lang-en">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-primary fw-bold fs-8">
                                                <i class="ki-duotone ki-message-text-2 fs-7 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                Draft Reply
                                            </span>
                                            <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-9 draft-copy-btn">Copy</button>
                                        </div>
                                        <p class="draft-text text-gray-700 fs-7 mb-0 lh-lg">{{ $acknowledgment }}</p>
                                    </div>
                                @endif

                                {{-- Draft Reply — Urdu --}}
                                @if($acknowledgmentUr)
                                    <div class="bg-light-primary rounded p-3 ai-lang-ur" style="display:none;direction:rtl;text-align:right;">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-row-reverse">
                                            <span class="text-primary fw-bold fs-8" style="font-family:serif;">
                                                <i class="ki-duotone ki-message-text-2 fs-7 ms-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                مسودہ جواب
                                            </span>
                                            <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-9 draft-copy-btn">نقل کریں</button>
                                        </div>
                                        <p class="draft-text text-gray-700 fs-7 mb-0 lh-lg" style="font-family:serif;line-height:2;">{{ $acknowledgmentUr }}</p>
                                    </div>
                                @endif
                            @endif


                        @elseif($ai)
                            <div class="text-muted fs-7">Analysis data unavailable.</div>

                        @else
                            {{-- No analysis yet --}}
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-35px">
                                    <div class="symbol-label bg-light">
                                        <i class="ki-duotone ki-time fs-3 text-muted">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-700 fs-7 fw-semibold">Pending</div>
                                    <div class="text-muted fs-8">AI analysis runs in the background after submission.</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Assign Modal --}}
                @can('assign', $issue)
                <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header border-bottom">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0">Assign Issue</h5>
                                    <div class="text-muted fs-8">Select who should handle this issue</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0" style="max-height: 420px; overflow-y: auto;">
                                @forelse($staff as $u)
                                    @php $isCurrent = $issue->assigned_user_id === $u->id; @endphp
                                    <form method="POST" action="{{ url('/admin/issues/'.$issue->id.'/assign') }}">
                                        @csrf
                                        <input type="hidden" name="assigned_user_id" value="{{ $u->id }}">
                                        <button type="submit"
                                                class="w-100 border-0 text-start p-0 bg-transparent {{ $isCurrent ? 'bg-light-primary' : '' }}"
                                                {{ $isCurrent ? 'disabled' : '' }}
                                                title="{{ $isCurrent ? 'Already assigned' : 'Assign to '.$u->name }}">
                                            <div class="d-flex align-items-center gap-4 px-6 py-4 border-bottom bg-hover-light">
                                                <div class="symbol symbol-40px flex-shrink-0">
                                                    <div class="symbol-label fw-bold fs-5
                                                        {{ $isCurrent ? 'bg-primary text-white' : 'bg-light-primary text-primary' }}">
                                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold text-gray-800 fs-6">{{ $u->name }}</span>
                                                        @if($isCurrent)
                                                            <span class="badge badge-light-success fs-9">Current</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex gap-2 mt-1 flex-wrap">
                                                        <span class="badge badge-light-{{ $u->role_label === 'Branch Manager' ? 'warning' : 'info' }} fs-9">
                                                            {{ $u->role_label }}
                                                        </span>
                                                        <span class="badge badge-light-secondary fs-9">
                                                            <i class="ki-duotone ki-geolocation fs-9 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                            {{ $u->branch_names }}
                                                        </span>
                                                    </div>
                                                    @if($u->category_names->isNotEmpty())
                                                    <div class="d-flex gap-1 mt-1 flex-wrap">
                                                        @foreach($u->category_names as $catName)
                                                            <span class="badge badge-light-success fs-9">
                                                                <i class="ki-duotone ki-category fs-9 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                                {{ $catName }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                    @else
                                                    <div class="text-muted fs-9 mt-1">No categories assigned</div>
                                                    @endif
                                                </div>
                                                @if(!$isCurrent)
                                                    <i class="ki-duotone ki-arrow-right fs-4 text-primary flex-shrink-0"><span class="path1"></span><span class="path2"></span></i>
                                                @endif
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="text-muted text-center py-10 fs-6">No staff available to assign.</div>
                                @endforelse
                            </div>
                            <div class="modal-footer border-top py-3">
                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Private Note (hidden from everyone except the author) --}}
                @if(!auth()->user()->hasRole('admin'))
                <div class="card mb-5">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">
                            <i class="ki-duotone ki-notepad fs-4 text-warning me-2">
                                <span class="path1"></span><span class="path2"></span>
                                <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                            </i>
                            My Private Note
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-light-warning fs-8">Only visible to you</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.admin.issue.note.save', $issue->id) }}">
                            @csrf
                            <textarea name="content" rows="5"
                                      class="form-control form-control-solid mb-3"
                                      placeholder="Write your private notes here…">{{ old('content', $myNote?->content) }}</textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="ki-duotone ki-check fs-5"><span class="path1"></span><span class="path2"></span></i>
                                    Save Note
                                </button>
                                @if($myNote)
                                <form method="POST" action="{{ route('tenant.admin.issue.note.destroy', $issue->id) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('Clear your note?')">
                                        Clear
                                    </button>
                                </form>
                                @endif
                            </div>
                        </form>
                        @if($myNote)
                            <div class="text-muted fs-8 mt-3">
                                Last saved {{ $myNote->updated_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif


            </div>
        </div>
    </div>
</div>
{{-- Mark as Spam Modal --}}
@if(auth()->user()->hasRole(['admin', 'branch_manager']) && !$issue->is_spam)
<div class="modal fade" id="markSpamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Mark as Spam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('tenant.admin.issue.spam.mark', $issue->id) }}">
                @csrf
                <div class="modal-body pt-4">
                    @if($issue->assignedTo)
                    <div class="alert alert-warning d-flex align-items-center gap-3 py-3 mb-4">
                        <i class="ki-duotone ki-information-5 fs-2 text-warning flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="fs-7">
                            This issue is currently assigned to <strong>{{ $issue->assignedTo->name }}</strong>.
                            Marking it as spam will <strong>automatically unassign</strong> them.
                        </div>
                    </div>
                    @endif
                    <p class="text-muted fs-7 mb-4">
                        Provide a reason for marking this issue as spam. This will be visible to admins and branch managers.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Reason</label>
                        <textarea name="spam_reason" class="form-control form-control-solid" rows="3"
                                  maxlength="500" placeholder="e.g. Duplicate submission, abusive content, irrelevant…" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Confirm — Mark as Spam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($issue->assigned_user_id && auth()->user()->hasRole('admin'))
<div class="modal fade" id="unassignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Unassign Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <p class="text-gray-700 fs-6 mb-0">
                    Are you sure you want to unassign this issue from
                    <strong>{{ $issue->assignedTo?->name }}</strong>?
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('tenant.admin.issue.unassign', $issue->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">Unassign</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Scroll conversation to the latest message on page load
    const chatBody = document.getElementById('chat-body');
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

    // AI language toggle (EN / Urdu)
    document.querySelectorAll('.ai-lang-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var lang = this.dataset.lang;
            document.querySelectorAll('.ai-lang-btn').forEach(function (b) {
                b.classList.toggle('btn-light-primary', b.dataset.lang === lang);
                b.classList.toggle('btn-light', b.dataset.lang !== lang);
            });
            document.querySelectorAll('.ai-lang-en').forEach(function (el) { el.style.display = lang === 'en' ? '' : 'none'; });
            document.querySelectorAll('.ai-lang-ur').forEach(function (el) { el.style.display = lang === 'ur' ? '' : 'none'; });
        });
    });

    // Draft reply copy button — works on both HTTP and HTTPS
    document.querySelectorAll('.draft-copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const text = btn.closest('.bg-light-primary').querySelector('.draft-text').textContent.trim();
            const original = btn.textContent;

            function flash() {
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = original; }, 2000);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(flash);
            } else {
                // HTTP fallback via execCommand
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(ta);
                flash();
            }
        });
    });
</script>
@endpush
