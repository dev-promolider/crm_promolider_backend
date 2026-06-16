<template>
  <!-- Modal para editar e-book -->
  <div class="modal fade" id="editEbookModal" tabindex="-1" role="dialog" aria-labelledby="editEbookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editEbookModalLabel">Editar E-book</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="updateEbook">
            <!-- Información básica -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="titulo">Título <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="titulo"
                    v-model="form.titulo"
                    :class="{ 'is-invalid': errors.titulo }"
                    required
                  />
                  <div v-if="errors.titulo" class="invalid-feedback">
                    {{ errors.titulo[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="autor">Autor <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="autor"
                    v-model="form.autor"
                    :class="{ 'is-invalid': errors.autor }"
                    required
                  />
                  <div v-if="errors.autor" class="invalid-feedback">
                    {{ errors.autor[0] }}
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="categoria">Categoría <span class="text-danger">*</span></label>
                  <select
                    class="form-control"
                    id="categoria"
                    v-model="form.categoria"
                    :class="{ 'is-invalid': errors.categoria }"
                    required
                  >
                    <option value="">Seleccionar categoría</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <div v-if="errors.categoria" class="invalid-feedback">
                    {{ errors.categoria[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-group">
                  <label for="precio">Precio <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control"
                    id="precio"
                    v-model="form.precio"
                    :class="{ 'is-invalid': errors.precio }"
                    required
                  />
                  <div v-if="errors.precio" class="invalid-feedback">
                    {{ errors.precio[0] }}
                  </div>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-group">
                  <label for="paginas">Páginas <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    min="1"
                    class="form-control"
                    id="paginas"
                    v-model="form.paginas"
                    :class="{ 'is-invalid': errors.paginas }"
                    required
                  />
                  <div v-if="errors.paginas" class="invalid-feedback">
                    {{ errors.paginas[0] }}
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="descripcion">Descripción <span class="text-danger">*</span></label>
              <textarea
                class="form-control"
                id="descripcion"
                rows="4"
                v-model="form.descripcion"
                :class="{ 'is-invalid': errors.descripcion }"
                required
              ></textarea>
              <div v-if="errors.descripcion" class="invalid-feedback">
                {{ errors.descripcion[0] }}
              </div>
            </div>

            <!-- Archivos -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="portada">Portada</label>
                  <input
                    type="file"
                    class="form-control-file"
                    id="portada"
                    @change="handleFileChange('portada', $event)"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                  />
                  <small class="form-text text-muted">
                    Formatos permitidos: JPEG, PNG, JPG, WEBP. Máximo 2MB.
                  </small>
                  <div v-if="errors.portada" class="text-danger">
                    {{ errors.portada[0] }}
                  </div>
                  
                  <!-- Previsualización de portada actual -->
                  <div v-if="currentEbook && currentEbook.images && currentEbook.images.length > 0" class="mt-2">
                    <small class="text-muted">Portada actual:</small>
                    <div class="mt-1">
                      <img :src="currentEbook.images[0].image" alt="Portada actual" class="img-thumbnail" style="max-height: 100px;">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="archivo_pdf">Archivo PDF</label>
                  <input
                    type="file"
                    class="form-control-file"
                    id="archivo_pdf"
                    @change="handleFileChange('archivo_pdf', $event)"
                    accept="application/pdf"
                  />
                  <small class="form-text text-muted">
                    Formato permitido: PDF. Máximo 10MB.
                  </small>
                  <div v-if="errors.archivo_pdf" class="text-danger">
                    {{ errors.archivo_pdf[0] }}
                  </div>
                  
                  <!-- Información del PDF actual -->
                  <div v-if="currentEbook && currentEbook.documents && currentEbook.documents.length > 0" class="mt-2">
                    <small class="text-muted">PDF actual:</small>
                    <div class="mt-1">
                      <a :href="currentEbook.documents[0].document" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> Ver PDF
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Capítulos -->
            <div class="form-group">
              <label>Capítulos</label>
              <div class="border p-3 rounded">
                <div v-for="(capitulo, index) in form.capitulos" :key="index" class="mb-3">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h6 class="mb-0">Capítulo {{ index + 1 }}</h6>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        @click="removeChapter(index)"
                        v-if="form.capitulos.length > 1"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-8">
                          <div class="form-group">
                            <label>Título del capítulo</label>
                            <input
                              type="text"
                              class="form-control"
                              v-model="capitulo.titulo"
                              :class="{ 'is-invalid': errors[`capitulos.${index}.titulo`] }"
                              required
                            />
                            <div v-if="errors[`capitulos.${index}.titulo`]" class="invalid-feedback">
                              {{ errors[`capitulos.${index}.titulo`][0] }}
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Páginas</label>
                            <input
                              type="number"
                              min="1"
                              class="form-control"
                              v-model="capitulo.paginas"
                              :class="{ 'is-invalid': errors[`capitulos.${index}.paginas`] }"
                              required
                            />
                            <div v-if="errors[`capitulos.${index}.paginas`]" class="invalid-feedback">
                              {{ errors[`capitulos.${index}.paginas`][0] }}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label>Contenido</label>
                        <textarea
                          class="form-control"
                          rows="4"
                          v-model="capitulo.contenido"
                          :class="{ 'is-invalid': errors[`capitulos.${index}.contenido`] }"
                          required
                        ></textarea>
                        <div v-if="errors[`capitulos.${index}.contenido`]" class="invalid-feedback">
                          {{ errors[`capitulos.${index}.contenido`][0] }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm mt-2"
                  @click="addChapter"
                >
                  <i class="fas fa-plus"></i> Agregar Capítulo
                </button>
              </div>
            </div>
          </form>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button
            type="button"
            class="btn btn-primary"
            @click="updateEbook"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm mr-2" role="status"></span>
            {{ isLoading ? 'Actualizando...' : 'Actualizar E-book' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "EbookEditModal",
  props: {
    categories: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      currentEbook: null,
      isLoading: false,
      errors: {},
      form: {
        titulo: '',
        descripcion: '',
        precio: '',
        autor: '',
        categoria: '',
        paginas: '',
        portada: null,
        archivo_pdf: null,
        capitulos: [{
          titulo: '',
          contenido: '',
          paginas: ''
        }]
      }
    };
  },
  methods: {
    // Abrir modal con datos del e-book
    openModal(ebook) {
      this.currentEbook = ebook;
      this.resetForm();
      this.loadEbookData(ebook);
      $('#editEbookModal').modal('show');
    },

    // Cargar datos del e-book en el formulario
    loadEbookData(ebook) {
      this.form.titulo = ebook.title || '';
      this.form.descripcion = ebook.description || '';
      this.form.precio = ebook.price || '';
      this.form.autor = ebook.author || '';
      this.form.categoria = ebook.category_id || '';
      this.form.paginas = ebook.pages || '';
      
      // Cargar capítulos si existen
      if (ebook.chapters && ebook.chapters.length > 0) {
        this.form.capitulos = ebook.chapters.map(chapter => ({
          titulo: chapter.title || '',
          contenido: chapter.content || '',
          paginas: chapter.pages || ''
        }));
      }
    },

    // Manejar cambios de archivos
    handleFileChange(field, event) {
      const file = event.target.files[0];
      if (file) {
        this.form[field] = file;
      }
    },

    // Agregar capítulo
    addChapter() {
      this.form.capitulos.push({
        titulo: '',
        contenido: '',
        paginas: ''
      });
    },

    // Eliminar capítulo
    removeChapter(index) {
      if (this.form.capitulos.length > 1) {
        this.form.capitulos.splice(index, 1);
      }
    },

    // Actualizar e-book
    async updateEbook() {
      if (!this.currentEbook) return;
    
      this.isLoading = true;
      this.errors = {};
    
      try {
        const formData = new FormData();
    
        // Agregar campos básicos
        formData.append('titulo', this.form.titulo);
        formData.append('descripcion', this.form.descripcion);
        formData.append('precio', this.form.precio);
        formData.append('autor', this.form.autor);
        formData.append('categoria', this.form.categoria);
        formData.append('paginas', this.form.paginas);
    
        // IMPORTANTE: Cambiar el método a PUT para que coincida con la ruta
        formData.append('_method', 'PUT');
    
        // Agregar archivos si se seleccionaron
        if (this.form.portada) {
          formData.append('portada', this.form.portada);
        }
        if (this.form.archivo_pdf) {
          formData.append('archivo_pdf', this.form.archivo_pdf);
        }
    
        // Agregar capítulos
        this.form.capitulos.forEach((capitulo, index) => {
          formData.append(`capitulos[${index}][titulo]`, capitulo.titulo);
          formData.append(`capitulos[${index}][contenido]`, capitulo.contenido);
          formData.append(`capitulos[${index}][paginas]`, capitulo.paginas);
        });
    
        // DEBUG: Mostrar datos que se van a enviar
        console.log('Datos del formulario:', this.form);
        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
          console.log(key, ':', value);
        }
    
        // CAMBIO IMPORTANTE: Usar POST con _method=PUT en lugar de PUT directo
        const response = await axios.post(`/marketing/ebook/${this.currentEbook.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-HTTP-Method-Override': 'PUT'
          }
        });
    
        $('#editEbookModal').modal('hide');
        this.$emit('ebook-updated', response.data.data);
    
        this.$swal.fire({
          title: 'Éxito',
          text: 'E-book actualizado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
    
      } catch (error) {
        console.error('Error al actualizar e-book:', error);
        console.error('Response data:', error.response?.data);
        console.error('Response status:', error.response?.status);
    
        // MOSTRAR ERRORES ESPECÍFICOS DE VALIDACIÓN
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
          console.error('Errores de validación:', this.errors);
        }
    
        this.$swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'Error al actualizar el e-book',
          icon: 'error'
        });
      } finally {
        this.isLoading = false;
      }
    },

    // Resetear formulario
    resetForm() {
      this.form = {
        titulo: '',
        descripcion: '',
        precio: '',
        autor: '',
        categoria: '',
        paginas: '',
        portada: null,
        archivo_pdf: null,
        capitulos: [{
          titulo: '',
          contenido: '',
          paginas: ''
        }]
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

.card {
  border: 1px solid #dee2e6;
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
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
</style>