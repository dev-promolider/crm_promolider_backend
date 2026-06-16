<template>
  <div class="container-fluid py-1">
    <div class="card course-form-card">
      <div class="card-body" v-loading="loading" element-loading-text="Cargando..."
        element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
        <form @submit.prevent="submit" id="upload" enctype="multipart/form-data" method="post" class="needs-validation">
          <div class="form-section mb-3">
            <h3 class="section-title">Información General</h3>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Título del Minicurso</label>
                  <input type="text" v-model="form.title" class="form-control" placeholder="Título del minicurso"
                    required />
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Categoría</label>
                  <select v-model="form.id_categories" id="category-select" class="form-control w-100" required>
                    <option :value="null" disabled>Seleccione Categoría</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Descripción</label>
                  <textarea class="form-control" v-model="form.description" rows="2"
                    placeholder="Descripción del minicurso" required></textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Objetivos de Aprendizaje</label>
                  <textarea class="form-control" v-model="form.objective" rows="2" placeholder="Objetivos del minicurso"
                    required></textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Fecha</label>
                  <input type="date" class="form-control" v-model="form.date" required />
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Hora</label>
                  <input type="time" class="form-control" v-model="form.hour" required />
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Nivel de dificultad</label>
                  <select v-model="form.difficulty" class="form-control w-100" required>
                    <option :value="null" disabled>Seleccione nivel de dificultad</option>
                    <option v-for="i in 5" :key="i" :value="i">
                      Nivel {{ i }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Enlace al minicurso</label>
                  <input type="text" class="form-control" v-model="form.courseLink" placeholder="Link del minicurso"
                    required />
                </div>
              </div>
            </div>

            <div class="row g-3 mt-3">
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Subir Imagen Destacada</label>
                  <input type="file" @change="handleFileUpload($event, 'images')" class="form-control"
                    accept=".jpg,.jpeg,.png,.webp" multiple>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Subir Video Introductorio (mp4)</label>
                  <input type="file" @change="handleFileUpload($event, 'video')" class="form-control" accept=".mp4">
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Subir Presentación (pdf)</label>
                  <input type="file" @change="handleFileUpload($event, 'presentation')" class="form-control" accept=".pdf">
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label class="form-label">Subir Otros Documentos</label>
                  <input type="file" @change="handleFileUpload($event, 'documents')" class="form-control"
                    accept=".doc,.pdf,.txt" multiple>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-success btn-lg px-4" :disabled="isSubmitting">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    categoriesList: Array
  },
  data() {
    return {
      form: {
        title: '',
        id_categories: null,
        description: '',
        objective: '',
        date: null,
        hour: null,
        difficulty: null,
        courseLink: '',
      },
      // ✅ CAMBIO: Objeto para almacenar los archivos seleccionados
      files: {
        images: [],
        video: null,
        presentation: null,
        documents: []
      },
      isSubmitting: false,
      loading: false,
    };
  },
  mounted() {
    const categorySelect = document.getElementById('category-select');
    if (categorySelect && this.categoriesList && this.categoriesList.length > 0) {
      this.categoriesList.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categorySelect.appendChild(option);
      });
    }
  },
  methods: {
    // ✅ CAMBIO: Un solo método para manejar todos los uploads
    handleFileUpload(event, fileType) {
      if (event.target.multiple) {
        this.files[fileType] = event.target.files;
      } else {
        this.files[fileType] = event.target.files[0];
      }
    },

    submit() {
      this.isSubmitting = true;
      const formData = new FormData();

      // 1. Agrega los datos del formulario de texto
      Object.keys(this.form).forEach(key => {
        // Asegurarse de no enviar valores nulos si no es necesario
        if (this.form[key] !== null) {
          formData.append(key, this.form[key]);
        }
      });

      // 2. Agrega los archivos
      // Imágenes (múltiples)
      if (this.files.images && this.files.images.length > 0) {
        for (let i = 0; i < this.files.images.length; i++) {
          formData.append('images[]', this.files.images[i]);
        }
      }
      // Video (único)
      if (this.files.video) {
        formData.append('video', this.files.video);
      }
      // Presentación (único)
      if (this.files.presentation) {
        formData.append('presentation', this.files.presentation);
      }
      // Documentos (múltiples)
      if (this.files.documents && this.files.documents.length > 0) {
        for (let i = 0; i < this.files.documents.length; i++) {
          formData.append('documents[]', this.files.documents[i]);
        }
      }

      // ✅ CAMBIO: Imprimir el contenido de FormData en la consola antes de enviar
      console.log("------ Contenido del FormData que se enviará ------");
      for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
      }
      console.log("-------------------------------------------------");

      // 3. Enviar los datos
      axios.post('/api/create-course', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(response => {
        this.$message.success('Curso creado correctamente.');
        this.resetForm();
      })
      .catch(error => {
        this.$message.error('Error al crear el curso.');
        console.error("Error de Axios:", error.response || error);
      })
      .finally(() => {
        this.isSubmitting = false;
      });
    },

    resetForm() {
      // Limpia el formulario de texto
      this.form = {
        title: '',
        id_categories: null,
        description: '',
        objective: '',
        date: null,
        hour: null,
        difficulty: null,
        courseLink: '',
      };
      // Limpia los archivos
      this.files = {
        images: [],
        video: null,
        presentation: null,
        documents: []
      };
      // Limpia los inputs de archivo (opcional, pero buena práctica)
      document.querySelectorAll('input[type="file"]').forEach(input => {
        input.value = '';
      });
      // Resetea el select de categoría
      const categorySelect = document.getElementById('category-select');
      if (categorySelect) {
         categorySelect.selectedIndex = 0;
      }
    },
  },
};
</script>

<style scoped>
/* Los estilos generales no cambian */
.form-section {
  padding-bottom: 1rem;
}
.btn-success {
  background-color: #4caf50;
  border-color: #4caf50;
}
.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.form-group {
    margin-bottom: 1rem;
}
</style>