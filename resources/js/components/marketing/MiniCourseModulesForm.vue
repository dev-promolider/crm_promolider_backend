<template>
  <div class="mini-course-modules-form">
    <div :class="isModal ? '' : 'card'">
        <div v-if="!isModal" class="card-header">
            <h5 class="mb-0">🎯 Módulos del Curso</h5>
        </div>
      <div :class="isModal ? '' : 'card-body'">
        <form @submit.prevent="submitForm" enctype="multipart/form-data">
          <!-- Mostrar errores generales -->
          <div v-if="errors.modulos" class="alert alert-danger">
            <ul class="mb-0">
              <li v-for="(error, key) in errors.modulos" :key="key">
                {{ error[0] }}
              </li>
            </ul>
          </div>
          
          <!-- Lista de módulos -->
          <div
            v-for="(modulo, index) in form.modulos"
            :key="index"
            class="card mb-3 border"
          >
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
              <h6 class="mb-0">📖 Módulo {{ index + 1 }}</h6>
              <button
                type="button"
                class="btn btn-sm btn-danger"
                @click="removeModulo(index)"
                v-if="form.modulos.length > 1"
                title="Eliminar módulo"
              >
                🗑️
              </button>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="form-group mb-3">
                    <label class="form-label">Título del Módulo *</label>
                    <input
                      type="text"
                      v-model="modulo.titulo"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`modulos.${index}.titulo`] }"
                      placeholder="Título del módulo"
                      required
                    />
                    <div v-if="errors[`modulos.${index}.titulo`]" class="invalid-feedback">
                      {{ errors[`modulos.${index}.titulo`][0] }}
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label class="form-label">Duración (min) *</label>
                    <input
                      type="number"
                      v-model="modulo.duracion"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`modulos.${index}.duracion`] }"
                      placeholder="15"
                      min="1"
                      required
                    />
                    <div v-if="errors[`modulos.${index}.duracion`]" class="invalid-feedback">
                      {{ errors[`modulos.${index}.duracion`][0] }}
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group mb-3">
                    <label class="form-label">Contenido del Módulo *</label>
                    <textarea
                      v-model="modulo.contenido"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`modulos.${index}.contenido`] }"
                      rows="3"
                      placeholder="Describe el contenido de este módulo"
                      required
                    ></textarea>
                    <div v-if="errors[`modulos.${index}.contenido`]" class="invalid-feedback">
                      {{ errors[`modulos.${index}.contenido`][0] }}
                    </div>
                  </div>
                </div>
                
                <!-- Documentos del módulo -->
                <div class="col-12">
                  <div class="form-group mb-3">
                    <label class="form-label">📄 Documentos del Módulo</label>
                    <input
                      type="file"
                      @change="handleModuleDocumentUpload($event, index)"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`modulos.${index}.documentos`] }"
                      accept=".doc,.docx,.pdf,.xls,.xlsx,.txt"
                      multiple
                      :ref="`documentInput-${index}`"
                    />
                    <div v-if="errors[`modulos.${index}.documentos`]" class="invalid-feedback">
                      {{ errors[`modulos.${index}.documentos`][0] }}
                    </div>
                    <small class="form-text text-muted">
                      Formatos permitidos: DOC, DOCX, PDF, XLS, XLSX, TXT. Tamaño máximo por archivo: 5MB
                    </small>
                    
                    <!-- Mostrar archivos seleccionados -->
                    <div v-if="modulo.documentos && modulo.documentos.length > 0" class="mt-2">
                      <small class="text-muted">Archivos seleccionados ({{ modulo.documentos.length }}):</small>
                      <ul class="list-unstyled">
                        <li v-for="(doc, docIndex) in modulo.documentos" :key="docIndex" class="d-flex align-items-center justify-content-between bg-light p-2 mt-1 rounded">
                          <span class="text-truncate flex-grow-1 me-2">
                            📄 {{ doc.name }} <small class="text-muted">({{ formatFileSize(doc.size) }})</small>
                          </span>
                          <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            @click="removeModuleDocument(index, docIndex)"
                            style="min-width: 30px;"
                          >
                            ✕
                          </button>
                        </li>
                      </ul>
                      
                      <button
                        type="button"
                        class="btn btn-sm btn-secondary mt-2"
                        @click="clearAllModuleDocuments(index)"
                      >
                        🗑️ Limpiar todos
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Videos del módulo -->
                <div class="col-12">
                  <div class="form-group mb-3">
                    <label class="form-label">🎬 Videos del Módulo</label>
                    
                    <div v-for="(video, videoIndex) in modulo.videos" :key="videoIndex" class="border rounded p-3 mb-2 bg-light">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">🎥 Video {{ videoIndex + 1 }}</h6>
                        <button
                          type="button"
                          class="btn btn-sm btn-danger"
                          @click="removeModuleVideo(index, videoIndex)"
                          v-if="modulo.videos.length > 1"
                        >
                          🗑️ Eliminar
                        </button>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group mb-2">
                            <label class="form-label">Archivo de Video *</label>
                            <input
                              type="file"
                              accept=".mp4,.mov,.avi,.webm"
                              @change="handleModuleVideoUpload($event, index, videoIndex)"
                              class="form-control form-control-sm"
                              :class="{ 'is-invalid': errors[`modulos.${index}.videos.${videoIndex}`] }"
                            />
                            <div v-if="errors[`modulos.${index}.videos.${videoIndex}`]" class="invalid-feedback">
                              {{ errors[`modulos.${index}.videos.${videoIndex}`][0] }}
                            </div>
                            <small class="form-text text-muted">
                              Formatos: MP4, MOV, AVI, WEBM. Máximo: 500MB
                            </small>
                          </div>
                        </div>
                        
                        <div class="col-md-6">
                          <div class="form-group mb-2">
                            <label class="form-label">Título del Video *</label>
                            <input
                              type="text"
                              v-model="video.title"
                              class="form-control form-control-sm"
                              :class="{ 'is-invalid': errors[`modulos.${index}.video_titles.${videoIndex}`] }"
                              placeholder="Título del video"
                            />
                            <div v-if="errors[`modulos.${index}.video_titles.${videoIndex}`]" class="invalid-feedback">
                              {{ errors[`modulos.${index}.video_titles.${videoIndex}`][0] }}
                            </div>
                          </div>
                        </div>
                        
                        <div class="col-md-6">
                          <div class="form-group mb-2">
                            <label class="form-label">Descripción</label>
                            <textarea
                              v-model="video.description"
                              class="form-control form-control-sm"
                              rows="2"
                              placeholder="Descripción del video"
                            ></textarea>
                          </div>
                        </div>
                        
                        <div class="col-md-3">
                          <div class="form-group mb-2">
                            <label class="form-label">Duración (min) *</label>
                            <input
                              type="number"
                              v-model="video.duration"
                              class="form-control form-control-sm"
                              min="1"
                              placeholder="30"
                            />
                          </div>
                        </div>
                        
                        <div class="col-md-3">
                          <div class="form-group mb-2">
                            <label class="form-label">Orden *</label>
                            <input
                              type="number"
                              v-model="video.order"
                              class="form-control form-control-sm"
                              min="1"
                              :placeholder="videoIndex + 1"
                            />
                          </div>
                        </div>
                      </div>
                      
                      <!-- Vista previa del video -->
                      <div v-if="video.file" class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-success mb-0">
                            🎬 Archivo seleccionado: {{ video.file.name }}
                          </small>
                          <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            @click="removeModuleVideoFile(index, videoIndex)"
                            title="Eliminar video"
                          >
                            ✕
                          </button>
                        </div>
                      
                        <video
                          v-if="video.previewUrl"
                          :src="video.previewUrl"
                          controls
                          class="w-100 rounded border"
                          style="max-height: 300px;"
                        ></video>
                      </div>
                    </div>
                    
                    <!-- Botón para agregar video al módulo -->
                    <button
                      type="button"
                      class="btn btn-sm btn-primary"
                      @click="addModuleVideo(index)"
                    >
                      ➕ Agregar Video al Módulo
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Botón para agregar módulo -->
          <button
            type="button"
            class="btn btn-primary mb-3"
            @click="addModulo"
          >
            ➕ Agregar Módulo
          </button>

          <!-- Mostrar mensaje de éxito o error -->
          <div v-if="message" class="alert" :class="messageType === 'success' ? 'alert-success' : 'alert-danger'">
            {{ message }}
          </div>

          <!-- Botones de acción -->
          <div class="d-flex justify-content-between">
              <button
                  v-if="!isModal"
                  type="button"
                  class="btn btn-secondary"
                  @click="goBack"
                  :disabled="loading"
              >
                  ← Volver a Herramientas
              </button>
              <button
                  v-if="isModal"
                  type="button"
                  class="btn btn-secondary"
                  @click="cancelModal"
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
                  {{ loading ? 'Guardando...' : (isModal ? 'Incorporar Clase' : 'Guardar Módulos') }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MiniCourseModulesForm",
  props: {
      miniCourse: {
          type: Object,
          required: true
      },
      isModal: {
          type: Boolean,
          default: false
      }
  },
  data() {
    return {
      form: {
        modulos: [
          {
            titulo: '',
            contenido: '',
            duracion: '',
            documentos: [],
            videos: [
              {
                file: null,
                title: '',
                description: '',
                duration: '',
                order: 1
              }
            ]
          }
        ]
      },
      errors: {},
      loading: false,
      message: '',
      messageType: ''
    };
  },
  methods: {
    // Manejar documentos del módulo
    handleModuleDocumentUpload(event, moduleIndex) {
      const newFiles = Array.from(event.target.files);
      
      // Si ya hay documentos, agregar los nuevos a los existentes
      if (this.form.modulos[moduleIndex].documentos && this.form.modulos[moduleIndex].documentos.length > 0) {
        // Verificar archivos duplicados por nombre
        const existingNames = this.form.modulos[moduleIndex].documentos.map(f => f.name);
        const uniqueNewFiles = newFiles.filter(file => !existingNames.includes(file.name));
        
        if (uniqueNewFiles.length < newFiles.length) {
          alert(`Se omitieron ${newFiles.length - uniqueNewFiles.length} archivo(s) duplicado(s)`);
        }
        
        // Agregar solo archivos únicos
        this.form.modulos[moduleIndex].documentos = [
          ...this.form.modulos[moduleIndex].documentos,
          ...uniqueNewFiles
        ];
      } else {
        // Si no hay documentos previos, usar los nuevos
        this.form.modulos[moduleIndex].documentos = newFiles;
      }
      
      console.log(`Total documentos del módulo ${moduleIndex}:`, this.form.modulos[moduleIndex].documentos.map(f => f.name));
      
      // Limpiar el input para permitir seleccionar los mismos archivos nuevamente si es necesario
      event.target.value = '';
    },

    // Remover documento específico del módulo
    removeModuleDocument(moduleIndex, docIndex) {
      this.form.modulos[moduleIndex].documentos.splice(docIndex, 1);
    },

    // Limpiar todos los documentos del módulo
    clearAllModuleDocuments(moduleIndex) {
      this.form.modulos[moduleIndex].documentos = [];
      
      // Limpiar también el input file
      const documentInput = this.$refs[`documentInput-${moduleIndex}`];
      if (documentInput && documentInput[0]) {
        documentInput[0].value = '';
      }
    },

    // Formatear tamaño de archivo
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Manejar videos del módulo
    handleModuleVideoUpload(event, moduleIndex, videoIndex) {
      const file = event.target.files[0];
      if (file) {
        this.form.modulos[moduleIndex].videos[videoIndex].file = file;
      
        // Crear una URL para previsualización
        const url = URL.createObjectURL(file);
        this.form.modulos[moduleIndex].videos[videoIndex].previewUrl = url;
      }
    },

    // Agregar video al módulo
    addModuleVideo(moduleIndex) {
      const nextOrder = this.form.modulos[moduleIndex].videos.length + 1;
      this.form.modulos[moduleIndex].videos.push({
        file: null,
        title: '',
        description: '',
        duration: '',
        order: nextOrder,
        previewUrl: null
      });
    },

    // Remover video del módulo
    removeModuleVideo(moduleIndex, videoIndex) {
      // Liberar la URL del objeto para evitar memory leaks
      const video = this.form.modulos[moduleIndex].videos[videoIndex];
      if (video.previewUrl) {
        URL.revokeObjectURL(video.previewUrl);
      }
      
      this.form.modulos[moduleIndex].videos.splice(videoIndex, 1);
    },

    // Eliminar archivo de video específico (sin eliminar el video completo)
    removeModuleVideoFile(moduleIndex, videoIndex) {
      const video = this.form.modulos[moduleIndex].videos[videoIndex];
      
      // Liberar la URL del objeto para evitar memory leaks
      if (video.previewUrl) {
        URL.revokeObjectURL(video.previewUrl);
      }
      
      // Limpiar propiedades del video
      video.file = null;
      video.previewUrl = null;
    },
    
    addModulo() {
      this.form.modulos.push({
        titulo: '',
        contenido: '',
        duracion: '',
        documentos: [],
        videos: [
          {
            file: null,
            title: '',
            description: '',
            duration: '',
            order: 1,
            previewUrl: null
          }
        ]
      });
    },
    
    removeModulo(index) {
      // Liberar URLs de videos antes de eliminar el módulo
      const modulo = this.form.modulos[index];
      if (modulo.videos) {
        modulo.videos.forEach(video => {
          if (video.previewUrl) {
            URL.revokeObjectURL(video.previewUrl);
          }
        });
      }
      
      this.form.modulos.splice(index, 1);
    },
  
    cancelModal() {
        this.$emit('cancel');
    },

    async submitForm() {
      this.loading = true;
      this.errors = {};
      this.message = '';

      try {
        const formData = new FormData();

        // Agregar el ID del mini curso
        formData.append('mini_course_id', this.miniCourse.id);

        // Agregar módulos con sus documentos y videos
        this.form.modulos.forEach((modulo, index) => {
          formData.append(`modulos[${index}][titulo]`, modulo.titulo);
          formData.append(`modulos[${index}][contenido]`, modulo.contenido);
          formData.append(`modulos[${index}][duracion]`, modulo.duracion);
          
          // Agregar documentos del módulo
          if (modulo.documentos && modulo.documentos.length > 0) {
            modulo.documentos.forEach((documento, docIndex) => {
              formData.append(`modulos[${index}][documentos][${docIndex}]`, documento);
            });
          }

          // Agregar videos del módulo
          if (modulo.videos && modulo.videos.length > 0) {
            modulo.videos.forEach((video, videoIndex) => {
              if (video.file) {
                formData.append(`modulos[${index}][videos][${videoIndex}]`, video.file);
                formData.append(`modulos[${index}][video_titles][${videoIndex}]`, video.title);
                formData.append(`modulos[${index}][video_descriptions][${videoIndex}]`, video.description || '');
                formData.append(`modulos[${index}][video_durations][${videoIndex}]`, video.duration);
                formData.append(`modulos[${index}][video_orders][${videoIndex}]`, video.order);
              }
            });
          }
        });

        // Agregar token CSRF
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', token);

        // DEBUG: Ver qué se está enviando
        console.log('FormData contents:');
        for (let pair of formData.entries()) {
          if (pair[1] instanceof File) {
            console.log(pair[0] + ': [File] ' + pair[1].name + ' (' + pair[1].size + ' bytes)');
          } else {
            console.log(pair[0] + ': ' + pair[1]);
          }
        }

        const response = await fetch(`/marketing/mini-course/${this.miniCourse.id}/modules/store`, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          }
        });

        const data = await response.json();

        if (response.ok) {
          this.message = data.message || 'Módulos guardados exitosamente';
          this.messageType = 'success';

          if (this.isModal) {
              // Emitir evento de éxito para el modal
              this.$emit('success', data);
          } else {
              // Redirigir después de 2 segundos (comportamiento original)
              setTimeout(() => {
                  window.location.href = '/marketing/tools';
              }, 2000);
          }
        } else {
          if (data.errors) {
            this.errors = data.errors;
          }
          this.message = data.message || 'Error al guardar los módulos';
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
  },

  // Limpiar URLs de objetos cuando el componente se destruye
  beforeDestroy() {
    this.form.modulos.forEach(modulo => {
      if (modulo.videos) {
        modulo.videos.forEach(video => {
          if (video.previewUrl) {
            URL.revokeObjectURL(video.previewUrl);
          }
        });
      }
    });
  }
};
</script>

<style scoped>
.mini-course-modules-form {
  max-width: 100%;
}

.card-header {
  background-color: #f8f9fa;
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

.list-unstyled li {
  font-size: 0.875rem;
}

.text-truncate {
  max-width: 200px;
}

.bg-light { 
  background-color: #f8f9fa !important;
}

.btn-xs {
  padding: 0.125rem 0.25rem;
  font-size: 0.75rem;
}

.btn-sm {
  font-size: 0.875rem;
  line-height: 1.2;
}

.text-truncate {
  max-width: none;
}

.flex-grow-1 {
  flex-grow: 1;
}

.form-control-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.border {
  border: 1px solid #dee2e6 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}

.badge {
  font-size: 0.8em;
  padding: 0.25em 0.6em;
}

.modal {
    background-color: rgba(0,0,0,0.5);
}

.modal-xl {
    max-width: 90%;
}
</style>