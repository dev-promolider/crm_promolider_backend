<template>
  <div class="main-layout">
    <!-- Este contenedor asegura que el contenido no se meta debajo del sidebar -->
    <div class="page-body">
      
      <div class="img-container">
        <img src="/dashboard/images/ia.png" alt="Liderbot Hero" class="full-img" />
      </div>

      <div class="img-container">
        <img src="/dashboard/images/hotmartyliderbot.png" alt="Hotmart vs Liderbot" class="full-img" />
      </div>

      <div class="img-container">
        <img src="/dashboard/images/3ideas.png" alt="3 Ideas" class="full-img" />
      </div>

      <div class="img-container">
        <img src="/dashboard/images/plataforma.png" alt="Plataforma" class="full-img" />
      </div>

      <div class="cta-section">
        <h2 class="cta-title-green">
          ¡ASEGURAR MI PUESTO Y<br />ACTIVAR LIDERBOT AHORA!
        </h2>
        <div class="cta-action-group">
          <p class="cta-note-alt">
            (Quedan 4 días para que expire tu posición gratuita)
          </p>
          <button class="cta-btn-3d" @click="router.push('/pago')">
            QUIERO MI PUESTO
            <span class="icon-play-btn">▶</span>
          </button>
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
@import url('https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Nunito:wght@400;500;600;700&display=swap');

/* Contenedor maestro para evitar desbordamientos */
.main-layout {
  width: 100%;
  display: flex;
  justify-content: flex-end; 
  background: #f8fafc;
}

.page-body {
  width: 100%;
  max-width: 100%; 
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin: 0 auto;
  box-sizing: border-box;
}

.img-container {
  background: #f8fafc;
  overflow: hidden;
  width: 100%;
}

.full-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
}

.cta-section {
  display: flex;
  flex-direction: row; 
  align-items: center;
  justify-content: center;
  gap: 30px;
  padding: 40px 20px;
  width: 100%;
  background: transparent;
  border-radius: 10px;
  margin: 0 auto;
}

.cta-title-green {
  font-family: 'Exo 2', sans-serif;
  font-size: 34px;
  font-weight: 900;
  color: #1ed700;
  line-height: 1;
  text-align: right;
  flex: 1;
}

.cta-action-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.cta-note-alt {
  font-family: 'Nunito', sans-serif;
  font-size: 15px;
  color: #9ca3af;
  margin-bottom: 8px;
}

.cta-btn-3d {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 100px;
  padding: 25px 45px;
  background: linear-gradient(180deg, #32e632 0%, #1db100 100%);
  color: #fff;
  border-radius: 12px;
  font-family: 'Exo 2', sans-serif;
  font-size: 24px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: inset 0 4px 8px rgba(255, 255, 255, 0.5), 0 8px 15px rgba(0, 0, 0, 0.1);
  border: none;
}
.icon-play-btn {
  font-size: 30px;
  color: white;
  filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));
}
/* ── SOLUCIÓN PARA MÓVIL Y SIDEBAR ── */

@media (max-width: 950px) {
  .cta-section {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .cta-title-green {
    text-align: center;
    font-size: 20px;
  }
  
  .cta-action-group {
    align-items: center;
  }
}

@media (max-width: 450px) {
  .page-body {
    padding: 10px;
  }

  .cta-btn-3d {
    width: 100%;
    font-size: 16px;
    padding: 12px;
    white-space: normal;
  }
}
</style>