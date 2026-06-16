<template>
  <div class="container">
    <div class="card shadow-lg">
      <!-- Imagen de la masterclass -->

      <div v-if="masterclass.images.length > 1">
        <div id="masterclassCarousel" class="carousel slide shadow-sm rounded-lg" data-bs-ride="carousel">
          <ol class="carousel-indicators">
            <li v-for="(image, index) in masterclass.images" :key="index" :data-bs-target="'#masterclassCarousel'" :data-bs-slide-to="index" :class="{ active: index === 0 }"></li>
          </ol>
          <div class="carousel-inner">
            <div v-for="(image, index) in masterclass.images" :key="index" class="carousel-item" :class="{ active: index === 0 }">
              <div class="image-container">
                <img :src="image.image" class="d-block w-100" alt="Imagen de la Masterclass">
                <!-- Capa de texto superpuesta -->
                <div class="text-overlay">
                  <h2>{{ masterclass.title }}</h2>
                  <span>{{ masterclass.category_name }}</span>
                  <span><i class="fa-regular fa-calendar"></i>{{ formatDate(masterclass.date) }}</span>

                  <!-- Contenedor de botones -->
                  <div class="buttons-container">
                    <button v-if="!isRegistered" @click="registerMasterclass" :disabled="loading" class="btn btn-success btn-md shadow-sm px-4 py-2">
                      <i class="fa-solid fa-user-plus"></i>
                      {{ loading ? 'Registrando...' : 'Registrarse' }}
                    </button>
                    <button v-else @click="showInvitationModal" class="btn btn-primary btn-md shadow-sm px-4 py-2">
                      <i class="fa-solid fa-share"></i> Invitar
                    </button>
                  </div>
                </div>
                <!-- Texto en la parte superior derecha -->
                <div class="text-overlay-top-right">
                  <span class="badge badge-pill" :class="getStatusClass(masterclass.status)">
                    {{ getStatusText(masterclass.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <a class="carousel-control-prev" href="#masterclassCarousel" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </a>
          <a class="carousel-control-next" href="#masterclassCarousel" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </a>
        </div>
      </div>

      <div v-else>
        <div class="image-container">
          <img :src="masterclass.images[0].image" class="img-fluid" alt="Imagen de la Masterclass">
          <!-- Capa de texto superpuesta -->
          <div class="text-overlay">
            <h2>{{ masterclass.title }}</h2>
            <span>{{ masterclass.category_name }}</span>
            <span><i class="fa-regular fa-xs fa-calendar"></i>{{ formatDate(masterclass.date) }}</span>

            <!-- Contenedor de botones -->
            <div class="buttons-container">
              <button v-if="!isRegistered" @click="registerMasterclass" :disabled="loading" class="btn btn-success btn-md shadow-sm px-4 py-2">
                <i class="fa-solid fa-user-plus"></i>
                {{ loading ? 'Registrando...' : 'Registrarse' }}
              </button>
              <button v-else @click="showInvitationModal" class="btn btn-primary btn-md shadow-sm px-4 py-2">
                <i class="fa-solid fa-share"></i> Invitar
              </button>
            </div>
          </div>
          <!-- Texto en la parte superior derecha -->
          <div class="text-overlay-top-right">
            <span class="badge badge-pill" :class="getStatusClass(masterclass.status)">
              {{ getStatusText(masterclass.status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Sección de Descripción, Objetivos y Documentos -->
      <div class="row g-0 text-justify m-2 information-container">
        <div class="col-sm-6 col-md-8">
          <h4>Descripcion</h4>
          <p>{{ masterclass.description }}</p>
          <h4>Objetivos</h4>
          <p>{{ masterclass.objectives }}</p>
          <h4>Documentos</h4>
          <ul class="list-group">
            <li v-for="document in masterclass.documents" :key="document.id" class="list-group-item">
              <a :href="document.document" target="_blank" class="text-decoration-none">
                <i class="fa-solid fa-file-pdf"></i> Descargar Documento {{ document.id }}
              </a>
            </li>
          </ul>
        </div>

        <div class="col-6 col-md-4">
          <h4>Contacto</h4>
          <p>
            Email:<a :href="'mailto:' + masterclass.email_contact">{{ masterclass.email_contact }}</a>
          </p>
          <p>
            Teléfono: <a :href="'tel:' + masterclass.phone_contact">{{ masterclass.phone_contact }}</a>
          </p>
          <h4>Compartir en:</h4>
          <div class="social-icons">
            <a :href="getWhatsappUrl()" target="_blank" class="icon whatsapp">
              <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a :href="getFacebookUrl()" target="_blank" class="icon facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/" target="_blank" class="icon instagram">
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
              <i class="fa-solid fa-share"></i> Invitar a Masterclass: {{ masterclass.title }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
          
            <!-- ✅ Mostrar solo uno de los dos bloques -->
            <template v-if="!newInvitationLink">
              <div v-if="invitationData.existInvitation" class="alert alert-info">
                <h6><i class="fa-solid fa-info-circle"></i> Link de Invitación Activo</h6>
                <p>Ya tienes un link de invitación activo para esta masterclass.</p>
                <div class="input-group mb-3">
                  <input type="text" class="form-control" :value="invitationData.invitationLink" readonly>
                  <button class="btn btn-outline-primary" @click="copyToClipboard(invitationData.invitationLink)">
                    <i class="fa-solid fa-copy"></i> Copiar
                  </button>
                </div>
              </div>
            
              <div v-else class="alert alert-warning">
                <h6><i class="fa-solid fa-exclamation-triangle"></i> Crear Link de Invitación</h6>
                <p>No tienes un link de invitación activo. Haz clic en el botón para crear uno.</p>
                <button @click="createInvitationLink" :disabled="loadingInvitation" class="btn btn-primary">
                  <i class="fa-solid fa-plus"></i>
                  {{ loadingInvitation ? 'Creando...' : 'Crear Link de Invitación' }}
                </button>
              </div>
            </template>
          
            <!-- ✅ Solo se muestra si hay un nuevo link -->
            <div v-else class="alert alert-success mt-3">
              <h6><i class="fa-solid fa-check-circle"></i> Link Creado Exitosamente</h6>
              <p>Tu link de invitación ha sido creado exitosamente.</p>
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
  name: 'MasterclassDetails',
  props: {
    masterclass: Object // Recibe los datos desde Blade
  },
  data() {
    return {
      isRegistered: false, // Para verificar si el usuario está registrado
      loading: false, // Para el estado de carga del registro
      loadingInvitation: false, // Para el estado de carga de invitación
      error: null,
      showModal: false, // Para mostrar/ocultar el modal
      showToast: false, // Para mostrar/ocultar el toast
      toastMessage: '', // Mensaje del toast
      invitationData: {
        existInvitation: false,
        invitationLink: ''
      },
      newInvitationLink: '' // Para el nuevo link creado
    };
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    
    getStatusText(status) {
      return status ? 'Activo' : 'Inactivo';
    },
    
    getStatusClass(status) {
      return status ? 'badge-success' : 'badge-danger';
    },

    getCurrentUrl() {
      return window.location.href;
    },
    
    getWhatsappUrl() {
      const text = `Te recomiendo esta masterclass: ${this.masterclass.title}`;
      return `https://wa.me/?text=${encodeURIComponent(text)}`;
    },
    
    getFacebookUrl() {
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.getCurrentUrl())}`;
    },
    
    getWhatsappShareUrl() {
      const link = this.getCurrentInvitationLink();
      const text = `¡Hola! Te invito a esta increíble masterclass: "${this.masterclass.title}" - ${this.formatDate(this.masterclass.date)}. Regístrate usando mi link de invitación: ${link}`;
      return `https://wa.me/?text=${encodeURIComponent(text)}`;
    },
    
    getFacebookShareUrl() {
      const link = this.getCurrentInvitationLink();
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
    },
    
    getCurrentInvitationLink() {
      return this.newInvitationLink || this.invitationData.invitationLink || '';
    },
    
    async registerMasterclass() {
      if (!this.masterclass.id) {
        alert('Masterclass no válida');
        return;
      }
      
      this.loading = true;
      this.error = null;
      
      try {
        const response = await fetch(`/masterclass/register-masterclass/${this.masterclass.id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.isRegistered = true;
          this.showToastMessage(data.message || 'Registrado exitosamente en la masterclass');
          // Verificar si ya existe una invitación después del registro
          this.checkExistingInvitation();
        } else {
          throw new Error(data.message || 'Error al registrarse en la masterclass');
        }
      } catch (error) {
        console.error('Error al registrarse:', error);
        this.error = error.message;
        alert('Hubo un problema al registrarse.');
      } finally {
        this.loading = false;
      }
    },
    
    async checkRegistration() {
      if (!this.masterclass.id) return;
      
      try {
        const response = await fetch(`/masterclass/check-registration/${this.masterclass.id}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.isRegistered = data.isRegistered;
          if (this.isRegistered) {
            this.checkExistingInvitation();
          }
        }
      } catch (error) {
        console.error('Error al verificar el registro:', error);
      }
    },
    
    async checkExistingInvitation() {
      if (!this.masterclass.id) return;
      
      try {
        const response = await fetch(`/masterclass/check-invitation/${this.masterclass.id}`, {
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
      if (!this.masterclass.id) return;
      
      this.loadingInvitation = true;
      
      try {
        const response = await fetch(`/masterclass/create-invitation/${this.masterclass.id}`, {
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
        console.error('Error al crear enlace:', error);
        alert('Hubo un problema al generar el enlace.');
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
      this.checkExistingInvitation(); // 🔄 refresca datos
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
    this.checkRegistration();
  },
  
  watch: {
    masterclass: {
      handler(newValue) {
        if (newValue && newValue.id) {
          this.checkRegistration();
        }
      },
      deep: true,
      immediate: true
    }
  }
};
</script>

<style scoped>
.masterclass-image {
  max-height: 350px;
  object-fit: cover;
  border-bottom: 4px solid #007bff;
}

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

.buttons-container .btn {
  width: fit-content;
  min-width: 160px;
  text-align: center;
}

.buttons-container .btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.information-container h4{
  display: block;
  color: black;
  font-weight: bold;
  margin-bottom: 15px;
}

.information-container p{
  display: block;
  color: black;
  margin-bottom: 10px;
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
  .buttons-container .btn {
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