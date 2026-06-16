<!DOCTYPE html>
<html lang="es-PE" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ecosistema Digital Promolíder | Pre-Registro Socios Fundadores</title>
    <meta name="description" content="Pre-registro exclusivo para Socios Fundadores del Ecosistema Digital Promolíder. Fusionamos E-learning, IA y Network Marketing para crear ingresos residuales reales.">
    <meta name="keywords" content="ecosistema digital, ingresos residuales, e-learning, IA, network marketing, promolíder, socio fundador">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://promolider.org/">
    <meta property="og:title" content="Ecosistema Digital Promolíder | Pre-Registro Socios Fundadores">
    <meta property="og:description" content="Fusionamos E-learning, IA y Network Marketing para crear la red de distribución más potente de la región.">
    <meta property="og:image" content="https://promolider.org/imagenes/logo promolider.png">
    <meta property="twitter:card" content="summary_large_image">
        <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#edfff3', 100: '#d5ffe2', 200: '#aeffc8', 300: '#70ff9e',
                            400: '#2bfd68', 500: '#0BF50B', 600: '#09d609', 700: '#00a800',
                            800: '#008400', 900: '#006b00',
                        },
                        dark: { 900: '#0a0a0a', 800: '#111827', 700: '#1f2937' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                        'ticker': 'ticker 30s linear infinite',
                    },
                    keyframes: {
                        float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
                        shimmer: { '0%': { backgroundPosition: '-200% 0' }, '100%': { backgroundPosition: '200% 0' } },
                        ticker: { '0%': { transform: 'translateX(0)' }, '100%': { transform: 'translateX(-50%)' } },
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0a0a0a;
            color: #e5e7eb;
            overflow-x: hidden;
        }
        /* Scroll progress */
        .scroll-progress {
            position: fixed; top: 0; left: 0; height: 3px;
            background: linear-gradient(90deg, #0BF50B, #00a800);
            z-index: 9999; transition: width 0.1s linear; width: 0%;
        }
        /* Nav */
        .nav-glass {
            background: rgba(10,10,10,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(11,245,11,0.1);
        }
        /* Hero gradient bg */
        .hero-bg {
            background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(11,245,11,0.12) 0%, transparent 70%),
                        radial-gradient(ellipse 60% 40% at 80% 50%, rgba(0,168,0,0.07) 0%, transparent 60%),
                        #0a0a0a;
        }
        /* Grid pattern */
        .grid-pattern {
            background-image: linear-gradient(rgba(11,245,11,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(11,245,11,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        /* Glow text */
        .text-glow { text-shadow: 0 0 40px rgba(11,245,11,0.4); }
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #0BF50B 0%, #00ff88 50%, #00a800 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Badge pulse */
        .badge-pulse::before {
            content: ''; position: absolute; width: 100%; height: 100%;
            border-radius: 9999px; background: #0BF50B;
            animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; opacity: 0.4;
        }
        /* Primary button */
        .btn-primary {
            background: linear-gradient(135deg, #0BF50B 0%, #00a800 100%);
            color: #0a0a0a; font-weight: 800;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .btn-primary::after {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s linear infinite; background-size: 200% 100%;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 40px -5px rgba(11,245,11,0.4); }
        /* Secondary button */
        .btn-secondary {
            background: transparent; color: #e5e7eb; font-weight: 700;
            border: 1px solid rgba(255,255,255,0.15);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover { border-color: rgba(11,245,11,0.5); color: #0BF50B; background: rgba(11,245,11,0.05); }
        /* Card dark */
        .card-dark {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s ease;
        }
        .card-dark:hover {
            background: rgba(11,245,11,0.04);
            border-color: rgba(11,245,11,0.2);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(11,245,11,0.1);
        }
        /* Input */
        .input-field {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #e5e7eb; transition: all 0.3s ease;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .input-field:focus {
            outline: none; border-color: #0BF50B;
            box-shadow: 0 0 0 3px rgba(11,245,11,0.15);
            background: rgba(11,245,11,0.03);
        }
        .input-field.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
        .error-msg { color: #f87171; font-size: 0.75rem; margin-top: 4px; display: none; }
        .error-msg.show { display: block; }
        /* Reveal animations */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-left.active { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-right.active { opacity: 1; transform: translateX(0); }
        /* Section divider */
        .section-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(11,245,11,0.3), transparent); }
        /* Toast */
        .toast { transform: translateY(20px); opacity: 0; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .toast.show { transform: translateY(0); opacity: 1; }
        /* Counter */
        .counter-number { font-variant-numeric: tabular-nums; }
        /* Countdown */
        .countdown-box {
            background: rgba(11,245,11,0.08);
            border: 1px solid rgba(11,245,11,0.2);
            border-radius: 12px; padding: 10px 16px; text-align: center; min-width: 64px;
        }
        .countdown-num { font-size: 2rem; font-weight: 900; color: #0BF50B; line-height: 1; }
        .countdown-label { font-size: 0.65rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
        /* Testimonial card */
        .testimonial-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 28px;
            transition: all 0.3s ease;
        }
        .testimonial-card:hover { border-color: rgba(11,245,11,0.25); transform: translateY(-4px); }
        /* Steps */
        .step-line { position: absolute; top: 28px; left: calc(50% + 28px); width: calc(100% - 56px); height: 2px; background: linear-gradient(90deg, rgba(11,245,11,0.4), rgba(11,245,11,0.1)); }
        /* Ticker */
        .ticker-wrapper { overflow: hidden; white-space: nowrap; }
        .ticker-track { display: inline-flex; animation: ticker 30s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }
        /* Image glow */
        .img-glow { filter: drop-shadow(0 0 20px rgba(11,245,11,0.15)); transition: filter 0.3s ease; }
        .img-glow:hover { filter: drop-shadow(0 0 35px rgba(11,245,11,0.3)); }
        /* For whom check/x */
        .check-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        /* Screen reader */
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border-width: 0; }
        /* Stat card */
        .stat-card {
            background: linear-gradient(135deg, rgba(11,245,11,0.08) 0%, rgba(0,168,0,0.04) 100%);
            border: 1px solid rgba(11,245,11,0.15); border-radius: 16px; padding: 24px; text-align: center;
        }
        /* Form card */
        .form-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="antialiased">

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- ===== NAVBAR ===== -->
    <nav class="nav-glass fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 md:h-18 flex items-center justify-between">
            <img src="/preregistro/new/imagenes/logo promolider negro.png" alt="Logo Promolíder" class="h-9 md:h-12 w-auto brightness-0 invert" width="140" height="48">
            <div class="hidden md:flex items-center gap-8">
                <a href="#como-funciona" class="text-gray-400 hover:text-primary-400 transition text-sm font-medium">Cómo funciona</a>
                <a href="#herramientas" class="text-gray-400 hover:text-primary-400 transition text-sm font-medium">Herramientas</a>
                <a href="#testimonios" class="text-gray-400 hover:text-primary-400 transition text-sm font-medium">Testimonios</a>
                <a href="#registro" class="btn-primary px-5 py-2 rounded-xl text-sm font-bold">Registrarme Gratis</a>
            </div>
            <a href="#registro" class="md:hidden btn-primary px-4 py-2 rounded-lg text-xs font-bold">Registrarme</a>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <header class="hero-bg grid-pattern relative pt-28 pb-16 lg:pt-36 lg:pb-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">

                <!-- Text Column -->
                <div class="lg:col-span-7 text-center lg:text-left flex flex-col items-center lg:items-start">

                    <!-- Badge -->
                    <div class="reveal inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/30 text-primary-400 text-xs sm:text-sm font-semibold mb-6">
                        <span class="relative w-2 h-2 rounded-full bg-primary-500 badge-pulse"></span>
                        PRE-REGISTRO EXCLUSIVO — CUPOS LIMITADOS
                    </div>

                    <!-- Headline -->
                    <h1 class="reveal text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-black tracking-tight mb-5 leading-[1.05] text-white">
                        El Ecosistema Digital que está
                        <span class="gradient-text text-glow block mt-1">redefiniendo los ingresos residuales.</span>
                    </h1>

                    <p class="reveal text-base sm:text-lg md:text-xl text-gray-400 mb-6 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Fusionamos <strong class="text-white">E-learning, IA y Network Marketing</strong> para crear la red de distribución más potente de Latinoamérica.
                    </p>

                    <!-- Countdown -->
                    <div class="reveal mb-8 w-full max-w-lg mx-auto lg:mx-0">
                        <p class="text-xs text-gray-500 uppercase tracking-widest mb-3 text-center lg:text-left">⏳ Cierre del pre-registro en:</p>
                        <div class="flex items-center gap-3 justify-center lg:justify-start">
                            <div class="countdown-box"><div class="countdown-num" id="cd-days">02</div><div class="countdown-label">Días</div></div>
                            <span class="text-primary-500 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-hours">14</div><div class="countdown-label">Horas</div></div>
                            <span class="text-primary-500 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-mins">37</div><div class="countdown-label">Min</div></div>
                            <span class="text-primary-500 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-secs">00</div><div class="countdown-label">Seg</div></div>
                        </div>
                    </div>

                    <!-- Social proof counter -->
                    <div class="reveal flex items-center gap-4 mb-8 bg-white/5 backdrop-blur-sm rounded-2xl px-5 py-3 border border-white/10 w-fit mx-auto lg:mx-0">
                        <div class="flex -space-x-2">
                            <div class="w-9 h-9 rounded-full bg-primary-500/20 border-2 border-dark-900 flex items-center justify-center text-xs font-bold text-primary-400">M</div>
                            <div class="w-9 h-9 rounded-full bg-blue-500/20 border-2 border-dark-900 flex items-center justify-center text-xs font-bold text-blue-400">C</div>
                            <div class="w-9 h-9 rounded-full bg-amber-500/20 border-2 border-dark-900 flex items-center justify-center text-xs font-bold text-amber-400">R</div>
                            <div class="w-9 h-9 rounded-full bg-purple-500/20 border-2 border-dark-900 flex items-center justify-center text-xs font-bold text-purple-400">A</div>
                            <div class="w-9 h-9 rounded-full bg-white/10 border-2 border-dark-900 flex items-center justify-center text-xs font-bold text-gray-400">+</div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white"><span class="counter-number text-primary-400" id="heroCounter">1,247</span> personas ya se registraron</p>
                            <p class="text-xs text-gray-500">en las últimas 48 horas</p>
                        </div>
                    </div>

                    <div class="reveal flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                        <a href="#registro" class="btn-primary w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 text-base font-black rounded-xl shadow-lg">
                            RESERVAR MI POSICIÓN GRATIS <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#como-funciona" class="btn-secondary w-full sm:w-auto inline-flex justify-center items-center px-6 py-4 text-base rounded-xl">
                            Ver cómo funciona <i class="fa-solid fa-arrow-down ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Form Column -->
                <div class="lg:col-span-5 relative w-full max-w-md mx-auto mt-8 lg:mt-0 reveal-right">
                    <div class="absolute -inset-4 bg-primary-500/10 blur-3xl rounded-full"></div>
                    <div class="relative z-10 form-card p-6 sm:p-8 rounded-2xl shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-500/15 border border-primary-500/30 mb-3">
                                <i class="fa-solid fa-lock text-primary-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-white mb-1">Comienza Ahora</h3>
                            <p class="text-gray-400 text-sm">Reserva tu posición <strong class="text-primary-400">GRATIS</strong> por 7 días.</p>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-xs text-primary-400 bg-primary-500/10 px-3 py-1.5 rounded-full border border-primary-500/20">
                                <i class="fa-solid fa-fire text-primary-500 animate-pulse"></i>
                                <span id="recentSignup">Alguien de Bogotá se acaba de registrar</span>
                            </div>
                        </div>
                        <form class="form-registro space-y-4" novalidate>
                            <div>
                                <label for="nombre_hero" class="sr-only">Nombre</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-500 text-sm"></i>
                                    </div>
                                    <input type="text" id="nombre_hero" name="nombre" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm placeholder-gray-500 transition-all"
                                        placeholder="Tu nombre">
                                </div>
                                <p class="error-msg">Por favor ingresa tu nombre</p>
                            </div>
                            <div>
                                <label for="apellido_hero" class="sr-only">Apellido</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-500 text-sm"></i>
                                    </div>
                                    <input type="text" id="apellido_hero" name="apellido" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu apellido">
                                </div>
                                <p class="error-msg">Por favor ingresa tu apellido</p>
                            </div>
                            <div>
                                <label for="whatsapp_hero" class="sr-only">WhatsApp</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-brands fa-whatsapp text-gray-500 text-sm"></i>
                                    </div>
                                    <input type="tel" id="whatsapp_hero" name="whatsapp" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu WhatsApp (+ código de país)">
                                </div>
                                <p class="error-msg">Por favor ingresa tu WhatsApp</p>
                            </div>
                            <div>
                                <label for="email_hero" class="sr-only">Correo electrónico</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-envelope text-gray-500 text-sm"></i>
                                    </div>
                                    <input type="email" id="email_hero" name="email" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu correo electrónico">
                                </div>
                                <p class="error-msg">Por favor ingresa un correo válido</p>
                            </div>
                            <div class="pt-1">
                                <button type="submit" class="btn-primary w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-lg font-black text-base">
                                    RESERVAR MI POSICIÓN <i class="fa-solid fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                            <p class="text-xs text-center text-gray-600 mt-2">
                                <i class="fa-solid fa-shield-halved mr-1 text-primary-600"></i> Tus datos están seguros. Cero spam.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== TICKER / SOCIAL PROOF BAR ===== -->
    <div class="bg-primary-500/10 border-y border-primary-500/20 py-3 overflow-hidden">
        <div class="ticker-wrapper">
            <div class="ticker-track">
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> María G. de Lima se registró hace 2 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Carlos M. de Bogotá se registró hace 5 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Rosa P. de Ciudad de México se registró hace 7 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Diego F. de Santiago se registró hace 9 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Ana R. de Quito se registró hace 11 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Luis V. de Buenos Aires se registró hace 14 min</span>
                <!-- Duplicate for seamless loop -->
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> María G. de Lima se registró hace 2 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Carlos M. de Bogotá se registró hace 5 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Rosa P. de Ciudad de México se registró hace 7 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Diego F. de Santiago se registró hace 9 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Ana R. de Quito se registró hace 11 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-400 font-medium"><i class="fa-solid fa-circle-check"></i> Luis V. de Buenos Aires se registró hace 14 min</span>
            </div>
        </div>
    </div>


    <!-- ===== STATS BAR ===== -->
    <section class="py-12 lg:py-16 relative z-10" style="background: rgba(255,255,255,0.02);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 reveal">
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text counter-number" data-target="1247">1,247</div>
                    <div class="text-gray-400 text-sm mt-1">Socios pre-registrados</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">14+</div>
                    <div class="text-gray-400 text-sm mt-1">Países de Latinoamérica</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">4</div>
                    <div class="text-gray-400 text-sm mt-1">Herramientas de IA incluidas</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">100%</div>
                    <div class="text-gray-400 text-sm mt-1">Digital y desde casa</div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== EL GANCHO ===== -->
    <section class="py-16 lg:py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="order-2 md:order-1 reveal-left flex justify-center">
                    <img src="/preregistro/new/imagenes/arbol.png"
                         alt="Árbol de crecimiento que simboliza el desarrollo de tu red de ingresos"
                         class="w-3/4 max-w-xs object-contain img-glow"
                         loading="lazy" width="400" height="400">
                </div>
                <div class="order-1 md:order-2 reveal-right">
                    <div class="card-dark p-8 md:p-10 rounded-2xl">
                        <i class="fa-solid fa-quote-left text-4xl text-primary-500/30 mb-6 block"></i>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold leading-relaxed text-white mb-6">
                            "No es solo un curso, es tu propio negocio de <span class="gradient-text">E-commerce Educativo</span>. Construye una red donde ganas por cada productor, distribuidor y consumidor. Ingresos residuales reales, impulsados por tecnología."
                        </h2>
                        <a href="#registro" class="btn-primary inline-flex justify-center items-center px-7 py-3.5 rounded-xl shadow-lg text-base font-black">
                            Crear mi propio negocio <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== CÓMO FUNCIONA ===== -->
    <section id="como-funciona" class="py-16 lg:py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-500 text-sm font-bold uppercase tracking-widest">Proceso simple</span>
                <h2 class="text-3xl md:text-5xl font-black text-white mt-2 mb-4">¿Cómo funciona?</h2>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">En 3 pasos estarás dentro del ecosistema y generando ingresos.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 reveal">
                <!-- Step 1 -->
                <div class="card-dark rounded-2xl p-8 text-center relative">
                    <div class="w-14 h-14 rounded-full bg-primary-500/15 border border-primary-500/30 flex items-center justify-center mx-auto mb-5">
                        <span class="text-2xl font-black text-primary-400">1</span>
                    </div>
                    <h3 class="text-xl font-black text-white mb-3">Te registras gratis</h3>
                    <p class="text-gray-400 leading-relaxed">Completa el formulario en menos de 60 segundos y reserva tu posición de Socio Fundador sin costo.</p>
                </div>
                <!-- Step 2 -->
                <div class="card-dark rounded-2xl p-8 text-center relative" style="border-color: rgba(11,245,11,0.2); background: rgba(11,245,11,0.04);">
                    <div class="w-14 h-14 rounded-full bg-primary-500/20 border border-primary-500/40 flex items-center justify-center mx-auto mb-5">
                        <span class="text-2xl font-black text-primary-400">2</span>
                    </div>
                    <h3 class="text-xl font-black text-white mb-3">Recibes acceso prioritario</h3>
                    <p class="text-gray-400 leading-relaxed">Te contactamos por WhatsApp con tu acceso exclusivo a las 4 herramientas de IA y al ecosistema completo.</p>
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-dark-900 text-xs font-black px-3 py-1 rounded-full">MÁS POPULAR</div>
                </div>
                <!-- Step 3 -->
                <div class="card-dark rounded-2xl p-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-primary-500/15 border border-primary-500/30 flex items-center justify-center mx-auto mb-5">
                        <span class="text-2xl font-black text-primary-400">3</span>
                    </div>
                    <h3 class="text-xl font-black text-white mb-3">Construyes tu red</h3>
                    <p class="text-gray-400 leading-relaxed">Usas las herramientas para crear cursos, atraer leads y automatizar ventas. Tu negocio crece en piloto automático.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== PARA QUIÉN ES ===== -->
    <section class="py-16 lg:py-24 relative z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-500 text-sm font-bold uppercase tracking-widest">Identifícate</span>
                <h2 class="text-3xl md:text-5xl font-black text-white mt-2 mb-4">¿Es esto para ti?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 reveal">
                <!-- SÍ es para ti -->
                <div class="card-dark rounded-2xl p-8" style="border-color: rgba(11,245,11,0.2);">
                    <h3 class="text-lg font-black text-primary-400 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i> SÍ es para ti si...
                    </h3>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-300">Quieres generar ingresos desde casa sin depender de un jefe</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-300">Eres emprendedor digital o quieres serlo</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-300">Tienes conocimientos o experiencia que quieres monetizar</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-300">Buscas ingresos residuales que trabajen mientras duermes</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-300">Quieres aprovechar la IA para escalar tu negocio</span></div>
                </div>
                <!-- NO es para ti -->
                <div class="card-dark rounded-2xl p-8" style="border-color: rgba(239,68,68,0.15);">
                    <h3 class="text-lg font-black text-red-400 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark"></i> NO es para ti si...
                    </h3>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-400">Buscas hacerte rico de la noche a la mañana sin esfuerzo</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-400">No estás dispuesto a aprender y aplicar nuevas herramientas</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-400">Prefieres un salario fijo sin asumir ningún riesgo</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-500 mt-0.5 flex-shrink-0"></i><span class="text-gray-400">No tienes tiempo para dedicar al menos 1 hora diaria</span></div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>


    <!-- ===== HERRAMIENTAS ===== -->
    <section id="herramientas" class="py-16 lg:py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-500 text-sm font-bold uppercase tracking-widest">Arsenal tecnológico</span>
                <h2 class="text-3xl md:text-5xl font-black text-white mt-2 mb-4">
                    Herramientas de <span class="gradient-text">Growth Marketing</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">Al reservar tu cupo hoy, desbloqueas acceso inmediato a estas 4 herramientas de IA.</p>
            </div>

            <!-- Grid 2x2 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8 reveal">

                <!-- Herramienta 1 -->
                <div class="card-dark rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-5 mb-5">
                        <div class="w-14 h-14 bg-primary-500/10 rounded-xl flex items-center justify-center border border-primary-500/20 flex-shrink-0 group-hover:bg-primary-500/20 transition-colors">
                            <i class="fa-solid fa-robot text-2xl text-primary-400"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-500 font-bold uppercase tracking-wider mb-1">Herramienta 01</div>
                            <h3 class="text-xl font-black text-white">IA Content Agent</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/agente.png" alt="IA Content Agent para crear cursos" class="w-full h-40 object-contain mb-5 img-glow" loading="lazy">
                    <p class="text-gray-400 leading-relaxed mb-5">Crea cursos online en minutos con inteligencia artificial. Lanza contenido educativo de calidad <strong class="text-white">10 veces más rápido</strong>.</p>
                    <a href="#registro" class="inline-flex items-center text-primary-400 font-semibold text-sm hover:text-primary-300 transition-colors group-hover:gap-3 gap-2">
                        Quiero esta herramienta <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 2 -->
                <div class="card-dark rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-5 mb-5">
                        <div class="w-14 h-14 bg-primary-500/10 rounded-xl flex items-center justify-center border border-primary-500/20 flex-shrink-0 group-hover:bg-primary-500/20 transition-colors">
                            <i class="fa-solid fa-mobile-screen text-2xl text-primary-400"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-500 font-bold uppercase tracking-wider mb-1">Herramienta 02</div>
                            <h3 class="text-xl font-black text-white">App Academia Mobile</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/app.png" alt="App Academia Mobile" class="w-full h-40 object-contain mb-5 img-glow" loading="lazy">
                    <p class="text-gray-400 leading-relaxed mb-5">Gestiona ventas, métricas y comisiones desde tu celular. <strong class="text-white">Todo tu negocio en la palma de tu mano</strong>, disponible 24/7.</p>
                    <a href="#registro" class="inline-flex items-center text-primary-400 font-semibold text-sm hover:text-primary-300 transition-colors group-hover:gap-3 gap-2">
                        Asegurar mi acceso <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 3 -->
                <div class="card-dark rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-5 mb-5">
                        <div class="w-14 h-14 bg-primary-500/10 rounded-xl flex items-center justify-center border border-primary-500/20 flex-shrink-0 group-hover:bg-primary-500/20 transition-colors">
                            <i class="fa-solid fa-magnet text-2xl text-primary-400"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-500 font-bold uppercase tracking-wider mb-1">Herramienta 03</div>
                            <h3 class="text-xl font-black text-white">App LeadBoost</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/lead.png" alt="App LeadBoost para atraer clientes" class="w-full h-40 object-contain mb-5 img-glow" loading="lazy">
                    <p class="text-gray-400 leading-relaxed mb-5">Atrae clientes calificados con contenido magnético. <strong class="text-white">Convierte seguidores en socios</strong> sin perseguir a nadie.</p>
                    <a href="#registro" class="inline-flex items-center text-primary-400 font-semibold text-sm hover:text-primary-300 transition-colors group-hover:gap-3 gap-2">
                        Desbloquear LeadBoost <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 4 -->
                <div class="card-dark rounded-2xl p-6 lg:p-8 group" style="border-color: rgba(11,245,11,0.2); background: rgba(11,245,11,0.03);">
                    <div class="flex items-start gap-5 mb-5">
                        <div class="w-14 h-14 bg-primary-500/15 rounded-xl flex items-center justify-center border border-primary-500/30 flex-shrink-0 group-hover:bg-primary-500/25 transition-colors">
                            <i class="fa-solid fa-filter-circle-dollar text-2xl text-primary-400"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-500 font-bold uppercase tracking-wider mb-1">Herramienta 04</div>
                            <h3 class="text-xl font-black text-white">Smart Funnels con IA</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/smart.png" alt="Smart Funnels con IA" class="w-full h-40 object-contain mb-5 img-glow" loading="lazy">
                    <p class="text-gray-400 leading-relaxed mb-5">Embudos automatizados que asesoran, educan y cierran ventas por ti. <strong class="text-white">Tu negocio escalando en piloto automático</strong>.</p>
                    <a href="#registro" class="inline-flex items-center text-primary-400 font-semibold text-sm hover:text-primary-300 transition-colors group-hover:gap-3 gap-2">
                        Automatizar mis ventas <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>


    <!-- ===== TESTIMONIOS ===== -->
    <section id="testimonios" class="py-16 lg:py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-500 text-sm font-bold uppercase tracking-widest">Resultados reales</span>
                <h2 class="text-3xl md:text-5xl font-black text-white mt-2 mb-4">Lo que dicen nuestros socios</h2>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">Personas reales que ya están construyendo su red con Promolíder.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 reveal">

                <!-- Testimonio 1 -->
                <div class="testimonial-card">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-6">"En menos de 3 meses ya tenía mi primer curso publicado y mis primeras comisiones. La herramienta de IA me ahorró semanas de trabajo. Esto es real."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-500/20 border border-primary-500/30 flex items-center justify-center text-sm font-black text-primary-400">MG</div>
                        <div>
                            <div class="text-white font-bold text-sm">María García</div>
                            <div class="text-gray-500 text-xs">Lima, Perú · Socia Fundadora</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonio 2 -->
                <div class="testimonial-card" style="border-color: rgba(11,245,11,0.2);">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-6">"Llevaba años buscando algo así. El ecosistema combina todo lo que necesitas: formación, tecnología y una red de apoyo increíble. Mi ingreso pasivo ya supera mi sueldo anterior."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-sm font-black text-blue-400">CR</div>
                        <div>
                            <div class="text-white font-bold text-sm">Carlos Rodríguez</div>
                            <div class="text-gray-500 text-xs">Bogotá, Colombia · Socio Fundador</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonio 3 -->
                <div class="testimonial-card">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-6">"El LeadBoost cambió todo para mí. Antes perseguía clientes, ahora ellos me buscan a mí. En 60 días construí una red de 47 distribuidores activos."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-sm font-black text-purple-400">AP</div>
                        <div>
                            <div class="text-white font-bold text-sm">Andrea Pérez</div>
                            <div class="text-gray-500 text-xs">Ciudad de México · Socia Fundadora</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== FORMULARIO FINAL ===== -->
    <section id="registro" class="py-16 lg:py-24 relative z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- CTA Banner -->
            <div class="text-center mb-10 reveal">
                <span class="text-primary-500 text-sm font-bold uppercase tracking-widest">Última oportunidad</span>
                <h2 class="text-3xl md:text-5xl font-black text-white mt-2 mb-4">
                    Asegura tu lugar como<br><span class="gradient-text">Socio Fundador</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">Las posiciones son limitadas. Una vez cerrado el pre-registro, el precio de entrada aumenta.</p>
            </div>

            <div class="reveal">
                <div class="form-card rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

                    <!-- Left panel -->
                    <div class="w-full md:w-5/12 relative hidden md:flex flex-col justify-end p-10"
                         style="background: linear-gradient(135deg, rgba(11,245,11,0.12) 0%, rgba(0,168,0,0.06) 100%), url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;">
                        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(10,10,10,0.95) 0%, rgba(10,10,10,0.5) 60%, transparent 100%);"></div>
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-500/20 border border-primary-500/40 mb-5 animate-float">
                                <i class="fa-solid fa-lock text-3xl text-primary-400"></i>
                            </div>
                            <h3 class="text-3xl font-black text-white mb-3">Reserva Exclusiva</h3>
                            <p class="text-gray-300 text-base mb-6">Asegura hoy tu lugar antes del cierre definitivo.</p>
                            <div class="inline-flex items-center gap-2 bg-primary-500/15 border border-primary-500/30 rounded-xl px-4 py-2.5">
                                <span class="w-2 h-2 rounded-full bg-primary-400 animate-pulse"></span>
                                <span class="text-white text-sm font-bold"><span class="counter-number" id="formCounter">1,247</span> socios pre-registrados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Form -->
                    <div class="w-full md:w-7/12 p-7 sm:p-10">
                        <div class="md:hidden text-center mb-8">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-500/15 border border-primary-500/30 mb-3 animate-float">
                                <i class="fa-solid fa-lock text-xl text-primary-400"></i>
                            </div>
                            <h2 class="text-2xl font-black text-white mb-1">Reserva Exclusiva</h2>
                            <p class="text-primary-400 text-sm font-semibold">Estás a un paso de asegurar tu lugar.</p>
                        </div>
                        <div class="hidden md:block mb-7">
                            <h2 class="text-2xl font-black text-white mb-1">Completa tu registro</h2>
                            <p class="text-gray-500 text-sm">Todos los campos son requeridos.</p>
                        </div>
                        <form class="form-registro space-y-5" novalidate>
                            <div>
                                <label for="nombre_final" class="block text-sm font-semibold text-gray-300 mb-2">Nombre</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-500"></i>
                                    </div>
                                    <input type="text" id="nombre_final" name="nombre" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu nombre">
                                </div>
                                <p class="error-msg">Por favor ingresa tu nombre</p>
                            </div>
                            <div>
                                <label for="apellido_final" class="block text-sm font-semibold text-gray-300 mb-2">Apellido</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-500"></i>
                                    </div>
                                    <input type="text" id="apellido_final" name="apellido" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu apellido">
                                </div>
                                <p class="error-msg">Por favor ingresa tu apellido</p>
                            </div>
                            <div>
                                <label for="whatsapp_final" class="block text-sm font-semibold text-gray-300 mb-2 flex justify-between">
                                    <span>WhatsApp</span>
                                    <span class="text-primary-400 text-xs bg-primary-500/10 px-2 py-0.5 rounded-md border border-primary-500/20">Acceso prioritario</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-brands fa-whatsapp text-gray-500"></i>
                                    </div>
                                    <input type="tel" id="whatsapp_final" name="whatsapp" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="+1 (555) 000-0000">
                                </div>
                                <p class="error-msg">Por favor ingresa un número de WhatsApp válido</p>
                            </div>
                            <div>
                                <label for="email_final" class="block text-sm font-semibold text-gray-300 mb-2">Correo Electrónico</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-envelope text-gray-500"></i>
                                    </div>
                                    <input type="email" id="email_final" name="email" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="tu@correo.com">
                                </div>
                                <p class="error-msg">Por favor ingresa un correo electrónico válido</p>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn-primary w-full flex justify-center items-center py-4 px-8 rounded-xl shadow-lg text-base font-black">
                                    ACCEDER AL ECOSISTEMA <i class="fa-solid fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                            <p class="text-xs text-center text-gray-600 mt-3">
                                <i class="fa-solid fa-shield-halved mr-1 text-primary-600"></i> Tus datos están seguros. Cero spam. Cancela cuando quieras.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-white/5 relative z-10 pt-14 pb-8" style="background: rgba(0,0,0,0.4);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">

                <!-- Col 1 -->
                <div class="flex flex-col items-center md:items-start">
                    <img src="/preregistro/new/imagenes/logo promolider negro.png" alt="Logo Promolíder" class="h-10 md:h-12 w-auto brightness-0 invert mb-5" width="140" height="48">
                    <p class="text-gray-500 text-sm leading-relaxed text-center md:text-left">
                        Juntos lograremos monetizar tus conocimientos y experiencia. Únete a la red de emprendedores digitales más potente de Latinoamérica.
                    </p>
                </div>

                <!-- Col 2 -->
                <div class="flex flex-col items-center md:items-start">
                    <h4 class="text-white font-bold text-base mb-4">Contáctame Tu patrocinador</h4>

                    <p class="text-gray-300 font-semibold text-sm mb-3">
                        <i class="fa-solid fa-user text-primary-400 mr-2"></i>
                        {{ $nombre_referidor }} {{ $apellido_referidor }}
                    </p>

                    <a href="mailto:{{ $correo_referidor }}"
                    class="text-gray-400 hover:text-primary-400 transition-colors flex items-center gap-3 text-sm">
                        <i class="fa-regular fa-envelope text-primary-400"></i>
                        {{ $correo_referidor }}
                    </a>

                    <a href="tel:{{ $telefono_referidor }}"
                    class="text-gray-400 hover:text-primary-400 transition-colors flex items-center gap-3 text-sm mt-2">
                        <i class="fa-solid fa-phone text-primary-400"></i>
                        {{ $telefono_referidor }}
                    </a>
                </div>

                <!-- Col 3 -->
                <div class="flex flex-col items-center md:items-end">
                    <h4 class="text-white font-bold text-base mb-5">Síguenos</h4>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/profile.php?id=100063926738412" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 hover:border-primary-500/30 transition-all" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f text-sm"></i>
                        </a>
                        <a href="https://www.instagram.com/promoliderorg/" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 hover:border-primary-500/30 transition-all" aria-label="Instagram">
                            <i class="fa-brands fa-instagram text-sm"></i>
                        </a>
                        <a href="https://www.linkedin.com/company/promol%C3%ADder/" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 hover:border-primary-500/30 transition-all" aria-label="LinkedIn">
                            <i class="fa-brands fa-linkedin-in text-sm"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCj-NmJXOnJt55aDRh1R0LLg" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 hover:border-primary-500/30 transition-all" aria-label="YouTube">
                            <i class="fa-brands fa-youtube text-sm"></i>
                        </a>
                        <a href="https://www.tiktok.com/@promolider" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 hover:border-primary-500/30 transition-all" aria-label="TikTok">
                            <i class="fa-brands fa-tiktok text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-600 text-sm">&copy; 2026 PromoLider. Todos los derechos reservados.</p>
                <div class="flex gap-5 text-sm text-gray-600">
                    <a href="#" class="hover:text-primary-400 transition-colors">Términos y Condiciones</a>
                    <a href="#" class="hover:text-primary-400 transition-colors">Políticas de Privacidad</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 left-4 sm:left-auto z-50 pointer-events-none"></div>

    <!-- External JavaScript -->
    <script>
        window.USERNAME = @json($username);
        window.LADO = @json($lado);
    </script>
    <script src="/preregistro/new/landing.js"></script>

    <!-- Countdown Timer Script -->
    <script>
        // Set deadline: 2 days, 14 hours, 37 minutes from now
        const deadline = new Date();
        deadline.setDate(deadline.getDate() + 2);
        deadline.setHours(deadline.getHours() + 14);
        deadline.setMinutes(deadline.getMinutes() + 37);

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = deadline.getTime() - now;
            if (distance <= 0) {
                document.getElementById('cd-days').textContent = '00';
                document.getElementById('cd-hours').textContent = '00';
                document.getElementById('cd-mins').textContent = '00';
                document.getElementById('cd-secs').textContent = '00';
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById('cd-days').textContent = String(days).padStart(2, '0');
            document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('cd-mins').textContent = String(mins).padStart(2, '0');
            document.getElementById('cd-secs').textContent = String(secs).padStart(2, '0');
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>

</body>
</html>
