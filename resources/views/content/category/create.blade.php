@extends('layouts/contentLayoutMaster')
@section('title', 'Crear Categoría')
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('category-index') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.category') }}</a>
</div>
<category-create></category-create>
@endsection