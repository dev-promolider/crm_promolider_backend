<template>
  <div class="trivia-public-main">
    <div class="trivia-header">
      <h2>{{ dinamica.nombre }}</h2>
      <p class="trivia-desc">{{ dinamica.descripcion }}</p>
      <div class="trivia-status" :class="statusClass">
        <span v-if="hayGanador"><i class="feather icon-award"></i> Finalizada</span>
        <span v-else-if="dinamica.is_active"><i class="feather icon-check-circle"></i> Activa</span>
        <span v-else><i class="feather icon-clock"></i> Pendiente de activación</span>
      </div>
    </div>

    <div v-if="hayGanador" class="alert alert-success text-center">
      <h5><i class="feather icon-award"></i> ¡Juego terminado!</h5>
      <p>Esta dinámica ha finalizado porque ya hay un ganador.<br><strong>¡Gracias por participar!</strong></p>
      <a href="#participantes" class="btn btn-primary mt-2">
        <i class="feather icon-users"></i> Ver participantes y ganadores
      </a>
    </div>

    <div v-else-if="dinamica.is_active && !hayGanador">
      <div class="row">
        <div class="col-md-4">
          <div class="card bg-light turn-list">
            <div class="card-header">
              <h6 class="mb-0"><i class="feather icon-list"></i> Lista de Turnos</h6>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                <div v-for="registro in registros" :key="registro.id"
                  class="list-group-item"
                  :class="{
                    'bg-warning text-dark': turnoActual && turnoActual.id === registro.id,
                    'bg-success text-white': registro.ha_ganado,
                    'bg-secondary text-white': registro.ha_jugado && !registro.ha_ganado
                  }">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>Turno #{{ registro.turno }}</strong><br>
                      <small>{{ registro.nombre }} {{ registro.apellido }}</small>
                    </div>
                    <div>
                      <span v-if="registro.ha_ganado" class="badge badge-light">🏆 GANÓ</span>
                      <span v-else-if="registro.ha_jugado" class="badge badge-light text-dark">✓ Jugó</span>
                      <span v-else-if="turnoActual && turnoActual.id === registro.id" class="badge badge-dark">▶ Jugando</span>
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
          <div v-if="esMiTurno" class="alert alert-success">
            <h5><i class="feather icon-play"></i> ¡Es tu turno!</h5>
            <p class="mb-0">Gira la ruleta ahora</p>
          </div>
          <div v-else class="alert alert-info">
            <h5><i class="feather icon-eye"></i> Observando</h5>
            <p v-if="turnoActual">Está jugando: <strong>{{ turnoActual.nombre }} {{ turnoActual.apellido }}</strong> (Turno #{{ turnoActual.turno }})</p>
          </div>
          <div v-if="usuarioRegistro && dinamica.premios && dinamica.premios.length">
            <dinamica-ruleta
              :premios="dinamica.premios"
              :puede-girar="esMiTurno"
              :registrar-ganador-url="registrarGanadorUrl"
              :marcar-jugado-url="marcarJugadoUrl"
            />
            <div v-if="turnoActual" class="temporizador-container mb-3">
              <temporizador-turno :tiempo-inicial="tiempoRestante || 90" @timeout="reloadPage" />
            </div>
          </div>
          <div v-else-if="dinamica.premios && dinamica.premios.length">
            <div class="alert alert-info">Regístrate para poder ver y participar en la ruleta.</div>
          </div>
          <div v-else>
            <div class="alert alert-warning">No hay premios configurados para esta dinámica.</div>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="!dinamica.is_active && !hayGanador">
      <div class="alert alert-warning">
        La dinámica aún no está activa. Regístrate y espera la activación.
      </div>
    </div>
    <div v-if="showRegisterPanel">
      <div class="register-panel">
        <span class="badge badge-primary mb-2">Paso 1</span>
        <h5 class="mb-1">Regístrate para participar</h5>
        <p class="text-muted mb-0 small">Obtén tu turno para jugar. Tus datos se usan solo para esta dinámica.</p>
        <form @submit.prevent="registrarUsuario" class="mt-3">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="nombre">Nombre *</label>
                <input type="text" v-model="form.nombre" class="form-control" id="nombre" required placeholder="Tu nombre">
                <small class="form-text text-muted">Como aparece en tu documento.</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="apellido">Apellido *</label>
                <input type="text" v-model="form.apellido" class="form-control" id="apellido" required placeholder="Tu apellido">
                <small class="form-text text-muted">Lo usaremos para identificar tu turno.</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="email">Email *</label>
                <input type="email" v-model="form.email" class="form-control" id="email" required placeholder="tu@email.com">
                <small class="form-text text-muted">Te avisaremos si ganaste.</small>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-end mt-2">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="feather icon-user-plus"></i> Registrarme y obtener turno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TriviaPublicMain',
  props: {
    dinamica: { type: Object, required: true },
    hayGanador: { type: Boolean, default: false },
    registros: { type: Array, default: () => [] },
    turnoActual: { type: Object, default: null },
    usuarioRegistro: { type: Object, default: null },
    esMiTurno: { type: Boolean, default: false },
    registrarGanadorUrl: { type: String, default: '' },
    marcarJugadoUrl: { type: String, default: '' },
    tiempoRestante: { type: Number, default: 90 },
    showRegisterPanel: { type: Boolean, default: false }
  },
  data() {
    return {
      form: { nombre: '', apellido: '', email: '' }
    }
  },
  computed: {
    statusClass() {
      if (this.hayGanador) return 'status-finalizada';
      if (this.dinamica.is_active) return 'status-activa';
      return 'status-pendiente';
    }
  },
  methods: {
    registrarUsuario() {
      // Aquí deberías hacer la petición AJAX para registrar
      // Simulación de registro exitoso:
      this.$emit('registro-exitoso');
    },
    reloadPage() {
      window.location.reload();
    }
  },
  watch: {
    // Si la dinámica se activa, redirigir automáticamente
    'dinamica.is_active'(nuevo) {
      if (nuevo && !this.hayGanador && this.usuarioRegistro) {
        window.location.href = `/d/${this.dinamica.slug}/preview`;
      }
    }
  },
  mounted() {
    // Si ya está activa y el usuario está registrado, redirigir
    if (this.dinamica.is_active && this.usuarioRegistro && !this.hayGanador) {
      window.location.href = `/d/${this.dinamica.slug}/preview`;
    }
  }
}
</script>

<style scoped>
.trivia-public-main {
  background: #f8fafc;
  border-radius: 16px;
  padding: 2rem 1rem;
  box-shadow: 0 4px 24px rgba(0,0,0,0.07);
  max-width: 1200px;
  margin: 2rem auto;
}
.trivia-header {
  text-align: center;
  margin-bottom: 2rem;
}
.trivia-header h2 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2d3748;
}
.trivia-desc {
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}
.trivia-status {
  display: inline-block;
  padding: 0.5rem 1.2rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.1rem;
  margin-top: 0.5rem;
}
.status-finalizada { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
.status-activa { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
.status-pendiente { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #fff; }
.turn-list { margin-bottom: 1.5rem; }
.register-panel { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 2rem 1.5rem; margin-top: 2rem; }
</style>
