<template>
  <div class="preview-view">
    <!-- Header del Preview -->
    <div class="bg-white shadow-sm border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
      <button 
        @click="$emit('back-to-editor')"
        class="btn btn-outline-secondary"
      >
        <i class="fas fa-arrow-left mr-2"></i>
        Volver al Editor
      </button>
      <div class="d-flex">
        <button 
          @click="$emit('save-template')"
          class="btn btn-success"
        >
          <i class="fas fa-save mr-2"></i>
          Guardar
        </button>
      </div>
    </div>

    <!-- Contenido del Preview -->
    <div class="preview-content" v-html="livePreviewContent"></div>
  </div>
</template>

<script>
export default {
  name: 'PreviewView',
  
  props: {
    livePreviewContent: {
      type: String,
      default: ''
    }
  },

  emits: ['back-to-editor', 'save-template']
};
</script>

<style scoped>
.preview-view {
  min-height: 100vh;
  background: white;
}

.preview-content {
  font-family: system-ui, sans-serif;
  line-height: 1.6;
  min-height: calc(100vh - 76px);
}

/* Asegurar que el contenido del preview se muestre correctamente */
.preview-content >>> * {
  max-width: 100%;
}

.preview-content >>> img {
  max-width: 100%;
  height: auto;
}
</style>