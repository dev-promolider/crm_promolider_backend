<template>
  <!-- Modal para editar masterclass -->
  <div class="modal fade" id="editMasterclassModal" tabindex="-1" role="dialog" aria-labelledby="editMasterclassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editMasterclassModalLabel">Editar Masterclass</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="updateMasterclass">
            <!-- Información básica -->
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="title">Título <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="title"
                    v-model="form.title"
                    :class="{ 'is-invalid': errors.title }"
                    required
                  />
                  <div v-if="errors.title" class="invalid-feedback">
                    {{ errors.title[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="form-group">
                  <label for="id_categories">Categoría <span class="text-danger">*</span></label>
                  <select
                    class="form-control"
                    id="id_categories"
                    v-model="form.id_categories"
                    :class="{ 'is-invalid': errors.id_categories }"
                    required
                  >
                    <option value="">Seleccionar categoría</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <div v-if="errors.id_categories" class="invalid-feedback">
                    {{ errors.id_categories[0] }}
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="description">Descripción</label>
              <textarea
                class="form-control"
                id="description"
                rows="4"
                v-model="form.description"
                :class="{ 'is-invalid': errors.description }"
              ></textarea>
              <div v-if="errors.description" class="invalid-feedback">
                {{ errors.description[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="objective">Objetivos</label>
              <textarea
                class="form-control"
                id="objective"
                rows="3"
                v-model="form.objective"
                :class="{ 'is-invalid': errors.objective }"
              ></textarea>
              <div v-if="errors.objective" class="invalid-feedback">
                {{ errors.objective[0] }}
              </div>
            </div>

            <!-- Información de contacto y fecha -->
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="date">Fecha</label>
                  <input
                    type="date"
                    class="form-control"
                    id="date"
                    v-model="form.date"
                    :class="{ 'is-invalid': errors.date }"
                  />
                  <div v-if="errors.date" class="invalid-feedback">
                    {{ errors.date[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="form-group">
                  <label for="email_contact">Email de contacto</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email_contact"
                    v-model="form.email_contact"
                    :class="{ 'is-invalid': errors.email_contact }"
                  />
                  <div v-if="errors.email_contact" class="invalid-feedback">
                    {{ errors.email_contact[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="form-group">
                  <label for="phone_contact">Teléfono de contacto</label>
                  <input
                    type="tel"
                    class="form-control"
                    id="phone_contact"
                    v-model="form.phone_contact"
                    :class="{ 'is-invalid': errors.phone_contact }"
                  />
                  <div v-if="errors.phone_contact" class="invalid-feedback">
                    {{ errors.phone_contact[0] }}
                  </div>
                </div>
              </div>

              <!-- Enlace del Meeting -->
              <div class="col-md-8">
                <div class="form-group">
                  <label for="meeting_link">Meeting Link</label>
                  <input
                    type="url"
                    class="form-control"
                    id="meeting_link"
                    v-model="form.meeting_link"
                    :class="{ 'is-invalid': errors.meeting_link }"
                    placeholder="https://example.com/meeting"
                  />
                  <div v-if="errors.meeting_link" class="invalid-feedback">
                    {{ errors.meeting_link[0] }}
                  </div>
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-4">
                <div class="form-group">
                  <label for="status">Status</label>
                  <select
                    id="status"
                    class="form-control"
                    v-model.number="form.status"
                    :class="{ 'is-invalid': errors.status }"
                  >
                    <option :value="0">Draft</option>
                    <option :value="1">Published</option>
                  </select>
                  <div v-if="errors.status" class="invalid-feedback">
                    {{ errors.status[0] }}
                  </div>
                </div>
              </div>
              
            </div>

            <!-- Archivos -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="images">Imágenes</label>
                  <input
                    type="file"
                    class="form-control-file"
                    id="images"
                    @change="handleFileChange('images', $event)"
                    accept="image/jpeg,image/png,image/jpg"
                    multiple
                  />
                  <small class="form-text text-muted">
                    Formatos permitidos: JPEG, PNG, JPG. Máximo 2MB por imagen.
                  </small>
                  <div v-if="errors.images" class="text-danger">
                    {{ errors.images[0] }}
                  </div>
                  
                  <!-- Previsualización de imágenes actuales -->
                  <div v-if="currentMasterclass && currentMasterclass.images && currentMasterclass.images.length > 0" class="mt-2">
                    <small class="text-muted">Imágenes actuales:</small>
                    <div class="mt-1 d-flex flex-wrap">
                      <div v-for="(image, index) in currentMasterclass.images" :key="index" class="mr-2 mb-2">
                        <img :src="image.image" :alt="`Imagen ${index + 1}`" class="img-thumbnail" style="max-height: 80px;">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="documents">Documentos</label>
                  <input
                    type="file"
                    class="form-control-file"
                    id="documents"
                    @change="handleFileChange('documents', $event)"
                    accept="application/pdf,.doc,.docx"
                    multiple
                  />
                  <small class="form-text text-muted">
                    Formatos permitidos: PDF, DOC, DOCX. Máximo 5MB por documento.
                  </small>
                  <div v-if="errors.documents" class="text-danger">
                    {{ errors.documents[0] }}
                  </div>
                  
                  <!-- Información de documentos actuales -->
                  <div v-if="currentMasterclass && currentMasterclass.documents && currentMasterclass.documents.length > 0" class="mt-2">
                    <small class="text-muted">Documentos actuales:</small>
                    <div class="mt-1">
                      <div v-for="(document, index) in currentMasterclass.documents" :key="index" class="mb-1">
                        <a :href="document.document" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-file"></i> Documento {{ index + 1 }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button
            type="button"
            class="btn btn-primary"
            @click="updateMasterclass"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm mr-2" role="status"></span>
            {{ isLoading ? 'Actualizando...' : 'Actualizar Masterclass' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MasterclassEditModal",
  props: {
    categories: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      currentMasterclass: null,
      isLoading: false,
      errors: {},
      form: {
        title: '',
        id_categories: '',
        description: '',
        objective: '',
        date: '',
        email_contact: '',
        phone_contact: '',
        meeting_link: '',   // ✅ Añadir
        status: '',         // ✅ Añadir
        images: null,
        documents: null
      }
    };
  },
  methods: {
    // Abrir modal con datos de la masterclass
    openModal(masterclass) {
      this.currentMasterclass = masterclass;
      this.resetForm();
      this.loadMasterclassData(masterclass);
      $('#editMasterclassModal').modal('show');
    },

    // Cargar datos de la masterclass en el formulario
    loadMasterclassData(masterclass) {
      this.form.title = masterclass.title || '';
      this.form.id_categories = masterclass.id_categories || '';
      this.form.description = masterclass.description || '';
      this.form.objective = masterclass.objectives || ''; // nota: si el campo en backend es "objective", usa masterclass.objective
      this.form.date = masterclass.date || '';
      this.form.email_contact = masterclass.email_contact || '';
      this.form.phone_contact = masterclass.phone_contact || '';
      this.form.meeting_link = masterclass.meeting_link || '';  // ✅ Añadir
      this.form.status = masterclass.status ?? '';              // ✅ Añadir
    },

    // Manejar cambios de archivos
    handleFileChange(field, event) {
      const files = event.target.files;
      if (files && files.length > 0) {
        this.form[field] = files;
      }
    },

    // Actualizar masterclass
    async updateMasterclass() {
      if (!this.currentMasterclass) return;
    
      this.isLoading = true;
      this.errors = {};
    
      try {
        const formData = new FormData();
    
        // Agregar campos básicos
        formData.append('title', this.form.title);
        formData.append('id_categories', this.form.id_categories);
        formData.append('description', this.form.description);
        formData.append('objective', this.form.objective);
        formData.append('date', this.form.date);
        formData.append('email_contact', this.form.email_contact);
        formData.append('phone_contact', this.form.phone_contact);
        formData.append('meeting_link', this.form.meeting_link);
        formData.append('status', this.form.status);
    
        // Agregar archivos si se seleccionaron
        if (this.form.images && this.form.images.length > 0) {
          Array.from(this.form.images).forEach((file, index) => {
            formData.append(`images[${index}]`, file);
          });
        }
        
        if (this.form.documents && this.form.documents.length > 0) {
          Array.from(this.form.documents).forEach((file, index) => {
            formData.append(`documents[${index}]`, file);
          });
        }
    
        // DEBUG: Mostrar datos que se van a enviar
        console.log('Datos del formulario:', this.form);
        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
          console.log(key, ':', value);
        }
    
        const response = await axios.post(`/masterclass/${this.currentMasterclass.id}/update`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
    
        $('#editMasterclassModal').modal('hide');
        this.$emit('masterclass-updated', response.data.data);
    
        this.$swal.fire({
          title: 'Éxito',
          text: 'Masterclass actualizada correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
    
      } catch (error) {
        console.error('Error al actualizar masterclass:', error);
        console.error('Response data:', error.response?.data);
        console.error('Response status:', error.response?.status);
    
        // Mostrar errores específicos de validación
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
          console.error('Errores de validación:', this.errors);
        }
    
        this.$swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'Error al actualizar la masterclass',
          icon: 'error'
        });
      } finally {
        this.isLoading = false;
      }
    },

    // Resetear formulario
    resetForm() {
      this.form = {
        title: '',
        id_categories: '',
        description: '',
        objective: '',
        date: '',
        email_contact: '',
        phone_contact: '',
        images: null,
        documents: null
      };
      this.errors = {};
    }
  }
};
</script>

<style scoped>
.modal-lg {
  max-width: 90%;
}

.form-group {
  margin-bottom: 1rem;
}

.img-thumbnail {
  border: 1px solid #dee2e6;
  padding: 0.25rem;
  background-color: #fff;
  border-radius: 0.25rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.text-danger {
  color: #dc3545!important;
}

.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.d-flex {
  display: flex;
}

.flex-wrap {
  flex-wrap: wrap;
}

.mr-2 {
  margin-right: 0.5rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mb-1 {
  margin-bottom: 0.25rem;
}
</style>