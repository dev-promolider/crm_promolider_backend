<template>
  <div class="page-body">
    <div class="content-wrapper">
      <!-- 1. Hero / Banner Superior -->
      <div class="hero-container">
        <img src="/dashboard/images/exponencial.png" alt="Marketplace" class="hero-img" />
      </div>

      <!-- 2. Grid Principal (Características e Imagen de Estrategia) -->
      <div class="main-grid">
        <div class="features-column">
          <img src="/dashboard/images/4ideas2.png" alt="Características" class="resp-img" />
        </div>
        
        <div class="strategy-column">
          <img src="/dashboard/images/ecomer.png" alt="La Estrategia Ganadora" class="resp-img shadow-card" />
        </div>
      </div>

      <!-- 3. Frase Intermedia -->
      <div class="mid-phrase">
        <p>Convierte tu aprendizaje en un activo y el de tus invitados en tu mayor flujo de caja.</p>
      </div>

      <!-- 4. Footer con CTA y Botón -->
      <footer class="final-action-area">
        <div class="cta-text-box">
          <h1 class="footer-cta-text">¡RESERVAR MI POSICIÓN Y<br />COMENZAR A MONETIZAR!</h1>
        </div>

        <div class="button-group">
          <span class="reservation-note">(Asegura tu lugar en el binario hoy mismo)</span>
          <button class="reserve-btn" @click="router.push('/pago')">
            RESERVAR POSICIÓN <span class="play-icon">▶</span>
          </button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&display=swap");

.page-body {
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 10px;
  font-family: "Exo 2", sans-serif;
}

.content-wrapper {
  max-width: 1300px; /* Ancho ajustado para reducir espacios blancos laterales */
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 25px;
}

/* Hero Section */
.hero-container {
  width: 100%;
  display: flex;
  justify-content: center;
}

.hero-img {
  width: 100%;
  max-width: 1200px;
  height: auto;
}

/* Grid Layout */
.main-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr; /* Ajuste de proporción para que encajen mejor */
  gap: 30px;
  align-items: center;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.resp-img {
  width: 100%;
  height: auto;
  display: block;
}

.shadow-card {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border-radius: 15px;
}

/* Texto Intermedio */
.mid-phrase {
  text-align: center;
  margin-top: 20px;
}

.mid-phrase p {
  font-size: 24px;
  font-weight: 800;
  color: #94a3b8; /* Gris suave como en la imagen */
}

/* Footer / CTA Area */
.final-action-area {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 60px;
  margin-top: 30px;
  width: 100%;
  padding-bottom: 50px;
}

.footer-cta-text {
  color: #1ed700;
  font-size: 38px;
  font-weight: 900;
  line-height: 1.1;
  text-align: center;
  margin: 0;
}

.button-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.reservation-note {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 600;
}

.reserve-btn {
  background: linear-gradient(180deg, #39ff14 0%, #1db100 100%);
  color: white;
  border: none;
  padding: 18px 45px;
  border-radius: 14px;
  font-weight: 900;
  font-size: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 8px 0 #15803d, 0 12px 20px rgba(0, 0, 0, 0.15);
  white-space: nowrap;
}

.play-icon {
  font-size: 20px;
}

/* Responsivo para móviles */
@media (max-width: 1000px) {
  .main-grid {
    grid-template-columns: 1fr;
  }
  .final-action-area {
    flex-direction: column;
    gap: 30px;
  }
  .footer-cta-text {
    font-size: 28px;
  }
  .mid-phrase p {
    font-size: 18px;
  }
}
</style>