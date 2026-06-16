@php
    $limitEnabled = $registrationLimitEnabled ?? false;
    $bannerForced = $forceVisible ?? false;
    $shouldRender = $bannerForced || $limitEnabled;
    $bannerClosed = $limitEnabled ? ($registrationClosed ?? false) : false;
    $fallbackLabel = $fallbackLabel ?? 'Registro abierto';
    $fallbackValue = $fallbackValue ?? 'Disponible en este momento';
    $fallbackMeta = $fallbackMeta ?? 'Completa tus datos para asegurar tu lugar.';
@endphp
@if($shouldRender)
    <div
        class="registration-banner {{ $bannerClosed ? 'is-closed' : '' }}"
        @if($limitEnabled && !$bannerClosed && isset($registrationSecondsLeft))
            data-countdown-seconds="{{ max(0, (int) $registrationSecondsLeft) }}"
        @endif
        @if($limitEnabled && !empty($registrationWindowMinutes))
            data-window-minutes="{{ $registrationWindowMinutes }}"
        @endif
    >
        <div class="registration-banner__icon">
            <i class="feather icon-clock"></i>
        </div>
        <div>
            @if($limitEnabled)
                @if($bannerClosed)
                    <p class="registration-banner__label">Registro cerrado</p>
                    <p class="registration-banner__value">
                        {{ $registrationDeadlineClosedLabel ?? 'El cupo se completó y el periodo terminó.' }}
                    </p>
                    @if(!empty($registrationWindowMinutes))
                        <p class="registration-banner__meta">Ventana de {{ $registrationWindowMinutes }} min concluida</p>
                    @endif
                @else
                    <p class="registration-banner__label">Registro disponible</p>
                    <p class="registration-banner__value" data-countdown-display>
                        @if(isset($registrationSecondsLeft))
                            {{ gmdate('i:s', max(0, $registrationSecondsLeft)) }}
                        @elseif(isset($registrationMinutesLeft))
                            {{ str_pad($registrationMinutesLeft, 2, '0', STR_PAD_LEFT) }}:00
                        @elseif(!empty($registrationDeadlineTime))
                            Hasta {{ $registrationDeadlineTime }} hrs
                        @else
                            Ventana activa
                        @endif
                    </p>
                    <p class="registration-banner__meta" data-window-label>
                        @if(!empty($registrationDeadlineLabel))
                            {{ $registrationDeadlineLabel }}
                        @elseif(!empty($registrationWindowMinutes))
                            Máx. {{ $registrationWindowMinutes }} min de registro
                        @else
                            Tiempo limitado
                        @endif
                    </p>
                @endif
            @else
                <p class="registration-banner__label">{{ $fallbackLabel }}</p>
                <p class="registration-banner__value">{{ $fallbackValue }}</p>
                <p class="registration-banner__meta">{{ $fallbackMeta }}</p>
            @endif
        </div>
    </div>
@endif
