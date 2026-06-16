@extends('layouts/contentLayoutMaster')
@section('title', 'Crear Masterclass')
@section('content')
    <div class="d-flex flex-row-reverse">
        <a href="{{ route('marketing.tools') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.tools') }}</a>
    </div>
    <masterclass-create :categories-list='@json($categories)'
                   :route='@json(route('marketing.tools'))'></masterclass-create>
@endsection