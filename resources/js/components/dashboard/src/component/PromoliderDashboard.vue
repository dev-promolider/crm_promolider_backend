<template>
  <div class="dashboard-wrapper">

    <!-- Dashboard body -->
    <div class="dashboard-body">

      <!-- Left panel -->
      <div class="left-panel">

        <!-- Condición -->
        <div class="card">
          <h3 class="card-title">Condición</h3>
          <div class="condition-list">
            <div class="condition-item">
              <span class="condition-label">Membresía activa:</span>
              <span class="check-icon">✕</span>
            </div>
            <div class="condition-item">
              <span class="condition-label">Activos de OPC:</span>
              <span class="check-icon">✕</span>
            </div>
            <div class="condition-item">
              <span class="condition-label">Calificado:</span>
              <span class="check-icon">✕</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Right panel -->
      <div class="right-panel">

        <!-- Countdown -->
        <div class="card countdown-card">
          <p class="countdown-greeting">
            <strong>¡Felicitaciones {{ userName }}!</strong> Tu posición Temporal Gratuito en el ecosistema ha sido reservada
          </p>
          <p class="countdown-text">
            Conserva tu ubicación antes del <strong>CIERRE DE 5 DÍAS</strong>
          </p>
          <div class="countdown-grid">
            <div class="time-block" v-for="unit in timeUnits" :key="unit.label">
              <div class="time-digits">
                <span v-for="(d, i) in unit.digits" :key="i" class="digit">{{ d }}</span>
              </div>
              <span class="time-label">{{ unit.label }}</span>
            </div>
          </div>
          <button class="lock-btn" @click="navigateToPago">Bloqueo mi posición ahora</button>
        </div>

        <!-- Tree section -->
        <div class="tree-section">
          <div class="tree-tabs">
            <button
              v-for="tab in tabs"
              :key="tab"
              class="tree-tab"
              :class="{ 'tree-tab--active': activeTab === tab }"
              @click="activeTab = tab"
            >{{ tab }}</button>
          </div>

          <div class="tree-canvas">
            <!-- Árbol Binario: imagen -->
            <div v-if="activeTab === 'Árbol Binario'" class="tree-img-wrapper">
              <img
                :src="'/dashboard/images/imagenes.png'"
                alt="Árbol Binario"
                class="tree-img"
              />
            </div>

            <!-- Árbol Uninivel: imagen distinta o placeholder -->
            <div v-else class="tree-img-wrapper">
              <img
                :src="'/dashboard/images/imagenes.png'"
                alt="Árbol Uninivel"
                class="tree-img"
              />
            </div>
          </div>

          <div class="volume-row">
            <button class="volume-btn">Volumen Izquierdo</button>
            <button class="volume-btn">Volumen Derecho</button>
          </div>
        </div>

        

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()

const activeTab = ref<string>('Árbol Binario')
const tabs: string[] = ['Árbol Uninivel', 'Árbol Binario']

const userName = computed<string>(() => sessionStorage.getItem('preregistro_nombre') || 'Invitado')

interface TimeUnit {
  label: string
  digits: string[]
}

const totalSeconds = ref<number>(5 * 24 * 60 * 60 - 1)

function navigateToPago(): void {
  router.push('/pago')
}

const timeUnits = ref<TimeUnit[]>([
  { label: 'DÍAS',     digits: ['0', '4'] },
  { label: 'HORAS',    digits: ['1', '2'] },
  { label: 'MINUTOS',  digits: ['5', '9'] },
  { label: 'SEGUNDOS', digits: ['3', '1'] },
])

function pad(n: number): string[] {
  return String(n).padStart(2, '0').split('')
}

function updateCountdown(): void {
  const s = totalSeconds.value
  timeUnits.value = [
    { label: 'DÍAS',     digits: pad(Math.floor(s / 86400)) },
    { label: 'HORAS',    digits: pad(Math.floor((s % 86400) / 3600)) },
    { label: 'MINUTOS',  digits: pad(Math.floor((s % 3600) / 60)) },
    { label: 'SEGUNDOS', digits: pad(s % 60) },
  ]
  if (totalSeconds.value > 0) totalSeconds.value--
}

let timer: ReturnType<typeof setInterval>
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800&family=Nunito:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ── WRAPPER ── */
.dashboard-wrapper {
  background: #f0f4f8;
  min-height: calc(100vh - 60px);
  font-family: 'Nunito', sans-serif;
}

/* ── DASHBOARD BODY ── */
.dashboard-body {
  display: flex;
  gap: 20px;
  padding: 20px 24px;
  align-items: flex-start;
}

/* ── CARDS ── */
.card {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,.07);
}

.card-title {
  font-family: 'Exo 2', sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #374151;
  margin-bottom: 14px;
}

/* ── LEFT PANEL ── */
.left-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 220px;
  min-width: 200px;
  flex-shrink: 0;
}

.condition-list { display: flex; flex-direction: column; gap: 10px; }
.condition-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.condition-label { font-size: 13px; color: #4b5563; font-weight: 500; }
.check-icon {
  width: 26px; height: 26px;
  border-radius: 6px;
  background: #18d600;
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
}

/* ── RIGHT PANEL ── */
.right-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 0;
}

/* ── COUNTDOWN ── */
.countdown-card {
  text-align: center;
  padding: 40px 30px;
  min-height: 350px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.countdown-greeting {
  font-size: 22px;
  color: #6b7280;
  margin-bottom: 12px;
  font-weight: 500;
  max-width: 760px;
}
.countdown-greeting strong {
  display: block;
  font-size: 38px;
  font-weight: 900;
  color: #18d600;
  background: linear-gradient(135deg, #2ae014 0%, #119e00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 16px;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.countdown-text {
  font-size: 18px;
  color: #374151;
  margin-bottom: 24px;
  font-weight: 500;
  max-width: 760px;
}
.countdown-text strong { color: #18d600; }

.countdown-grid {
  display: flex;
  justify-content: center;
  gap: 36px;
  margin-bottom: 36px;
  flex-wrap: wrap;
  width: 100%;
}
.time-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.time-digits { display: flex; gap: 12px; }
.digit {
  width: 44px; height: 52px;
  background: #18d600;
  color: #fff;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Exo 2', sans-serif;
  font-size: 26px;
  font-weight: 800;
  box-shadow: 0 3px 10px rgba(24,214,0,.4), inset 0 -3px 0 rgba(0,0,0,.15);
}
.time-label {
  font-size: 14px;
  font-weight: 900;
  color: #1f2937;
  letter-spacing: 1.5px;
}

.lock-btn {
  padding: 14px 36px;
  background: #18d600;
  color: #fff;
  border: none;
  border-radius: 50px;
  font-family: 'Exo 2', sans-serif;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 18px rgba(24,214,0,.4);
  transition: transform 0.15s;
  width: 100%;
  max-width: 340px;
  margin: 10px auto 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.lock-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 22px rgba(24,214,0,.5); }

/* ── TREE ── */
.tree-section {
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,.07);
  overflow: hidden;
}

.tree-tabs {
  display: flex;
  padding: 14px 20px 0;
  gap: 4px;
}
.tree-tab {
  padding: 8px 20px;
  border: none;
  background: transparent;
  font-family: 'Nunito', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  transition: all 0.18s;
  border-bottom: 3px solid transparent;
}
.tree-tab--active {
  background: #f0fdf4;
  color: #18d600;
  border-bottom: 3px solid #18d600;
}

.tree-canvas {
  margin: 12px 16px;
  border: 2px dashed #e5e7eb;
  border-radius: 10px;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* ── IMAGEN DEL ÁRBOL ── */
.tree-img-wrapper {
  width: 100%;
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.tree-img {
  width: 100%;
  max-width: 100%;
  height: auto;
  object-fit: contain;
  display: block;
  border-radius: 8px;
}

.volume-row {
  display: flex;
  justify-content: space-between;
  padding: 14px 20px 18px;
  gap: 12px;
}
.volume-btn {
  flex: 1;
  padding: 9px 16px;
  background: #18d600;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-family: 'Nunito', sans-serif;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.18s;
  text-align: center;
}
.volume-btn:hover { background: #14b800; }

/* ══════════════════════════════════
   RESPONSIVO
══════════════════════════════════ */

/* Tablet (≤ 900px) */
@media (max-width: 900px) {
  .dashboard-body {
    flex-direction: column;
    padding: 16px;
    gap: 16px;
  }

  .left-panel {
    width: 100%;
    min-width: unset;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .left-panel .card {
    flex: 1;
    min-width: 180px;
  }

  .countdown-grid {
    gap: 8px;
  }

  .digit {
    width: 30px;
    height: 38px;
    font-size: 18px;
  }
}

/* Mobile (≤ 600px) */
@media (max-width: 600px) {
  .dashboard-body {
    padding: 12px;
    gap: 12px;
  }

  .left-panel {
    flex-direction: column;
  }

  .countdown-card {
    padding: 16px 12px;
  }

  .countdown-text {
    font-size: 13px;
  }

  .countdown-grid {
    gap: 6px;
  }

  .digit {
    width: 26px;
    height: 34px;
    font-size: 16px;
  }

  .time-label {
    font-size: 9px;
  }

  .lock-btn {
    font-size: 13px;
    padding: 10px 20px;
  }

  .tree-tabs {
    padding: 10px 12px 0;
  }

  .tree-tab {
    padding: 6px 14px;
    font-size: 12px;
  }

  .tree-canvas {
    margin: 8px 10px;
    min-height: 150px;
  }

  .volume-row {
    flex-direction: column;
    gap: 8px;
    padding: 10px 12px 14px;
  }

  .volume-btn {
    width: 100%;
  }

  .card-title {
    font-size: 13px;
  }

  .condition-label {
    font-size: 12px;
  }
}
</style>