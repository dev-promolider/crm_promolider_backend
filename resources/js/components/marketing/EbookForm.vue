<template>
  <div class="ebook-form">
    <form @submit.prevent="submitForm" enctype="multipart/form-data">
      <div class="row">
        <!-- Información básica -->
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="titulo" class="form-label">Título del E-book *</label>
            <input
              type="text"
              id="titulo"
              v-model="form.titulo"
              class="form-control"
              :class="{ 'is-invalid': errors.titulo }"
              placeholder="Ingrese el título del e-book"
              required
            />
            <div v-if="errors.titulo" class="invalid-feedback">
              {{ Array.isArray(errors.titulo) ? errors.titulo[0] : errors.titulo }}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="autor" class="form-label">Autor *</label>
            <input
              type="text"
              id="autor"
              v-model="form.autor"
              class="form-control"
              :class="{ 'is-invalid': errors.autor }"
              placeholder="Nombre del autor"
              required
            />
            <div v-if="errors.autor" class="invalid-feedback">
              {{ Array.isArray(errors.autor) ? errors.autor[0] : errors.autor }}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="paginas" class="form-label">Número de Páginas *</label>
            <input
              type="number"
              id="paginas"
              v-model="form.paginas"
              class="form-control"
              :class="{ 'is-invalid': errors.paginas }"
              placeholder="50"
              min="1"
              required
            />
            <div v-if="errors.paginas" class="invalid-feedback">
              {{ Array.isArray(errors.paginas) ? errors.paginas[0] : errors.paginas }}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="categoria" class="form-label">Categoría *</label>
            <select
              id="categoria"
              v-model="form.categoria"
              class="form-control"
              :class="{ 'is-invalid': errors.categoria }"
              required
            >
              <option value="">Seleccione una categoría</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <div v-if="errors.categoria" class="invalid-feedback">
              {{ Array.isArray(errors.categoria) ? errors.categoria[0] : errors.categoria }}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="portada" class="form-label">Portada del E-book</label>
            <input
              type="file"
              id="portada"
              @change="handlePortadaUpload"
              class="form-control"
              :class="{ 'is-invalid': errors.portada }"
              accept="image/jpeg,image/png,image/jpg,image/webp"
            />
            <div v-if="errors.portada" class="invalid-feedback">
              {{ Array.isArray(errors.portada) ? errors.portada[0] : errors.portada }}
            </div>
            <small class="form-text text-muted">
              Tamaño máximo: 2MB. Formatos permitidos: JPEG, PNG, JPG, WEBP
            </small>
          </div>
        </div>

        <div class="col-12">
          <div class="form-group mb-3">
            <label for="archivo_pdf" class="form-label">Archivo PDF del E-book</label>
            <input
              type="file"
              id="archivo_pdf"
              @change="handlePdfUpload"
              class="form-control"
              :class="{ 'is-invalid': errors.archivo_pdf }"
              accept=".pdf"
            />
            <div v-if="errors.archivo_pdf" class="invalid-feedback">
              {{ Array.isArray(errors.archivo_pdf) ? errors.archivo_pdf[0] : errors.archivo_pdf }}
            </div>
            <small class="form-text text-muted">
              Tamaño máximo: 10MB. Formatos permitidos: PDF
            </small>
          </div>
        </div>

        <div class="col-12">
          <div class="form-group mb-3">
            <label for="descripcion" class="form-label">Descripción *</label>
            <textarea
              id="descripcion"
              v-model="form.descripcion"
              class="form-control"
              :class="{ 'is-invalid': errors.descripcion }"
              rows="4"
              placeholder="Describe el contenido del e-book"
              required
            ></textarea>
            <div v-if="errors.descripcion" class="invalid-feedback">
              {{ Array.isArray(errors.descripcion) ? errors.descripcion[0] : errors.descripcion }}
            </div>
          </div>
        </div>
      </div>

      <!-- Capítulos del e-book -->
      <div class="row">
        <div class="col-12">
          <h5>Capítulos del E-book</h5>
          <div
            v-for="(capitulo, index) in form.capitulos"
            :key="index"
            class="card mb-3"
          >
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Capítulo {{ index + 1 }}</h6>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger"
                @click="removeCapitulo(index)"
                v-if="form.capitulos.length > 1"
              >
                <i class="fa fa-trash"></i>
              </button>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="form-group mb-3">
                    <label class="form-label">Título del Capítulo *</label>
                    <input
                      type="text"
                      v-model="capitulo.titulo"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`capitulos.${index}.titulo`] }"
                      placeholder="Título del capítulo"
                      required
                    />
                    <div v-if="errors[`capitulos.${index}.titulo`]" class="invalid-feedback">
                      {{ Array.isArray(errors[`capitulos.${index}.titulo`]) ? errors[`capitulos.${index}.titulo`][0] : errors[`capitulos.${index}.titulo`] }}
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label class="form-label">Páginas *</label>
                    <input
                      type="number"
                      v-model="capitulo.paginas"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`capitulos.${index}.paginas`] }"
                      placeholder="5"
                      min="1"
                      required
                    />
                    <div v-if="errors[`capitulos.${index}.paginas`]" class="invalid-feedback">
                      {{ Array.isArray(errors[`capitulos.${index}.paginas`]) ? errors[`capitulos.${index}.paginas`][0] : errors[`capitulos.${index}.paginas`] }}
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group mb-3">
                    <label class="form-label">Contenido del Capítulo *</label>
                    <textarea
                      v-model="capitulo.contenido"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`capitulos.${index}.contenido`] }"
                      rows="4"
                      placeholder="Describe el contenido de este capítulo"
                      required
                    ></textarea>
                    <div v-if="errors[`capitulos.${index}.contenido`]" class="invalid-feedback">
                      {{ Array.isArray(errors[`capitulos.${index}.contenido`]) ? errors[`capitulos.${index}.contenido`][0] : errors[`capitulos.${index}.contenido`] }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button
            type="button"
            class="btn btn-outline-primary mb-3"
            @click="addCapitulo"
          >
            <i class="fa fa-plus"></i> Agregar Capítulo
          </button>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="d-flex justify-content-between">
        <button
          type="button"
          class="btn btn-secondary"
          @click="goBack"
        >
          Cancelar
        </button>
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Guardando...' : 'Crear E-book' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "EbookForm",
  props: {
    categories: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      form: {
        titulo: '',
        descripcion: '',
        autor: '',
        categoria: '',
        paginas: '',
        portada: null,
        archivo_pdf: null,
        capitulos: [
          {
            titulo: '',
            contenido: '',
            paginas: ''
          }
        ]
      },
      errors: {},
      loading: false
    };
  },
  methods: {
    handlePortadaUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          this.errors.portada = 'El archivo no puede superar los 2MB';
          event.target.value = '';
          return;
        }
        this.form.portada = file;
        if (this.errors.portada) delete this.errors.portada;
      }
    },
    
    handlePdfUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 10 * 1024 * 1024) {
          this.errors.archivo_pdf = 'El archivo no puede superar los 10MB';
          event.target.value = '';
          return;
        }
        this.form.archivo_pdf = file;
        if (this.errors.archivo_pdf) delete this.errors.archivo_pdf;
      }
    },
    
    addCapitulo() {
      this.form.capitulos.push({
        titulo: '',
        contenido: '',
        paginas: ''
      });
    },
    
    removeCapitulo(index) {
      this.form.capitulos.splice(index, 1);
    },
    
    async submitForm() {
      this.loading = true;
      this.errors = {};
      
      try {
        const formData = new FormData();
        
        formData.append('titulo', this.form.titulo);
        formData.append('descripcion', this.form.descripcion);
        formData.append('autor', this.form.autor);
        formData.append('categoria', this.form.categoria);
        formData.append('paginas', this.form.paginas);
        
        if (this.form.portada) formData.append('portada', this.form.portada);
        if (this.form.archivo_pdf) formData.append('archivo_pdf', this.form.archivo_pdf);
        
        this.form.capitulos.forEach((capitulo, index) => {
          formData.append(`capitulos[${index}][titulo]`, capitulo.titulo);
          formData.append(`capitulos[${index}][contenido]`, capitulo.contenido);
          formData.append(`capitulos[${index}][paginas]`, capitulo.paginas);
        });
        
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', token);
        
        const response = await fetch('/marketing/ebook/store', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.showSuccessMessage(data.message);
          setTimeout(() => {
            window.location.href = '/marketing/tools';
          }, 1500);
        } else {
          if (data.errors) this.errors = data.errors;
          this.showErrorMessage(data.message || 'Error al crear el e-book');
        }
      } catch (error) {
        console.error('Error:', error);
        this.showErrorMessage('Error de conexión. Intente nuevamente.');
      } finally {
        this.loading = false;
      }
    },
    
    showSuccessMessage(message) {
      alert(message);
    },
    showErrorMessage(message) {
      alert(message);
    },
    goBack() {
      window.location.href = '/marketing/tools';
    }
  }
};
</script>

<style scoped>
.ebook-form {
  max-width: 100%;
}
.card-header {
  background-color: #f8f9fa;
}
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
.is-invalid {
  border-color: #dc3545;
}
.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}
</style>
