@extends('layouts/contentLayoutMaster')
@section('title', 'Suscripciones')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

<course-list :courses='@json($courses)' :path='@json($path)'></course-list>

{{-- {!! $shareComponent !!}
<div style="background-color:red">

    <a href="http://www.facebook.com/sharer.php?u=http%3A%2F%2Fpromolider.xyz%2Flogin&og:title=xwaewaeaweawe&og:description=aeweaeaeawewa" target="_blank">Enlace 1
    </a>

    <a href="http://www.facebook.com/sharer.php?u=http%3A%2F%2Fpromolider.xyz%2Flogin&og:type=article&og:title=xwaewaeaweawe&og:description=aeweaeaeawewa" target="_blank">Enlace 2
    </a>

    <a href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fpromolider.xyz%2Flogin&quote=THE_CUSTOM_TEXT
    " target="_blank">
        Enlace 3
    </a>

</div> --}}
@endsection
