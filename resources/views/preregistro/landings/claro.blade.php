<!DOCTYPE html>
<html lang="es-PE" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
    <link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                        }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                        'ticker': 'ticker 30s linear infinite',
                        'ping-slow': 'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
                    },
                    keyframes: {
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
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
            background-color: #f8faf9;
            color: #1f2937;
            overflow-x: hidden;
        }

        /* ── Scroll progress ── */
        .scroll-progress {
            position: fixed; top: 0; left: 0; height: 3px; width: 0%;
            background: linear-gradient(90deg, #0BF50B, #008400);
            z-index: 9999; transition: width 0.1s linear;
        }

        /* ── Navbar ── */
        .nav-light {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,168,0,0.1);
            box-shadow: 0 1px 12px rgba(0,0,0,0.06);
        }

        /* ── Hero background ── */
        .hero-bg {
            background:
                radial-gradient(ellipse 70% 50% at 50% -5%, rgba(11,245,11,0.10) 0%, transparent 65%),
                radial-gradient(ellipse 50% 40% at 90% 60%, rgba(0,168,0,0.06) 0%, transparent 55%),
                #f8faf9;
        }
        .grid-pattern {
            background-image:
                linear-gradient(rgba(0,168,0,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,168,0,0.05) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        /* ── Ambient blobs ── */
        .ambient-blob {
            position: absolute; border-radius: 50%;
            filter: blur(100px); pointer-events: none; z-index: 0;
        }

        /* ── Typography ── */
        .gradient-text {
            background: linear-gradient(135deg, #008400 0%, #0BF50B 60%, #00a800 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* ── Badge pulse ── */
        .badge-pulse::before {
            content: ''; position: absolute; width: 100%; height: 100%;
            border-radius: 9999px; background: #0BF50B;
            animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite; opacity: 0.35;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: linear-gradient(135deg, #0BF50B 0%, #00a800 100%);
            color: #fff; font-weight: 800;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .btn-primary::after {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
            animation: shimmer 2s linear infinite; background-size: 200% 100%;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 36px -6px rgba(11,245,11,0.35); }

        .btn-outline {
            background: #fff; color: #374151; font-weight: 700;
            border: 1.5px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .btn-outline:hover { border-color: #00a800; color: #008400; box-shadow: 0 4px 16px rgba(0,168,0,0.1); }

        /* ── Cards ── */
        .card-light {
            background: #fff;
            border: 1px solid #f0fdf4;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .card-light:hover {
            border-color: #aeffc8;
            box-shadow: 0 12px 32px rgba(0,168,0,0.1);
            transform: translateY(-4px);
        }
        .card-accent {
            background: linear-gradient(135deg, #edfff3 0%, #f0fff4 100%);
            border: 1.5px solid #aeffc8;
            box-shadow: 0 4px 20px rgba(11,245,11,0.08);
            transition: all 0.3s ease;
        }
        .card-accent:hover { box-shadow: 0 14px 36px rgba(11,245,11,0.14); transform: translateY(-4px); }

        /* ── Inputs ── */
        .input-field {
            background: #f9fafb; border: 1.5px solid #e5e7eb;
            color: #111827; transition: all 0.3s ease;
        }
        .input-field::placeholder { color: #9ca3af; }
        .input-field:focus {
            outline: none; border-color: #0BF50B;
            box-shadow: 0 0 0 3px rgba(11,245,11,0.12);
            background: #fff;
        }
        .input-field.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.08); }
        .error-msg { color: #ef4444; font-size: 0.75rem; margin-top: 4px; display: none; }
        .error-msg.show { display: block; }

        /* ── Reveal animations ── */
        .reveal { opacity: 0; transform: translateY(28px); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-36px); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
        .reveal-left.active { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(36px); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
        .reveal-right.active { opacity: 1; transform: translateX(0); }

        /* ── Section divider ── */
        .section-divider { height: 1px; background: linear-gradient(90deg, transparent, #aeffc8, #0BF50B, #aeffc8, transparent); opacity: 0.4; }

        /* ── Ticker ── */
        .ticker-wrapper { overflow: hidden; white-space: nowrap; }
        .ticker-track { display: inline-flex; animation: ticker 32s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }

        /* ── Countdown ── */
        .countdown-box {
            background: #fff; border: 1.5px solid #d5ffe2;
            border-radius: 14px; padding: 10px 18px; text-align: center; min-width: 68px;
            box-shadow: 0 2px 10px rgba(0,168,0,0.08);
        }
        .countdown-num { font-size: 2rem; font-weight: 900; color: #008400; line-height: 1; }
        .countdown-label { font-size: 0.62rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 3px; }

        /* ── Stat card ── */
        .stat-card {
            background: #fff; border: 1.5px solid #d5ffe2;
            border-radius: 16px; padding: 24px; text-align: center;
            box-shadow: 0 2px 12px rgba(0,168,0,0.07);
            transition: all 0.3s ease;
        }
        .stat-card:hover { box-shadow: 0 10px 28px rgba(0,168,0,0.13); transform: translateY(-3px); }

        /* ── Step number ── */
        .step-num {
            width: 52px; height: 52px; border-radius: 50%;
            background: linear-gradient(135deg, #d5ffe2, #aeffc8);
            border: 2px solid #70ff9e;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; font-weight: 900; color: #008400;
            margin: 0 auto 20px;
        }

        /* ── For whom ── */
        .check-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 11px; }

        /* ── Image ── */
        .img-soft {
            filter: drop-shadow(0 8px 24px rgba(0,168,0,0.12));
            transition: filter 0.3s ease;
        }
        .img-soft:hover { filter: drop-shadow(0 14px 36px rgba(0,168,0,0.22)); }

        /* ── Toast ── */
        .toast { transform: translateY(20px); opacity: 0; transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
        .toast.show { transform: translateY(0); opacity: 1; }

        /* ── Counter ── */
        .counter-number { font-variant-numeric: tabular-nums; }

        /* ── Screen reader ── */
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border-width: 0; }
    </style>
</head>
<body class="antialiased">

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- ===== NAVBAR ===== -->
    <nav class="nav-light fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 md:h-18 flex items-center justify-between">
            <img src="/preregistro/new/imagenes/logo promolider negro.png" alt="Logo Promolíder" class="h-9 md:h-12 w-auto" width="140" height="48">
            <div class="hidden md:flex items-center gap-8">
                <a href="#como-funciona" class="text-gray-500 hover:text-primary-700 transition text-sm font-medium">Cómo funciona</a>
                <a href="#herramientas" class="text-gray-500 hover:text-primary-700 transition text-sm font-medium">Herramientas</a>
                <a href="#registro" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-bold">Registrarme Gratis</a>
            </div>
            <a href="#registro" class="md:hidden btn-primary px-4 py-2 rounded-lg text-xs font-bold">Registrarme</a>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <header class="hero-bg grid-pattern relative pt-28 pb-16 lg:pt-36 lg:pb-24 overflow-hidden">
        <!-- Ambient blobs -->
        <div class="ambient-blob w-96 h-96 bg-primary-200 opacity-30 top-0 left-0 -translate-x-1/3 -translate-y-1/3"></div>
        <div class="ambient-blob w-80 h-80 bg-primary-300 opacity-20 top-1/2 right-0 translate-x-1/3 -translate-y-1/2"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">

                <!-- Text Column -->
                <div class="lg:col-span-7 text-center lg:text-left flex flex-col items-center lg:items-start">

                    <!-- Badge -->
                    <div class="reveal inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 border border-primary-200 text-primary-800 text-xs sm:text-sm font-bold mb-6">
                        <span class="relative w-2 h-2 rounded-full bg-primary-500 badge-pulse"></span>
                        PRE-REGISTRO EXCLUSIVO — CUPOS LIMITADOS
                    </div>

                    <!-- Headline -->
                    <h1 class="reveal text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-black tracking-tight mb-5 leading-[1.05] text-gray-900">
                        El Ecosistema Digital que está
                        <span class="gradient-text block mt-1">redefiniendo los ingresos residuales.</span>
                    </h1>

                    <p class="reveal text-base sm:text-lg md:text-xl text-gray-500 mb-7 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Fusionamos <strong class="text-gray-800">E-learning, IA y Network Marketing</strong> para crear la red de distribución más potente de Latinoamérica.
                    </p>

                    <!-- Countdown -->
                    <div class="reveal mb-8 w-full max-w-lg mx-auto lg:mx-0">
                        <p class="text-xs text-gray-400 uppercase tracking-widest mb-3 text-center lg:text-left font-semibold">⏳ Cierre del pre-registro en:</p>
                        <div class="flex items-center gap-3 justify-center lg:justify-start">
                            <div class="countdown-box"><div class="countdown-num" id="cd-days">02</div><div class="countdown-label">Días</div></div>
                            <span class="text-primary-600 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-hours">14</div><div class="countdown-label">Horas</div></div>
                            <span class="text-primary-600 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-mins">37</div><div class="countdown-label">Min</div></div>
                            <span class="text-primary-600 text-2xl font-black">:</span>
                            <div class="countdown-box"><div class="countdown-num" id="cd-secs">00</div><div class="countdown-label">Seg</div></div>
                        </div>
                    </div>

                    <!-- Social proof counter -->
                    <div class="reveal flex items-center gap-4 mb-8 bg-white/90 backdrop-blur-sm rounded-2xl px-5 py-3 border border-gray-100 shadow-sm w-fit mx-auto lg:mx-0">
                        <div class="flex -space-x-2">
                            <div class="w-9 h-9 rounded-full bg-primary-100 border-2 border-white flex items-center justify-center text-xs font-bold text-primary-700">M</div>
                            <div class="w-9 h-9 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-xs font-bold text-blue-700">C</div>
                            <div class="w-9 h-9 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center text-xs font-bold text-amber-700">R</div>
                            <div class="w-9 h-9 rounded-full bg-purple-100 border-2 border-white flex items-center justify-center text-xs font-bold text-purple-700">A</div>
                            <div class="w-9 h-9 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-bold text-gray-500">+</div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900"><span class="counter-number text-primary-700" id="heroCounter">1,247</span> personas ya se registraron</p>
                            <p class="text-xs text-gray-400">en las últimas 48 horas</p>
                        </div>
                    </div>

                    <div class="reveal flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                        <a href="#registro" class="btn-primary w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 text-base font-black rounded-xl shadow-lg">
                            RESERVAR MI POSICIÓN GRATIS <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#como-funciona" class="btn-outline w-full sm:w-auto inline-flex justify-center items-center px-6 py-4 text-base rounded-xl">
                            Ver cómo funciona <i class="fa-solid fa-arrow-down ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Form Column -->
                <div class="lg:col-span-5 relative w-full max-w-md mx-auto mt-8 lg:mt-0 reveal-right">
                    <div class="absolute -inset-4 bg-primary-300/20 blur-3xl rounded-full"></div>
                    <div class="relative z-10 bg-white p-6 sm:p-8 rounded-2xl shadow-2xl border border-primary-100">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-50 border border-primary-200 mb-3">
                                <i class="fa-solid fa-lock text-primary-700 text-lg"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-gray-900 mb-1">Comienza Ahora</h3>
                            <p class="text-gray-500 text-sm">Reserva tu posición <strong class="text-gray-900">GRATIS</strong> por 7 días.</p>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-xs text-primary-700 bg-primary-50 px-3 py-1.5 rounded-full border border-primary-200">
                                <i class="fa-solid fa-fire text-primary-500 animate-pulse"></i>
                                <span id="recentSignup">Alguien de Bogotá se acaba de registrar</span>
                            </div>
                        </div>
                        <form class="form-registro space-y-4" novalidate>
                            <div>
                                <label for="nombre_hero" class="sr-only">Nombre</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text" id="nombre_hero" name="nombre" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu nombre">
                                </div>
                                <p class="error-msg">Por favor ingresa tu nombre</p>
                            </div>
                            <div>
                                <label for="apellido_hero" class="sr-only">Apellido</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-400 text-sm"></i>
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
                                        <i class="fa-brands fa-whatsapp text-gray-400 text-sm"></i>
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
                                        <i class="fa-regular fa-envelope text-gray-400 text-sm"></i>
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
                            <p class="text-xs text-center text-gray-400 mt-2">
                                <i class="fa-solid fa-shield-halved mr-1 text-primary-600"></i> Tus datos están seguros. Cero spam.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== TICKER ===== -->
    <div class="bg-primary-50 border-y border-primary-200 py-3 overflow-hidden">
        <div class="ticker-wrapper">
            <div class="ticker-track">
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> María G. de Lima se registró hace 2 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Carlos M. de Bogotá se registró hace 5 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Rosa P. de Ciudad de México se registró hace 7 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Diego F. de Santiago se registró hace 9 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Ana R. de Quito se registró hace 11 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Luis V. de Buenos Aires se registró hace 14 min</span>
                <!-- duplicate for seamless loop -->
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> María G. de Lima se registró hace 2 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Carlos M. de Bogotá se registró hace 5 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Rosa P. de Ciudad de México se registró hace 7 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Diego F. de Santiago se registró hace 9 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Ana R. de Quito se registró hace 11 min</span>
                <span class="inline-flex items-center gap-2 px-8 text-sm text-primary-800 font-medium"><i class="fa-solid fa-circle-check text-primary-600"></i> Luis V. de Buenos Aires se registró hace 14 min</span>
            </div>
        </div>
    </div>


    <!-- ===== STATS BAR ===== -->
    <section class="py-12 lg:py-16 bg-white relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 reveal">
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text counter-number">1,247</div>
                    <div class="text-gray-500 text-sm mt-1">Socios pre-registrados</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">14+</div>
                    <div class="text-gray-500 text-sm mt-1">Países de Latinoamérica</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">4</div>
                    <div class="text-gray-500 text-sm mt-1">Herramientas de IA incluidas</div>
                </div>
                <div class="stat-card">
                    <div class="text-3xl lg:text-4xl font-black gradient-text">100%</div>
                    <div class="text-gray-500 text-sm mt-1">Digital y desde casa</div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== EL GANCHO ===== -->
    <section class="py-16 lg:py-24 relative z-10 bg-white/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="order-2 md:order-1 reveal-left flex justify-center">
                    <img src="/preregistro/new/imagenes/arbol.png"
                         alt="Árbol de crecimiento que simboliza el desarrollo de tu red de ingresos"
                         class="w-3/4 max-w-xs object-contain img-soft"
                         loading="lazy" width="400" height="400">
                </div>
                <div class="order-1 md:order-2 reveal-right">
                    <div class="card-accent p-8 md:p-10 rounded-2xl">
                        <i class="fa-solid fa-quote-left text-4xl text-primary-300 mb-6 block"></i>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold leading-relaxed text-gray-800 mb-6">
                            "No es solo un curso, es tu propio negocio de <span class="gradient-text font-black">E-commerce Educativo</span>. Construye una red donde ganas por cada productor, distribuidor y consumidor. Ingresos residuales reales, impulsados por tecnología."
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
    <section id="como-funciona" class="py-16 lg:py-24 relative z-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-700 text-sm font-bold uppercase tracking-widest">Proceso simple</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 mt-2 mb-4">¿Cómo funciona?</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">En 3 pasos estarás dentro del ecosistema y generando ingresos.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 reveal">

                <!-- Step 1 -->
                <div class="card-light rounded-2xl p-8 text-center">
                    <div class="step-num">1</div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">Te registras gratis</h3>
                    <p class="text-gray-500 leading-relaxed">Completa el formulario en menos de 60 segundos y reserva tu posición de Socio Fundador sin costo.</p>
                </div>

                <!-- Step 2 (destacado) -->
                <div class="card-accent rounded-2xl p-8 text-center relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-xs font-black px-3 py-1 rounded-full shadow-md">MÁS POPULAR</div>
                    <div class="step-num" style="background: linear-gradient(135deg,#aeffc8,#70ff9e); border-color:#0BF50B;">2</div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">Recibes acceso prioritario</h3>
                    <p class="text-gray-500 leading-relaxed">Te contactamos por WhatsApp con tu acceso exclusivo a las 4 herramientas de IA y al ecosistema completo.</p>
                </div>

                <!-- Step 3 -->
                <div class="card-light rounded-2xl p-8 text-center">
                    <div class="step-num">3</div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">Construyes tu red</h3>
                    <p class="text-gray-500 leading-relaxed">Usas las herramientas para crear cursos, atraer leads y automatizar ventas. Tu negocio crece en piloto automático.</p>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ===== PARA QUIÉN ES ===== -->
    <section class="py-16 lg:py-24 relative z-10 bg-white/60">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-700 text-sm font-bold uppercase tracking-widest">Identifícate</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 mt-2 mb-4">¿Es esto para ti?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 reveal">

                <!-- SÍ -->
                <div class="card-accent rounded-2xl p-8">
                    <h3 class="text-lg font-black text-primary-800 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-primary-600"></i> SÍ es para ti si...
                    </h3>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-600 mt-0.5 flex-shrink-0"></i><span class="text-gray-700">Quieres generar ingresos desde casa sin depender de un jefe</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-600 mt-0.5 flex-shrink-0"></i><span class="text-gray-700">Eres emprendedor digital o quieres serlo</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-600 mt-0.5 flex-shrink-0"></i><span class="text-gray-700">Tienes conocimientos o experiencia que quieres monetizar</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-600 mt-0.5 flex-shrink-0"></i><span class="text-gray-700">Buscas ingresos residuales que trabajen mientras duermes</span></div>
                    <div class="check-item"><i class="fa-solid fa-check text-primary-600 mt-0.5 flex-shrink-0"></i><span class="text-gray-700">Quieres aprovechar la IA para escalar tu negocio</span></div>
                </div>

                <!-- NO -->
                <div class="card-light rounded-2xl p-8" style="border-color:#fee2e2;">
                    <h3 class="text-lg font-black text-red-600 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-red-500"></i> NO es para ti si...
                    </h3>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-400 mt-0.5 flex-shrink-0"></i><span class="text-gray-500">Buscas hacerte rico de la noche a la mañana sin esfuerzo</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-400 mt-0.5 flex-shrink-0"></i><span class="text-gray-500">No estás dispuesto a aprender y aplicar nuevas herramientas</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-400 mt-0.5 flex-shrink-0"></i><span class="text-gray-500">Prefieres un salario fijo sin asumir ningún riesgo</span></div>
                    <div class="check-item"><i class="fa-solid fa-xmark text-red-400 mt-0.5 flex-shrink-0"></i><span class="text-gray-500">No tienes tiempo para dedicar al menos 1 hora diaria</span></div>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>


    <!-- ===== HERRAMIENTAS ===== -->
    <section id="herramientas" class="py-16 lg:py-24 relative z-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-primary-700 text-sm font-bold uppercase tracking-widest">Arsenal tecnológico</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 mt-2 mb-4">
                    Herramientas de <span class="gradient-text">Growth Marketing</span>
                </h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">Al reservar tu cupo hoy, desbloqueas acceso inmediato a estas 4 herramientas de IA.</p>
            </div>

            <!-- Grid 2×2 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8 reveal">

                <!-- Herramienta 1 -->
                <div class="card-light rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-14 h-14 bg-primary-50 rounded-xl flex items-center justify-center border border-primary-100 flex-shrink-0 group-hover:bg-primary-100 transition-colors">
                            <i class="fa-solid fa-robot text-2xl text-primary-700"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-600 font-bold uppercase tracking-wider mb-1">Herramienta 01</div>
                            <h3 class="text-xl font-black text-gray-900">IA Content Agent</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/agente.png" alt="IA Content Agent para crear cursos"
                         class="w-full h-40 object-contain mb-5 img-soft" loading="lazy">
                    <p class="text-gray-500 leading-relaxed mb-5">Crea cursos online en minutos con inteligencia artificial. Lanza contenido educativo de calidad <strong class="text-gray-800">10 veces más rápido</strong>.</p>
                    <a href="#registro" class="inline-flex items-center gap-2 text-primary-700 font-semibold text-sm hover:text-primary-900 transition-colors">
                        Quiero esta herramienta <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 2 -->
                <div class="card-light rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-14 h-14 bg-primary-50 rounded-xl flex items-center justify-center border border-primary-100 flex-shrink-0 group-hover:bg-primary-100 transition-colors">
                            <i class="fa-solid fa-mobile-screen text-2xl text-primary-700"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-600 font-bold uppercase tracking-wider mb-1">Herramienta 02</div>
                            <h3 class="text-xl font-black text-gray-900">App Academia Mobile</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/app.png" alt="App Academia Mobile"
                         class="w-full h-40 object-contain mb-5 img-soft" loading="lazy">
                    <p class="text-gray-500 leading-relaxed mb-5">Gestiona ventas, métricas y comisiones desde tu celular. <strong class="text-gray-800">Todo tu negocio en la palma de tu mano</strong>, disponible 24/7.</p>
                    <a href="#registro" class="inline-flex items-center gap-2 text-primary-700 font-semibold text-sm hover:text-primary-900 transition-colors">
                        Asegurar mi acceso <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 3 -->
                <div class="card-light rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-14 h-14 bg-primary-50 rounded-xl flex items-center justify-center border border-primary-100 flex-shrink-0 group-hover:bg-primary-100 transition-colors">
                            <i class="fa-solid fa-magnet text-2xl text-primary-700"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-600 font-bold uppercase tracking-wider mb-1">Herramienta 03</div>
                            <h3 class="text-xl font-black text-gray-900">App LeadBoost</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/lead.png" alt="App LeadBoost para atraer clientes"
                         class="w-full h-40 object-contain mb-5 img-soft" loading="lazy">
                    <p class="text-gray-500 leading-relaxed mb-5">Atrae clientes calificados con contenido magnético. <strong class="text-gray-800">Convierte seguidores en socios</strong> sin perseguir a nadie.</p>
                    <a href="#registro" class="inline-flex items-center gap-2 text-primary-700 font-semibold text-sm hover:text-primary-900 transition-colors">
                        Desbloquear LeadBoost <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Herramienta 4 (destacada) -->
                <div class="card-accent rounded-2xl p-6 lg:p-8 group">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center border border-primary-200 flex-shrink-0 group-hover:bg-primary-200 transition-colors">
                            <i class="fa-solid fa-filter-circle-dollar text-2xl text-primary-800"></i>
                        </div>
                        <div>
                            <div class="text-xs text-primary-700 font-bold uppercase tracking-wider mb-1">Herramienta 04</div>
                            <h3 class="text-xl font-black text-gray-900">Smart Funnels con IA</h3>
                        </div>
                    </div>
                    <img src="/preregistro/new/imagenes/smart.png" alt="Smart Funnels con IA"
                         class="w-full h-40 object-contain mb-5 img-soft" loading="lazy">
                    <p class="text-gray-600 leading-relaxed mb-5">Embudos automatizados que asesoran, educan y cierran ventas por ti. <strong class="text-gray-900">Tu negocio escalando en piloto automático</strong>.</p>
                    <a href="#registro" class="inline-flex items-center gap-2 text-primary-800 font-semibold text-sm hover:text-primary-900 transition-colors">
                        Automatizar mis ventas <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>


    <!-- ===== FORMULARIO FINAL ===== -->
    <section id="registro" class="py-16 lg:py-24 relative z-10 bg-white/60">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-10 reveal">
                <span class="text-primary-700 text-sm font-bold uppercase tracking-widest">Última oportunidad</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 mt-2 mb-4">
                    Asegura tu lugar como<br><span class="gradient-text">Socio Fundador</span>
                </h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">Las posiciones son limitadas. Una vez cerrado el pre-registro, el precio de entrada aumenta.</p>
            </div>

            <div class="reveal">
                <div class="bg-white rounded-2xl shadow-xl border border-primary-100 overflow-hidden flex flex-col md:flex-row">

                    <!-- Left panel -->
                    <div class="w-full md:w-5/12 relative hidden md:flex flex-col justify-end p-10"
                         style="background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;">
                        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,80,0,0.88) 0%, rgba(0,100,0,0.55) 55%, rgba(0,168,0,0.15) 100%);"></div>
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/20 border border-white/30 mb-5 animate-float">
                                <i class="fa-solid fa-lock text-3xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-black text-white mb-3">Reserva Exclusiva</h3>
                            <p class="text-green-100 text-base mb-6">Asegura hoy tu lugar antes del cierre definitivo.</p>
                            <div class="inline-flex items-center gap-2 bg-white/15 border border-white/20 rounded-xl px-4 py-2.5 backdrop-blur-sm">
                                <span class="w-2 h-2 rounded-full bg-primary-400 animate-pulse"></span>
                                <span class="text-white text-sm font-bold"><span class="counter-number" id="formCounter">1,247</span> socios pre-registrados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Form -->
                    <div class="w-full md:w-7/12 p-7 sm:p-10">
                        <div class="md:hidden text-center mb-8">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-50 border border-primary-200 mb-3 animate-float">
                                <i class="fa-solid fa-lock text-xl text-primary-700"></i>
                            </div>
                            <h2 class="text-2xl font-black text-gray-900 mb-1">Reserva Exclusiva</h2>
                            <p class="text-primary-700 text-sm font-semibold">Estás a un paso de asegurar tu lugar.</p>
                        </div>
                        <div class="hidden md:block mb-7">
                            <h2 class="text-2xl font-black text-gray-900 mb-1">Completa tu registro</h2>
                            <p class="text-gray-400 text-sm">Todos los campos son requeridos.</p>
                        </div>
                        <form class="form-registro space-y-5" novalidate>
                            <div>
                                <label for="nombre_final" class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" id="nombre_final" name="nombre" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu nombre">
                                </div>
                                <p class="error-msg">Por favor ingresa tu nombre</p>
                            </div>
                            <div>
                                <label for="apellido_final" class="block text-sm font-semibold text-gray-700 mb-2">Apellido</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" id="apellido_final" name="apellido" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="Tu apellido">
                                </div>
                                <p class="error-msg">Por favor ingresa tu apellido</p>
                            </div>
                            <div>
                                <label for="whatsapp_final" class="block text-sm font-semibold text-gray-700 mb-2 flex justify-between">
                                    <span>WhatsApp</span>
                                    <span class="text-primary-700 text-xs bg-primary-50 px-2 py-0.5 rounded-md border border-primary-200 font-bold">Acceso prioritario</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-brands fa-whatsapp text-gray-400"></i>
                                    </div>
                                    <input type="tel" id="whatsapp_final" name="whatsapp" required
                                        class="input-field block w-full pl-10 pr-3 py-3.5 rounded-xl text-sm transition-all"
                                        placeholder="+1 (555) 000-0000">
                                </div>
                                <p class="error-msg">Por favor ingresa un número de WhatsApp válido</p>
                            </div>
                            <div>
                                <label for="email_final" class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-envelope text-gray-400"></i>
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
                            <p class="text-xs text-center text-gray-400 mt-3">
                                <i class="fa-solid fa-shield-halved mr-1 text-primary-600"></i> Tus datos están seguros. Cero spam. Cancela cuando quieras.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="bg-white border-t border-gray-100 relative z-10 pt-14 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12 text-center md:text-left">

                <!-- Col 1 -->
                <div class="flex flex-col items-center md:items-start">
                    <img src="/preregistro/new/imagenes/logo promolider negro.png" alt="Logo Promolíder" class="h-10 md:h-12 w-auto mb-5" width="140" height="48">
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Juntos lograremos monetizar tus conocimientos y experiencia. Únete a la red de emprendedores digitales más potente de Latinoamérica.
                    </p>
                </div>

                <!-- Col 2 -->
                <div class="flex flex-col items-center md:items-start">
                    <h4 class="text-gray-900 font-bold text-base mb-4">Contáctame Tu patrocinador</h4>

                    <p class="text-gray-800 font-semibold text-sm mb-3">
                        <i class="fa-solid fa-user text-primary-600 mr-2"></i>
                        {{ $nombre_referidor }} {{ $apellido_referidor }}
                    </p>

                    <a href="mailto:{{ $correo_referidor }}"
                    class="text-gray-500 hover:text-primary-700 transition-colors flex items-center gap-3 text-sm">
                        <i class="fa-regular fa-envelope text-primary-600"></i>
                        {{ $correo_referidor }}
                    </a>

                    <a href="tel:{{ $telefono_referidor }}"
                    class="text-gray-500 hover:text-primary-700 transition-colors flex items-center gap-3 text-sm mt-2">
                        <i class="fa-solid fa-phone text-primary-600"></i>
                        {{ $telefono_referidor }}
                    </a>
                </div>

                <!-- Col 3 -->
                <div class="flex flex-col items-center md:items-end">
                    <h4 class="text-gray-900 font-bold text-base mb-5">Síguenos</h4>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/profile.php?id=100063926738412" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f text-sm"></i>
                        </a>
                        <a href="https://www.instagram.com/promoliderorg/" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all" aria-label="Instagram">
                            <i class="fa-brands fa-instagram text-sm"></i>
                        </a>
                        <a href="https://www.linkedin.com/company/promol%C3%ADder/" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all" aria-label="LinkedIn">
                            <i class="fa-brands fa-linkedin-in text-sm"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCj-NmJXOnJt55aDRh1R0LLg" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all" aria-label="YouTube">
                            <i class="fa-brands fa-youtube text-sm"></i>
                        </a>
                        <a href="https://www.tiktok.com/@promolider" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all" aria-label="TikTok">
                            <i class="fa-brands fa-tiktok text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-sm">&copy; 2026 PromoLider. Todos los derechos reservados.</p>
                <div class="flex gap-5 text-sm text-gray-400">
                    <a href="#" class="hover:text-primary-700 transition-colors">Términos y Condiciones</a>
                    <a href="#" class="hover:text-primary-700 transition-colors">Políticas de Privacidad</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 left-4 sm:left-auto z-50 pointer-events-none"></div>

    <!-- JavaScript -->
    <script>
        window.USERNAME = @json($username);
        window.LADO = @json($lado);
    </script>
    <script src="{{ asset('preregistro/new/landing.js') }}"></script>

    <!-- Countdown Timer -->
    <script>
        const deadline = new Date();
        deadline.setDate(deadline.getDate() + 2);
        deadline.setHours(deadline.getHours() + 14);
        deadline.setMinutes(deadline.getMinutes() + 37);

        function updateCountdown() {
            const distance = deadline.getTime() - Date.now();
            if (distance <= 0) {
                ['cd-days','cd-hours','cd-mins','cd-secs'].forEach(id => document.getElementById(id).textContent = '00');
                return;
            }
            document.getElementById('cd-days').textContent  = String(Math.floor(distance / 86400000)).padStart(2,'0');
            document.getElementById('cd-hours').textContent = String(Math.floor((distance % 86400000) / 3600000)).padStart(2,'0');
            document.getElementById('cd-mins').textContent  = String(Math.floor((distance % 3600000) / 60000)).padStart(2,'0');
            document.getElementById('cd-secs').textContent  = String(Math.floor((distance % 60000) / 1000)).padStart(2,'0');
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>

</body>
</html>
