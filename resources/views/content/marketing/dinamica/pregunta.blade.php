
@extends('layouts.fullLayoutMaster')

@section('title', 'Pregunta Trivia')

@section('content')
<div>
    <trivia-pregunta
        :categoria='@json($categoria ?? "Cultura General")'
        :numero-pregunta='@json((int) ($numeroPregunta ?? 1))'
        :valor-pregunta='@json((float) ($valorPregunta ?? 10))'
        :pregunta='@json($pregunta ?? "")'
        :opciones='@json($opciones ?? [])'
        :slug='@json(request()->route('slug'))'
        :user-id='@json($usuarioId ?? null)'
        :total-preguntas='@json((int) ($cantidadPreguntas ?? 0))'
        :tiempo-limite='@json((int) ($tiempoPregunta ?? 30))'
        :resultados-url='@json($resultadosUrl ?? null)'
    />
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap');
html, body, .app-content, .content-wrapper {
    background: var(--trivia-bg) !important;
    min-height: 100vh !important;
    height: 100vh !important;
    width: 100vw !important;
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100vw !important;
    box-sizing: border-box;
    overflow: hidden;
}
:root {
    --trivia-bg: #05060a;
    --trivia-card: #10121a;
    --trivia-card-alt: #121625;
    --trivia-border: rgba(255, 255, 255, 0.08);
    --trivia-tone: #b6c2ff;
    --trivia-accent: #5efcdb;
    --trivia-highlight: linear-gradient(135deg, #5efcdb, #5c6bff);
}
.trivia-public-shell.pregunta-full-center {
    min-height: 100vh;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 0;
    background: radial-gradient(circle at top, rgba(94, 252, 219, 0.15), transparent 45%),
        radial-gradient(circle at 20% 20%, rgba(92, 107, 255, 0.18), transparent 40%),
        var(--trivia-bg);
    font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
    color: #f7f8ff;
}
.trivia-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 40px;
}
 .hero-content {
    padding: 2.5rem;
    border-radius: 28px;
    background: rgba(13, 16, 27, 0.95);
    border: 1px solid var(--trivia-border);
    box-shadow: 0 25px 60px rgba(5, 6, 10, 0.6);
    max-width: 900px;
    width: 100%;
    margin-bottom: 20px;
}
.hero-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.35em;
    font-size: 0.75rem;
    color: var(--trivia-tone);
    margin-bottom: 1rem;
}
.trivia-layout {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    height: auto;
    flex: 1;
}
.card {
    background: var(--trivia-card-alt);
    border-radius: 26px;
    padding: 2rem;
    border: 1px solid var(--trivia-border);
    box-shadow: 0 18px 50px rgba(5, 6, 10, 0.55);
}
.pregunta-card {
    font-size: 1.2rem;
    color: var(--trivia-tone);
    margin-bottom: 0;
    margin-right: 1.2rem;
    display: inline-block;
    vertical-align: middle;
    width: 900px; /* O prueba 1000px, 1100px, etc. */
}
 .pregunta-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.2rem;
    gap: 1.2rem;
}
    margin-bottom: 60px;
    font-size: 1.08rem;
    color: #ffd666;
        margin-bottom: 0;
        margin-right: 1.2rem;
        display: inline-block;
        vertical-align: middle;
    }
    vertical-align: middle;
}
.pregunta-estado-label {
    color: var(--trivia-tone);
    font-size: 0.98rem;
    font-weight: 500;
    margin-right: 0.3rem;
}
}
.pregunta-num {
    font-size: 1.2rem;
    color: var(--trivia-tone);
    margin-bottom: 1.2rem;
}
.pregunta-valor {
    font-size: 1.08rem;
    color: #ffd666;
    font-weight: 600;
    margin-bottom: 1.1rem;
    background: rgba(255, 214, 102, 0.08);
    border-radius: 10px;
    padding: 0.4rem 1rem;
    display: inline-block;
}
.pregunta-text {
    font-size: 2.1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 2.5rem;
    letter-spacing: 0.01em;
}
.opciones-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem 2.5rem;
    justify-items: center;
    max-width: 500px;
    margin: 0 auto;
}
.opcion-radio-label {
    width: 100%;
    cursor: pointer;
}
 .opcion-btn-innovador {
    display: flex;
    align-items: center;
    gap: 1.1rem;
    background: var(--trivia-card);
    border-radius: 16px;
    border: 2.5px solid var(--trivia-accent);
    box-shadow: 0 4px 18px 0 rgba(94,252,219,0.10), 0 1.5px 8px 0 rgba(20,30,60,0.10);
    padding: 1.1rem 2.2rem;
    font-size: 1.18rem;
    font-weight: 600;
    color: #fff;
    transition: transform 0.18s cubic-bezier(.4,1.6,.6,1), box-shadow 0.18s, border-color 0.18s, background 0.18s;
    position: relative;
}
 .opcion-radio-label:hover .opcion-btn-innovador {
    transform: scale(1.06) rotate(-2deg);
    box-shadow: 0 8px 32px 0 rgba(94,252,219,0.18), 0 2.5px 12px 0 rgba(20,30,60,0.13);
    border-color: #ffd666;
    background: linear-gradient(90deg, #5efcdb 0%, #5c6bff 100%);
    color: #222;
}
.opcion-radio:checked + .opcion-btn-innovador {
    border-color: #ffd666;
    box-shadow: 0 8px 32px 0 rgba(94,252,219,0.18), 0 2.5px 12px 0 rgba(20,30,60,0.13);
    background: linear-gradient(90deg, #5efcdb 0%, #5c6bff 100%);
    color: #222;
}
.opcion-letra {
    display: inline-block;
    width: 2.2rem;
    height: 2.2rem;
    background: linear-gradient(135deg, #5efcdb, #5c6bff);
    color: #222;
    font-size: 1.25rem;
    font-weight: 800;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.7rem;
    box-shadow: 0 2px 8px rgba(94,252,219,0.10);
    border: 2px solid #fff;
}
.opcion-text {
    flex: 1;
    text-align: left;
    font-size: 1.13rem;
    font-weight: 600;
    color: inherit;
}
</style>
@endpush
