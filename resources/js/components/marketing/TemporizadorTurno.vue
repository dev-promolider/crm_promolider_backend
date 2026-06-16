<template>
  <div class="temporizador-display" :class="colorClass">
    <i class="feather icon-clock"></i>
    <span>{{ minutos }}:{{ segundos < 10 ? '0' : '' }}{{ segundos }}</span>
  </div>
</template>

<script>
export default {
  name: 'TemporizadorTurno',
  props: {
    expiresAt: {
      type: [String, Number, Date],
      required: true
    },
    duration: {
      type: Number,
      default: 90
    }
  },
  data() {
    return {
      tiempoRestante: 0,
      intervalId: null
    };
  },
  computed: {
    minutos() {
      return Math.floor(this.tiempoRestante / 60);
    },
    segundos() {
      return this.tiempoRestante % 60;
    },
    colorClass() {
      if (this.tiempoRestante <= 10) return 'danger';
      if (this.tiempoRestante <= 30) return 'warning';
      return '';
    }
  },
  watch: {
    expiresAt: {
      immediate: true,
      handler() {
        this.resetTimer();
      }
    }
  },
  mounted() {
    this.resetTimer();
  },
  beforeDestroy() {
    this.clearTimer();
  },
  methods: {
    calcularSegundosRestantes() {
      if (!this.expiresAt) {
        return 0;
      }
      const expires = new Date(this.expiresAt).getTime();
      const now = Date.now();
      const diff = Math.floor((expires - now) / 1000);
      return diff > 0 ? diff : 0;
    },
    resetTimer() {
      this.clearTimer();
      this.tiempoRestante = this.calcularSegundosRestantes();

      if (this.tiempoRestante <= 0) {
        return;
      }

      this.intervalId = setInterval(() => {
        this.tiempoRestante = this.calcularSegundosRestantes();
        if (this.tiempoRestante <= 0) {
          this.$emit('timeout');
          this.clearTimer();
        }
      }, 1000);
    },
    clearTimer() {
      if (this.intervalId) {
        clearInterval(this.intervalId);
        this.intervalId = null;
      }
    }
  }
};
</script>

<style scoped>
.temporizador-display {
  font-size: 2rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #222;
}
.temporizador-display.warning {
  color: #ff9800;
}
.temporizador-display.danger {
  color: #f44336;
}
</style>
