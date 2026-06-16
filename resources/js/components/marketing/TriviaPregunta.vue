<template>
  <div class="trivia-public-shell pregunta-full-center">
    <section class="trivia-hero">
      <div class="hero-content">
        <div class="hero-eyebrow">Trivia</div>
        <h1 style="font-size:2rem;margin-bottom:1.5rem;">
          CATEGORÍA: <span style="color:var(--trivia-accent)">{{ categoria || 'Cultura General' }}</span>
        </h1>
      </div>
    </section>
    <section class="trivia-layout">
      <div class="card card-overview pregunta-card">
        <div class="pregunta-block">
          <div class="pregunta-header-row">
            <div class="pregunta-num">
              Pregunta #{{ numeroPregunta || 1 }}
            </div>
            <div class="pregunta-valor pregunta-valor-final">
              <span class="pregunta-estado-label">Valor:</span> {{ valorPregunta || 10 }} pts
            </div>
          </div>
          <div v-if="tiempoLimite" class="pregunta-timer">
            <div class="timer-label">Tiempo restante</div>
            <div class="timer-bar">
              <div class="timer-fill" :style="{ width: timerPercentage + '%' }"></div>
            </div>
            <div class="timer-countdown">{{ tiempoRestante }}s</div>
          </div>
          <div class="pregunta-text">
            {{ pregunta }}
          </div>
          <div class="pregunta-header-row pregunta-valor-final">
            <div></div>
          </div>
          <form class="opciones-list" @submit.prevent="submitRespuesta">
            <label v-for="(opcion, idx) in opciones" :key="idx" class="opcion-radio-label">
              <input type="radio" :value="idx" v-model="opcionSeleccionada" class="opcion-radio" style="display:none;" />
              <span class="opcion-btn-innovador" :class="{ selected: opcionSeleccionada == idx }">
                <span class="opcion-letra">{{ letra(idx) }}</span>
                <span class="opcion-text">{{ opcion }}</span>
              </span>
            </label>
            <button
              type="submit"
              class="btn btn-primary"
              style="margin-top:2.2rem;font-size:1.2rem;"
              :disabled="enviando"
            >
              {{ enviando ? 'Enviando…' : 'Responder' }}
            </button>
          </form>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TriviaPregunta',
  props: {
    categoria: String,
    numeroPregunta: Number,
    valorPregunta: Number,
    pregunta: String,
    opciones: Array,
    slug: String,
    userId: {
      type: [Number, String],
      default: null
    },
    totalPreguntas: {
      type: Number,
      default: 0
    },
    tiempoLimite: {
      type: Number,
      default: 30
    },
    resultadosUrl: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      opcionSeleccionada: null,
      enviando: false,
      tiempoRestante: this.tiempoLimite,
      timerId: null,
      inicioPregunta: null,
      autoSubmitted: false
    };
  },
  computed: {
    timerPercentage() {
      if (!this.tiempoLimite) {
        return 0;
      }
      return Math.max(0, Math.min(100, (this.tiempoRestante / this.tiempoLimite) * 100));
    }
  },
  mounted() {
    this.applyTriviaTheme();
    this.startTimer();
  },
  beforeDestroy() {
    this.clearTriviaTheme();
    this.clearTimer();
  },
  methods: {
    letra(idx) {
      return String.fromCharCode(65 + idx);
    },
    startTimer() {
      this.clearTimer();
      this.tiempoRestante = this.tiempoLimite;
      this.inicioPregunta = Date.now();
      if (!this.tiempoLimite) {
        return;
      }
      this.timerId = window.setInterval(() => {
        const elapsed = Math.floor((Date.now() - this.inicioPregunta) / 1000);
        const remaining = Math.max(0, this.tiempoLimite - elapsed);
        this.tiempoRestante = remaining;
        if (remaining <= 0) {
          this.handleTimeout();
        }
      }, 250);
    },
    clearTimer() {
      if (this.timerId) {
        window.clearInterval(this.timerId);
        this.timerId = null;
      }
    },
    handleTimeout() {
      if (this.autoSubmitted || this.enviando) {
        return;
      }
      this.autoSubmitted = true;
      this.clearTimer();
      this.submitRespuesta({ forceTimeout: true });
    },
    async submitRespuesta(options = {}) {
      const forceTimeout = options.forceTimeout || false;
      if (!forceTimeout && this.opcionSeleccionada === null) {
        alert('Selecciona una opción.');
        return;
      }
      if (this.enviando) {
        return;
      }

      this.enviando = true;
      this.clearTimer();
      try {
        const elapsedMs = Math.max(0, Date.now() - (this.inicioPregunta || Date.now()));
        const payload = {
          elapsed_ms: elapsedMs
        };
        if (forceTimeout) {
          payload.timeout = true;
        } else {
          payload.opcion_index = Number(this.opcionSeleccionada);
        }

        const response = await axios.post(`/d/${this.slug}/pregunta/${this.numeroPregunta}/respuesta`, payload);

        this.persistAnsweredLocally();
        this.autoSubmitted = true;

        const destino = response.data?.completado && response.data?.resultadosUrl
          ? response.data.resultadosUrl
          : (response.data?.previewUrl || `/d/${this.slug}/preview`);

        window.location.replace(destino);
      } catch (error) {
        const message = error?.response?.data?.message || 'No pudimos registrar tu respuesta, inténtalo de nuevo.';
        alert(message);
      } finally {
        this.enviando = false;
      }
    },
    persistAnsweredLocally() {
      const numero = Number(this.numeroPregunta || 1);
      const answeredKey = this.getAnsweredStorageKey();
      const answered = this.readAnsweredFromStorage(answeredKey);
      if (!answered.includes(numero)) {
        answered.push(numero);
        localStorage.setItem(answeredKey, JSON.stringify(answered));
      }
    },
    getAnsweredStorageKey() {
      const base = `trivia_${this.slug}`;
      return this.userId ? `${base}_user_${this.userId}_answered` : `${base}_answered`;
    },
    readAnsweredFromStorage(key) {
      try {
        const stored = JSON.parse(localStorage.getItem(key)) || [];
        return stored.map((value) => Number(value)).filter((value) => !Number.isNaN(value));
      } catch (error) {
        return [];
      }
    },
    applyTriviaTheme() {
      if (typeof document === 'undefined') {
        return;
      }
      if (document.documentElement) {
        document.documentElement.classList.add('trivia-question-active');
      }
      if (document.body) {
        document.body.classList.add('trivia-question-active');
      }
    },
    clearTriviaTheme() {
      if (typeof document === 'undefined') {
        return;
      }
      if (document.documentElement) {
        document.documentElement.classList.remove('trivia-question-active');
      }
      if (document.body) {
        document.body.classList.remove('trivia-question-active');
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap');
:global(html.trivia-question-active),
:global(body.trivia-question-active) {
  background: var(--trivia-bg) !important;
  min-height: 100vh;
  width: 100%;
  margin: 0;
  padding: 0;
  max-width: 100vw;
  box-sizing: border-box;
  overflow-x: hidden;
  overflow-y: auto;
}
:global(body.trivia-question-active .app-content),
:global(body.trivia-question-active .content-wrapper) {
  background: var(--trivia-bg) !important;
  min-height: 100vh;
  height: auto;
  overflow-y: auto;
  overflow-x: hidden;
}
:global(:root) {
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
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: stretch;
  padding: clamp(1.5rem, 4vw, 3rem) clamp(1rem, 4vw, 2.5rem) 3rem;
  gap: 2rem;
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
  width: 100%;
  max-width: 960px;
  margin: 0 auto;
}
 .hero-content {
    padding: 2.5rem;
    border-radius: 28px;
    background: rgba(13, 16, 27, 0.95);
    border: 1px solid var(--trivia-border);
    box-shadow: 0 25px 60px rgba(5, 6, 10, 0.6);
  max-width: 900px;
  width: 100%;
  margin: 0 auto 1.5rem;
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
  max-width: 960px;
  margin: 0 auto;
    min-height: 70vh;
    flex: 1 0 auto;
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
  margin: clamp(1rem, 2vw, 1.5rem) auto clamp(3rem, 4vw, 4.5rem);
  display: block;
  width: 100%;
  max-width: 900px;
  min-height: clamp(480px, 70vh, 620px);
}
 .pregunta-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.2rem;
    gap: 1.2rem;
}
.pregunta-estado-label {
    color: var(--trivia-tone);
    font-size: 0.98rem;
    font-weight: 500;
    margin-right: 0.3rem;
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
.pregunta-timer {
  margin-bottom: 1.8rem;
}
.timer-label {
  font-size: 0.9rem;
  color: #b6c2ff;
  margin-bottom: 0.3rem;
}
.timer-bar {
  width: 100%;
  height: 10px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 999px;
  overflow: hidden;
  position: relative;
}
.timer-fill {
  height: 100%;
  background: linear-gradient(90deg, #5efcdb 0%, #ff9a62 100%);
  transition: width 0.25s linear;
}
.timer-countdown {
  margin-top: 0.3rem;
  font-size: 0.9rem;
  color: #ffd666;
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
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.4rem 1.8rem;
  justify-items: stretch;
  width: 100%;
  max-width: 820px;
  margin: 0 auto;
}
.opciones-list button {
  grid-column: 1 / -1;
  justify-self: center;
  width: min(320px, 80%);
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
 .opcion-radio-label:hover .opcion-btn-innovador,
 .opcion-btn-innovador.selected {
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

@media (max-width: 768px) {
  .trivia-public-shell.pregunta-full-center {
    padding: 1.5rem 1rem 2.5rem;
    gap: 1.5rem;
  }
  .trivia-layout {
    min-height: auto;
  }
  .hero-content {
    padding: 1.5rem;
  }
  .pregunta-text {
    font-size: 1.6rem;
  }
  .opciones-list {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  .opciones-list button {
    width: min(320px, 100%);
  }
  .opcion-btn-innovador {
    padding: 1rem 1.25rem;
  }
}
</style>
