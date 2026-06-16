@extends('layouts/fullLayoutMaster')

@section('title', 'Reset Password')

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/pages/page-auth.css')) }}">
@endsection

@section('content')
<div class="auth-wrapper auth-v1 px-2">
  <div class="auth-inner py-2">
    <!-- Reset Password v1 -->
    <div class="card mb-0">
      <div class="card-body">
        
        <!-- Header con Logo Promolíder -->
        <div class="text-center mb-4" style="background-color: #35424a; margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 2rem 1rem; border-radius: 0.357rem 0.357rem 0 0;">
          <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" 
               alt="Promolíder Logo" 
               title="Promolíder" 
               style="max-width: 200px; height: auto; width: auto;" 
               class="img-fluid" />
        </div>

        <!-- Título y descripción -->
        <h4 class="card-title mb-1">Restablecer Contraseña 🔒</h4>
        <p class="card-text mb-2">Tu nueva contraseña debe ser diferente a las contraseñas utilizadas anteriormente</p>

        <!-- Formulario de restablecimiento -->
        <form class="auth-reset-password-form mt-2" method="POST" action="{{ route('password.update') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">

          <!-- Campo Email -->
          <div class="form-group">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   placeholder="tucorreo@ejemplo.com" 
                   aria-describedby="email" 
                   tabindex="1" 
                   value="{{ $email ?? old('email') }}" 
                   readonly 
                   required />
            @error('email')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <!-- Campo Nueva Contraseña -->
          <div class="form-group">
            <label for="reset-password-new">Nueva Contraseña</label>
            <div class="input-group input-group-merge form-password-toggle">
              <input type="password" 
                     class="form-control form-control-merge @error('password') is-invalid @enderror" 
                     id="reset-password-new" 
                     name="password" 
                     placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                     aria-describedby="reset-password-new" 
                     tabindex="2" 
                     required />
              <div class="input-group-append">
                <span class="input-group-text cursor-pointer">
                  <i data-feather="eye"></i>
                </span>
              </div>
            </div>
            @error('password')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <!-- Campo Confirmar Contraseña -->
          <div class="form-group">
            <label for="reset-password-confirm">Confirmar Contraseña</label>
            <div class="input-group input-group-merge form-password-toggle">
              <input type="password" 
                     class="form-control form-control-merge" 
                     id="reset-password-confirm" 
                     name="password_confirmation" 
                     autocomplete="new-password" 
                     placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                     aria-describedby="reset-password-confirm" 
                     tabindex="3" 
                     required />
              <div class="input-group-append">
                <span class="input-group-text cursor-pointer">
                  <i data-feather="eye"></i>
                </span>
              </div>
            </div>
          </div>

          <!-- Botón Submit -->
          <button type="submit" class="btn btn-success btn-block" tabindex="4">
            Establecer Nueva Contraseña
          </button>
        </form>

        <!-- Link de regreso -->
        <p class="text-center mt-2">
          @if (Route::has('main-login'))
            <a href="{{ route('main-login') }}">
              <i data-feather="chevron-left"></i> Volver al inicio de sesión
            </a>
          @endif
        </p>

      </div>
    </div>
    <!-- /Reset Password v1 -->
  </div>
</div>
@endsection

@section('vendor-script')
{{-- Vendor scripts si son necesarios --}}
{{-- <script src="{{asset(mix('vendors/js/forms/validation/jquery.validate.min.js'))}}"></script> --}}
@endsection

@section('page-script')
{{-- Page specific scripts --}}
{{-- <script src="{{asset(mix('js/scripts/pages/page-auth-reset-password.js'))}}"></script> --}}
@endsection