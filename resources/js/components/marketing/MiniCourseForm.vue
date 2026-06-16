<template>
  <div class="mini-course-basic-form">
    <div 
      class="mini-course-basic-form"
      v-loading="loading"
      element-loading-text="Guardando..."
      element-loading-spinner="el-icon-loading"
      element-loading-background="rgba(0, 0, 0, 0.6)"
    >
      <form @submit.prevent="submitForm" enctype="multipart/form-data">
        <div class="row">
          <!-- Información básica -->
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label for="titulo" class="form-label">Título del Mini Curso *</label>
              <input
                type="text"
                id="titulo"
                v-model="form.titulo"
                class="form-control"
                :class="{ 'is-invalid': errors.titulo }"
                placeholder="Ingrese el título"
                required
              />
              <div v-if="errors.titulo" class="invalid-feedback">
                {{ errors.titulo[0] }}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label for="duracion" class="form-label">Duración estimada (minutos) *</label>
              <input
                type="number"
                id="duracion"
                v-model="form.duracion"
                class="form-control"
                :class="{ 'is-invalid': errors.duracion }"
                placeholder="60"
                min="1"
                required
              />
              <div v-if="errors.duracion" class="invalid-feedback">
                {{ errors.duracion[0] }}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label for="nivel" class="form-label">Nivel *</label>
              <select
                id="nivel"
                v-model="form.nivel"
                class="form-select"
                :class="{ 'is-invalid': errors.nivel }"
                required
              >
                <option value="">Seleccione un nivel</option>
                <option value="principiante">Principiante</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
              </select>
              <div v-if="errors.nivel" class="invalid-feedback">
                {{ errors.nivel[0] }}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label for="categoria" class="form-label">Categoría *</label>
              <select
                id="categoria"
                v-model="form.categoria"
                class="form-select"
                :class="{ 'is-invalid': errors.categoria }"
                required
              >
                <option value="">Seleccione una categoría</option>
                <option 
                  v-for="categoria in categorias" 
                  :key="categoria.id" 
                  :value="categoria.id"
                >
                  {{ categoria.name }}
                </option>
              </select>
              <div v-if="errors.categoria" class="invalid-feedback">
                {{ errors.categoria[0] }}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label for="imagen" class="form-label">Imagen del curso</label>
              <input
                type="file"
                id="imagen"
                @change="handleFileUpload"
                class="form-control"
                :class="{ 'is-invalid': errors.imagen }"
                accept="image/jpeg,image/png,image/jpg,image/webp"
              />
              <div v-if="errors.imagen" class="invalid-feedback">
                {{ errors.imagen[0] }}
              </div>
              <small class="form-text text-muted">
                Formatos permitidos: JPEG, PNG, JPG, WEBP. Tamaño máximo: 2MB
              </small>
            </div>
          </div>

          <!-- Vista previa de imagen -->
          <div v-if="form.imagePreview" class="col-12 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <p class="text-muted mb-0">Previsualización:</p>
              <button
                type="button"
                class="btn btn-sm btn-danger"
                @click="removeMainImage"
                title="Eliminar imagen"
              >
                ✕ Eliminar
              </button>
            </div>
            <img 
              :src="form.imagePreview" 
              alt="Vista previa de la imagen" 
              class="img-fluid rounded border" 
              style="max-height: 300px;" 
            />
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
                placeholder="Describe el contenido del mini curso"
                required
              ></textarea>
              <div v-if="errors.descripcion" class="invalid-feedback">
                {{ errors.descripcion[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- Mostrar mensaje de éxito o error -->
        <div v-if="message" class="alert" :class="messageType === 'success' ? 'alert-success' : 'alert-danger'">
          {{ message }}
        </div>

        <!-- Botones de acción -->
        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-secondary"
            @click="goBack"
            :disabled="loading"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="btn btn-success"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Guardando...' : 'Crear Mini Curso' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "MiniCourseBasicForm",
  props: {
    categorias: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      form: {
        titulo: '',
        descripcion: '',
        duracion: '',
        nivel: '',
        categoria: '',
        imagen: null,
        imagePreview: null
      },
      errors: {},
      loading: false,
      message: '',
      messageType: ''
    };
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      this.form.imagen = file;

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
          this.form.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.form.imagePreview = null;
      }
    },
    
    removeMainImage() {
      this.form.imagen = null;
      this.form.imagePreview = null;
      
      const imageInput = document.getElementById('imagen');
      if (imageInput) {
        imageInput.value = '';
      }
    },

    async submitForm() {
      this.loading = true;
      this.errors = {};
      this.message = '';

      try {
        const formData = new FormData();

        // Agregar campos básicos
        formData.append('titulo', this.form.titulo);
        formData.append('descripcion', this.form.descripcion);
        formData.append('duracion', this.form.duracion);
        formData.append('nivel', this.form.nivel);
        formData.append('categoria', this.form.categoria);

        // Agregar imagen si existe
        if (this.form.imagen) {
          formData.append('imagen', this.form.imagen);
        }

        // Agregar token CSRF
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', token);

        const response = await fetch('/marketing/mini-course/store', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          }
        });

        const data = await response.json();

        if (response.ok) {          
          this.$message.success('Mini curso creado exitosamente. Ahora puedes agregar módulos desde la lista de herramientas.');

          // Redirigir después de 3 segundos para que el usuario pueda leer el mensaje
          setTimeout(() => {
            window.location.href = '/marketing/tools';
          }, 3000);
        } else {
          if (data.errors) {
            this.errors = data.errors;
          }
          this.message = data.message || 'Error al crear el mini curso';
          this.messageType = 'error';
        }
      } catch (error) {
        console.error('Error:', error);
        this.message = 'Error de conexión. Por favor intenta nuevamente.';
        this.messageType = 'error';
      } finally {
        this.loading = false;
      }
    },
    
    goBack() {
      window.location.href = '/marketing/tools';
    }
  }
};
</script>

<style scoped>
.mini-course-basic-form {
  max-width: 100%;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.form-text {
  font-size: 0.875rem;
}

.alert ul {
  padding-left: 1.5rem;
}
</style>