<template>
  <div class="app-layout">
    <!-- Overlay para cerrar el menú en móvil al tocar fuera -->
    <div v-if="isMenuOpen" class="sidebar-overlay" @click="isMenuOpen = false"></div>

    <!-- Sidebar: Se mantiene igual, solo agregamos la clase dinámica -->
    <aside class="sidebar" :class="{ 'sidebar-active': isMenuOpen }">
      <div class="sidebar-logo">
        <img :src="imgLogo" alt="Promolider" class="sidebar-logo-img" />
        <!-- Botón para cerrar el menú en móvil -->
        <button class="close-menu-btn" @click="isMenuOpen = false">✕</button>
      </div>

      <nav class="sidebar-nav">
        <RouterLink to="/" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgIconoCuadrado" alt="" class="nav-icon" />
          <span>Paneles de control</span>
        </RouterLink>
        <RouterLink to="/liderbot" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgCubo" alt="" class="nav-icon" />
          <span>Liderbot</span>
        </RouterLink>
        <RouterLink to="/leadboost" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgTv" alt="" class="nav-icon" />
          <span>LeadBoost</span>
        </RouterLink>
        <RouterLink to="/smartfunnel" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgEstrella" alt="" class="nav-icon" />
          <span>Smart Funnel con IA</span>
        </RouterLink>
        <RouterLink to="/ecomercademico" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgAvion" alt="" class="nav-icon" />
          <span>App E-comer Académico</span>
        </RouterLink>
        <RouterLink to="/reportes" class="nav-item" active-class="active" @click="isMenuOpen = false">
          <img :src="imgCirculo" alt="" class="nav-icon" />
          <span>Reportes</span>
        </RouterLink>
      </nav>

      <div class="sidebar-bottom">
        <button class="recomprar-btn">Recomprar OPC</button>
      </div>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <!-- Botón Hamburguesa para abrir el menú en móvil -->
          <button class="mobile-toggle" @click="isMenuOpen = true">☰</button>
        </div>
        
        <div class="topbar-right">
          <div class="notif-badge"><span class="badge-num">0</span></div>
          <button class="icon-btn"><img :src="imgMoneda" alt="" class="icon-img" /></button>
          <button class="icon-btn"><img :src="imgManzana" alt="" class="icon-img" /></button>
          <button class="icon-btn"><img :src="imgCampana" alt="" class="icon-img" /></button>
          
          <div class="user-info">
            <div class="user-text">
              <span class="user-name">{{ userName }}</span>
              <span class="user-role">{{ userEmail }}</span>
            </div>
            <div class="user-avatar">{{ userInitials }}</div>
          </div>
        </div>
      </header>

      <!-- Aquí se carga tu página de Liderbot -->
      <div class="router-view-container">
        <RouterView />
      </div>

      <footer class="footer">
        COPYRIGHT 2028 <a href="#" class="footer-link">Promolider</a>. Todos los Derechos Reservados
      </footer>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
const isMenuOpen = ref(false);

// Image paths (prevents Vite from resolving them as module imports)
const imgLogo = '/dashboard/images/Logo.png';
const imgIconoCuadrado = '/dashboard/images/iconoCuadrado.png';
const imgCubo = '/dashboard/images/cubo.png';
const imgTv = '/dashboard/images/tv.png';
const imgEstrella = '/dashboard/images/estrella.png';
const imgAvion = '/dashboard/images/avion.png';
const imgCirculo = '/dashboard/images/circulo.png';
const imgMoneda = '/dashboard/images/moneda.png';
const imgManzana = '/dashboard/images/manzana.png';
const imgCampana = '/dashboard/images/campana.png';

// Obtener datos del usuario desde sessionStorage (seteados desde el preregistro)
const userName = ref(sessionStorage.getItem('preregistro_nombre') || 'Invitado');
const userEmail = ref(sessionStorage.getItem('preregistro_correo') || '');
const userInitials = computed(() => {
  const name = userName.value;
  if (!name || name === 'Invitado') return '?';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800&family=Nunito:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Nunito', sans-serif;
  background: #f0f4f8;
  overflow-x: hidden;
}

/* SIDEBAR - Mantenemos tus estilos originales */
.sidebar {
  width: 200px;
  min-width: 200px;
  background: #000;
  display: flex;
  flex-direction: column;
  padding-bottom: 24px;
  position: fixed;
  top: 0; left: 0;
  height: 100vh;
  z-index: 1000;
  transition: transform 0.3s ease;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 16px;
}

.sidebar-logo-img {
  max-width: 120px;
  height: auto;
  display: block;
}

.notif-badge {
  width: 28px;        
  height: 28px;       
  background-color: #22c55e; 
  color: #ffffff;     
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px; 
  font-weight: 700;   
  font-size: 14px;
  line-height: 1;
}

.close-menu-btn {
  display: none; 
  background: none; 
  border: none; 
  color: #fff; 
  font-size: 20px; 
  cursor: pointer;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 12px 8px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 10px 12px;
  border-radius: 8px;
  color: #9ca3af;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
}

.nav-icon {
  width: 24px;
  height: 24px;
  display: block;
  flex-shrink: 0;
}

.nav-item.active { background: #111827; color: #fff; }
.nav-item:hover { background: #18d500; color: #fff; }

.recomprar-btn {
  margin: 0 12px;
  padding: 10px;
  background: #18d500;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  width: calc(100% - 24px);
}

/* MAIN CONTENT */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: 200px; 
  min-width: 0;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 28px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 99;
}

.mobile-toggle {
  display: none; 
  background: none; border: none; font-size: 24px; cursor: pointer;
}

.topbar-right { display: flex; align-items: center; gap: 35px; }
.icon-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; }
.icon-img {
  width: 26px;
  height: 26px;
  display: block;
}

.user-info { display: flex; align-items: center; gap: 10px; }
.user-text { display: flex; flex-direction: column; align-items: flex-end; }
.user-name { font-size: 13px; font-weight: 700; color: #53cd37; }
.user-role { font-size: 11px; color: #9ca3af; }
.user-avatar {
  width: 36px; 
  height: 36px; 
  border-radius: 50%;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: #fff; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.router-view-container {
  padding: 0;
  flex: 1;
}

.footer {
  text-align: center; padding: 14px; font-size: 12px; color: #9ca3af;
  background: #fff; border-top: 1px solid #e5e7eb;
}

/* ── AJUSTES DINÁMICOS PARA MÓVIL ── */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.sidebar-active {
    transform: translateX(0);
  }
  .close-menu-btn { display: block; }
  
  .main-content {
    margin-left: 0;
  }
  
  .mobile-toggle { display: block; }
  
  .sidebar-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); z-index: 999;
  }
  
  .user-text { display: none; }
  .topbar { padding: 12px 15px; }
}
</style>