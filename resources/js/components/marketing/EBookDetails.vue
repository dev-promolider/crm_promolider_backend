<template>
  <div class="container">
    <div class="card shadow-lg">
      <!-- Imagen/Portada del E-book -->
      <div v-if="ebook.images && ebook.images.length > 1">
        <div id="ebookCarousel" class="carousel slide shadow-sm rounded-lg" data-bs-ride="carousel">
          <ol class="carousel-indicators">
            <li v-for="(image, index) in ebook.images" :key="index" :data-bs-target="'#ebookCarousel'" :data-bs-slide-to="index" :class="{ active: index === 0 }"></li>
          </ol>
          <div class="carousel-inner">
            <div v-for="(image, index) in ebook.images" :key="index" class="carousel-item" :class="{ active: index === 0 }">
              <div class="image-container">
                <img :src="image.image" class="d-block w-100" alt="Portada del E-book">
                <!-- Capa de texto superpuesta -->
                <div class="text-overlay">
                  <h2>{{ ebook.title }}</h2>
                  <span>{{ ebook.category_name }}</span>
                  <span><i class="fa-solid fa-user"></i> {{ ebook.author }}</span>
                  <span><i class="fa-solid fa-file-lines"></i> {{ ebook.pages }} páginas</span>
                  <span><i class="fa-solid fa-dollar-sign"></i> ${{ ebook.price }}</span>

                  <!-- Contenedor de botones -->
                  <div class="buttons-container">
                    <button v-if="!isPurchased" @click="purchaseEbook" :disabled="loading" class="btn btn-success btn-md shadow-sm px-4 py-2">
                      <i class="fa-solid fa-cart-shopping"></i> 
                      {{ loading ? 'Registrando...' : 'Registrar' }}
                    </button>
                    <div v-else class="purchased-actions">
                      <button @click="downloadEbook" class="btn btn-primary btn-md shadow-sm px-4 py-2">
                        <i class="fa-solid fa-download"></i> Descargar
                      </button>
                      <button @click="showInvitationModal" class="btn btn-info btn-md shadow-sm px-4 py-2">
                        <i class="fa-solid fa-share"></i> Invitar
                      </button>
                    </div>
                  </div>
                </div>
                <!-- Estado en la parte superior derecha -->
                <div class="text-overlay-top-right">
                  <span class="badge badge-pill" :class="getStatusClass(ebook.status)">
                    {{ getStatusText(ebook.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <a class="carousel-control-prev" href="#ebookCarousel" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </a>
          <a class="carousel-control-next" href="#ebookCarousel" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </a>
        </div>
      </div>

      <div v-else-if="ebook.images && ebook.images.length === 1">
        <div class="image-container">
          <img :src="ebook.images[0].image" class="img-fluid" alt="Portada del E-book">
          <!-- Capa de texto superpuesta -->
          <div class="text-overlay">
            <h2>{{ ebook.title }}</h2>
            <span>{{ ebook.category_name }}</span>
            <span><i class="fa-solid fa-user"></i> {{ ebook.author }}</span>
            <span><i class="fa-solid fa-file-lines"></i> {{ ebook.pages }} páginas</span>
            <span><i class="fa-solid fa-dollar-sign"></i> ${{ ebook.price }}</span>

            <!-- Contenedor de botones -->
            <div class="buttons-container">
              <button v-if="!isPurchased" @click="purchaseEbook" :disabled="loading" class="btn btn-success btn-md shadow-sm px-4 py-2">
                <i class="fa-solid fa-cart-shopping"></i> 
                {{ loading ? 'Registrando...' : 'Registrar' }}
              </button>
              <div v-else class="purchased-actions">
                <button @click="downloadEbook" class="btn btn-primary btn-md shadow-sm px-4 py-2">
                  <i class="fa-solid fa-download"></i> Descargar
                </button>
                <button @click="showInvitationModal" class="btn btn-info btn-md shadow-sm px-4 py-2">
                  <i class="fa-solid fa-share"></i> Invitar
                </button>
              </div>
            </div>
          </div>
          <!-- Estado en la parte superior derecha -->
          <div class="text-overlay-top-right">
            <span class="badge badge-pill" :class="getStatusClass(ebook.status)">
              {{ getStatusText(ebook.status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Imagen por defecto si no hay imágenes -->
      <div v-else class="image-container">
        <img src="/images/default-ebook.jpg" class="img-fluid" alt="E-book sin portada">
        <div class="text-overlay">
          <h2>{{ ebook.title || 'Sin título' }}</h2>
          <span>{{ ebook.category_name || 'Sin categoría' }}</span>
          <span><i class="fa-solid fa-user"></i> {{ ebook.author || 'Sin autor' }}</span>
          <span><i class="fa-solid fa-file-lines"></i> {{ ebook.pages || 0 }} páginas</span>
          <span><i class="fa-solid fa-dollar-sign"></i> ${{ ebook.price || 0 }}</span>
        </div>
      </div>

      <!-- Sección de Información -->
      <div class="row g-0 text-justify m-2 information-container">
        <div class="col-sm-6 col-md-8">
          <h4>Descripción</h4>
          <p>{{ ebook.description || 'Sin descripción disponible' }}</p>
          
          <h4>Capítulos</h4>
          <div v-if="ebook.chapters && ebook.chapters.length > 0">
            <div v-for="(chapter, index) in ebook.chapters" :key="chapter.id" class="chapter-item">
              <h6><i class="fa-solid fa-bookmark"></i> Capítulo {{ index + 1 }}: {{ chapter.title }}</h6>
              <p class="chapter-content">{{ chapter.content }}</p>
              <small class="text-muted">{{ chapter.pages }} páginas</small>
              <hr v-if="index < ebook.chapters.length - 1">
            </div>
          </div>
          <p v-else class="text-muted">No hay capítulos disponibles.</p>
          
          <h4>Documentos</h4>
          <ul class="list-group" v-if="ebook.documents && ebook.documents.length > 0">
            <li v-for="document in ebook.documents" :key="document.id" class="list-group-item">
              <a :href="document.document" target="_blank" class="text-decoration-none">
                <i class="fa-solid fa-file-pdf"></i> Descargar PDF
              </a>
            </li>
          </ul>
          <p v-else class="text-muted">No hay documentos disponibles.</p>
        </div>

        <div class="col-6 col-md-4">
          <h4>Información del Autor</h4>
          <p><strong>Autor:</strong> {{ ebook.author || 'Sin autor' }}</p>
          <p><strong>Páginas:</strong> {{ ebook.pages || 0 }}</p>
          <p><strong>Precio:</strong> ${{ ebook.price || 0 }}</p>
          <p><strong>Categoría:</strong> {{ ebook.category_name || 'Sin categoría' }}</p>
          
          <h4>Compartir en:</h4>
          <div class="social-icons">
            <a :href="getWhatsappUrl()" 
               target="_blank" class="icon whatsapp">
              <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a :href="getFacebookUrl()" 
               target="_blank" class="icon facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a :href="'https://www.instagram.com/'" 
               target="_blank" class="icon instagram">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Invitación -->
    <div v-if="showModal" class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fa-solid fa-share"></i> Invitar a E-book: {{ ebook.title }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <!-- Mostrar link existente solo si NO se acaba de crear uno nuevo -->
            <div v-if="invitationData.existInvitation && !newInvitationLink" class="alert alert-info">
              <h6><i class="fa-solid fa-info-circle"></i> Link de Invitación Activo</h6>
              <p>Ya tienes un link de invitación activo para este e-book.</p>
              <div class="input-group mb-3">
                <input type="text" class="form-control" :value="invitationData.invitationLink" readonly>
                <button class="btn btn-outline-primary" @click="copyToClipboard(invitationData.invitationLink)">
                  <i class="fa-solid fa-copy"></i> Copiar
                </button>
              </div>
            </div>
            
            <!-- Mostrar botón para crear link solo si no existe ninguno -->
            <div v-if="!invitationData.existInvitation && !newInvitationLink" class="alert alert-warning">
              <h6><i class="fa-solid fa-exclamation-triangle"></i> Crear Link de Invitación</h6>
              <p>No tienes un link de invitación activo. Haz clic en el botón para crear uno.</p>
              <button @click="createInvitationLink" :disabled="loadingInvitation" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> 
                {{ loadingInvitation ? 'Creando...' : 'Crear Link de Invitación' }}
              </button>
            </div>

            <!-- Mostrar mensaje de éxito solo cuando se crea un nuevo link -->
            <div v-if="newInvitationLink" class="alert alert-success mt-3">
              <h6><i class="fa-solid fa-check-circle"></i> Link Creado Exitosamente</h6>
              <p>Tu link de invitación ha sido creado y es válido por 7 días.</p>
              <div class="input-group mb-3">
                <input type="text" class="form-control" :value="newInvitationLink" readonly>
                <button class="btn btn-outline-success" @click="copyToClipboard(newInvitationLink)">
                  <i class="fa-solid fa-copy"></i> Copiar
                </button>
              </div>
            </div>

            <div class="mt-4">
              <h6>Compartir via:</h6>
              <div class="share-buttons">
                <a :href="getWhatsappShareUrl()" target="_blank" class="btn btn-success btn-sm">
                  <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
                <a :href="getFacebookShareUrl()" target="_blank" class="btn btn-primary btn-sm">
                  <i class="fa-brands fa-facebook"></i> Facebook
                </a>
                <button @click="copyToClipboard(getCurrentInvitationLink())" class="btn btn-info btn-sm">
                  <i class="fa-solid fa-copy"></i> Copiar Link
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de notificación -->
    <div v-if="showToast" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
      <div class="toast show" role="alert">
        <div class="toast-header">
          <i class="fa-solid fa-check-circle text-success me-2"></i>
          <strong class="me-auto">Éxito</strong>
          <button type="button" class="btn-close" @click="showToast = false"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EbookDetails',
  props: {
    ebook: {
      type: Object,
      default: () => ({
        id: null,
        title: '',
        author: '',
        pages: 0,
        price: 0,
        description: '',
        category_name: '',
        status: 1,
        images: [],
        documents: [],
        chapters: []
      })
    }
  },
  data() {
    return {
      isPurchased: false,
      loading: false,
      loadingInvitation: false,
      error: null,
      showModal: false,
      showToast: false,
      toastMessage: '',
      invitationData: {
        existInvitation: false,
        invitationLink: ''
      },
      newInvitationLink: ''
    };
  },
  methods: {
    getStatusText(status) {
      return status ? 'Disponible' : 'No disponible';
    },
    
    getStatusClass(status) {
      return status ? 'badge-success' : 'badge-danger';
    },
    
    getCurrentUrl() {
      return window.location.href;
    },
    
    getWhatsappUrl() {
      const text = `Te recomiendo este e-book: ${this.ebook.title}`;
      return `https://wa.me/?text=${encodeURIComponent(text)}`;
    },
    
    getFacebookUrl() {
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.getCurrentUrl())}`;
    },
    
    getWhatsappShareUrl() {
      const link = this.getCurrentInvitationLink();
      const text = `¡Hola! Te invito a conocer este increíble e-book: "${this.ebook.title}" por ${this.ebook.author}. Regístrate usando mi link de invitación: ${link}`;
      return `https://wa.me/?text=${encodeURIComponent(text)}`;
    },
    
    getFacebookShareUrl() {
      const link = this.getCurrentInvitationLink();
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
    },
    
    getCurrentInvitationLink() {
      return this.newInvitationLink || this.invitationData.invitationLink || '';
    },
    
    async purchaseEbook() {
      if (!this.ebook.id) {
        alert('E-book no válido');
        return;
      }
      
      this.loading = true;
      this.error = null;
      
      try {
        const response = await fetch(`/marketing/ebook/purchase/${this.ebook.id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.isPurchased = true;
          this.showToastMessage(data.message || 'E-book comprado exitosamente');
          // Verificar si ya existe una invitación después de la compra
          this.checkExistingInvitation();
        } else {
          throw new Error(data.message || 'Error al resgistrar el e-book');
        }
      } catch (error) {
        console.error('Error al registrar el e-book:', error);
        this.error = error.message;
        alert('Hubo un problema al procesar la compra.');
      } finally {
        this.loading = false;
      }
    },
    
    downloadEbook() {
      if (this.ebook.documents && this.ebook.documents.length > 0) {
        window.open(this.ebook.documents[0].document, '_blank');
      } else {
        alert('No hay archivo disponible para descargar.');
      }
    },
    
    async checkPurchase() {
      if (!this.ebook.id) return;
      
      try {
        const response = await fetch(`/marketing/ebook/check-purchase/${this.ebook.id}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.isPurchased = data.isPurchased;
          if (this.isPurchased) {
            this.checkExistingInvitation();
          }
        }
      } catch (error) {
        console.error('Error al verificar la compra:', error);
      }
    },
    
    async checkExistingInvitation() {
      if (!this.ebook.id) return;
      
      try {
        const response = await fetch(`/marketing/ebook/check-invitation/${this.ebook.id}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.invitationData = {
            existInvitation: data.existInvitation,
            invitationLink: data.invitationLink || ''
          };
        }
      } catch (error) {
        console.error('Error al verificar invitación:', error);
      }
    },
    
    async createInvitationLink() {
      if (!this.ebook.id) return;
      
      this.loadingInvitation = true;
      
      try {
        const response = await fetch(`/marketing/ebook/invitation-link/${this.ebook.id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.newInvitationLink = data.link;
          this.invitationData.existInvitation = true;
          this.invitationData.invitationLink = data.link;
          this.showToastMessage('Link de invitación creado exitosamente');
        } else {
          throw new Error(data.message || 'Error al crear link de invitación');
        }
      } catch (error) {
        console.error('Error al crear link de invitación:', error);
        alert('Hubo un problema al crear el link de invitación.');
      } finally {
        this.loadingInvitation = false;
      }
    },
    
    showInvitationModal() {
      this.showModal = true;
      this.newInvitationLink = '';
      this.checkExistingInvitation();
    },
    
    closeModal() {
      this.showModal = false;
      this.newInvitationLink = '';
    },
    
    async copyToClipboard(text) {
      try {
        await navigator.clipboard.writeText(text);
        this.showToastMessage('Link copiado al portapapeles');
      } catch (error) {
        console.error('Error al copiar:', error);
        alert('No se pudo copiar el link');
      }
    },
    
    showToastMessage(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  },
  
  mounted() {
    this.checkPurchase();
  },
  
  watch: {
    ebook: {
      handler(newValue) {
        if (newValue && newValue.id) {
          this.checkPurchase();
        }
      },
      deep: true,
      immediate: true
    }
  }
};
</script>

<style scoped>
.image-container {
  position: relative;
  width: 100%;
  height: 300px;
  overflow: hidden;
  border-radius: 10px;
}

.image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-container::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to right, rgb(24,28,28) 0%, rgba(0, 0, 0, 0) 100%);
  z-index: 1;
  pointer-events: none;
  border-radius: 10px;
}

.text-overlay {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  font-weight: bold;
  text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
  z-index: 2;
}

.text-overlay h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: white;
}

.text-overlay span {
  font-size: 16px;
  display: block;
  opacity: 0.9;
  margin-bottom: 5px;
}

.text-overlay-top-right {
  position: absolute;
  top: 20px;
  right: 20px;
  color: white;
  font-weight: bold;
  z-index: 2;
  font-size: 18px;
  padding: 5px 10px;
  border-radius: 5px;
}

.buttons-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 15px;
}

.purchased-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.buttons-container .btn,
.purchased-actions .btn {
  width: fit-content;
  min-width: 160px;
  text-align: center;
}

.buttons-container .btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.information-container h4 {
  display: block;
  color: black;
  font-weight: bold;
  margin-bottom: 15px;
}

.information-container p {
  display: block;
  color: black;
  margin-bottom: 10px;
}

.chapter-item {
  margin-bottom: 20px;
}

.chapter-item h6 {
  color: #007bff;
  margin-bottom: 8px;
}

.chapter-content {
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.social-icons {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.social-icons .icon {
  width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: #25D366;
  color: white;
  font-size: 22px;
  text-decoration: none;
  transition: background 0.3s ease, transform 0.2s ease;
}

.social-icons .icon:hover {
  transform: scale(1.1);
}

.social-icons .whatsapp {
  background-color: #25D366;
}

.social-icons .facebook {
  background-color: #1877F2;
}

.social-icons .instagram {
  background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF);
}

.share-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.share-buttons .btn {
  display: flex;
  align-items: center;
  gap: 5px;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-pill {
  border-radius: 10px;
  padding: 8px 12px;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 10px;
}

.modal {
  z-index: 1050;
}

.toast-container {
  z-index: 1055;
}

.toast {
  min-width: 250px;
}

.modal-content {
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.alert {
  border-radius: 8px;
}

.input-group {
  border-radius: 6px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

@media (max-width: 768px) {
  .purchased-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .purchased-actions .btn {
    width: 100%;
    min-width: auto;
  }
  
  .share-buttons {
    flex-direction: column;
  }
  
  .share-buttons .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>