<template>
  <div class="mini-course-viewer">
    <!-- Header del Curso -->
    <div class="course-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="course-title mb-3">{{ miniCourse.title }}</h1>
          <div class="course-meta mb-3">
            <span class="badge bg-primary me-2">
              <i class="fa fa-clock"></i> {{ miniCourse.duration }} minutos
            </span>
            <span class="badge bg-success me-2">
              <i class="fa fa-signal"></i> {{ formatLevel(miniCourse.level) }}
            </span>
            <span class="badge bg-info">
              <i class="fa fa-folder"></i> {{ miniCourse.category_name }}
            </span>
          </div>
          <p class="course-description">{{ miniCourse.description }}</p>
        </div>
        <div class="col-md-4 text-center" v-if="miniCourse.images && miniCourse.images.length > 0">
          <img 
            :src="miniCourse.images[0].image" 
            alt="Imagen del curso"
            class="course-image img-fluid rounded shadow-sm"
          >
        </div>
      </div>
    </div>

    <!-- Navegación por pestañas -->
    <ul class="nav nav-pills mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link active" 
          :class="{ active: activeTab === 'modules' }"
          @click="activeTab = 'modules'"
          type="button"
        >
          <i class="fa fa-list-alt me-1"></i>
          Módulos ({{ miniCourse.modules ? miniCourse.modules.length : 0 }})
        </button>
      </li>
      <li class="nav-item" role="presentation" v-if="miniCourse.videos && miniCourse.videos.length > 0">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'videos' }"
          @click="activeTab = 'videos'"
          type="button"
        >
          <i class="fa fa-play me-1"></i>
          Videos ({{ miniCourse.videos.length }})
        </button>
      </li>
      <li class="nav-item" role="presentation" v-if="miniCourse.documents && miniCourse.documents.length > 0">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'documents' }"
          @click="activeTab = 'documents'"
          type="button"
        >
          <i class="fa fa-file-alt me-1"></i>
          Documentos ({{ miniCourse.documents.length }})
        </button>
      </li>
      <li class="nav-item" role="presentation" v-if="miniCourse.images && miniCourse.images.length > 1">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'gallery' }"
          @click="activeTab = 'gallery'"
          type="button"
        >
          <i class="fa fa-images me-1"></i>
          Galería ({{ miniCourse.images.length }})
        </button>
      </li>
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content">
      <!-- Módulos -->
      <div v-show="activeTab === 'modules'" class="tab-pane">
        <div class="modules-container">
          <div 
            v-for="(modulo, index) in miniCourse.modules" 
            :key="'module-' + index"
            class="module-card card mb-3 shadow-sm"
          >
            <div class="card-header bg-light">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                  <span class="module-number">{{ index + 1 }}.</span>
                  {{ modulo.title }}
                </h5>
                <span class="badge bg-primary">
                  <i class="fa fa-clock"></i> {{ modulo.duration }} mins
                </span>
              </div>
            </div>
            <div class="card-body">
              <p class="module-content">{{ modulo.content }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Videos -->
      <div v-show="activeTab === 'videos'" class="tab-pane">
        <div class="videos-container">
          <div class="row">
            <div 
              v-for="(video, index) in miniCourse.videos" 
              :key="'video-' + index"
              class="col-md-6 mb-4"
            >
              <div class="video-card card h-100 shadow-sm">
                <div class="video-wrapper">
                  <video 
                    :src="video.video" 
                    class="card-img-top video-player"
                    controls
                    preload="metadata"
                  >
                    Tu navegador no soporta el elemento video.
                  </video>
                </div>
                <div class="card-body">
                  <h6 class="card-title">{{ video.title }}</h6>
                  <p class="card-text text-muted" v-if="video.description">
                    {{ video.description }}
                  </p>
                  <div class="video-info">
                    <small class="text-muted">
                      <i class="fa fa-clock"></i> {{ video.duration }} mins
                      <span class="ms-2">
                        <i class="fa fa-sort-numeric-up"></i> Orden: {{ video.order }}
                      </span>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documentos -->
      <div v-show="activeTab === 'documents'" class="tab-pane">
        <div class="documents-container">
          <div class="row">
            <div 
              v-for="(doc, index) in miniCourse.documents" 
              :key="'doc-' + index"
              class="col-md-4 mb-3"
            >
              <div class="document-card card h-100 shadow-sm text-center">
                <div class="card-body d-flex flex-column">
                  <div class="document-icon mb-3">
                    <i class="fa fa-file-alt fa-3x text-primary"></i>
                  </div>
                  <h6 class="card-title">Documento {{ index + 1 }}</h6>
                  <div class="mt-auto">
                    <a 
                      :href="doc.document" 
                      target="_blank"
                      class="btn btn-primary btn-sm"
                    >
                      <i class="fa fa-download me-1"></i>
                      Ver documento
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Galería de imágenes -->
      <div v-show="activeTab === 'gallery'" class="tab-pane">
        <div class="gallery-container">
          <div class="row">
            <div 
              v-for="(image, index) in miniCourse.images" 
              :key="'image-' + index"
              class="col-md-4 mb-3"
            >
              <div class="image-card card shadow-sm">
                <img 
                  :src="image.image" 
                  :alt="'Imagen ' + (index + 1)"
                  class="card-img gallery-image"
                  @click="openImageModal(image.image, index)"
                  style="cursor: pointer;"
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para ver imágenes en grande -->
    <div 
      class="modal fade" 
      id="imageModal" 
      tabindex="-1" 
      v-if="showModal"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Imagen {{ currentImageIndex + 1 }}</h5>
            <button 
              type="button" 
              class="btn-close" 
              @click="closeImageModal"
            ></button>
          </div>
          <div class="modal-body text-center">
            <img 
              :src="currentImage" 
              alt="Imagen ampliada"
              class="img-fluid"
            >
          </div>
          <div class="modal-footer" v-if="miniCourse.images.length > 1">
            <button 
              type="button" 
              class="btn btn-secondary me-auto"
              @click="previousImage"
              :disabled="currentImageIndex === 0"
            >
              <i class="fa fa-chevron-left"></i> Anterior
            </button>
            <button 
              type="button" 
              class="btn btn-secondary"
              @click="nextImage"
              :disabled="currentImageIndex === miniCourse.images.length - 1"
            >
              Siguiente <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MiniCourseViewer",
  props: {
    miniCourse: {
      type: Object,
      required: true,
      default: () => ({})
    }
  },
  data() {
    return {
      activeTab: 'modules',
      showModal: false,
      currentImage: '',
      currentImageIndex: 0
    };
  },
  methods: {
    formatLevel(level) {
      const levels = {
        'principiante': 'Principiante',
        'intermedio': 'Intermedio',
        'avanzado': 'Avanzado'
      };
      return levels[level] || level;
    },

    openImageModal(imageSrc, index) {
      this.currentImage = imageSrc;
      this.currentImageIndex = index;
      this.showModal = true;
      // Usar Bootstrap modal si está disponible
      if (typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
      }
    },

    closeImageModal() {
      this.showModal = false;
    },

    previousImage() {
      if (this.currentImageIndex > 0) {
        this.currentImageIndex--;
        this.currentImage = this.miniCourse.images[this.currentImageIndex].image;
      }
    },

    nextImage() {
      if (this.currentImageIndex < this.miniCourse.images.length - 1) {
        this.currentImageIndex++;
        this.currentImage = this.miniCourse.images[this.currentImageIndex].image;
      }
    }
  },

  mounted() {
    // Configurar el modal para que se cierre correctamente
    if (this.showModal) {
      document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
          this.closeImageModal();
        }
      });
    }
  }
};
</script>

<style scoped>
.mini-course-viewer {
  max-width: 100%;
}

.course-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 15px;
  margin-bottom: 2rem;
}

.course-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.course-description {
  font-size: 1.1rem;
  line-height: 1.6;
  opacity: 0.9;
}

.course-image {
  max-height: 200px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.course-meta .badge {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
}

.nav-pills .nav-link {
  color: #6c757d;
  font-weight: 500;
  border-radius: 25px;
  padding: 0.75rem 1.5rem;
  margin-right: 0.5rem;
  transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
}

.nav-pills .nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.module-card {
  border: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.module-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.module-number {
  color: #667eea;
  font-weight: 700;
  font-size: 1.2rem;
}

.module-content {
  line-height: 1.6;
  color: #6c757d;
}

.video-card {
  border: none;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.video-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.video-wrapper {
  position: relative;
  overflow: hidden;
}

.video-player {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.document-card {
  border: none;
  transition: transform 0.3s ease;
}

.document-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.document-icon {
  opacity: 0.7;
}

.image-card {
  border: none;
  overflow: hidden;
}

.gallery-image {
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-image:hover {
  transform: scale(1.05);
}

.modal-content {
  border: none;
  border-radius: 15px;
}

.modal-body img {
  max-height: 70vh;
  object-fit: contain;
}

/* Animaciones */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tab-pane {
  animation: fadeIn 0.5s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .course-title {
    font-size: 2rem;
  }
  
  .course-header {
    padding: 1.5rem;
  }
  
  .nav-pills .nav-link {
    padding: 0.5rem 1rem;
    margin-bottom: 0.5rem;
  }
}
</style>