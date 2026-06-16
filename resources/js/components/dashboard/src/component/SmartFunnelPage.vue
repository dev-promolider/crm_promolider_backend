<template>
  <div class="main-layout">
    <div class="page-body">
      <!-- 1. BANNER SUPERIOR -->
      <div class="img-full-width">
        <img src="/dashboard/images/247.png" alt="Hero Banner" class="resp-img" />
      </div>

      <!-- 2. GRID DE BENEFICIOS -->
      <div class="img-full-width">
        <img src="/dashboard/images/5ideas.png" alt="Beneficios" class="resp-img" />
      </div>

      <!-- 3. IMAGEN SRTA (AJUSTADA IGUAL QUE 5IDEAS) -->
      <div class="img-full-width">
        <img
          src="/dashboard/images/srta.png"
          alt="Smart Funnel"
          class="resp-img srta-adjustment"
        />
      </div>

      <!-- MENSAJE IA CENTRADO -->
      <div class="ia-message-centered">
        <h2 class="cta-top-text">No busques socios. Deja que nuestra IA</h2>
        <p class="cta-mid-text">los encuentre, los convenza y los registre debajo de ti.</p>
      </div>

      <!-- 5. FOOTER ALINEADO -->
      <div class="cta-container-aligned">
        <div class="cta-left">
          <h1 class="cta-main-title">¡RESERVO MI PUESTO<br />AHORA MISMO!</h1>
        </div>

        <div class="cta-right">
          <button class="cta-btn-3d" @click="router.push('/pago')">
            QUIERO MI PUESTO <span class="play-icon">▶</span>
          </button>
          <div class="timer-wrapper">
            <p class="timer-warning">Asegura tu lugar antes de que el cronómetro llegue a cero</p>
            <div class="countdown-timer">
              <div class="timer-unit"><span>00</span><label>DIAS</label></div>
              <div class="timer-unit"><span>00</span><label>HORAS</label></div>
              <div class="timer-unit"><span>05</span><label>MINUTOS</label></div>
              <div class="timer-unit"><span>31</span><label>SEGUNDOS</label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&display=swap");

.main-layout {
  width: 100%;
  background: #f8fafc;
  min-height: 100vh;
  font-family: "Exo 2", sans-serif;
}

.page-body {
  /* Se aumentó el ancho máximo para reducir el espacio en blanco lateral */
  max-width: 1300px; 
  margin: 0 auto;
  padding: 20px 10px; /* Reducción de padding lateral */
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* --- IMÁGENES RESPONSIVAS --- */
.img-full-width {
  width: 100%;
  display: flex;
  justify-content: center;
}

.resp-img {
  width: 100%;
  height: auto;
  /* El ancho de la imagen ahora llega hasta los 1200px para llenar más la pantalla */
  max-width: 1200px; 
}

.srta-adjustment {
  filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.05));
}

/* --- TEXTO IA CENTRADO --- */
.ia-message-centered {
  text-align: center;
  margin-top: 10px;
}

.cta-top-text {
  color: #1ed700;
  font-size: 32px;
  font-weight: 800;
  margin-bottom: 5px;
}

.cta-mid-text {
  color: #1ed700;
  font-size: 20px;
  font-weight: 700;
}

/* --- FOOTER ALINEADO --- */
.cta-container-aligned {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 0 40px; /* Margen interno para que no pegue al borde del todo */
  gap: 40px;
}

.cta-main-title {
  color: #1ed700;
  font-size: 38px;
  font-weight: 900;
  line-height: 1.1;
  margin: 0;
}

.cta-right {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.cta-btn-3d {
  background: linear-gradient(180deg, #39ff14 0%, #1db100 100%);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 18px 45px;
  font-size: 22px;
  font-weight: 900;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 6px 0 #15803d, 0 12px 20px rgba(0, 0, 0, 0.15);
}

.play-icon { font-size: 24px; }

.timer-wrapper { text-align: center; }

.timer-warning {
  font-size: 11px;
  color: #9ca3af;
  margin-bottom: 8px;
}

.countdown-timer { display: flex; gap: 8px; justify-content: center; }

.timer-unit span {
  font-family: monospace;
  font-size: 18px;
  font-weight: bold;
  color: #15803d;
  border: 1px solid #e2e8f0;
  padding: 4px 6px;
  border-radius: 6px;
  background: #fff;
}

.timer-unit label {
  font-size: 8px;
  font-weight: 800;
  color: #9ca3af;
  margin-top: 3px;
  display: block;
}

@media (max-width: 900px) {
  .cta-container-aligned { flex-direction: column; text-align: center; padding: 0; }
  .cta-main-title { font-size: 28px; }
}
</style>