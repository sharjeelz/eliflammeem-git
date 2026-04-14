

@if ($errors->any())
<!--begin::Alert-->
<!--begin::Alert-->
        <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
            <i class="ki-solid ki-notification-bing fs-2hx text-danger me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Error</h4>
                <span>{{$errors->first()}}</span>
            </div>
        </div>
        <!--end::Alert-->
@endif
@if (session('error'))
<div class="alert alert-danger d-flex align-items-center p-5 mb-10">
    <i class="ki-solid ki-notification-bing fs-2hx text-danger me-4"></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-danger">Error</h4>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif
@if (session('plan_error'))
<div class="alert alert-warning d-flex align-items-center p-5 mb-10">
    <i class="ki-duotone ki-lock-2 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-warning">Plan Upgrade Required</h4>
        <span>{{ session('plan_error') }}</span>
    </div>
</div>
@endif
@if (session('ok'))
<div class="alert alert-success d-flex align-items-center p-5 mb-10">
    <i class="ki-solid ki-notification-bing fs-2hx text-success me-4"></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-success">Success</h4>
        <span>{{ session('ok') }}</span>
    </div>
</div>
@endif
@if (session('info'))
<div class="alert alert-primary d-flex align-items-center p-5 mb-10">
    <i class="ki-duotone ki-information-5 fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-primary">Info</h4>
        <span>{{ session('info') }}</span>
    </div>
</div>
@endif