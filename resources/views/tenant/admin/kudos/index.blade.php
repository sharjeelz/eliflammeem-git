@extends('layouts.tenant_admin')
@section('page_title', 'Compliments')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Compliments</h1>
        </div>
        @endpush

        {{-- Filters --}}
        <div class="card mb-5">
            <div class="card-body py-4">
                <form method="GET" action="{{ route('tenant.admin.kudos.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted fw-semibold fs-7 mb-0">From</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                            class="form-control form-control-sm form-control-solid w-150px">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted fw-semibold fs-7 mb-0">To</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                            class="form-control form-control-sm form-control-solid w-150px">
                    </div>
                    @if(auth()->user()->hasRole('admin'))
                    <select name="branch_id" class="form-select form-select-sm form-select-solid w-160px">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select name="category_id" class="form-select form-select-sm form-select-solid w-180px">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.kudos.index') }}" class="btn btn-sm btn-light">Reset</a>
                    <span class="ms-auto text-muted fs-7">{{ $kudos->total() }} {{ Str::plural('compliment', $kudos->total()) }}</span>
                </form>
            </div>
        </div>

        @if($kudos->isEmpty())
        <div class="card">
            <div class="card-body text-center py-20">
                <i class="ki-duotone ki-heart fs-5x text-gray-300 mb-5">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="text-gray-600 fw-semibold fs-5 mb-2">No compliments yet</div>
                <div class="text-muted fs-7">Parents and teachers can share compliments from the public portal.</div>
            </div>
        </div>
        @else

        {{-- Cards grid --}}
        <div class="row g-5 mb-5">
            @foreach($kudos as $kudo)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body p-6">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-success fw-bold text-success fs-6">
                                        {{ strtoupper(substr($kudo->contact?->name ?? 'A', 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-gray-800 fs-6">{{ $kudo->contact?->name ?? 'Anonymous' }}</div>
                                    <div class="text-muted fs-8">
                                        @if($kudo->contact?->role)
                                            <span class="badge badge-light-primary fs-9 me-1">{{ ucfirst($kudo->contact->role) }}</span>
                                        @endif
                                        {{ $kudo->branch?->name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="text-muted fs-8">{{ $kudo->created_at->format('M d, Y') }}</span>
                                <span class="text-muted fs-9">{{ $kudo->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if($kudo->category)
                        <div class="mb-3">
                            <span class="badge badge-light-info fs-8">{{ $kudo->category->name }}</span>
                        </div>
                        @endif

                        <p class="text-gray-700 fs-6 mb-0" style="line-height:1.7">
                            <i class="ki-duotone ki-questionnaire-tablet fs-4 text-success me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            {{ $kudo->message }}
                        </p>
                    </div>
                    @if(auth()->user()->hasRole('admin'))
                    <div class="card-footer py-3 px-6 d-flex justify-content-end border-0">
                        <form method="POST" action="{{ route('tenant.admin.kudos.destroy', $kudo->id) }}"
                            onsubmit="return confirm('Delete this compliment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $kudos->links() }}
        </div>

        @endif
    </div>
</div>

@endsection
