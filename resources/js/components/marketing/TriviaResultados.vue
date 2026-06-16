<template>
  <div class="trivia-results-shell">
    <section class="results-hero">
      <h1>Resultados de {{ dinamica.nombre || 'la trivia' }}</h1>
      <p class="hero-sub">Categoría {{ categoria }} · Puntaje máximo {{ puntajeTotal }} pts</p>
      <div class="hero-stats">
        <div class="stat-card">
          <span>Total participantes</span>
          <strong>{{ totalParticipantes }}</strong>
        </div>
        <div class="stat-card">
          <span>Casillas jugadas</span>
          <strong>{{ casillasTotales }}</strong>
        </div>
      </div>
    </section>

    <section class="results-grid">
      <article class="panel winner-panel">
        <header>
          <small>Ganador</small>
          <h2>{{ winnerName || 'En espera' }}</h2>
        </header>
        <p v-if="winnerName" class="winner-email">{{ winnerEmail }}</p>
        <div class="winner-points">
          <span>Puntaje logrado</span>
          <strong>{{ winnerPoints }} pts</strong>
        </div>
        <p class="winner-note" v-if="winnerName">
          ¡Felicitaciones! Este participante alcanzó la meta establecida.
        </p>
        <p class="winner-note" v-else>
          El equipo aún puede validar respuestas antes de anunciar al ganador.
        </p>
      </article>

    </section>

    <section class="leaderboard-section">
      <header>
        <div>
          <small>Participantes registrados</small>
          <h3>Tabla de posiciones</h3>
        </div>
        <span class="chip">{{ leaderboard.length }} personas</span>
      </header>

      <div v-if="leaderboard.length" class="leaderboard-table">
        <article
          v-for="(participant, index) in leaderboard"
          :key="participant.id"
          class="leaderboard-row"
          :class="{
            'is-winner': participant.haGanado,
            'is-active': !participant.haGanado && participant.haJugado
          }"
        >
          <div class="rank">#{{ index + 1 }}</div>
          <div class="participant-meta">
            <strong>{{ displayName(participant) }}</strong>
            <small>{{ participant.email }}</small>
          </div>
          <div class="participant-status">
            <span v-if="participant.haGanado" class="badge">🏆 Ganó</span>
            <span v-else-if="participant.haJugado" class="badge neutral">Completó</span>
            <span v-else class="badge muted">Pendiente</span>
          </div>
          <div class="participant-points">
            <strong>{{ participant.puntaje }} pts</strong>
            <small
              v-if="participant.tiempoTotal || participant.tiempoTotal === 0"
              class="participant-time"
            >{{ participant.tiempoTotal }} s</small>
          </div>
        </article>
      </div>
      <div v-else class="empty-state">
        Nadie se ha registrado todavía. Comparte la trivia para sumar participantes.
      </div>
    </section>

    <section class="blocks-breakdown" v-if="hasBlockBreakdown">
      <header>
        <div>
          <small>Detalle final</small>
          <h3>Puntajes por bloque</h3>
        </div>
      </header>
      <div class="blocks-table" :style="`--block-count: ${blocks.length}`">
        <div class="blocks-row blocks-row-head">
          <span>Participante</span>
          <span
            v-for="(block, index) in blocks"
            :key="`block-head-${index}`"
          >{{ block.title || `Bloque ${ index + 1 }` }}</span>
          <span>Total</span>
        </div>
        <div
          class="blocks-row"
          v-for="participant in leaderboard"
          :key="`block-score-${participant.id}`"
        >
          <span class="block-participant">{{ displayName(participant) }}</span>
          <span
            v-for="(block, index) in blocks"
            :key="`block-score-${participant.id}-${index}`"
          >{{ formatPoints(blockScore(participant, index)) }}</span>
          <span class="block-total">{{ formatPoints(participant.puntaje) }}</span>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'TriviaResultados',
  props: {
    dinamica: { type: Object, default: () => ({}) },
    categoria: { type: String, default: 'Cultura General' },
    puntajeTotal: { type: Number, default: 0 },
    puntajes: { type: Array, default: () => [] },
    leaderboard: { type: Array, default: () => [] },
    winner: { type: Object, default: null },
    totalParticipantes: { type: Number, default: 0 },
    casillasTotales: { type: Number, default: 0 },
    slug: { type: String, default: '' },
    blocks: { type: Array, default: () => [] }
  },
  computed: {
    winnerName() {
      return this.winner ? this.displayName(this.winner) : null;
    },
    winnerEmail() {
      return this.winner && this.winner.email ? this.winner.email : null;
    },
    winnerPoints() {
      if (this.winner && typeof this.winner.puntaje !== 'undefined') {
        return this.winner.puntaje;
      }
      if (this.leaderboard && this.leaderboard.length) {
        const mejorMarca = this.leaderboard[0]?.puntaje ?? this.puntajeTotal;
        return typeof mejorMarca === 'number' ? mejorMarca : this.puntajeTotal;
      }
      return this.puntajeTotal;
    },
    hasBlockBreakdown() {
      return this.blocks.length > 0 && this.leaderboard.length > 0;
    }
  },
  methods: {
    displayName(participant) {
      const nombre = participant.nombre || '';
      const apellido = participant.apellido || '';
      return (nombre + ' ' + apellido).trim() || participant.email || 'Participante';
    },
    blockScore(participant, blockIndex) {
      if (!participant || !Array.isArray(participant.blocks)) {
        return 0;
      }
      const match = participant.blocks.find(block => block.index === blockIndex);
      return match ? match.puntaje : 0;
    },
    formatPoints(value) {
      const normalized = Number(value || 0);
      return `${normalized % 1 === 0 ? normalized : normalized.toFixed(1)} pts`;
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap');
.trivia-results-shell {
  min-height: 100vh;
  width: 100vw;
  margin-left: calc(50% - 50vw);
  margin-right: calc(50% - 50vw);
  padding: 4rem clamp(1rem, 4vw, 3rem) 5rem;
  background: radial-gradient(circle at top, rgba(94, 252, 219, 0.18), transparent 45%),
    radial-gradient(circle at 20% 20%, rgba(92, 107, 255, 0.20), transparent 40%),
    #05060a;
  color: #f7f8ff;
  font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
  --results-panel-width: clamp(720px, 75vw, 980px);
}
.results-hero {
  max-width: 900px;
  margin: 0 auto 3rem;
  text-align: center;
}
.results-hero h1 {
  margin: 1.5rem 0 0.5rem;
  font-size: clamp(2.1rem, 4vw, 3.2rem);
}
.hero-sub {
  color: #98a3d3;
  font-size: 1.05rem;
  margin-bottom: 2rem;
}
.hero-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}
.stat-card {
  background: rgba(13, 16, 27, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.2rem;
}
.stat-card span {
  font-size: 0.75rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #8f9cd8;
}
.stat-card strong {
  display: block;
  margin-top: 0.5rem;
  font-size: 1.8rem;
}
.results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.5rem;
  margin: 0 auto 3rem;
  width: 100%;
  max-width: var(--results-panel-width);
}
.panel {
  background: rgba(13, 16, 27, 0.92);
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 2rem;
  box-shadow: 0 25px 60px rgba(5, 6, 10, 0.45);
  width: 100%;
}
.panel header small {
  letter-spacing: 0.25em;
  text-transform: uppercase;
  color: #8ea0ff;
  font-size: 0.7rem;
}
.panel header h2,
.panel header h3 {
  margin-top: 0.5rem;
}
.winner-email {
  color: #a9b6ff;
  margin: 0.5rem 0 1rem;
}
.winner-points {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(94, 252, 219, 0.08);
  border: 1px solid rgba(94, 252, 219, 0.35);
  border-radius: 18px;
  padding: 1rem 1.25rem;
}
.winner-points strong {
  font-size: 1.6rem;
  color: #5efcdb;
}
.winner-note {
  margin-top: 1rem;
  color: #94a0d6;
  font-size: 0.95rem;
}
.leaderboard-section {
  max-width: var(--results-panel-width);
  background: rgba(13, 16, 27, 0.9);
  border-radius: 30px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 2rem;
  width: 100%;
  margin: 0 auto;
}
.leaderboard-section header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.leaderboard-section header small {
  letter-spacing: 0.22em;
  font-size: 0.7rem;
  color: #8ea0ff;
  text-transform: uppercase;
}
.chip {
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.35rem 1rem;
  font-size: 0.85rem;
}
.leaderboard-table {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.leaderboard-row {
  display: grid;
  grid-template-columns: 60px minmax(0, 1fr) 160px 120px;
  gap: 1rem;
  align-items: center;
  padding: 1rem 1.2rem;
  border-radius: 18px;
  background: rgba(5, 6, 10, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.rank {
  font-size: 1.4rem;
  font-weight: 700;
  color: #ffd666;
}
.participant-meta strong {
  font-size: 1.05rem;
}
.participant-meta small {
  color: #9eaad8;
}
.participant-status {
  text-align: right;
}
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem 0.8rem;
  border-radius: 999px;
  font-size: 0.85rem;
  background: rgba(94, 252, 219, 0.15);
  border: 1px solid rgba(94, 252, 219, 0.5);
  color: #5efcdb;
}
.badge.neutral {
  background: rgba(255, 214, 102, 0.15);
  border-color: rgba(255, 214, 102, 0.4);
  color: #ffd666;
}
.badge.muted {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.15);
  color: #c3c8e5;
}
.participant-points strong {
  font-size: 1.2rem;
}
.participant-time {
  display: block;
  font-size: 0.85rem;
  color: #9eaad8;
}
.leaderboard-row.is-winner {
  border-color: rgba(94, 252, 219, 0.5);
  box-shadow: 0 15px 40px rgba(94, 252, 219, 0.15);
}
.leaderboard-row.is-active:not(.is-winner) {
  border-color: rgba(255, 214, 102, 0.4);
}
.empty-state {
  padding: 2rem;
  border-radius: 20px;
  border: 1px dashed rgba(255, 255, 255, 0.25);
  text-align: center;
  color: #98a4d1;
}
.blocks-breakdown {
  margin-top: 3rem;
  background: rgba(13, 16, 27, 0.92);
  border-radius: 30px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 2rem;
}
.blocks-breakdown header small {
  letter-spacing: 0.22em;
  font-size: 0.7rem;
  color: #8ea0ff;
  text-transform: uppercase;
}
.blocks-table {
  margin-top: 1.5rem;
  overflow-x: auto;
}
.blocks-row {
  display: grid;
  grid-template-columns: minmax(190px, 2fr) repeat(var(--block-count), minmax(110px, 1fr)) minmax(120px, 1fr);
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.95rem;
}
.blocks-row-head {
  font-size: 0.85rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #8ea0ff;
}
.blocks-row:last-child {
  border-bottom: none;
}
.block-participant {
  font-weight: 600;
}
.block-total {
  font-weight: 600;
  color: #5efcdb;
}
.blocks-row span {
  white-space: nowrap;
}
@media (max-width: 768px) {
  .leaderboard-row {
    grid-template-columns: 50px 1fr;
    grid-template-rows: auto auto;
    row-gap: 0.5rem;
  }
  .participant-status,
  .participant-points {
    text-align: left;
  }
  .blocks-row {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    font-size: 0.9rem;
  }
}
</style>
