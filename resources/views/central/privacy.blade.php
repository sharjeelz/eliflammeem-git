@extends('layouts.central')

@section('title', \App\Models\AppSetting::get('privacy_title', 'Privacy Policy'))
@section('description', 'Read the Privacy Policy for ElifLammeem.')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">

    {{-- Page Header --}}
    <div class="mb-12">
        <p class="text-primary-600 font-bold text-sm uppercase tracking-[0.2em] mb-3">Legal</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">
            {{ \App\Models\AppSetting::get('privacy_title', 'Privacy Policy') }}
        </h1>
        <p class="mt-4 text-slate-400 text-sm font-medium">Last updated: {{ date('d F Y') }}</p>
    </div>

    {{-- Content --}}
    <article class="prose max-w-none">
        {!! \App\Models\AppSetting::get('privacy_content') !!}
    </article>

</div>
@endsection
