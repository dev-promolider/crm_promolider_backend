@extends('layouts/contentLayoutMaster')
@section('title', "Certificado | $course->title")
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('courses.index') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.course') }}</a>
</div>
<course-certificate-config :course='@json($course)'></course-certificate-config>
@endsection
