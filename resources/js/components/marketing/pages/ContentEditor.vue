<template>
  <div class="editor-panel" :class="{ 'full-width': !showPreview }">
    <div class="editor-content">
      <div class="card">
        <div class="card-header bg-light">
          <h6 class="mb-0">
            <i class="fas fa-edit mr-2"></i>
            Campos Editables
          </h6>
          <small class="text-muted">{{ selectedTemplate.description }}</small>
        </div>

        <div class="card-body">
          <div v-for="(value, field) in editedContent" :key="field" class="form-group mb-4">
            <label class="font-weight-semibold text-dark mb-2">
              <i :class="getFieldIcon(field)" class="mr-2"></i>
              {{ formatFieldName(field) }}
            </label>

            <!-- Campo de Título -->
            <input
              v-if="isTitle(field)"
              :value="editedContent[field]"
              @input="updateField(field, $event.target.value)"
              type="text"
              class="form-control form-control-lg"
              :placeholder="'Ingresa el ' + formatFieldName(field).toLowerCase() + '...'"
            />

            <!-- Campo de Botón -->
            <input
              v-else-if="isButton(field)"
              :value="editedContent[field]"
              @input="updateField(field, $event.target.value)"
              type="text"
              class="form-control"
              placeholder="Texto del botón..."
            />

            <!-- Campo de Descripción -->
            <textarea
              v-else
              :value="editedContent[field]"
              @input="updateField(field, $event.target.value)"
              class="form-control"
              rows="3"
              :placeholder="'Ingresa la ' + formatFieldName(field).toLowerCase() + '...'"
            ></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentEditor',

  props: {
    editedContent: {
      type: Object,
      default: () => ({})
    },
    selectedTemplate: {
      type: Object,
      required: true
    },
    showPreview: {
      type: Boolean,
      default: true
    }
  },

  emits: ['update-content'],

  methods: {
    updateField(field, value) {
      const updatedContent = { ...this.editedContent, [field]: value };
      this.$emit('update-content', updatedContent);
    },

    formatFieldName(field) {
      return field.replace(/_/g, ' ')
                  .replace(/\b\w/g, l => l.toUpperCase());
    },

    isTitle(field) {
      return field.includes('title') || field.includes('headline');
    },

    isButton(field) {
      return field.includes('button') || field.includes('cta');
    },

    getFieldIcon(field) {
      if (this.isTitle(field)) return 'fas fa-heading';
      if (this.isButton(field)) return 'fas fa-mouse-pointer';
      return 'fas fa-align-left';
    }
  }
};
</script>

<style scoped>
.editor-panel {
  width: 50%;
  transition: width 0.3s ease;
  border-right: 1px solid 
#dee2e6;
  background: white;
}

.editor-panel.full-width {
  width: 100%;
}

.editor-content {
  height: 100%;
  overflow-y: auto;
  padding: 1.5rem;
}

.form-control:focus {
  border-color: 
#007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.font-weight-semibold {
  font-weight: 600;
}

.form-control-lg {
  font-size: 1.25rem;
  font-weight: 600;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 992px) {
  .editor-panel, .editor-panel.full-width {
    width: 100% !important;
  }
}
</style>