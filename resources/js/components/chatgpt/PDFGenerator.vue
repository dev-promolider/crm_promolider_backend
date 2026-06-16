<template>
  <div v-if="showButton" class="pdf-generator">
    <button
      class="pdf-btn"
      :disabled="isGenerating"
      @click="handleGeneratePDF"
    >
      <span v-if="!isGenerating">📄 Generar PDF</span>
      <span v-else>⏳ Generando...</span>
    </button>

    <!-- Modal de éxito -->
    <SuccessModal
      :isOpen="showSuccessModal"
      title="¡PDF Generado!"
      message="Tu PDF se ha descargado correctamente."
      subtitle="Revisa tu carpeta de descargas."
      @close="showSuccessModal = false"
    />
  </div>
</template>

<script>
import axios from 'axios'
import SuccessModal from './PDFSuccessModal.vue'

const AGENTE_API = 'https://agente.picklechatbot.promolider.org'

export default {
  name: 'PDFGenerator',

  components: {
    SuccessModal
  },

  props: {
    courseData: {
      type: Object,
      required: true
    },
    showButton: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      isGenerating: false,
      showSuccessModal: false
    }
  },

  methods: {
    async handleGeneratePDF() {
      this.isGenerating = true
    
      try {
        const response = await axios.post(
          `${AGENTE_API}/generate_pdf`,
          { ...this.courseData },
          {
            responseType: 'blob' // 🔥 CLAVE
          }
        )
        
        // Crear URL temporal del PDF
        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        
        // Crear link invisible
        const link = document.createElement('a')
        link.href = url
        link.download = 'curso-digital.pdf'
        document.body.appendChild(link)
        link.click()
        
        // Limpieza
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
        
        this.showSuccessModal = true
      } catch (error) {
        console.error('Error al generar PDF:', error)
        alert('❌ Error al generar el PDF')
      } finally {
        this.isGenerating = false
      }
    }
  }
}
</script>

<style scoped>
.pdf-generator {
  margin: var(--spacing-lg) 0;
  display: flex;
  justify-content: center;
}

.btn-generate-pdf {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md) var(--spacing-xl);
  background: #20e404;
  color: white;
  border: none;
  border-radius: var(--border-radius-lg);
  font-size: var(--font-size-md);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: 0 4px 12px rgba(5, 238, 16, 0.3);
}

.btn-generate-pdf:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
  transform: translateY(-2px);
}

.btn-generate-pdf:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pdf-btn {
  background: linear-gradient(135deg, #20e404, #18b803);
  color: #0f172a; /* texto oscuro para mejor contraste */
  border: none;
  padding: 14px 30px;
  font-size: 16px;
  font-weight: 700;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 10px 25px rgba(32, 228, 4, 0.35);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.pdf-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(32, 228, 4, 0.5);
}

.pdf-btn:active:not(:disabled) {
  transform: scale(0.96);
}

.pdf-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
}
</style>