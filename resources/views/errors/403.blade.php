@extends('layouts.tenant_admin')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center vh-100 text-center">
    <h1 class="display-1 text-danger">403</h1>
    <p class="fs-3 fw-semibold mb-3">Forbidden</p>
    <p class="mb-4">
       
        {{ 
           
            ($message ?? $exception->getMessage())
            ?: 'You do not have permission to access this page.'
        }}
    </p>
    <a href="{{ url()->previous() }}" class="btn btn-primary">Go Back</a>
</div>
@endsection