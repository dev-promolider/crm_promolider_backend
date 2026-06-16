<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click="handleOverlayClick">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">Cambiar nombre</h3>
        </div>

        <div class="modal-body">
          <input
            ref="inputRef"
            v-model="newName"
            type="text"
            class="name-input"
            placeholder="Nombre de la conversación"
            @keyup.enter="handleConfirm"
            @keyup.esc="handleCancel"
          />
        </div>

        <div class="modal-footer">
          <button @click="handleCancel" class="btn btn-cancel">
            Cancelar
          </button>

          <button
            @click="handleConfirm"
            class="btn btn-confirm"
            :disabled="!newName.trim()"
          >
            Guardar
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "RenameModal",

  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    currentName: {
      type: String,
      default: ""
    }
  },

  data() {
    return {
      newName: ""
    };
  },

  watch: {
    isOpen(isOpen) {
      if (isOpen) {
        // Reset contenido
        this.newName = this.currentName;

        // Esperar al siguiente tick para enfocar
        this.$nextTick(() => {
          if (this.$refs.inputRef) {
            this.$refs.inputRef.focus();
            this.$refs.inputRef.select();
          }
        });
      }
    }
  },

  methods: {
    handleConfirm() {
      if (!this.newName.trim()) return;

      this.$emit("confirm", this.newName.trim());
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
/* ——— MISMO CSS QUE YA TENÍAS ——— */

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

.name-input {
  width: 100%;
  padding: var(--spacing-md);
  background: var(--color-bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  color: var(--color-text-primary);
  font-size: var(--font-size-md);
  transition: all var(--transition-base);
}

.name-input:focus {
  outline: none;
  border-color: var(--color-accent-primary);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.name-input::placeholder {
  color: var(--color-text-tertiary);
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

.btn-cancel:active {
  transform: scale(0.98);
}

.btn-confirm {
  background: var(--color-accent-primary);
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-confirm:hover:not(:disabled) {
  background: var(--color-accent-hover);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-confirm:active:not(:disabled) {
  transform: scale(0.98);
  background: var(--color-accent-active);
}

.btn-confirm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Transiciones */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-container,
.modal-fade-leave-active .modal-container {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from .modal-container,
.modal-fade-leave-to .modal-container {
  opacity: 0;
  transform: scale(0.95) translateY(-10px);
}

/* Responsive */
@media (max-width: 768px) {
  .modal-container {
    max-width: 90%;
  }

  .modal-header {
    padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-md);
  }

  .modal-body {
    padding: 0 var(--spacing-lg) var(--spacing-lg);
  }

  .modal-footer {
    padding: var(--spacing-md) var(--spacing-lg) var(--spacing-lg);
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
  }
}
</style>
