<template>
  <div class="chat-input-wrapper">
    <div class="chat-input-container">
      <form @submit.prevent="handleSubmit" class="chat-form">
        
        <div class="input-pill">
          <input
            v-model="inputText"
            type="text"
            placeholder="Escribe tu pregunta aquí..."
            class="chat-input"
            :disabled="isLoading"
            @keydown.enter.prevent="handleSubmit"
          />

          <button
            type="submit"
            class="btn-send"
            :class="{ 'is-loading': isLoading }"
            :disabled="!inputText.trim() || isLoading"
            :title="isLoading ? 'Procesando...' : 'Enviar'"
          >
            <svg
              v-if="!isLoading"
              viewBox="0 0 24 24"
              width="18"
              height="18"
              stroke="currentColor"
              stroke-width="2.5"
              fill="none"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>

            <div v-else class="spinner"></div>
          </button>
        </div>

        <p class="input-hint">Presiona <strong>Enter</strong> para enviar</p>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "ChatInput",

  props: {
    isLoading: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      inputText: "",
    };
  },

  methods: {
    handleSubmit() {
      if (!this.inputText.trim() || this.isLoading) return;

      this.$emit("send", this.inputText.trim());
      this.inputText = "";
    },
  },
};
</script>

<style scoped>
/* --- WRAPPER GENERAL (REDUCIDO) --- */
.chat-input-wrapper {
  width: 100%;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  /* CAMBIO: Reduje mucho el padding vertical (antes era 20px 30px) */
  padding: 12px 16px; 
  position: relative;
  z-index: 20;
}

.chat-input-container {
  max-width: 800px;
  margin: 0 auto;
  width: 100%;
}

/* --- CÁPSULA (INPUT + BOTÓN) --- */
.input-pill {
  display: flex;
  align-items: center;
  background: #f9fafb;
  border: 1px solid transparent;
  /* CAMBIO: Bordes un poco menos pronunciados para altura menor */
  border-radius: 24px; 
  /* CAMBIO: Padding interno mínimo */
  padding: 4px; 
  transition: all 0.25s ease;
}

.input-pill:focus-within {
  background: #e5e7eb;
  border-color: #20e404;
  box-shadow: 0 0 0 2px rgba(32, 228, 4, 0.1); /* Glow más sutil */
}

/* --- INPUT TEXTO (REDUCIDO) --- */
.chat-input {
  flex: 1;
  background: transparent;
  border: none;
  /* CAMBIO: Menos padding vertical y fuente un poco más pequeña */
  padding: 8px 16px; 
  font-size: 0.95rem; 
  color: #111111;
  outline: none;
}

.chat-input::placeholder {
  color: #9ca3af;
}

.chat-input:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

/* --- BOTÓN ENVIAR (REDUCIDO) --- */
.btn-send {
  display: flex;
  align-items: center;
  justify-content: center;
  /* CAMBIO: Tamaño reducido de 44px a 34px */
  width: 34px; 
  height: 34px; 
  border-radius: 50%;
  border: none;
  cursor: pointer;
  color: #ffffff;
  background: #20e404;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-send:disabled {
  background: #76ec65;
  cursor: not-allowed;
  box-shadow: none;
  opacity: 1;
}

.btn-send:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 3px 10px rgba(32, 228, 4, 0.4);
}

.btn-send:active:not(:disabled) {
  transform: scale(0.95);
}

.btn-send svg {
  margin-left: -1px; 
  margin-top: 1px;
}

/* --- SPINNER (AJUSTADO) --- */
.spinner {
  width: 14px; /* Un poco más pequeño */
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* --- HINT TEXT (PEGADO ARRIBA) --- */
.input-hint {
  text-align: center;
  font-size: 0.7rem; /* Letra más pequeña */
  color: #9ca3af;
  /* CAMBIO: Margen reducido de 12px a 6px */
  margin-top: 6px; 
  margin-bottom: 0;
}

.input-hint strong {
  color: #6b7280;
}

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
  .chat-input-wrapper {
    /* En móvil aún más compacto */
    padding: 8px 12px; 
  }
  
  .chat-input {
    font-size: 15px;
    padding: 8px 12px;
  }
  
  .btn-send {
    width: 32px;
    height: 32px;
  }
  
  .input-hint {
    display: none; /* Ocultar hint en móvil para ahorrar espacio */
  }
}
</style>