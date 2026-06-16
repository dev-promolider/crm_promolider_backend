@extends('layouts.contentLayoutMaster')

@section('title', 'Crear E-book')

@section('content')
    <div class="d-flex flex-row-reverse">
        <a href="{{ route('marketing.tools') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.tools') }}</a>
    </div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Información General</h4>
        </div>
        <div class="card-body">
            <ebook-form :categories="{{ json_encode($categories) }}"></ebook-form>
        </div>
    </div>
@endsection