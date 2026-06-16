<template>
  <div class="preview-panel">
    <div class="preview-header">
      <h6 class="mb-0">
        <i class="fas fa-eye mr-2"></i>
        Vista Previa en Tiempo Real
      </h6>
      <div class="preview-controls">
        <button 
          @click="$emit('change-device', 'desktop')" 
          class="btn btn-sm"
          :class="previewDevice === 'desktop' ? 'btn-primary' : 'btn-outline-secondary'"
          title="Vista de escritorio"
        >
          <i class="fas fa-desktop"></i>
        </button>
        <button 
          @click="$emit('change-device', 'tablet')" 
          class="btn btn-sm"
          :class="previewDevice === 'tablet' ? 'btn-primary' : 'btn-outline-secondary'"
          title="Vista de tablet"
        >
          <i class="fas fa-tablet-alt"></i>
        </button>
        <button 
          @click="$emit('change-device', 'mobile')" 
          class="btn btn-sm"
          :class="previewDevice === 'mobile' ? 'btn-primary' : 'btn-outline-secondary'"
          title="Vista móvil"
        >
          <i class="fas fa-mobile-alt"></i>
        </button>
      </div>
    </div>
    
    <div class="preview-content-wrapper">
      <div 
        class="preview-iframe-container"
        :class="{
          'device-desktop': previewDevice === 'desktop',
          'device-tablet': previewDevice === 'tablet',
          'device-mobile': previewDevice === 'mobile'
        }"
      >
        <iframe 
          ref="previewFrame"
          class="preview-iframe"
          :srcdoc="livePreviewContent"
          frameborder="0"
          sandbox="allow-same-origin allow-scripts"
          @load="onPreviewLoad"
          @error="onPreviewError"
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LivePreview',
  
  props: {
    previewDevice: {
      type: String,
      default: 'desktop'
    },
    livePreviewContent: {
      type: String,
      default: ''
    }
  },

  emits: ['change-device'],

  watch: {
    livePreviewContent() {
      this.updateIframe();
    }
  },

  methods: {
    updateIframe() {
      if (this.$refs.previewFrame && this.livePreviewContent) {
        this.$refs.previewFrame.srcdoc = this.livePreviewContent;
      }
    },

    onPreviewLoad() {
      console.log('Preview cargado correctamente');
    },

    onPreviewError(error) {
      console.error('Error al cargar preview:', error);
    }
  },

  mounted() {
    this.updateIframe();
  }
};
</script>

<style scoped>
.preview-panel {
  width: 50%;
  background: #f8f9fa;
  display: flex;
  flex-direction: column;
}

.preview-header {
  background: white;
  border-bottom: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.preview-controls {
  display: flex;
  gap: 0.5rem;
}

.preview-controls .btn {
  padding: 0.25rem 0.5rem;
  transition: all 0.2s ease;
}

.preview-controls .btn:hover {
  transform: translateY(-1px);
}

.preview-content-wrapper {
  flex: 1;
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: #e9ecef;
  overflow: auto;
}

.preview-iframe-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
}

.preview-iframe-container.device-desktop {
  width: 100%;
  height: calc(100vh - 200px);
}

.preview-iframe-container.device-tablet {
  width: 768px;
  height: 1024px;
  max-width: 90%;
  max-height: calc(100vh - 200px);
}

.preview-iframe-container.device-mobile {
  width: 375px;
  height: 667px;
  max-width: 90%;
  max-height: calc(100vh - 200px);
}

.preview-iframe {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  border: none;
}

/* Efectos de dispositivo */
.preview-iframe-container.device-tablet::before {
  content: '';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 4px;
  background: #333;
  border-radius: 2px;
  z-index: 10;
}

.preview-iframe-container.device-mobile::before {
  content: '';
  position: absolute;
  top: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 4px;
  background: #333;
  border-radius: 2px;
  z-index: 10;
}

.preview-iframe-container.device-mobile::after {
  content: '';
  position: absolute;
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 30px;
  border: 2px solid #333;
  border-radius: 50%;
  z-index: 10;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 992px) {
  .preview-panel {
    width: 100% !important;
    height: 50vh;
  }
  
  .preview-iframe-container.device-desktop,
  .preview-iframe-container.device-tablet,
  .preview-iframe-container.device-mobile {
    width: 100%;
    height: 100%;
  }

  .preview-iframe-container.device-tablet::before,
  .preview-iframe-container.device-mobile::before,
  .preview-iframe-container.device-mobile::after {
    display: none;
  }
}

@media (max-width: 768px) {
  .preview-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .preview-controls {
    justify-content: center;
  }
}
</style>