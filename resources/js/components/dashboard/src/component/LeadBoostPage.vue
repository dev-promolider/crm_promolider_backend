<template>
  <div class="page-body">
    <div class="content-wrapper">
      
      <!-- Banner Superior -->
      <div class="hero-banner">
        <img
          src="/dashboard/images/automatizado.png"
          alt="El Magneto de Prospectos Automatizado"
          class="hero-img-fluid"
        />
      </div>

      <!-- Sección de Imagen Centralizada -->
      <div class="main-content">
        <section class="image-container">
          <img
            src="/dashboard/images/4ideas.png"
            alt="Pasos LeadBoost"
            class="steps-image"
          />
        </section>
      </div>

      <!-- Pie de página con Botón (Alineado y Centrado) -->
      <footer class="final-action-area">
        <div class="cta-text-container">
          <h3 class="footer-cta-text">
            ¡ACTIVAR LEADBOOST Y<br />BLOQUEAR MI POSICIÓN!
          </h3>
        </div>

        <div class="button-group">
          <span class="reservation-note">
            (Reserva tu lugar ahora; gratis solo por 7 días)
          </span>
          <button class="bloom-btn" @click="router.push('/pago')">
            BLOQUEAR MI PUESTO
            <span class="play-icon">▶</span>
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
@import url("https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Nunito:wght@400;500;600;700&display=swap");

.page-body {
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 10px;
  font-family: "Nunito", sans-serif;
}

.content-wrapper {
  max-width: 1300px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Banner Superior */
.hero-banner {
  background: #f6f8fa; 
  border-radius: 150px; 
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden; 
  width: 100%;
}

.hero-img-fluid {
  width: 100%;
  height: auto;
  display: block;
}

.main-content {
  display: flex;
  justify-content: center;
  width: 100%;
}

.image-container {
  width: 100%;
  max-width: 1200px;
}

.steps-image {
  width: 100%;
  height: auto;
  display: block;
}

/* Footer Alineado: Estilo Imagen 2 */
.final-action-area {
  display: flex;
  justify-content: center; 
  align-items: center;    
  margin-top: 50px;
  gap: 60px;              
  width: 100%;
  padding-bottom: 40px;
}

.cta-text-container {
  display: flex;
  justify-content: center;
}

.footer-cta-text {
  color: #1cda05;
  font-family: "Exo 2", sans-serif;
  font-size: 38px;
  font-weight: 900;
  line-height: 1;
  text-align: center; /* Centra las dos líneas de texto */
  margin: 0;
}

.button-group {
  display: flex;
  flex-direction: column;
  align-items: center; /* Centra la nota sobre el botón */
  gap: 10px;
}

.reservation-note {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 600;
}

.bloom-btn {
  background: linear-gradient(180deg, #39ff14 0%, #1db100 100%);
  color: white;
  border: none;
  padding: 18px 45px;
  border-radius: 14px;
  font-weight: 900;
  font-size: 24px;
  cursor: pointer;
  box-shadow: 0 8px 0 #15803d, 0 12px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 15px;
  white-space: nowrap;
}

.play-icon {
  font-size: 20px;
}

@media (max-width: 1000px) {
  .final-action-area {
    flex-direction: column;
    gap: 30px;
  }
  .footer-cta-text {
    font-size: 28px;
  }
  .hero-banner {
    border-radius: 40px;
  }
}
</style>