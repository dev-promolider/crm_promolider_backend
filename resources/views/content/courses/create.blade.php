@extends('layouts/contentLayoutMaster')
@section('title', $title)
@section('content')
    <div class="d-flex flex-row-reverse">
        <a href="{{ route('courses.index') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.products') }}</a>
    </div>
    <course-create :categories-list='@json($categories)'
        :levels-list='@json($levels)'
        :route='@json(route('courses.index'))'
        :product-type-id='@json($product_type_id)'>
    </course-create>
@endsection
