<template>
  <div class="bg-white shadow-sm border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <button 
        @click="handleBackToGallery"
        class="btn btn-outline-secondary mr-3"
      >
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Plantillas
      </button>
      <h5 class="mb-0 text-muted">
        <i class="fas fa-edit mr-2"></i>
        Editando: {{ selectedTemplate.name }}
        
        <!-- ✅ MEJORADO: Badges más específicos según el estado -->
        <span v-if="hasUnsavedChanges && !isPublished" class="badge badge-warning ml-2">
          <i class="fas fa-exclamation-circle mr-1"></i>
          Cambios sin guardar
        </span>
        <span v-else-if="hasUnsavedChanges && isPublished" class="badge badge-warning ml-2">
          <i class="fas fa-edit mr-1"></i>
          Modificando publicación
        </span>
        <span v-else-if="savedTemplateId && !isPublished" class="badge badge-success ml-2">
          <i class="fas fa-check-circle mr-1"></i>
          Borrador guardado
        </span>
        <span v-else-if="isPublished" class="badge badge-info ml-2">
          <i class="fas fa-cloud mr-1"></i>
          Publicado
        </span>
      </h5>
    </div>

    <div class="d-flex align-items-center">
      <!-- Toggle para mostrar/ocultar preview -->
      <div class="form-check form-switch mr-3">
        <input 
          class="form-check-input" 
          type="checkbox" 
          id="togglePreview"
          :checked="showPreview"
          @change="$emit('toggle-preview', $event.target.checked)"
        >
        <label class="form-check-label" for="togglePreview">
          Vista en tiempo real
        </label>
      </div>

      <!-- Toggle para auto-guardado -->
      <div class="form-check form-switch mr-3">
        <input 
          class="form-check-input" 
          type="checkbox" 
          id="toggleAutoSave"
          :checked="autoSaveEnabled"
          @change="$emit('toggle-auto-save', $event.target.checked)"
        >
        <label class="form-check-label" for="toggleAutoSave">
          Auto-guardar
        </label>
      </div>

      <button 
        @click="$emit('open-full-preview')"
        class="btn btn-outline-primary mr-2"
      >
        <i class="fas fa-external-link-alt mr-2"></i>
        Vista Completa
      </button>

      <!-- Botón de Guardar/Actualizar -->
      <button 
        @click="savedTemplateId ? $emit('update-template') : $emit('save-template')"
        class="btn btn-success mr-2"
        :disabled="isSaving"
      >
        <i v-if="isSaving" class="fas fa-spinner fa-spin mr-2"></i>
        <i v-else class="fas fa-save mr-2"></i>
        {{ isSaving ? 'Guardando...' : (savedTemplateId ? 'Actualizar' : 'Guardar') }}
      </button>

      <!-- ✅ BOTÓN DE PUBLICAR MEJORADO -->
      <button 
        @click="$emit('publish-template')"
        class="btn"
        :class="isPublished ? 'btn-secondary' : 'btn-primary'"
        :disabled="isSaving || isPublished"
        v-if="savedTemplateId"
        :title="getPublishButtonTitle()"
      >
        <i v-if="isPublished" class="fas fa-check mr-2"></i>
        <i v-else class="fas fa-cloud-upload-alt mr-2"></i>
        {{ getPublishButtonText() }}
      </button>

      <!-- Mostrar URL si está publicada -->
      <div v-if="publishedUrl && isPublished" class="ml-3">
        <div class="btn-group">
          <button 
            @click="$emit('open-published')"
            class="btn btn-outline-success btn-sm"
            title="Ver página publicada"
          >
            <i class="fas fa-external-link-alt mr-1"></i>
            Ver página
          </button>
          <button 
            @click="$emit('copy-url')"
            class="btn btn-outline-info btn-sm"
            title="Copiar enlace"
          >
            <i class="fas fa-copy mr-1"></i>
            Copiar enlace
          </button>
        </div>
      </div>

      <!-- ✅ NUEVO: Indicador cuando la plantilla fue publicada pero tiene cambios -->
      <div v-if="!isPublished && publishedUrl" class="ml-3">
        <small class="text-muted">
          <i class="fas fa-info-circle mr-1"></i>
          Esta plantilla fue publicada anteriormente
        </small>
      </div>

      <!-- Modal para mostrar URL después de publicar -->
      <div v-if="showUrlModal" class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">
                <i class="fas fa-check-circle mr-2"></i>
                ¡Página Publicada Exitosamente!
              </h5>
              <button type="button" class="close text-white" @click="$emit('close-url-modal')">
                <span>&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="mb-3">Tu página ha sido publicada y está disponible en el siguiente enlace:</p>

              <div class="input-group mb-3">
                <input 
                  type="text" 
                  class="form-control" 
                  :value="publishedUrl" 
                  readonly
                  id="publishedUrlInput"
                >
                <div class="input-group-append">
                  <button 
                    class="btn btn-outline-secondary" 
                    type="button"
                    @click="$emit('copy-url')"
                  >
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
              </div>
            
              <div class="text-center">
                <button 
                  @click="$emit('open-published')" 
                  class="btn btn-primary mr-2"
                >
                  <i class="fas fa-external-link-alt mr-1"></i>
                  Ver página
                </button>
                <button 
                  @click="$emit('close-url-modal')" 
                  class="btn btn-secondary"
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showUrlModal" class="modal-backdrop fade show"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditorHeader',
  
  props: {
    selectedTemplate: {
      type: Object,
      required: true
    },
    hasUnsavedChanges: {
      type: Boolean,
      default: false
    },
    savedTemplateId: {
      type: [String, Number],
      default: null
    },
    showPreview: {
      type: Boolean,
      default: true
    },
    autoSaveEnabled: {
      type: Boolean,
      default: true
    },
    isSaving: {
      type: Boolean,
      default: false
    },
    publishedUrl: {
      type: String,
      default: null
    },
    showUrlModal: {
      type: Boolean,
      default: false
    },
    isPublished: {
      type: Boolean,
      default: false
    }
  },

  emits: [
    'back-to-gallery',
    'refresh-templates',
    'toggle-preview',
    'toggle-auto-save',
    'open-full-preview',
    'save-template',
    'update-template',
    'publish-template',
    'copy-url',
    'open-published',
    'close-url-modal'
  ],

  methods: {
    handleBackToGallery() {
      this.$emit('refresh-templates');
      this.$emit('back-to-gallery');
    },

    // ✅ NUEVO: Método para obtener el texto del botón de publicar
    getPublishButtonText() {
      if (this.isPublished) {
        return 'Ya Publicado';
      }
      return this.publishedUrl ? 'Republicar' : 'Publicar';
    },

    // ✅ NUEVO: Método para obtener el tooltip del botón de publicar
    getPublishButtonTitle() {
      if (this.isPublished) {
        return 'Esta plantilla ya está publicada. Actualiza para poder publicar cambios.';
      }
      if (this.publishedUrl) {
        return 'Publicar los cambios realizados';
      }
      return 'Publicar plantilla por primera vez';
    }
  }
};
</script>

<style scoped>
.form-check-input:checked {
  background-color: #007bff;
  border-color: #007bff;
}

.badge {
  font-size: 0.7em;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.btn-group .btn {
  border-radius: 0;
}

.btn-group .btn:first-child {
  border-top-left-radius: 0.25rem;
  border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
  border-top-right-radius: 0.25rem;
  border-bottom-right-radius: 0.25rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal {
  z-index: 1050;
}

.modal-backdrop {
  z-index: 1040;
}

/* ✅ NUEVO: Estilo para el indicador de plantilla previamente publicada */
.text-muted {
  font-size: 0.875rem;
}
</style>