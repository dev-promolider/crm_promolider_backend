<template>
  <div class="trivia-public-shell">
    <section class="trivia-hero">
      <div class="hero-content">
        <div class="hero-eyebrow">Bloque {{ currentBlockPosition }}</div>
        <h1 class="hero-title">{{ currentBlockTitle }}</h1>
        <p v-if="currentBlockCategory" class="hero-subtitle">
          Categoría: <span>{{ currentBlockCategory }}</span>
        </p>
        <div class="hero-meta">
          <span>Puntaje máximo {{ formatScore(puntajeObjetivo) }} pts</span>
          <span>{{ currentBlockAnswered }} / {{ currentBlockTotal }} preguntas</span>
          <span>{{ formatScore(totalScore) }} pts acumulados</span>
        </div>
      </div>
    </section>
    <section class="trivia-layout">
      <div class="card card-overview preview-card">
        <div v-if="currentCasillas.length" class="blocks-grid blocks-grid-row">
          <a
            v-for="numero in currentCasillas"
            :key="numero"
            :href="getPreguntaUrl(numero)"
            class="casilla-link"
            :class="{ 'casilla-disabled': isCasillaRespondida(numero) }"
            :aria-disabled="isCasillaRespondida(numero)"
            :data-casilla="numero"
            style="text-decoration:none;"
            @click="handleCasillaClick(numero, $event)"
          >
            <article class="block-card block-card-big">
              <span class="casilla-num casilla-num-big">{{ numero }}</span>
              <small v-if="isCasillaRespondida(numero)" class="casilla-tag">RESPONDIDA</small>
            </article>
          </a>
        </div>
        <p v-else class="empty-state">
          Este bloque no tiene preguntas configuradas aún.
        </p>

        <div class="block-progress">
          <span>Respondidas {{ currentBlockAnswered }} / {{ currentBlockTotal }}</span>
          <span v-if="hasBlockStages">Bloque {{ currentBlockPosition }} de {{ blocks.length }}</span>
        </div>

        <div v-if="todasRespondidas && resultadosUrl" class="completion-cta">
          <a :href="resultadosUrl" class="cta-link">Ver resultados finales</a>
        </div>
      </div>
    </section>

    <div v-if="summaryBlock" class="block-summary-overlay">
      <div class="block-summary-card">
        <p class="summary-eyebrow">Bloque {{ summaryBlockPosition }} completado</p>
        <h2>{{ summaryBlock.title }}</h2>
        <p class="summary-score">
          Las puntuaciones permanecen ocultas hasta finalizar todos los bloques.
        </p>
        <button
          type="button"
          class="cta-link summary-cta"
          @click="handleSummaryAction"
        >
          {{ summaryCtaLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TriviaMain',
  props: {
    categoria: {
      type: String,
      default: 'Cultura General'
    },
    cantidadCasillas: {
      type: Number,
      default: 5
    },
    estadoInteraccion: {
      type: String,
      default: 'espera'
    },
    slug: {
      type: String,
      required: true
    },
    userId: {
      type: [Number, String],
      default: null
    },
    resultadosUrl: {
      type: String,
      default: ''
    },
    blocks: {
      type: Array,
      default: () => []
    },
    nextPlayableIndex: {
      type: Number,
      default: 0
    },
    lastCompletedIndex: {
      type: Number,
      default: null
    },
    totalScore: {
      type: Number,
      default: 0
    },
    initialAnsweredNumbers: {
      type: Array,
      default: () => []
    },
    hasMultipleBlocks: {
      type: Boolean,
      default: false
    },
    puntajeMaximo: {
      type: Number,
      default: 0
    }
  },
  data() {
    const blockCount = Array.isArray(this.blocks) ? this.blocks.length : 0;
    const normalizeIndex = (value) => {
      if (!blockCount) {
        return 0;
      }
      const numeric = Number(value);
      if (!Number.isFinite(numeric)) {
        return 0;
      }
      return Math.min(Math.max(0, Math.floor(numeric)), blockCount - 1);
    };
    const initialIndex = blockCount ? normalizeIndex(this.nextPlayableIndex) : 0;

    return {
      casillasRespondidas: [],
      clientNextPlayableIndex: initialIndex,
      currentBlockIndex: initialIndex,
      summaryBlockIndex: null,
      blockAckKey: this.buildBlockAckKey()
    };
  },
  mounted() {
    this.initializeCasillasRespondidas();
    this.summaryBlockIndex = this.computeInitialSummaryBlockIndex();
    window.addEventListener('storage', this.handleStorageSync);
  },
  beforeDestroy() {
    window.removeEventListener('storage', this.handleStorageSync);
  },
  watch: {
    nextPlayableIndex(newValue) {
      const normalized = this.clampIndex(newValue);
      this.clientNextPlayableIndex = normalized;
      if (!this.summaryBlock) {
        this.currentBlockIndex = normalized;
      }
    },
    blocks: {
      handler() {
        const normalized = this.clampIndex(this.clientNextPlayableIndex);
        this.clientNextPlayableIndex = normalized;
        this.currentBlockIndex = normalized;
      },
      deep: true
    }
  },
  computed: {
    answeredStorageKey() {
      const base = `trivia_${this.slug}`;
      return this.userId ? `${base}_user_${this.userId}_answered` : `${base}_answered`;
    },
    puntajeObjetivo() {
      return this.puntajeMaximo > 0 ? this.puntajeMaximo : this.cantidadCasillas;
    },
    hasBlockStages() {
      return Array.isArray(this.blocks) && this.blocks.length > 0;
    },
    defaultCasillas() {
      return Array.from({ length: this.cantidadCasillas }, (_, idx) => idx + 1);
    },
    currentCasillas() {
      if (this.hasBlockStages) {
        const candidate = this.blocks[this.currentBlockIndex];
        if (candidate && Array.isArray(candidate.questionNumbers) && candidate.questionNumbers.length) {
          return candidate.questionNumbers;
        }
      }
      return this.defaultCasillas;
    },
    currentBlock() {
      if (!this.hasBlockStages) {
        return null;
      }
      return this.blocks[this.currentBlockIndex] || null;
    },
    currentBlockTitle() {
      if (this.currentBlock) {
        return this.currentBlock.title;
      }
      return this.hasBlockStages ? 'Bloque' : `Trivia ${this.categoria}`;
    },
    currentBlockCategory() {
      if (this.currentBlock && this.currentBlock.categoryName) {
        return this.currentBlock.categoryName;
      }
      return this.hasBlockStages ? null : this.categoria;
    },
    currentBlockTotal() {
      if (this.hasBlockStages && this.currentBlock) {
        return this.currentBlock.totalQuestions || this.currentCasillas.length;
      }
      return this.defaultCasillas.length;
    },
    currentBlockAnswered() {
      if (!this.currentCasillas.length) {
        return 0;
      }
      const answeredSet = new Set(this.casillasRespondidas.map((value) => Number(value)));
      return this.currentCasillas.filter((numero) => answeredSet.has(Number(numero))).length;
    },
    currentBlockPosition() {
      if (!this.hasBlockStages) {
        return 1;
      }
      return Math.min(this.currentBlockIndex + 1, this.blocks.length);
    },
    todasRespondidas() {
      if (this.hasBlockStages) {
        return this.blocks.every((block) => block.completed);
      }
      return this.cantidadCasillas > 0 && this.casillasRespondidas.length >= this.cantidadCasillas;
    },
    summaryBlock() {
      if (typeof this.summaryBlockIndex === 'number' && this.summaryBlockIndex >= 0) {
        return this.blocks[this.summaryBlockIndex] || null;
      }
      return null;
    },
    summaryBlockPosition() {
      return this.summaryBlock ? this.summaryBlock.index + 1 : null;
    },
    summaryCtaLabel() {
      if (this.summaryBlock && this.nextPlayableIndex >= this.blocks.length && this.resultadosUrl) {
        return 'Ver resultados finales';
      }
      return 'Jugar siguiente bloque';
    }
  },
  methods: {
    clampIndex(value) {
      if (!this.hasBlockStages) {
        return 0;
      }
      const numeric = Number(value);
      if (!Number.isFinite(numeric)) {
        return 0;
      }
      const maxIndex = Math.max(0, this.blocks.length - 1);
      return Math.min(Math.max(0, Math.floor(numeric)), maxIndex);
    },
    goToBlock(index) {
      const normalized = this.clampIndex(index);
      if (normalized >= this.clientNextPlayableIndex) {
        this.clientNextPlayableIndex = normalized;
      }
      this.currentBlockIndex = normalized;
    },
    buildBlockAckKey() {
      const base = `trivia_${this.slug}`;
      return this.userId ? `${base}_user_${this.userId}_block_ack` : `${base}_block_ack`;
    },
    initializeCasillasRespondidas() {
      const fromServer = Array.isArray(this.initialAnsweredNumbers)
        ? this.initialAnsweredNumbers.map((value) => Number(value))
        : [];
      const fromStorage = this.readAnsweredFromStorage(this.answeredStorageKey);
      const merged = [...fromServer, ...fromStorage]
        .map((value) => Number(value))
        .filter((value) => !Number.isNaN(value));
      this.casillasRespondidas = Array.from(new Set(merged)).sort((a, b) => a - b);
    },
    handleStorageSync(event) {
      if (event.key === this.answeredStorageKey) {
        this.initializeCasillasRespondidas();
      }
      if (event.key === this.blockAckKey) {
        this.summaryBlockIndex = this.computeInitialSummaryBlockIndex();
      }
    },
    readAnsweredFromStorage(key) {
      try {
        const stored = JSON.parse(localStorage.getItem(key)) || [];
        return stored.map((value) => Number(value)).filter((value) => !Number.isNaN(value));
      } catch (error) {
        return [];
      }
    },
    isCasillaRespondida(numero) {
      return this.casillasRespondidas.includes(Number(numero));
    },
    handleCasillaClick(numero, event) {
      if (this.isCasillaRespondida(numero)) {
        event.preventDefault();
      }
    },
    getPreguntaUrl(i) {
      return `/d/${this.slug}/pregunta/${i}`;
    },
    computeInitialSummaryBlockIndex() {
      if (!this.hasBlockStages || this.blocks.length <= 1) {
        return null;
      }
      if (typeof this.lastCompletedIndex !== 'number' || this.lastCompletedIndex < 0) {
        return null;
      }
      const acknowledged = this.readAcknowledgedBlockIndex();
      if (this.lastCompletedIndex <= acknowledged) {
        return null;
      }
      return this.lastCompletedIndex;
    },
    readAcknowledgedBlockIndex() {
      try {
        const raw = localStorage.getItem(this.blockAckKey);
        if (raw === null) {
          return -1;
        }
        const parsed = parseInt(raw, 10);
        return Number.isNaN(parsed) ? -1 : parsed;
      } catch (error) {
        return -1;
      }
    },
    saveAcknowledgedBlockIndex(index) {
      try {
        localStorage.setItem(this.blockAckKey, String(index));
      } catch (error) {
        // ignore storage errors
      }
    },
    handleSummaryAction() {
      if (typeof this.summaryBlockIndex !== 'number') {
        return;
      }
      this.saveAcknowledgedBlockIndex(this.summaryBlockIndex);
      const nextIndex = this.summaryBlockIndex + 1;
      const reachedEnd = !this.hasBlockStages || nextIndex >= this.blocks.length;
      this.summaryBlockIndex = null;

      if (reachedEnd) {
        if (this.resultadosUrl) {
          window.location.href = this.resultadosUrl;
        } else {
          this.currentBlockIndex = this.clampIndex(this.blocks.length - 1);
        }
        return;
      }

      this.goToBlock(nextIndex);
    },
    formatScore(value) {
      const fixed = (Number(value) || 0).toFixed(1);
      return fixed.endsWith('.0') ? fixed.slice(0, -2) : fixed;
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap');
:global(:root) {
  --trivia-bg: #05060a;
  --trivia-card: #10121a;
  --trivia-card-alt: #121625;
  --trivia-border: rgba(255, 255, 255, 0.08);
  --trivia-tone: #b6c2ff;
  --trivia-accent: #5efcdb;
  --trivia-highlight: linear-gradient(135deg, #5efcdb, #5c6bff);
}
:global(body) {
  background: var(--trivia-bg);
  margin: 0;
  min-height: 100vh;
}
.trivia-public-shell {
  background: var(--trivia-bg);
  color: #fff;
  font-family: 'Space Grotesk', sans-serif;
  padding: clamp(1.5rem, 4vw, 3rem) clamp(1rem, 4vw, 2.75rem) 3rem;
  margin-left: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100%;
  position: relative;
}
.trivia-hero {
  margin-bottom: 2.5rem;
}
.hero-eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.4em;
  font-size: 0.7rem;
  color: var(--trivia-tone);
  margin-bottom: 0.9rem;
}
.hero-title {
  font-size: clamp(2.25rem, 4vw, 3rem);
  margin: 0;
}
.hero-subtitle {
  margin: 0.35rem 0 1.2rem;
  color: #cdd3ff;
  font-size: 1.05rem;
}
.hero-subtitle span {
  color: var(--trivia-accent);
}
.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  font-size: 0.95rem;
  color: #c3caef;
}
.trivia-layout {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
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
.preview-card {
  width: min(920px, 100%);
  max-width: 100%;
  margin: 0;
  padding: clamp(1.5rem, 3vw, 2.5rem);
}
.blocks-grid-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
  gap: 1.2rem;
  width: 100%;
}
.block-card {
  background: var(--trivia-card);
  border-radius: 18px;
  padding: 1.5rem 2.5rem;
  border: 2px solid var(--trivia-accent);
  box-shadow: 0 4px 18px 0 rgba(94,252,219,0.10), 0 1.5px 8px 0 rgba(20,30,60,0.10);
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  transition: transform 0.18s cubic-bezier(.4,1.6,.6,1), box-shadow 0.18s, border-color 0.18s, background 0.18s;
  position: relative;
  text-align: center;
}
.block-card-big {
  min-height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0.5rem;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.block-card-big::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 16px;
  background: radial-gradient(circle at top, rgba(255, 214, 102, 0.25), transparent 60%);
  opacity: 0;
  transition: opacity 0.25s ease;
}
.block-card-big:hover::before,
.casilla-link:focus .block-card-big::before {
  opacity: 1;
}
.casilla-num {
  font-size: 2.5rem;
  font-weight: 800;
  color: #ffd666;
  margin-bottom: 0.5rem;
}
.casilla-link {
  text-decoration: none;
  transition: transform 0.1s;
  display: block;
}
.casilla-link.casilla-disabled {
  pointer-events: none;
}
.casilla-link.casilla-disabled .block-card {
  border-color: rgba(182, 194, 255, 0.25);
  color: rgba(255, 255, 255, 0.4);
  background: rgba(16, 18, 26, 0.65);
  position: relative;
}
.casilla-tag {
  font-size: 0.75rem;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 0.4rem;
}
.casilla-link:hover .block-card,
.casilla-link:focus .block-card {
  transform: scale(1.08) rotate(-2deg);
  box-shadow: 0 8px 32px 0 rgba(94,252,219,0.18), 0 2.5px 12px 0 rgba(20,30,60,0.13);
  border-color: #ffd666;
  background: linear-gradient(90deg, #5efcdb 0%, #5c6bff 100%);
  color: #222;
}
.block-progress {
  margin-top: 1.5rem;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
  font-size: 0.95rem;
  color: #c3caef;
}
.completion-cta {
  margin-top: 1.5rem;
  text-align: center;
}
.hero-content {
  padding: clamp(2rem, 3vw, 2.8rem);
  border-radius: 32px;
  background: radial-gradient(circle at top left, rgba(94, 252, 219, 0.18), transparent 55%), rgba(13, 16, 27, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 35px 65px rgba(5, 6, 10, 0.7);
  width: min(920px, 100%);
  margin: 0;
}
.block-summary-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5, 6, 10, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  z-index: 50;
}
.block-summary-card {
  background: rgba(16, 18, 26, 0.95);
  border-radius: 28px;
  padding: 2.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
  max-width: 460px;
  width: 100%;
  box-shadow: 0 25px 65px rgba(0, 0, 0, 0.45);
}
.summary-eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.25em;
  font-size: 0.68rem;
  color: var(--trivia-tone);
  margin-bottom: 0.5rem;
}
.summary-score {
  margin: 0.4rem 0;
  font-size: 1.05rem;
  color: #cfd4ff;
}
.summary-score strong {
  color: #fff;
  font-size: 1.2rem;
}
.summary-cta {
  margin-top: 1.5rem;
  width: 100%;
}
.empty-state {
  text-align: center;
  color: #c3caef;
  padding: 1rem 0;
  font-size: 0.95rem;
}
</style>
