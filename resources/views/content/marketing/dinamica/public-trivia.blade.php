@php
    $pageConfigs = [
        'showMenu' => false,
        'blankPage' => true,
        'layoutWidth' => 'full',
    ];
    $registration = $registration_config ?? [];
    $triviaUi = $trivia_config ?? [];
    $blocks = $game_blocks ?? [];
    $participantsCount = method_exists($registros, 'total') ? $registros->total() : count($registros ?? []);
@endphp

@extends('layouts.fullLayoutMaster')

@section('title', $dinamica->nombre ?? 'Trivia')

@section('content')
<div
    class="trivia-public-shell"
    data-activation-url="{{ route('dinamica.public.status', $dinamica->slug) }}"
    data-auto-refresh="{{ $usuarioRegistro && !$dinamica->is_active ? '1' : '0' }}"
>
    <section class="trivia-hero">
        <div class="hero-content">
            <div class="hero-eyebrow">Trivia</div>
            <h1>{{ $triviaUi['name'] ?? $dinamica->nombre }}</h1>
        </div>
        {{-- Estado, modo inscripción y slug removidos por solicitud --}}
    </section>
    @if($usuarioRegistro)
        <div class="trivia-registered-message mb-3 text-center">
            <div class="registered-title">¡Registro completado!</div>
            @if($dinamica->is_active)
                <div class="registered-sub">¡Ya puedes jugar la trivia!</div>
            @else
                <span class="trivia-wait-message">En breve inicia la trivia, espera la activación.</span>
            @endif
            @if($dinamica->is_active)
                <a href="{{ route('dinamica.public.preview', ['slug' => $dinamica->slug]) }}" class="btn btn-lg btn-success mt-2 px-5 py-2" style="font-size:1.3rem;">
                    <i class="feather icon-play"></i> ¡Comenzar a jugar!
                </a>
            @endif
        </div>
    @endif

    @php
        $registrationLimitEnabled = $registration_limit_enabled ?? false;
        $registrationIsOpen = $registration_is_open ?? true;
        $registrationClosed = $registrationLimitEnabled && ! $registrationIsOpen;
        $registrationSecondsLeft = $registration_seconds_left ?? null;
        $registrationMinutesLeft = null;
        if ($registrationSecondsLeft !== null) {
            $registrationMinutesLeft = (int) max(0, ceil($registrationSecondsLeft / 60));
        }
        $registrationWindowMinutes = $registration_window_minutes ?? null;
        $registrationDeadlineTime = isset($registration_deadline) && $registration_deadline
            ? optional($registration_deadline)->timezone(config('app.timezone', 'UTC'))->format('H:i')
            : null;
        $registrationDeadlineDate = isset($registration_deadline) && $registration_deadline
            ? optional($registration_deadline)->timezone(config('app.timezone', 'UTC'))->format('d/m')
            : null;
        $registrationDeadlineLabel = null;
        $registrationDeadlineClosedLabel = null;
        if ($registrationDeadlineDate || $registrationDeadlineTime) {
            $parts = [];
            if ($registrationDeadlineDate) {
                $parts[] = 'el ' . $registrationDeadlineDate;
            }
            if ($registrationDeadlineTime) {
                $parts[] = 'a las ' . $registrationDeadlineTime . ' hrs';
            }
            $joined = implode(' ', $parts);
            $registrationDeadlineLabel = 'Hasta ' . $joined;
            $registrationDeadlineClosedLabel = 'Cerró ' . $joined;
        }
    @endphp

    <section class="trivia-layout">
        @if(!$usuarioRegistro)
            <div class="card card-register">
                <div class="card-header">
                    <div>
                        <small>Participa ahora</small>
                        <h3>Ingresa para jugar</h3>
                    </div>
                </div>

                @include('components.registration-banner', [
                    'registrationLimitEnabled' => $registrationLimitEnabled,
                    'registrationClosed' => $registrationClosed,
                    'registrationSecondsLeft' => $registrationSecondsLeft,
                    'registrationMinutesLeft' => $registrationMinutesLeft,
                    'registrationWindowMinutes' => $registrationWindowMinutes,
                    'registrationDeadlineLabel' => $registrationDeadlineLabel,
                    'registrationDeadlineClosedLabel' => $registrationDeadlineClosedLabel,
                    'registrationDeadlineTime' => $registrationDeadlineTime,
                ])

                @if(!$registrationClosed)
                    <form method="POST" action="{{ route('dinamica.public.register', $dinamica->slug) }}" class="register-form">
                        @csrf
                        <label>
                            <span>Nombre</span>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ingresa tu nombre" required>
                        </label>
                        <label>
                            <span>Apellido</span>
                            <input type="text" name="apellido" value="{{ old('apellido') }}" placeholder="Tu apellido" required>
                        </label>
                        <label>
                            <span>Correo corporativo</span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@empresa.com" required>
                        </label>
                        <button type="submit" class="cta-button">
                            <span>Registrarme ahora</span>
                            <i class="feather icon-arrow-right"></i>
                        </button>
                    </form>
                    <p class="helper-text">Solo registras tus datos una vez; el sistema te asigna acceso a la trivia.</p>
                @else
                    <p class="helper-text registration-closed-text">El registro se cerró automáticamente para esta trivia.</p>
                @endif
            </div>
        @endif

        <div class="card card-overview">
            <div class="card-header">
                <div>
                    <h3>Bloques en juego</h3>
                </div>
            </div>
            <div class="blocks-grid">
                @forelse($blocks as $block)
                    @php
                        $categoryName = $block['category_name'] ?? $block['category'] ?? null;
                    @endphp
                    <article class="block-card">
                        <div class="block-marker">
                            <span>Bloque {{ $loop->iteration }}</span>
                        </div>
                        <header>
                            <strong>{{ $block['title'] ?? 'Sin título' }}</strong>
                            @if($categoryName)
                                <small class="block-category">{{ $categoryName }}</small>
                            @endif
                        </header>
                    </article>
                @empty
                    <p class="empty-state">La empresa aún no define bloques visibles para esta trivia.</p>
                @endforelse
            </div>
        </div>
    </section>

</div>
@endsection

@push('styles')
<style>
    .trivia-registered-message {
        background: linear-gradient(90deg, #5efcdb 0%, #5c6bff 100%);
        color: #10121a;
        border-radius: 22px;
        padding: 2.5rem 2rem 2rem;
        box-shadow: 0 8px 32px rgba(92,107,255,0.18);
        max-width: 520px;
        margin: 0 auto 2.5rem;
        border: 2px solid #5efcdb;
        position: relative;
        animation: pop-in 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    }
        .trivia-registered-message {
            background: rgba(13, 16, 27, 0.95);
            color: #fff;
            max-width: 520px;
            margin: 0 auto 2.5rem;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(92,107,255,0.13);
            position: relative;
            animation: pop-in 0.7s cubic-bezier(.68,-0.55,.27,1.55);
            padding: 1.5rem 2rem 1.2rem 2rem;
            text-align: center;
            font-weight: 500;
        }
        .registered-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.3em;
        }
        .registered-sub {
            font-size: 1.08rem;
            font-weight: 500;
        }
    .trivia-registered-message .success-icon {
        font-size: 3.5rem;
        color: #1bc47d;
        margin-bottom: 0.5rem;
        animation: bounce-in 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    }
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap');
:root {
    --trivia-bg: #05060a;
    --trivia-card: #10121a;
    --trivia-card-alt: #121625;
    --trivia-border: rgba(255, 255, 255, 0.08);
    --trivia-tone: #b6c2ff;
    --trivia-accent: #5efcdb;
    --trivia-highlight: linear-gradient(135deg, #5efcdb, #5c6bff);
}
.trivia-public-shell {
    min-height: 100vh;
    padding: 4rem 1.5rem 5rem;
    background: radial-gradient(circle at top, rgba(94, 252, 219, 0.15), transparent 45%),
        radial-gradient(circle at 20% 20%, rgba(92, 107, 255, 0.18), transparent 40%),
        var(--trivia-bg);
    font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
    color: #f7f8ff;
}
.trivia-hero {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin: 0 auto 3rem;
    max-width: 940px;
    align-items: stretch;
}
.hero-content {
    padding: 2.5rem;
    border-radius: 28px;
    background: rgba(13, 16, 27, 0.95);
    border: 1px solid var(--trivia-border);
    box-shadow: 0 25px 60px rgba(5, 6, 10, 0.6);
}
.hero-content h1 {
    font-size: clamp(2.5rem, 4vw, 3.5rem);
    margin-bottom: 1rem;
}
.hero-content p {
    font-size: 1.1rem;
    color: #c3c8e5;
}
.hero-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.35em;
    font-size: 0.75rem;
    color: var(--trivia-tone);
    margin-bottom: 1rem;
}
.hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 2rem;
}
.badge-chip {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 18px;
    padding: 0.85rem 1.25rem;
    border: 1px solid var(--trivia-border);
    display: flex;
    flex-direction: column;
    min-width: 120px;
}
.badge-chip strong {
    font-size: 1.5rem;
}
.badge-chip small {
    color: #a6b0d4;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.hero-panel {
    background: var(--trivia-card);
    border-radius: 28px;
    padding: 2rem;
    border: 1px solid var(--trivia-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.panel-label {
    font-size: 0.8rem;
    letter-spacing: 0.2em;
    color: #8ea0ff;
    text-transform: uppercase;
}
.panel-title {
    font-size: 2rem;
    margin: 1rem 0;
}
.panel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}
.panel-grid p {
    margin: 0.3rem 0 0;
}
.trivia-layout {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    max-width: 780px;
    margin: 0 auto;
}
.card {
    background: var(--trivia-card-alt);
    border-radius: 26px;
    padding: 2rem;
    border: 1px solid var(--trivia-border);
    box-shadow: 0 18px 50px rgba(5, 6, 10, 0.55);
}
.registration-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    border-radius: 22px;
    padding: 1.1rem 1.3rem;
    margin-bottom: 1.35rem;
    background: linear-gradient(135deg, rgba(95, 252, 219, 0.15) 0%, rgba(92, 107, 255, 0.14) 100%);
    border: 1px solid rgba(94, 252, 219, 0.4);
    box-shadow: 0 18px 40px rgba(12, 16, 33, 0.45);
}
.registration-banner__icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: rgba(8, 11, 22, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #5efcdb;
}
.registration-banner__label {
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    color: #a5b4fc;
    margin: 0;
}
.registration-banner__value {
    margin: 0.15rem 0 0.1rem;
    font-size: 1.65rem;
    font-weight: 700;
    color: #f8fafc;
}
.registration-banner__meta {
    margin: 0;
    font-size: 0.9rem;
    color: #cbd5f5;
}
.registration-banner.is-closed {
    background: rgba(255, 99, 130, 0.12);
    border-color: rgba(255, 99, 130, 0.35);
}
.registration-banner.is-closed .registration-banner__icon {
    color: #fecdd3;
    background: rgba(49, 8, 14, 0.65);
}
.registration-banner.is-closed .registration-banner__label,
.registration-banner.is-closed .registration-banner__value,
.registration-banner.is-closed .registration-banner__meta {
    color: #fecdd3;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.card-header small {
    text-transform: uppercase;
    letter-spacing: 0.25em;
    font-size: 0.68rem;
    color: var(--trivia-tone);
}
.card-header h3 {
    margin-top: 0.5rem;
}
.pill {
    border-radius: 999px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    background: var(--trivia-highlight);
    color: #05060a;
}
.pill-outline {
    background: transparent;
    color: var(--trivia-tone);
    border: 1px solid var(--trivia-tone);
}
.register-form {
    display: grid;
    gap: 1rem;
}
.register-form label {
    display: flex;
    flex-direction: column;
    font-size: 0.9rem;
    color: #a9b6dd;
}
.register-form input {
    background: rgba(5, 6, 10, 0.6);
    border: 1px solid var(--trivia-border);
    border-radius: 16px;
    padding: 0.95rem 1.25rem;
    margin-top: 0.45rem;
    color: #fff;
    font-size: 1rem;
}
.cta-button {
    margin-top: 0.5rem;
    border: none;
    width: 100%;
    padding: 1.1rem 1.5rem;
    border-radius: 18px;
    font-size: 1.05rem;
    font-weight: 600;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    background: var(--trivia-highlight);
    color: #05060a;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.cta-button:hover {
    transform: translateY(-1px) scale(1.01);
}
.helper-text {
    margin-top: 0.75rem;
    color: #9eaad0;
    font-size: 0.85rem;
}
.registration-closed-text {
    color: #ffb4b4;
    font-weight: 600;
}
.blocks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
}
.block-card {
    position: relative;
    border: 1px solid var(--trivia-border);
    border-radius: 22px;
    padding: 1.5rem 1.75rem;
    background: radial-gradient(circle at top right, rgba(94, 252, 219, 0.2), transparent 45%),
        rgba(8, 12, 22, 0.92);
    overflow: hidden;
}
.block-card::after {
    content: '';
    position: absolute;
    width: 140px;
    height: 140px;
    background: rgba(92, 107, 255, 0.12);
    border-radius: 32% 68% 70% 30% / 41% 30% 70% 59%;
    top: -40px;
    right: -50px;
    filter: blur(0.5px);
}
.block-marker {
    display: inline-flex;
    padding: 0.35rem 0.95rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 0.72rem;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: #8ea0ff;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}
.block-card header {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.block-card header strong {
    font-size: 1.4rem;
}
.block-category {
    color: #9eaad0;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.empty-state {
    color: #8b93b8;
    font-size: 0.95rem;
    padding: 1rem 0;
}
.participants-section {
    margin: 3.5rem auto 0;
    background: rgba(10, 14, 24, 0.9);
    border-radius: 28px;
    border: 1px solid var(--trivia-border);
    padding: 2.5rem;
    max-width: 940px;
}
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.section-head small {
    text-transform: uppercase;
    letter-spacing: 0.25em;
    font-size: 0.7rem;
    color: var(--trivia-tone);
}
.participants-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.participant-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-radius: 18px;
    border: 1px solid var(--trivia-border);
    background: rgba(7, 9, 16, 0.65);
}
.participant-name {
    font-size: 1.1rem;
    margin-bottom: 0.2rem;
}
.participant-email {
    color: #8b93b8;
    font-size: 0.9rem;
}
.participant-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.35rem;
    font-weight: 500;
}
.status-badge {
    padding: 0.15rem 0.75rem;
    border-radius: 999px;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}
.status-badge.pending {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}
.status-badge.done {
    background: rgba(255, 214, 102, 0.2);
    color: #ffd666;
}
.status-badge.win {
    background: rgba(94, 252, 219, 0.2);
    color: #5efcdb;
}
.pagination-wrapper {
    margin-top: 1.5rem;
    text-align: center;
}
.alert {
    border-radius: 14px;
    padding: 0.9rem 1.1rem;
}
.alert-success {
    background: rgba(94, 252, 219, 0.12);
    color: #5efcdb;
}
.alert-danger {
    background: rgba(255, 107, 107, 0.12);
    color: #ff908f;
}
@media (max-width: 900px) {
    .trivia-layout {
        grid-template-columns: 1fr;
    }
    .hero-badges {
        flex-direction: column;
    }
    .participant-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .participant-meta {
        align-items: flex-start;
    }
}
    .trivia-wait-message {
        display: block;
        background: var(--trivia-card-alt);
        color: var(--trivia-accent);
        border-radius: 18px;
        padding: 1.1rem 2rem 1.1rem 2rem;
        box-shadow: 0 4px 18px rgba(92,107,255,0.10);
        max-width: 520px;
        margin: 0.7em auto 0 auto;
        border: 1.5px solid var(--trivia-accent);
        font-size: 1.13rem;
        font-weight: 500;
        text-align: center;
        letter-spacing: 0.01em;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const setupRegistrationCountdown = () => {
        const banner = document.querySelector('.registration-banner[data-countdown-seconds]');
        if (!banner) {
            return;
        }
        const display = banner.querySelector('[data-countdown-display]');
        if (!display) {
            return;
        }
        let seconds = parseInt(banner.getAttribute('data-countdown-seconds'), 10);
        if (Number.isNaN(seconds)) {
            return;
        }

        const renderTime = () => {
            const safe = Math.max(0, seconds);
            const mins = String(Math.floor(safe / 60)).padStart(2, '0');
            const secs = String(safe % 60).padStart(2, '0');
            display.textContent = `${mins}:${secs}`;
        };

        renderTime();

        if (seconds <= 0) {
            return;
        }

        const intervalId = setInterval(() => {
            seconds -= 1;
            renderTime();
            if (seconds <= 0) {
                clearInterval(intervalId);
            }
        }, 1000);
    };

    const setupParticipantsFeed = () => {
        const list = document.querySelector('[data-participants-list]');
        if (!list) {
            return;
        }

        const endpoint = list.getAttribute('data-feed-url');
        if (!endpoint) {
            return;
        }

        const dinamicaType = (list.getAttribute('data-dinamica-type') || 'trivia').toLowerCase();
        const emptyCopy = list.getAttribute('data-empty-state') || 'Aún no hay registros. Sé la primera persona en sumarse.';
        const pollIntervalMs = 8000;
        let pollHandle = null;

        const sanitize = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const buildStatus = (participant) => {
            if (participant.ha_ganado) {
                return { label: 'Ganó', className: 'status-badge win' };
            }
            if (participant.ha_jugado) {
                return { label: 'Jugó', className: 'status-badge done' };
            }
            return { label: 'Pendiente', className: 'status-badge pending' };
        };

        const buildRow = (participant) => {
            const status = buildStatus(participant);
            const fullName = sanitize(participant.full_name || `${participant.first_name || ''} ${participant.last_name || ''}`.trim());
            const email = sanitize(participant.email || '');
            const turnoMarkup = dinamicaType !== 'trivia' && participant.turno
                ? `<span>Turno #${sanitize(participant.turno)}</span>`
                : '';

            return `
                <div class="participant-row">
                    <div>
                        <p class="participant-name">${fullName}</p>
                        <span class="participant-email">${email}</span>
                    </div>
                    <div class="participant-meta">
                        ${turnoMarkup}
                        <span class="${status.className}">${status.label}</span>
                    </div>
                </div>
            `;
        };

        const renderParticipants = (participants) => {
            if (!participants.length) {
                list.innerHTML = `<div class="empty-state">${emptyCopy}</div>`;
                return;
            }
            list.innerHTML = participants.map(buildRow).join('');
        };

        const fetchParticipants = async () => {
            try {
                const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                renderParticipants(Array.isArray(payload.participants) ? payload.participants : []);
            } catch (error) {
                console.warn('No se pudo actualizar la lista de participantes', error);
            }
        };

        const startPolling = () => {
            if (pollHandle) {
                clearInterval(pollHandle);
            }
            pollHandle = setInterval(fetchParticipants, pollIntervalMs);
        };

        fetchParticipants();
        startPolling();

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && pollHandle) {
                clearInterval(pollHandle);
                pollHandle = null;
            } else if (!document.hidden && !pollHandle) {
                fetchParticipants();
                pollHandle = setInterval(fetchParticipants, pollIntervalMs);
            }
        });
    };

    const setupActivationWatcher = () => {
        const shell = document.querySelector('.trivia-public-shell');
        if (!shell) {
            return;
        }
    };

    setupRegistrationCountdown();
        const shouldAutoRefresh = shell.getAttribute('data-auto-refresh') === '1';
        const activationUrl = shell.getAttribute('data-activation-url');

        if (!shouldAutoRefresh || !activationUrl) {
            return;
        }

        const pollIntervalMs = 6000;
        let activationHandle = null;
        let hasReloaded = false;

        const checkActivation = async () => {
            if (hasReloaded) {
                return;
            }

            try {
                const response = await fetch(activationUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                if (payload && payload.is_active) {
                    hasReloaded = true;
                    window.location.reload();
                }
            } catch (error) {
                console.warn('No se pudo verificar el estado de la trivia', error);
            }
        };

        const startActivationPolling = () => {
            if (activationHandle) {
                clearInterval(activationHandle);
            }
            activationHandle = setInterval(checkActivation, pollIntervalMs);
        };

        checkActivation();
        startActivationPolling();

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && activationHandle) {
                clearInterval(activationHandle);
                activationHandle = null;
            } else if (!document.hidden && !activationHandle && !hasReloaded) {
                checkActivation();
                activationHandle = setInterval(checkActivation, pollIntervalMs);
            }
        });
    };

    setupParticipantsFeed();
    setupActivationWatcher();
});
</script>
@endpush
