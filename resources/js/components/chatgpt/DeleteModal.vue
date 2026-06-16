<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click="handleOverlayClick">
      <div class="modal-container" @click.stop>

        <div class="modal-header">
          <h3 class="modal-title">{{ title }}</h3>
        </div>

        <div class="modal-body">
          <p class="modal-message" v-html="message"></p>
          <p v-if="subtitle" class="modal-subtitle">{{ subtitle }}</p>
        </div>

        <div class="modal-footer">
          <button @click="handleCancel" class="btn btn-cancel">
            {{ cancelText }}
          </button>

          <button @click="handleConfirm" class="btn btn-delete">
            {{ confirmText }}
          </button>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "DeleteModal",

  props: {
    isOpen: { type: Boolean, default: false },
    title: { type: String, default: "¿Eliminar chat?" },
    message: { type: String, default: "Esto eliminará la conversación permanentemente." },
    subtitle: { type: String, default: "" },
    confirmText: { type: String, default: "Eliminar" },
    cancelText: { type: String, default: "Cancelar" }
  },

  methods: {
    handleConfirm() {
      this.$emit("confirm");
      this.$emit("close");
    },

    handleCancel() {
      this.$emit("cancel");
      this.$emit("close");
    },

    handleOverlayClick() {
      this.$emit("cancel");
      this.$emit("close");
    }
  }
};
</script>

<style scoped>
/* --- tus estilos aquí, no los modifiqué --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: var(--spacing-lg);
}

.modal-container {
  background: var(--color-bg-secondary);
  border-radius: var(--border-radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  max-width: 420px;
  width: 100%;
  overflow: hidden;
  animation: slideIn 0.2s ease-out;
}

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

.modal-header {
  padding: var(--spacing-xl) var(--spacing-xl) var(--spacing-lg);
}

.modal-title {
  font-size: var(--font-size-xl);
  font-weight: 600;
  color: var(--color-accent-primary);
  margin: 0;
}

.modal-body {
  padding: 0 var(--spacing-xl) var(--spacing-xl);
}

.modal-message {
  font-size: var(--font-size-md);
  color: var(--color-text-secondary);
  line-height: 1.5;
  margin: 0 0 var(--spacing-sm) 0;
}

.modal-message :deep(strong) {
  color: var(--color-accent-primary);
  font-weight: 650;
}

.modal-subtitle {
  font-size: var(--font-size-sm);
  color: var(--color-text-tertiary);
  line-height: 1.4;
  margin: 0;
}

.modal-footer {
  display: flex;
  gap: var(--spacing-md);
  padding: var(--spacing-lg) var(--spacing-xl) var(--spacing-xl);
  border-top: 1px solid var(--border-color);
}

.btn {
  flex: 1;
  padding: var(--spacing-md) var(--spacing-lg);
  border-radius: var(--border-radius-md);
  font-size: var(--font-size-md);
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  border: none;
  outline: none;
}

.btn-cancel {
  background: var(--color-bg-tertiary);
  color: var(--color-text-primary);
  border: 1px solid var(--border-color);
}

.btn-cancel:hover {
  background: var(--color-bg-quaternary);
  border-color: var(--color-accent-primary);
}

.btn-delete {
  background: #dc2626;
  color: white;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
}

.btn-delete:hover {
  background: #b91c1c;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
