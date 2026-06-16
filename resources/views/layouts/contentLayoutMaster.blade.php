@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

<!DOCTYPE html>
@php
    // Configuración general y recuperación de datos específicos para la página
    $configData = Helper::applClasses();
    $get_auth_config = Helper::getAuthConfig();
@endphp

<html
    lang="@if (session()->has('locale')) {{ session()->get('locale') }}@else{{ $configData['defaultLanguage'] }} @endif"
    data-textdirection="{{ env('MIX_CONTENT_DIRECTION') === 'rtl' ? 'rtl' : 'ltr' }}"
    class="{{ $configData['theme'] === 'light' ? '' : $configData['layoutTheme'] }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="conn-ws" content="{{ trim($__env->yieldContent('conn-ws', env('CONN_WS', ''))) }}">

    <!-- Iconos para diferentes tamaños de dispositivos (comentados para configuración futura) -->
    {{-- <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png"> --}}
    {{-- <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png"> --}}
    {{-- <link rel="manifest" href="/manifest.json"> --}}
    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <title>@yield('title') - Promolider</title>
    <!-- Favicon del sitio -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo/favicon_navidad (1).ico') }}">
    <!-- Tipografía principal -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
        rel="stylesheet">
    {{-- Incluir estilos principales y de proveedores --}}
    @include('panels/styles')

</head>
@isset($configData['mainLayoutType'])
    {{-- Determina el layout principal según la configuración: horizontal o vertical --}}
    @extends($configData['mainLayoutType'] === 'horizontal' ? 'layouts.horizontalLayoutMaster' : 'layouts.verticalLayoutMaster')
@endisset
<script type="text/javascript">
    // Objeto global con configuraciones de Laravel y permisos del usuario autenticado
    window.Laravel = {
        csrfToken: "{{ csrf_token() }}",
        jsPermissions: {!! auth()->check() ? auth()->user()->jsPermissions() : null !!}
    }
</script>
