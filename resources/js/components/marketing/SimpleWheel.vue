<template>
  <div style="display: flex; flex-direction: column; align-items: center;">
    <div :style="wheelStyle" class="wheel" @click="spin">
      <div v-for="(segment, i) in segments" :key="i" :style="segmentStyle(i)">{{ segment }}</div>
    </div>
    <button @click="spin" style="margin-top: 20px;">Girar ruleta</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      spinning: false,
      rotation: 0,
      segments: ['A', 'B', 'C', 'D', 'E', 'F'],
    }
  },
  computed: {
    wheelStyle() {
      return {
        width: '200px',
        height: '200px',
        border: '8px solid #3498db',
        borderRadius: '50%',
        position: 'relative',
        transition: 'transform 2s cubic-bezier(.17,.67,.83,.67)',
        transform: `rotate(${this.rotation}deg)`
      }
    }
  },
  methods: {
    segmentStyle(i) {
      const angle = 360 / this.segments.length;
      return {
        position: 'absolute',
        left: '50%',
        top: '50%',
        transform: `rotate(${i * angle}deg) translate(-50%, -90px)`,
        transformOrigin: '0 100%',
        fontWeight: 'bold',
        color: '#333',
      }
    },
    spin() {
      if (this.spinning) return;
      this.spinning = true;
      // Emitir giro por HTTP para que todos los clientes giren
      window.axios.get('/ruleta-spin');
    },
    handleSpinEvent(e) {
      // Girar la ruleta en todos los clientes
      const angle = e.angle || 0;
      this.rotation += 360 * 3 + angle;
      setTimeout(() => {
        this.spinning = false;
      }, 2000);
    }
  },
  mounted() {
    if (window.Echo) {
      window.Echo.channel('public-ruleta')
        .listen('RuletaSpinEvent', this.handleSpinEvent);
    }
  },
}
</script>

<style scoped>
.wheel {
  background: conic-gradient(#f1c40f 0% 16.6%, #e67e22 16.6% 33.3%, #e74c3c 33.3% 50%, #9b59b6 50% 66.6%, #2ecc71 66.6% 83.3%, #3498db 83.3% 100%);
  cursor: pointer;
}
</style>
