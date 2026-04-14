@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Import Contacts</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-gray-900">Import</li>
            </ul>
        </div>
        @endpush

        @if(session('import_warnings'))
            <div class="alert alert-warning mb-5">
                <div class="fw-bold mb-2">Some rows were skipped:</div>
                <ul class="mb-0">
                    @foreach(session('import_warnings') as $warn)
                        <li>{{ $warn }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-5">

            {{-- Upload card --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upload File</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.admin.contacts.import.store') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5">
                                <label class="form-label required fw-bold">Excel or CSV file</label>

                                <label for="file-input" class="dropzone-wrapper border border-dashed border-gray-300 rounded p-8 text-center cursor-pointer position-relative d-block mb-0">
                                    <i class="ki-duotone ki-file-up fs-3x text-primary mb-3">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <div class="fw-bold text-gray-700 mb-1">Click to browse or drag & drop</div>
                                    <div class="text-muted fs-7">Accepted: .xlsx, .xls, .csv — max 5 MB</div>
                                    <div id="file-name" class="mt-3 fw-semibold text-primary d-none"></div>
                                    <input id="file-input" type="file" name="file" accept=".xlsx,.xls,.csv"
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer"
                                           onchange="document.getElementById('file-name').textContent = this.files[0]?.name ?? '';
                                                     document.getElementById('file-name').classList.toggle('d-none', !this.files[0])">
                                </label>

                                @error('file')
                                    <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-arrow-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    Import
                                </button>
                                <a href="{{ route('tenant.admin.contacts.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Instructions card --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Instructions</h3>
                    </div>
                    <div class="card-body fs-6">

                        <p class="text-muted mb-4">
                            Download the template, fill it in, and upload it back. The first row must be the header row exactly as shown.
                        </p>

                        <a href="{{ route('tenant.admin.contacts.template') }}" class="btn btn-light-primary w-100 mb-6">
                            <i class="ki-duotone ki-arrow-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                            Download CSV Template
                        </a>

                        <table class="table table-sm table-bordered fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th>Column</th>
                                    <th>Required</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>name</code></td>
                                    <td><span class="badge badge-light-danger">Yes</span></td>
                                    <td>Full name</td>
                                </tr>
                                <tr>
                                    <td><code>role</code></td>
                                    <td><span class="badge badge-light-danger">Yes</span></td>
                                    <td><code>parent</code>, <code>teacher</code>, or <code>admin</code></td>
                                </tr>
                                <tr>
                                    <td><code>email</code></td>
                                    <td><span class="badge badge-light-warning">Conditional</span></td>
                                    <td>At least one of email or phone is required. Duplicate emails are skipped.</td>
                                </tr>
                                <tr>
                                    <td><code>phone</code></td>
                                    <td><span class="badge badge-light-warning">Conditional</span></td>
                                    <td>At least one of email or phone is required.</td>
                                </tr>
                                <tr>
                                    <td><code>branch</code></td>
                                    <td><span class="badge badge-light-success">No</span></td>
                                    <td>Branch name or code (must match exactly)</td>
                                </tr>
                                <tr>
                                    <td><code>external_id</code></td>
                                    <td><span class="badge badge-light-primary">Recommended</span></td>
                                    <td>Student / staff ID from your SIS. Used for smart upsert matching.</td>
                                </tr>
                                <tr>
                                    <td><code>is_active_in_sis</code></td>
                                    <td><span class="badge badge-light-primary">Recommended</span></td>
                                    <td>
                                        <code>1</code> / <code>yes</code> / <code>true</code> → active (upsert)<br>
                                        <code>0</code> / <code>no</code> / <code>false</code> → deactivate &amp; revoke code<br>
                                        <span class="text-muted">Blank = treated as active</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="notice d-flex bg-light-primary rounded border border-primary p-4 mt-4">
                            <i class="ki-duotone ki-information fs-2tx text-primary me-4">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fs-7 text-gray-700">
                                Upload the same file every week. Active rows are upserted; rows marked <code>is_active_in_sis = 0</code> are automatically deactivated and their access codes revoked.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- API Integration docs — only shown when plan allows and at least one key exists --}}
        @if($planAllowApi)
        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-abstract-26 fs-4 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                            API Integration
                        </h3>
                        <div class="card-toolbar">
                            @if($activeApiKeys->isEmpty())
                                <a href="{{ route('tenant.admin.settings.edit') }}?tab=api"
                                   class="btn btn-sm btn-light-primary">
                                    <i class="ki-duotone ki-key fs-5"><span class="path1"></span><span class="path2"></span></i>
                                    Create API Key
                                </a>
                            @else
                                <span class="badge badge-light-success">
                                    {{ $activeApiKeys->count() }} active key(s)
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">

                        @if($activeApiKeys->isEmpty())
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-0">
                            <i class="ki-duotone ki-information-5 fs-2tx text-warning flex-shrink-0 me-4 mt-1">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fs-6 text-gray-700">
                                Your plan supports API access, but you haven't created an API key yet.
                                Go to <a href="{{ route('tenant.admin.settings.edit') }}" class="fw-bold">Settings → API Keys</a> to generate one.
                            </div>
                        </div>
                        @else

                        <p class="text-muted fs-6 mb-5">
                            Use the REST API to upsert contacts programmatically from your SIS or any external system.
                            Authentication uses your API key as a Bearer token.
                        </p>

                        <div class="row g-6">

                            {{-- Endpoint + Auth --}}
                            <div class="col-lg-6">
                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Endpoint</h5>
                                <div class="bg-gray-100 rounded p-4 font-monospace fs-7 mb-4">
                                    <span class="badge badge-primary me-2">POST</span>
                                    <span class="text-gray-700">https://{{ $tenantDomain }}/api/v1/contacts</span>
                                </div>

                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Headers</h5>
                                <div class="bg-gray-100 rounded p-4 font-monospace fs-7 mb-4">
                                    <div class="text-muted">Authorization: Bearer <span class="text-primary">&lt;your-api-key&gt;</span></div>
                                    <div class="text-muted">Content-Type: application/json</div>
                                </div>

                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Your Active Keys</h5>
                                <table class="table table-sm table-row-dashed fs-7">
                                    <thead>
                                        <tr class="fw-semibold text-muted text-uppercase">
                                            <th>Name</th>
                                            <th>Prefix</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeApiKeys as $key)
                                        <tr>
                                            <td class="fw-semibold">{{ $key->name }}</td>
                                            <td><code class="text-muted fs-8">{{ $key->key_prefix }}...</code></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="text-muted fs-8">
                                    Full key was shown once on creation. To rotate, revoke and create a new key in
                                    <a href="{{ route('tenant.admin.settings.edit') }}">Settings → API Keys</a>.
                                </div>
                            </div>

                            {{-- Request body + Responses --}}
                            <div class="col-lg-6">
                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Request Body (JSON)</h5>
                                <div class="bg-gray-100 rounded p-4 font-monospace fs-8 mb-4" style="white-space:pre-wrap">{
  "name":        "Jane Smith",       <span class="text-danger">// required</span>
  "role":        "parent",           <span class="text-danger">// required: parent|teacher</span>
  "email":       "jane@school.com",  <span class="text-muted">// required if no phone</span>
  "phone":       "+1 555 0101",      <span class="text-muted">// required if no email</span>
  "external_id": "STU-001",          <span class="text-success">// recommended — used for upsert</span>
  "branch_id":   1,                  <span class="text-muted">// optional</span>
  "is_active":   true                <span class="text-muted">// optional, default true</span>
}</div>

                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Responses</h5>
                                <table class="table table-sm table-row-dashed fs-7">
                                    <tbody>
                                        <tr>
                                            <td><span class="badge badge-light-success">201</span></td>
                                            <td>Contact created — <code>"status": "created"</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-light-primary">200</span></td>
                                            <td>Contact updated — <code>"status": "updated"</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-light-warning">422</span></td>
                                            <td>Validation error or contact limit reached</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-light-danger">401</span></td>
                                            <td>Missing, invalid, or revoked API key</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-light-danger">429</span></td>
                                            <td>Rate limit exceeded (120 req/min)</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h5 class="fw-bold text-gray-800 mb-3 fs-6">Example cURL</h5>
                                <div class="bg-gray-900 rounded p-4 font-monospace fs-8 text-white position-relative" id="curl-block" style="white-space:pre-wrap">curl -X POST \
  https://{{ $tenantDomain }}/api/v1/contacts \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "role": "parent",
    "email": "jane@school.com",
    "external_id": "STU-001"
  }'
                                    <button onclick="navigator.clipboard.writeText(document.getElementById('curl-block').innerText.replace('Copy','').trim()); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',2000)"
                                            class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 fs-8">Copy</button>
                                </div>
                            </div>

                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
