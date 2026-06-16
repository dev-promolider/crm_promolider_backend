<template>
  <div class="ruleta-public-main">
    <div class="ruleta-header">
      <h2>{{ dinamica.nombre }}</h2>
      <p class="ruleta-desc">{{ dinamica.descripcion }}</p>
      <div class="ruleta-status" :class="statusClass">
        <span v-if="hayGanador"><i class="feather icon-award"></i> Finalizada</span>
        <span v-else-if="dinamica.is_active"><i class="feather icon-check-circle"></i> Activa</span>
        <span v-else><i class="feather icon-clock"></i> Pendiente de activación</span>
      </div>
    </div>

    <div v-if="closurePanelVisible" class="alert alert-success text-center">
      <h5><i class="feather icon-award"></i> ¡Juego terminado!</h5>
      <p>Esta dinámica ha finalizado porque ya hay un ganador.<br><strong>¡Gracias por participar!</strong></p>
      <a href="#participantes" class="btn btn-primary mt-2">
        <i class="feather icon-users"></i> Ver participantes y ganadores
      </a>
    </div>

    <div v-else-if="shouldShowGameArea">
      <div class="row">
        <div class="col-md-4">
          <div class="card bg-light turn-list">
            <div class="card-header">
              <h6 class="mb-0"><i class="feather icon-list"></i> Lista de Turnos</h6>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                <div
                  v-for="registro in participantsList"
                  :key="registro.id"
                  class="list-group-item"
                  :class="{
                    'bg-warning text-dark': activeTurno && activeTurno.id === registro.id,
                    'bg-success text-white': registro.ha_ganado,
                    'bg-secondary text-white': registro.ha_jugado && !registro.ha_ganado
                  }"
                >
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>Turno #{{ registro.turno }}</strong><br>
                      <small>{{ registro.nombre }} {{ registro.apellido }}</small>
                    </div>
                    <div>
                      <span v-if="registro.ha_ganado" class="badge badge-light">🏆 GANÓ</span>
                      <span v-else-if="registro.ha_jugado" class="badge badge-light text-dark">✓ Jugó</span>
                      <span v-else-if="activeTurno && activeTurno.id === registro.id" class="badge badge-dark">▶ Jugando</span>
                      <span v-else class="badge badge-secondary">En espera</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="usuarioRegistro && !esMiTurno && !usuarioRegistro.ha_jugado" class="alert alert-info mt-3">
              <strong>Tu turno: #{{ usuarioRegistro.turno }}</strong><br>
              <small>Espera tu turno para jugar</small>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div v-if="hayGanador" class="alert alert-warning">
            <h5 class="mb-1"><i class="feather icon-alert-triangle"></i> Tenemos un ganador</h5>
            <p class="mb-0">Cerrando la dinámica en segundos...</p>
          </div>
          <div v-if="esMiTurnoComputed" class="alert alert-success">
            <h5><i class="feather icon-play"></i> ¡Es tu turno!</h5>
            <p class="mb-0">Gira la ruleta ahora</p>
          </div>
          <div v-else class="alert alert-info">
            <h5><i class="feather icon-eye"></i> Observando</h5>
            <p v-if="activeTurno">Está jugando: <strong>{{ activeTurno.nombre }} {{ activeTurno.apellido }}</strong> (Turno #{{ activeTurno.turno }})</p>
          </div>
          <div v-if="dinamica.premios && dinamica.premios.length">
            <dinamica-ruleta
              :premios="dinamica.premios"
              :puede-girar="usuarioRegistro && esMiTurnoComputed"
              :registrar-ganador-url="registrarGanadorUrl"
              :marcar-jugado-url="marcarJugadoUrl"
              :spin-url="spinUrl"
              :channel="publicChannelName"
              :dinamica-slug="dinamica.slug"
            />
            <div v-if="activeTurno && timerExpiresAt" class="temporizador-container mb-3">
              <temporizador-turno
                :expires-at="timerExpiresAt"
                :duration="turnDurationSecondsValue"
                @timeout="handleTimerTimeout"
              />
            </div>
            <div v-if="!usuarioRegistro" class="alert alert-info mt-3">
              Regístrate para obtener un turno y poder girar cuando te toque.
            </div>
          </div>
          <div v-else>
            <div class="alert alert-warning">No hay premios configurados para esta dinámica.</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!dinamica.is_active && !hayGanador && usuarioRegistro">
      <div class="alert alert-info">
        <h5 class="mb-1"><i class="feather icon-info"></i> Registro completado</h5>
        <p class="mb-0">
          Te avisaremos apenas inicie la ruleta. Tu turno asignado es <strong>#{{ usuarioRegistro.turno }}</strong>.
        </p>
      </div>
    </div>

    <div v-if="canRegister">
      <div class="register-panel">
        <div class="register-panel__top">
          <div>
            <span class="register-panel__chip">
              <i class="feather icon-edit-3"></i>
              Paso 1
            </span>
            <h5 class="register-panel__title">Regístrate para participar</h5>
            <p class="register-panel__subtitle">Completa tus datos y obten un turno verificado para jugar.</p>
          </div>
          <div class="register-panel__trust">
            <i class="feather icon-shield"></i>
            Datos protegidos
          </div>
        </div>
        <div
          v-if="!dinamica.is_active && !hayGanador"
          class="register-panel__notice"
        >
          <i class="feather icon-clock"></i>
          <div>
            <strong>Ruleta en preparación</strong>
            <p class="mb-0">Regístrate ahora y te avisaremos apenas inicie.</p>
          </div>
        </div>
        <div
          v-if="registrationLimitEnabled && registrationWindowMinutes"
          class="registro-banner mt-3 mb-0"
        >
          <div class="registro-banner__icon">
            <i class="feather icon-clock"></i>
          </div>
          <div>
            <p class="registro-banner__label">Registro disponible</p>
            <p class="registro-banner__value">
              {{ countdownText }}
              <span class="registro-banner__meta">(máx. {{ registrationWindowMinutes }} min)</span>
            </p>
          </div>
        </div>
        <div class="register-panel__grid mt-4">
          <div class="register-panel__summary">
            <h6>Antes de enviar</h6>
            <ul class="register-panel__summary-list">
              <li class="register-panel__summary-item">
                <i class="feather icon-hash"></i>
                <div>
                  <span>Recibirás un turno único</span>
                  <small>Te notificaremos cuando se acerque tu número.</small>
                </div>
              </li>
              <li class="register-panel__summary-item">
                <i class="feather icon-lock"></i>
                <div>
                  <span>Un registro por persona</span>
                  <small>Validamos los datos para mantener la dinámica justa.</small>
                </div>
              </li>
              <li class="register-panel__summary-item">
                <i class="feather icon-bell"></i>
                <div>
                  <span>Recibe alertas</span>
                  <small>Usaremos tu email para avisarte si resultas ganador.</small>
                </div>
              </li>
            </ul>
          </div>
          <div class="register-panel__form-card">
            <form @submit.prevent="registrarUsuario">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label for="nombre">Nombre *</label>
                    <input
                      type="text"
                      v-model="form.nombre"
                      class="form-control"
                      id="nombre"
                      required
                      placeholder="Tu nombre"
                    >
                    <small class="form-text text-muted">Como aparece en tu documento.</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label for="apellido">Apellido *</label>
                    <input
                      type="text"
                      v-model="form.apellido"
                      class="form-control"
                      id="apellido"
                      required
                      placeholder="Tu apellido"
                    >
                    <small class="form-text text-muted">Lo usaremos para identificar tu turno.</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label for="email">Email *</label>
                    <input
                      type="email"
                      v-model="form.email"
                      class="form-control"
                      id="email"
                      required
                      placeholder="tu@email.com"
                    >
                    <small class="form-text text-muted">Te avisaremos si ganaste.</small>
                  </div>
                </div>
              </div>
              <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-end mt-2">
                <button type="submit" class="btn btn-primary btn-lg w-100 w-md-auto" :disabled="loading">
                  <i class="feather icon-user-plus"></i> Registrarme y obtener turno
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="registrationLimitEnabled && !usuarioRegistro" class="registro-closed mt-3">
      <div class="registro-closed__icon">
        <i class="feather icon-alert-circle"></i>
      </div>
      <div>
        <p class="registro-closed__label">Inscripciones cerradas</p>
        <p class="registro-closed__text">
          El cupo se llenó y el período de registro terminó. Esta dinámica ya no admite nuevas inscripciones.
        </p>
      </div>
    </div>


    <div v-if="participantsList && participantsList.length" class="card participants-card mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0">Participantes registrados</h6>
          <small class="text-muted">Se muestran los turnos asignados más recientes.</small>
        </div>
        <span class="badge badge-pill badge-secondary">{{ participantsList.length }}</span>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Turno</th>
              <th>Nombre</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="registro in participantsList"
              :key="registro.id"
              :class="{
                'table-success': usuarioRegistro && registro.id === usuarioRegistro.id,
                'table-active': activeTurno && registro.id === activeTurno.id
              }"
            >
              <td>#{{ registro.turno }}</td>
              <td>{{ registro.nombre }} {{ registro.apellido }}</td>
              <td>
                <span v-if="registro.ha_ganado" class="badge badge-success">Ganador</span>
                <span v-else-if="registro.ha_jugado" class="badge badge-secondary">Ya jugó</span>
                <span v-else-if="activeTurno && registro.id === activeTurno.id" class="badge badge-warning text-dark">Jugando</span>
                <span v-else class="badge badge-light">Pendiente</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'RuletaPublicMain',
  props: {
    dinamica: { type: Object, required: true },
    hayGanador: { type: Boolean, default: false },
    registros: { type: Array, default: () => [] },
    turnoActual: { type: Object, default: null },
    usuarioRegistro: { type: Object, default: null },
    esMiTurno: { type: Boolean, default: false },
    registrarGanadorUrl: { type: String, default: '' },
    marcarJugadoUrl: { type: String, default: '' },
    spinUrl: { type: String, default: '' },
    tiempoRestante: { type: Number, default: null },
    turnoStartedAt: { type: String, default: null },
    turnoExpiresAt: { type: String, default: null },
    turnoDurationSeconds: { type: Number, default: 90 },
    showRegisterPanel: { type: Boolean, default: false },
    registerUrl: { type: String, default: '' },
    registrationSecondsLeft: { type: Number, default: null },
    registrationWindowMinutes: { type: Number, default: null },
    registrationLimitEnabled: { type: Boolean, default: false },
    participantsFeedUrl: { type: String, default: '' },
    statusUrl: { type: String, default: '' }
  },
  data() {
    return {
      form: { nombre: '', apellido: '', email: '' },
      loading: false,
      remainingSeconds: this.registrationSecondsLeft,
      countdownTimer: null,
      participants: Array.isArray(this.registros) ? [...this.registros] : [],
      participantsTimer: null,
      channel: null,
      channelName: null,
      statusTimer: null,
      currentTurno: this.turnoActual ? { ...this.turnoActual } : null,
      currentTurnStartedAt: this.turnoStartedAt,
      currentTurnExpiresAt: this.turnoExpiresAt,
      turnDurationSecondsLocal: this.turnoDurationSeconds || this.tiempoRestante || 90,
      esMiTurnoLocal: this.esMiTurno,
      dinamicaSlug: this.dinamica && this.dinamica.slug ? this.dinamica.slug : '',
      closurePanelVisible: false,
      closureTimerId: null,
      closureDelayMs: 3000,
    };
  },
  computed: {
    statusClass() {
      if (this.hayGanador) return 'status-finalizada';
      if (this.dinamica.is_active) return 'status-activa';
      return 'status-pendiente';
    },
    canRegister() {
      if (!this.showRegisterPanel) return false;
      if (!this.registrationLimitEnabled) return true;
      return this.remainingSeconds === null || this.remainingSeconds > 0;
    },
    countdownText() {
      if (this.remainingSeconds === null || this.remainingSeconds === undefined) {
        return 'Sin límite';
      }
      const safeSeconds = Math.max(this.remainingSeconds, 0);
      const minutes = Math.floor(safeSeconds / 60)
        .toString()
        .padStart(2, '0');
      const seconds = (safeSeconds % 60)
        .toString()
        .padStart(2, '0');
      return `${minutes}:${seconds}`;
    },
    participantsList() {
      return this.participants;
    },
    publicChannelName() {
      return 'public-ruleta';
    },
    activeTurno() {
      return this.currentTurno;
    },
    esMiTurnoComputed() {
      return this.esMiTurnoLocal;
    },
    timerExpiresAt() {
      if (this.currentTurnExpiresAt) {
        return this.currentTurnExpiresAt;
      }
      if (this.tiempoRestante) {
        const fallback = Date.now() + this.tiempoRestante * 1000;
        return new Date(fallback).toISOString();
      }
      return null;
    },
    turnDurationSecondsValue() {
      return this.turnDurationSecondsLocal || this.turnoDurationSeconds || 90;
    },
    shouldShowGameArea() {
      if (this.closurePanelVisible) {
        return false;
      }
      if (!this.usuarioRegistro) {
        return false;
      }
      if (this.hayGanador) {
        return true;
      }
      return this.dinamica.is_active && !this.hayGanador;
    }
  },
  methods: {
    registrarUsuario() {
      if (!this.registerUrl || this.loading) {
        return;
      }
      this.loading = true;
      axios
        .post(this.registerUrl, this.form)
        .then((response) => {
          const message = response.data?.message || 'Registro exitoso. Revisa tu turno.';
          if (this.$swal) {
            this.$swal
              .fire({
                title: '¡Listo!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#38ef7d'
              })
              .then(() => window.location.reload());
          } else {
            window.location.reload();
          }
        })
        .catch((error) => {
          const message = error.response?.data?.message || 'No se pudo registrar. Intenta nuevamente.';
          if (this.$swal) {
            this.$swal.fire({
              title: 'Error',
              text: message,
              icon: 'error',
              confirmButtonColor: '#eb3349'
            });
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
    reloadPage() {
      window.location.reload();
    },
    startCountdown() {
      if (!this.registrationLimitEnabled || this.remainingSeconds === null) {
        return;
      }
      this.clearCountdown();
      this.countdownTimer = setInterval(() => {
        if (this.remainingSeconds > 0) {
          this.remainingSeconds -= 1;
        } else {
          this.clearCountdown();
        }
      }, 1000);
    },
    clearCountdown() {
      if (this.countdownTimer) {
        clearInterval(this.countdownTimer);
        this.countdownTimer = null;
      }
    },
    scheduleClosurePanel() {
      if (this.closurePanelVisible) {
        return;
      }
      if (this.closureTimerId) {
        return;
      }
      this.closureTimerId = setTimeout(() => {
        this.closurePanelVisible = true;
        this.clearClosureTimer();
      }, this.closureDelayMs);
    },
    clearClosureTimer() {
      if (this.closureTimerId) {
        clearTimeout(this.closureTimerId);
        this.closureTimerId = null;
      }
    },
    fetchParticipants() {
      if (!this.participantsFeedUrl) {
        return;
      }
      axios
        .get(this.participantsFeedUrl)
        .then(({ data }) => {
          if (!data || !Array.isArray(data.participants)) {
            return;
          }
          this.participants = data.participants.map((registro) => ({
            id: registro.id,
            nombre: registro.nombre || registro.first_name || '',
            apellido: registro.apellido || registro.last_name || '',
            turno: registro.turno,
            ha_jugado: Boolean(registro.ha_jugado),
            ha_ganado: Boolean(registro.ha_ganado)
          }));

          if (data.turno_actual) {
            this.applyTurnoPayload({
              turno: data.turno_actual,
              started_at: data.turno_actual.started_at,
              expires_at: data.turno_actual.expires_at,
              duration: data.turno_duration_seconds,
              remaining: data.turno_remaining_seconds
            });
          }
        })
        .catch(() => {
          /* silencioso */
        });
    },
    startParticipantsPolling() {
      if (!this.participantsFeedUrl) {
        return;
      }
      this.stopParticipantsPolling();
      this.fetchParticipants();
      this.participantsTimer = setInterval(() => {
        this.fetchParticipants();
      }, 5000);
    },
    stopParticipantsPolling() {
      if (this.participantsTimer) {
        clearInterval(this.participantsTimer);
        this.participantsTimer = null;
      }
    },
    startStatusPolling() {
      if (!this.statusUrl) {
        return;
      }
      this.stopStatusPolling();
      this.statusTimer = setInterval(() => {
        axios
          .get(this.statusUrl)
          .then(({ data }) => {
            if (data?.success && data.is_active && !this.dinamica.is_active) {
              window.location.reload();
            }
          })
          .catch(() => {
            /* silencioso */
          });
      }, 5000);
    },
    stopStatusPolling() {
      if (this.statusTimer) {
        clearInterval(this.statusTimer);
        this.statusTimer = null;
      }
    },
    updateEsMiTurno() {
      if (!this.usuarioRegistro || !this.currentTurno) {
        this.esMiTurnoLocal = false;
        return;
      }
      this.esMiTurnoLocal = this.usuarioRegistro.id === this.currentTurno.id;
    },
    applyTurnoPayload(payload = {}) {
      const turnoData = payload.turno || payload;

      if (!turnoData) {
        this.currentTurno = null;
        this.currentTurnStartedAt = null;
        this.currentTurnExpiresAt = null;
        this.esMiTurnoLocal = false;
        return;
      }

      this.currentTurno = {
        ...this.currentTurno,
        ...turnoData
      };

      this.currentTurnStartedAt = payload.started_at || turnoData.started_at || this.currentTurnStartedAt;
      this.currentTurnExpiresAt = payload.expires_at || turnoData.expires_at || this.currentTurnExpiresAt;

      if (payload.duration || payload.turno_duration_seconds) {
        this.turnDurationSecondsLocal = payload.duration || payload.turno_duration_seconds;
      }

      if (!this.currentTurnExpiresAt && (payload.remaining || payload.turno_remaining_seconds)) {
        const fallback = Date.now() + (payload.remaining || payload.turno_remaining_seconds) * 1000;
        this.currentTurnExpiresAt = new Date(fallback).toISOString();
      }

      this.updateEsMiTurno();
    },
    handleTurnTimerEvent(event) {
      if (event?.dinamicaSlug && event.dinamicaSlug !== this.dinamicaSlug) {
        return;
      }
      this.applyTurnoPayload({
        turno: event.turno,
        started_at: event.startedAt,
        expires_at: event.expiresAt,
        duration: event.duration
      });
      this.fetchParticipants();
    },
    handleTimerTimeout() {
      if (this.esMiTurnoComputed && this.marcarJugadoUrl) {
        axios
          .post(this.marcarJugadoUrl, { reason: 'timeout' })
          .then(() => {
            this.fetchParticipants();
          })
          .catch(() => {
            this.fetchParticipants();
          });
      } else {
        this.fetchParticipants();
      }
    },
    setupWebsocket() {
      if (!window.Echo) {
        return;
      }
      const channelName = this.publicChannelName;
      this.channelName = channelName;
      this.channel = window.Echo.channel(channelName);
      this.channel
        .listen('RuletaSpinEvent', () => {
          this.fetchParticipants();
        })
        .listen('TurnoTimerEvent', this.handleTurnTimerEvent);
    }
  },
  watch: {
    registros: {
      immediate: true,
      handler(value) {
        this.participants = Array.isArray(value) ? [...value] : [];
      }
    },
    turnoActual: {
      immediate: true,
      handler(value) {
        this.currentTurno = value ? { ...value } : null;
        this.updateEsMiTurno();
      }
    },
    turnoExpiresAt: {
      immediate: true,
      handler(value) {
        this.currentTurnExpiresAt = value;
      }
    },
    turnoStartedAt: {
      immediate: true,
      handler(value) {
        this.currentTurnStartedAt = value;
      }
    },
    turnoDurationSeconds: {
      immediate: true,
      handler(value) {
        if (value) {
          this.turnDurationSecondsLocal = value;
        }
      }
    },
    esMiTurno(value) {
      this.esMiTurnoLocal = value;
    },
    usuarioRegistro: {
      deep: true,
      handler() {
        this.updateEsMiTurno();
      }
    },
    hayGanador(newVal) {
      if (newVal) {
        this.scheduleClosurePanel();
      } else {
        this.clearClosureTimer();
        this.closurePanelVisible = false;
      }
    }
  },
  mounted() {
    this.startCountdown();
    this.startParticipantsPolling();
    this.setupWebsocket();
    this.startStatusPolling();
    if (this.hayGanador) {
      this.scheduleClosurePanel();
    }
  },
  beforeDestroy() {
    this.clearCountdown();
    this.stopParticipantsPolling();
    this.stopStatusPolling();
    this.clearClosureTimer();
    if (this.channelName && window.Echo) {
      window.Echo.leave(this.channelName);
      this.channelName = null;
      this.channel = null;
    }
  }
};
</script>

<style scoped>
.ruleta-public-main {
  background: #f8fafc;
  border-radius: 16px;
  padding: 2rem 1rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
  max-width: 1200px;
  margin: 2rem auto;
}
.ruleta-header {
  position: relative;
  text-align: center;
  margin-bottom: 2rem;
  padding: 2.75rem 1.5rem 2.25rem;
  border-radius: 28px;
  background: linear-gradient(125deg, #d1ffe4 0%, #a7f3d0 40%, #6ee7b7 100%);
  box-shadow: 0 25px 70px rgba(14, 116, 144, 0.18);
  overflow: hidden;
}
.ruleta-header::after {
  content: '';
  position: absolute;
  inset: 12px;
  border-radius: 22px;
  border: 1px solid rgba(16, 185, 129, 0.3);
  pointer-events: none;
}
.ruleta-header h2 {
  position: relative;
  font-size: 2.4rem;
  font-weight: 800;
  color: #064e3b;
  margin-bottom: 0.35rem;
}
.ruleta-desc {
  position: relative;
  color: #0f172a;
  font-size: 1.05rem;
  margin-bottom: 0.75rem;
  opacity: 0.85;
}
.ruleta-status {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 1.4rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 1rem;
  margin-top: 0.5rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
}
.status-finalizada {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
}
.status-activa {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  color: #fff;
}
.status-pendiente {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  color: #fff;
}
.turn-list {
  margin-bottom: 1.5rem;
}
.register-panel {
  background: linear-gradient(135deg, #f0fff4 0%, #dcfce7 45%, #bbf7d0 100%);
  border-radius: 24px;
  border: 1px solid rgba(16, 185, 129, 0.2);
  box-shadow: 0 24px 60px rgba(16, 185, 129, 0.2);
  padding: 2rem;
  margin-top: 2rem;
}
.register-panel__top {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}
.register-panel__chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.4rem 0.8rem;
  border-radius: 999px;
  background: rgba(5, 150, 105, 0.15);
  color: #065f46;
}
.register-panel__title {
  margin-top: 0.75rem;
  margin-bottom: 0.25rem;
  font-size: 1.75rem;
  color: #052e16;
}
.register-panel__subtitle {
  margin: 0;
  color: #086143;
  font-weight: 500;
}
.register-panel__trust {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(8, 145, 178, 0.12);
  color: #0f766e;
  padding: 0.6rem 1rem;
  border-radius: 999px;
  font-weight: 600;
}
.register-panel__notice {
  display: flex;
  gap: 0.8rem;
  align-items: flex-start;
  background: rgba(250, 204, 21, 0.2);
  border: 1px dashed rgba(180, 83, 9, 0.6);
  border-radius: 16px;
  padding: 1rem 1.2rem;
  margin-top: 1.5rem;
  color: #7c2d12;
}
.register-panel__notice strong {
  display: block;
  font-size: 0.95rem;
}
.register-panel__grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr);
  gap: 2rem;
}
.register-panel__summary {
  background: rgba(255, 255, 255, 0.65);
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.1);
}
.register-panel__summary h6 {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.8rem;
  color: #065f46;
  margin-bottom: 1rem;
}
.register-panel__summary-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.register-panel__summary-item {
  display: flex;
  gap: 0.85rem;
  align-items: flex-start;
  background: rgba(16, 185, 129, 0.12);
  border-radius: 14px;
  padding: 0.85rem 1rem;
  color: #064e3b;
}
.register-panel__summary-item i {
  font-size: 1.2rem;
  color: #047857;
}
.register-panel__summary-item span {
  display: block;
  font-weight: 700;
}
.register-panel__summary-item small {
  display: block;
  color: #065f46;
  font-size: 0.85rem;
}
.register-panel__form-card {
  background: #fff;
  border-radius: 24px;
  padding: 1.8rem;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
}
.register-panel__form-card label {
  font-weight: 600;
  color: #0f172a;
}
.register-panel__form-card .form-text {
  color: #475569;
}
.w-md-auto {
  width: 100%;
}
@media (min-width: 768px) {
  .w-md-auto {
    width: auto;
  }
}
@media (max-width: 767.98px) {
  .register-panel {
    padding: 1.5rem;
  }
  .register-panel__grid {
    grid-template-columns: 1fr;
  }
  .register-panel__trust {
    width: 100%;
    justify-content: center;
  }
  .register-panel__notice {
    flex-direction: column;
    text-align: left;
  }
  .register-panel__form-card {
    padding: 1.2rem;
  }
}
.participants-card {
  border-radius: 12px;
  box-shadow: 0 2px 18px rgba(15, 23, 42, 0.08);
}
.participants-card .card-header {
  background: #fff;
  border-bottom: 1px solid #f1f5f9;
}
.participants-card .table {
  font-size: 0.95rem;
}
.participants-card .table th {
  border-top: none;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.04em;
  color: #6b7280;
}
.participants-card .table td {
  vertical-align: middle;
}
.registro-closed {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  background: linear-gradient(135deg, #eef2ff 0%, #e0f2fe 45%, #cffafe 100%);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 18px;
  padding: 1.4rem 1.6rem;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.18);
}
.registro-closed__icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.75);
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.registro-closed__label {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.78rem;
  font-weight: 700;
  color: #1d4ed8;
  margin-bottom: 0.35rem;
}
.registro-closed__text {
  margin-bottom: 0.4rem;
  font-size: 1rem;
  color: #0f172a;
}
.registro-banner {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: linear-gradient(135deg, #fff7d6 0%, #ffd59e 45%, #ffaf7b 100%);
  border-radius: 16px;
  padding: 1rem 1.4rem;
  box-shadow: 0 8px 24px rgba(255, 175, 123, 0.35);
}
.registro-banner__icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #b45309;
}
.registro-banner__label {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
  font-weight: 700;
  color: #7c2d12;
  margin-bottom: 0.15rem;
}
.registro-banner__value {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}
.registro-banner__meta {
  font-size: 0.95rem;
  font-weight: 600;
  color: #9a3412;
  margin-left: 0.35rem;
}
@media (max-width: 576px) {
  .registro-banner {
    flex-direction: column;
    text-align: center;
  }
  .registro-banner__icon {
    margin: 0 auto;
  }
  .registro-closed {
    flex-direction: column;
    text-align: center;
  }
  .registro-closed__icon {
    margin: 0 auto;
  }
}
</style>
