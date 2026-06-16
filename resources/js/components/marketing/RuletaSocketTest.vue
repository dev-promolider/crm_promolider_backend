<template>
  <div class="socket-test-container">
    <div class="wheel-stage">
      <div class="wheel-pointer"></div>
      <div class="wheel" :style="wheelTransformStyle"></div>
    </div>
    <button
      class="btn btn-outline-primary btn-sm mt-3"
      :disabled="spinning"
      @click="triggerSpin"
    >
      <i class="feather icon-rotate-cw"></i>
      <span class="ml-1">Girar demo</span>
    </button>
    <p class="wheel-status" v-if="lastResult">
      Último color: <strong>{{ lastResult }}</strong>
    </p>
    <small class="text-muted">Comparte este enlace en dos pestañas para validar el websocket.</small>
  </div>
</template>

<script>
export default {
  name: 'RuletaSocketTest',
  props: {
    spinUrl: {
      type: String,
      default: '/ruleta-spin'
    },
    channel: {
      type: String,
      default: 'public-ruleta'
    }
  },
  data() {
    return {
      rotation: 0,
      spinning: false,
      lastResult: null,
      channelInstance: null
    };
  },
  computed: {
    wheelTransformStyle() {
      return {
        transform: `rotate(${this.rotation}deg)`
      };
    }
  },
  methods: {
    triggerSpin() {
      if (this.spinning) {
        return;
      }
      this.spinning = true;
      this.lastResult = null;

      if (window.axios) {
        window.axios
          .get(this.spinUrl)
          .catch(() => {
            this.spinning = false;
          });
      }
    },
    handleSpinEvent(event) {
      const baseAngle = typeof event?.angle === 'number' ? event.angle : 0;
      const normalizedAngle = ((baseAngle % 360) + 360) % 360;
      const extraTurns = 720; // dos vueltas completas

      // Aplicar animación
      this.rotation = this.rotation % 360; // evitar overflow acumulado
      this.rotation += extraTurns + normalizedAngle;

      // Esperar a que concluya la animación para liberar el botón
      setTimeout(() => {
        this.spinning = false;
        this.lastResult = normalizedAngle < 180 ? 'Blanco' : 'Negro';
      }, 2100);
    },
    subscribeChannel() {
      if (!window.Echo) {
        return;
      }
      this.channelInstance = window.Echo.channel(this.channel);
      this.channelInstance.listen('RuletaSpinEvent', this.handleSpinEvent);
    },
    leaveChannel() {
      if (this.channel && window.Echo) {
        window.Echo.leave(this.channel);
      }
      this.channelInstance = null;
    }
  },
  mounted() {
    this.subscribeChannel();
  },
  beforeDestroy() {
    this.leaveChannel();
  }
};
</script>

<style scoped>
.socket-test-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: 0.5rem;
}

.wheel-stage {
  position: relative;
  width: 220px;
  height: 220px;
}

.wheel {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(#111 0 180deg, #f8f8f8 180deg 360deg);
  border: 6px solid #111;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  transition: transform 2s cubic-bezier(.17,.67,.45,1.32);
}

.wheel-pointer {
  position: absolute;
  top: -14px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 12px solid transparent;
  border-right: 12px solid transparent;
  border-top: 20px solid #ff5252;
  z-index: 2;
}

.wheel-status {
  margin-top: 0.25rem;
}
</style>
