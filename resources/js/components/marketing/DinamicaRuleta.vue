<template>
  <div class="ruleta-container">
    <div v-if="!premios || premios.length === 0" class="alert alert-info">
      Cargando ruleta...
    </div>
    <div
      v-else
      class="ruleta-wrapper"
    >
      <div class="ruleta-pointer"></div>
      <div class="ruleta-wheel" :style="wheelStyle">
        <canvas
          ref="ruletaCanvas"
          class="ruleta-canvas"
          width="400"
          height="400"
        ></canvas>
      </div>
    </div>
    <button
      v-if="premios && premios.length > 0"
      v-show="!premioGanador || premioGanador.tipo === 'vacio'"
      @click="girar"
      class="btn btn-success btn-lg mt-4"
      :disabled="isSpinning || !puedeGirar || premioGanador"
    >
      <i class="feather icon-rotate-cw"></i> Girar Ruleta
    </button>
    <div v-if="premioGanador" class="alert mt-3" :class="resultadoClase">
      <strong>{{ resultadoTitulo }}</strong>
      <span>{{ resultadoDetalle }}</span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    premios: {
      type: Array,
      default: () => []
    },
    // Indica si el usuario actual tiene permitido girar (es su turno)
    puedeGirar: {
      type: Boolean,
      default: false
    },
    // Canal de Echo para sincronizar la ruleta en tiempo real
    channel: {
      type: String,
      default: 'public-ruleta'
    },
    // Endpoint HTTP que dispara el evento de giro en el servidor
    spinUrl: {
      type: String,
      default: '/ruleta-spin'
    },
    // Endpoint para marcar que el jugador ya jugó pero NO ganó (segmento vacío)
    marcarJugadoUrl: {
      type: String,
      default: ''
    },
    dinamicaSlug: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isSpinning: false,
      rotation: 0,
      premioGanador: null
    };
  },
  computed: {
    wheelStyle() {
      return {
        transform: `rotate(${this.rotation}deg)`
      };
    },
    resultadoTitulo() {
      if (!this.premioGanador) {
        return '';
      }
      if (this.premioGanador.tipo === 'vacio') {
        return this.puedeGirar ? 'Sin premio esta vez' : 'Turno sin premio';
      }
      return this.puedeGirar ? '¡Ganaste!' : 'Resultado de la ruleta';
    },
    resultadoDetalle() {
      if (!this.premioGanador) {
        return '';
      }
      if (this.premioGanador.tipo === 'vacio') {
        return 'La flecha cayó en un espacio vacío.';
      }
      return this.premioGanador.nombre || 'Premio asignado';
    },
    resultadoClase() {
      if (!this.premioGanador) {
        return 'alert-info';
      }
      if (this.premioGanador.tipo === 'vacio') {
        return this.puedeGirar ? 'alert-warning' : 'alert-secondary';
      }
      return this.puedeGirar ? 'alert-success' : 'alert-primary';
    }
  },
  watch: {
    premios: {
      handler() {
        this.$nextTick(() => {
          this.dibujarRuleta();
        });
      },
      deep: true
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.dibujarRuleta();
    });

    // Suscribirse al canal de WebSocket para recibir los giros
    if (window.Echo && this.channel) {
      window.Echo.channel(this.channel)
        .listen('RuletaSpinEvent', this.handleSpinEvent)
        .listen('TurnoTimerEvent', this.handleTurnReset);
    }
  },
  methods: {
    // Limita la ruleta a un máximo de segmentos, distribuyendo proporcionalmente
    premiosExpandido() {
      if (!this.premios || this.premios.length === 0) return [];

      const premiosConVacios = this.premios.length === 1
        ? [...this.premios, this.construirSegmentoVacio(this.premios[0])]
        : this.premios;

      const MAX_SEGMENTOS = 16;
      const pesos = premiosConVacios.map((p) => Math.max(1, parseInt(p.peso || 1, 10)));
      const pesoTotal = pesos.reduce((a, b) => a + b, 0);

      let segmentosPorPremio = premiosConVacios.map((premio, idx) => ({
        premio,
        count: Math.max(1, Math.round((pesos[idx] / pesoTotal) * MAX_SEGMENTOS))
      }));

      let suma = segmentosPorPremio.reduce((acum, item) => acum + item.count, 0);
      while (suma > MAX_SEGMENTOS) {
        const maxIdx = segmentosPorPremio.reduce((maxI, seg, i, arr) =>
          seg.count > arr[maxI].count ? i : maxI,
        0);
        if (segmentosPorPremio[maxIdx].count > 1) {
          segmentosPorPremio[maxIdx].count--;
          suma--;
        } else {
          break;
        }
      }
      while (suma < MAX_SEGMENTOS) {
        const minIdx = segmentosPorPremio.reduce((minI, seg, i, arr) =>
          seg.count < arr[minI].count ? i : minI,
        0);
        segmentosPorPremio[minIdx].count++;
        suma++;
      }

      const lista = [];
      segmentosPorPremio.forEach((seg) => {
        for (let i = 0; i < seg.count; i++) {
          lista.push(seg.premio);
        }
      });

      const premios = lista.filter((p) => p.tipo !== 'vacio');
      const vacios = lista.filter((p) => p.tipo === 'vacio');
      const total = lista.length;
      const resultado = new Array(total).fill(null);

      const stepPremio = total / (premios.length || 1);
      const stepVacio = total / (vacios.length || 1);
      let posPremio = 0;
      let posVacio = 0;

      for (let i = 0; i < premios.length; i++) {
        let pos = Math.round(posPremio);
        while (resultado[pos % total]) {
          pos++;
        }
        resultado[pos % total] = premios[i];
        posPremio += stepPremio;
      }

      for (let i = 0; i < vacios.length; i++) {
        let pos = Math.round(posVacio);
        while (resultado[pos % total]) {
          pos++;
        }
        resultado[pos % total] = vacios[i];
        posVacio += stepVacio;
      }

      for (let i = 0; i < total; i++) {
        if (!resultado[i]) {
          resultado[i] = premios[0] || vacios[0];
        }
      }

      if (this.premios.length === 1) {
        const premioOriginal = this.premios[0];
        const vacio = this.construirSegmentoVacio(premioOriginal);
        return resultado.map((_, idx) => (idx % 2 === 0 ? premioOriginal : { ...vacio }));
      }

      return resultado;
    },

    dibujarRuleta() {
      if (!this.$refs.ruletaCanvas || !this.premios || this.premios.length === 0) return;

      const premiosExpandido = this.premiosExpandido();
      const premiosCount = premiosExpandido.length;
      if (premiosCount === 0) return;

      const canvas = this.$refs.ruletaCanvas;
      const ctx = canvas.getContext('2d');
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;
      const radius = Math.min(centerX, centerY) - 20;

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const premioPalette = ['#d1fae5', '#a7f3d0', '#6ee7b7', '#34d399'];
      const vacioPalette = ['#bbf7d0', '#86efac', '#4ade80', '#16a34a'];

      const angleSlice = (Math.PI * 2) / premiosCount;

      premiosExpandido.forEach((premio, index) => {
        const startAngle = angleSlice * index;
        const endAngle = startAngle + angleSlice;

        // Dibujar segmento
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.lineTo(centerX, centerY);
        const palette = premio.tipo === 'vacio' ? vacioPalette : premioPalette;
        ctx.fillStyle = palette[index % palette.length];
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Texto del premio
        const textAngle = startAngle + angleSlice / 2;
        const textRadius = radius * 0.65;
        const maxTextWidth = radius * 0.5;
        const { lineas, fontSize } = this.prepararTextoSegmento(
          ctx,
          premio.nombre,
          maxTextWidth
        );

        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(textAngle);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Sombra para mayor legibilidad
        ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
        ctx.shadowBlur = 3;
        ctx.shadowOffsetX = 1;
        ctx.shadowOffsetY = 1;
        
        ctx.fillStyle = '#fff';
        ctx.font = `bold ${fontSize}px Arial`;
        
        // Stroke para mayor contraste
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.5)';
        ctx.lineWidth = 3;

        const lineHeight = fontSize + 4;
        const totalHeight = lineHeight * lineas.length;

        lineas.forEach((linea, lineIndex) => {
          const yOffset = lineIndex * lineHeight - (totalHeight - lineHeight) / 2;
          ctx.strokeText(linea, textRadius, yOffset);
          ctx.fillText(linea, textRadius, yOffset);
        });

        ctx.restore();
      });

      // Dibujar círculo central
      ctx.beginPath();
      ctx.arc(centerX, centerY, 30, 0, Math.PI * 2);
      ctx.fillStyle = '#fff';
      ctx.fill();
      ctx.strokeStyle = '#333';
      ctx.lineWidth = 2;
      ctx.stroke();

      // Centro decorativo
      ctx.beginPath();
      ctx.arc(centerX, centerY, 15, 0, Math.PI * 2);
      ctx.fillStyle = '#333';
      ctx.fill();
    },
    girar() {
      // El usuario que tiene el control dispara el giro solo si es su turno.
      if (this.isSpinning || !this.premios || this.premios.length === 0 || !this.puedeGirar) {
        return;
      }

      this.isSpinning = true;
      this.premioGanador = null;

      if (window.axios && this.spinUrl) {
        window.axios
          .post(this.spinUrl)
          .catch((error) => {
            this.isSpinning = false;
            const message = error?.response?.data?.message || 'No se pudo girar la ruleta.';
            if (this.$swal) {
              this.$swal.fire({
                title: 'No se puede girar',
                text: message,
                icon: 'warning',
                confirmButtonColor: '#eb3349'
              });
            } else {
              window.alert(message);
            }
          });
      } else {
        this.isSpinning = false;
      }
    },
    handleSpinEvent(e) {
      if (e?.dinamicaSlug && this.dinamicaSlug && e.dinamicaSlug !== this.dinamicaSlug) {
        return;
      }
      this.premioGanador = null;
      if (!this.premios || this.premios.length === 0) {
        this.isSpinning = false;
        return;
      }

      const baseAngle = e && typeof e.angle === 'number' ? e.angle : 0;
      const normalizedAngle = ((baseAngle % 360) + 360) % 360;
      const extraTurns = 720; // dos vueltas completas
      const currentBase = ((this.rotation % 360) + 360) % 360;

      this.rotation = currentBase + extraTurns + normalizedAngle;
      this.isSpinning = true;

      setTimeout(() => {
        this.isSpinning = false;
        this.determinarGanador();
      }, 2100);
    },
    determinarGanador() {
      const premiosExpandido = this.premiosExpandido();
      const premiosCount = premiosExpandido.length;
      const angleSlice = (Math.PI * 2) / premiosCount;

      if (!premiosCount) {
        return;
      }

      const rotationRad = (((this.rotation % 360) + 360) % 360) * (Math.PI / 180);

      // El puntero está arriba (ángulo -π/2 en canvas, pero usamos π*1.5)
      const pointerAngle = Math.PI * 1.5;
      const premioAngle = (pointerAngle - rotationRad + Math.PI * 2) % (Math.PI * 2);

      let premioIndex = Math.floor(premioAngle / angleSlice) % premiosCount;
      premioIndex = (premiosCount - premioIndex) % premiosCount;

      this.premioGanador = premiosExpandido[premioIndex];

      // Determinar si el segmento es "vacío" (sin premio)
      const esSegmentoVacio =
        this.premioGanador &&
        this.premioGanador.tipo === 'vacio';

      // Solo el jugador de turno notifica al backend
      if (this.puedeGirar && window.axios) {
        if (esSegmentoVacio && this.marcarJugadoUrl) {
          // Caso SIN premio: marcar que ya jugó, pero no registrar ganador
          window.axios
            .post(this.marcarJugadoUrl)
            .then(() => {
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            })
            .catch((error) => {
              console.error('Error marcando turno como jugado:', error);
            });
        } else if (!esSegmentoVacio && this.registrarGanadorUrl) {
          // Caso CON premio: registrar ganador y cerrar la dinámica
          window.axios
            .post(this.registrarGanadorUrl, {
              premio:
                this.premioGanador && this.premioGanador.nombre
                  ? this.premioGanador.nombre
                  : null
            })
            .then(() => {
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            })
            .catch((error) => {
              console.error('Error registrando ganador:', error);
            });
        }
      }
    },
    handleTurnReset(event) {
      if (event && event.dinamicaSlug && this.dinamicaSlug && event.dinamicaSlug !== this.dinamicaSlug) {
        return;
      }
      this.premioGanador = null;
    },
    prepararTextoSegmento(ctx, texto, maxWidth) {
      const contenido = texto || 'Premio';
      let fontSize = 14;

      ctx.font = `bold ${fontSize}px Arial`;

      while (ctx.measureText(contenido).width > maxWidth && fontSize > 9) {
        fontSize -= 1;
        ctx.font = `bold ${fontSize}px Arial`;
      }

      const palabras = contenido.split(' ');
      const lineas = [];
      let lineaActual = '';

      palabras.forEach((palabra) => {
        const candidata = lineaActual ? `${lineaActual} ${palabra}` : palabra;
        if (ctx.measureText(candidata).width <= maxWidth) {
          lineaActual = candidata;
        } else {
          if (lineaActual) lineas.push(lineaActual);
          lineaActual = palabra;
        }
      });

      if (lineaActual) lineas.push(lineaActual);

      if (lineas.length > 2) {
        const reducido = `${contenido.slice(0, 15)}...`;
        return this.prepararTextoSegmento(ctx, reducido, maxWidth);
      }

      return { lineas, fontSize };
    },
    construirSegmentoVacio(basePremio = null) {
      return {
        nombre: 'Sin premio',
        tipo: 'vacio',
        peso: basePremio?.peso ?? 20,
        probabilidad: basePremio?.probabilidad ?? 'media',
        limiteUsuario: 0,
        esVacio: true
      };
    }
  }
};
</script>

<style scoped>
.ruleta-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 0;
}

.ruleta-wrapper {
  position: relative;
  width: 400px;
  height: 400px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.ruleta-canvas {
  width: 100%;
  height: 100%;
  display: block;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.ruleta-pointer {
  position: absolute;
  top: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 15px solid transparent;
  border-right: 15px solid transparent;
  border-top: 20px solid #15803d;
  z-index: 10;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ruleta-wheel {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  transition: transform 2s cubic-bezier(.17, .67, .45, 1.32);
}
</style>
