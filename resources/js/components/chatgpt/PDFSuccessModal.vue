<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click="handleOverlayClick">
      <div class="modal-container" @click.stop>

        <!-- Ícono de éxito -->
        <div class="success-icon">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="64"
            height="64"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>

        <div class="modal-header">
          <h3 class="modal-title">{{ title }}</h3>
        </div>

        <div class="modal-body">
          <p class="modal-message">{{ message }}</p>
          <p v-if="subtitle" class="modal-subtitle">{{ subtitle }}</p>
        </div>

        <div class="modal-footer">
          <button @click="handleConfirm" class="btn btn-success">
            {{ confirmText }}
          </button>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'PDFSuccessModal',

  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: '¡Éxito!'
    },
    message: {
      type: String,
      default: 'Operación completada correctamente.'
    },
    subtitle: {
      type: String,
      default: ''
    },
    confirmText: {
      type: String,
      default: 'Aceptar'
    }
  },

  methods: {
    handleConfirm() {
      this.$emit('confirm')
      this.$emit('close')
    },

    handleOverlayClick() {
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-container {
  background: #ffffff;
  border-radius: 16px;
  border: 2px solid var(--color-success-border);
  box-shadow: 0 25px 60px rgba(16, 185, 129, 0.35);
  max-width: 420px;
  width: 100%;
  overflow: hidden;
  animation: slideIn 0.2s ease-out;
}

/* Animación */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Ícono */
.success-icon {
  display: flex;
  justify-content: center;
  padding: 2rem 2rem 0;
  color: var(--color-success);
}

/* Header */
.modal-header {
  padding: 1rem 2rem 0.5rem;
  text-align: center;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-success);
  margin: 0;
}

/* Body */
.modal-body {
  padding: 0 2rem 2rem;
  text-align: center;
}

.modal-message {
  font-size: 1rem;
  color: #374151;
  margin-bottom: 0.5rem;
}

.modal-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Footer */
.modal-footer {
  padding: 1.5rem 2rem 2rem;
  border-top: 1px solid var(--color-success-border);
}

/* Botón */
.btn-success {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  color: white;
  background: linear-gradient(
    135deg,
    var(--color-success),
    var(--color-success-hover)
  );
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
  transition: all 0.2s ease;
}

.btn-success:hover {
  background: var(--color-success-hover);
  transform: translateY(-1px);
}

.btn-success:active {
  background: var(--color-success-active);
  transform: scale(0.97);
}

/* Fade */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

</style>