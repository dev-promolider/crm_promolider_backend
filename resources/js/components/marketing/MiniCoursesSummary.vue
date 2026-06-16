<template>
  <div>
      <div class="card">
          <div class="card-header">
              <h3 class="d-inline">
                  Lista de Módulos <span> ({{ modules ? modules.length : 0 }}) </span>
              </h3>
              <button type="button" class="btn btn-success" @click="openPreviewModal()">
                  Previsualizar
              </button>
              <button type="button" class="btn btn-link" @click="showInputModule">
                  (+) Agregar Módulo
              </button>
          </div>
          <div class="card-body">
              <ol class="list-group">
                  <div v-if="!modules || modules.length === 0" class="text-center mt-3 mb-3">
                      <h4 class="d-inline">
                          <span> {{ message }} </span>
                      </h4>
                  </div>
                  <li class="list-group-item" v-if="showDialogAddModule == true">
                      <h3 class="d-inline">Módulo {{ modules ? modules.length + 1 : 1 }}:</h3>
                      <input placeholder="Ingrese el título del módulo" @keyup.enter="storeModule" type="text"
                          class="form-control d-inline input-module-title" aria-label="Default"
                          aria-describedby="inputGroup-sizing-default" v-model="title" />
                      <button type="button" class="btn btn-primary" @click="storeModule">Guardar</button>
                  </li>
                  <div v-if="modules && modules.length > 0">
                      <li class="list-group-item" v-for="(module, index) in modules" :key="module.module_id">
                          <div class="d-flex justify-content-between align-items-center">
                              <div class="flex-grow-1">
                                  <h4 class="mb-1">
                                      <strong>{{ index + 1 }}. {{ module.module_title }}</strong>
                                      <span class="badge bg-info ms-2">{{ module.module_duration }} min</span>
                                  </h4>
                                  <div class="btn-group" role="group">
                                      <button type="button" class="btn btn-sm btn-outline-primary" 
                                              @click="openAddClassModal(module)">
                                          Agregar Clase
                                      </button>
                                      <button type="button" class="btn btn-sm btn-outline-secondary" 
                                              @click="openEditModuleModal(module)">
                                          Editar
                                      </button>
                                      <button type="button" class="btn btn-sm btn-outline-danger" @click="showDeleteModal(module)">
                                          Eliminar
                                      </button>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="mt-2">
                              <p class="mb-2">{{ module.module_content }}</p>
                              
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Clases ({{ module.videos_count || 0 }}):</strong>
                                        <ul v-if="module.classes && module.classes.length" class="list-unstyled ms-3">
                                            <li v-for="clase in module.classes" :key="clase.video_id" class="mb-1">
                                                <i class="fas fa-play-circle text-primary me-1"></i>
                                                <a :href="clase.video_url" target="_blank">{{ clase.title }}</a>
                                                <small class="text-muted">({{ clase.duration }} min)</small>
                                            </li>
                                        </ul>
                                        <span v-else class="text-muted ms-3">No hay clases</span>
                                    </div>

                                    <div class="col-md-6">
                                        <strong>Documentos ({{ module.documents_count || 0 }}):</strong>
                                        <ul v-if="module.classes && module.classes.length" class="list-unstyled ms-3">
                                            <li v-for="clase in module.classes" v-if="clase.documents && clase.documents.length" :key="'class-' + clase.video_id">
                                                <div v-for="doc in clase.documents" :key="doc.document_id" class="mb-1">
                                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                                    <a :href="doc.document_url" target="_blank">Documento {{ doc.document_id }}</a>
                                                    <small class="text-muted">(Clase: {{ clase.title }})</small>
                                                </div>
                                            </li>
                                        </ul>
                                        <span v-else class="text-muted ms-3">No hay documentos</span>
                                    </div>
                                </div>
                          </div>
                      </li>
                  </div>
              </ol>
          </div>
      </div>
      
      <!-- Modal para previsualizar el mini curso -->
      <div v-if="showPreviewModal" class="modal fade show" tabindex="-1" style="display: block;">
          <div class="modal-dialog modal-xl">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title">Previsualización del Mini Curso</h5>
                      <button type="button" class="close" @click="closePreviewModal" aria-label="Cerrar">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <div class="modal-body p-0" style="height: 80vh; overflow: auto;">
                      <div v-if="loadingPreview" class="d-flex justify-content-center align-items-center h-100">
                          <div class="text-center">
                              <div class="spinner-border text-primary" role="status">
                                  <span class="sr-only">Cargando previsualización...</span>
                              </div>
                              <p class="mt-2">Cargando previsualización...</p>
                          </div>
                      </div>
                      <div v-else-if="previewError" class="d-flex justify-content-center align-items-center h-100">
                          <div class="text-center text-danger">
                              <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                              <p>{{ previewError }}</p>
                          </div>
                      </div>
                      <div v-else-if="previewContent" v-html="previewContent" class="preview-content">
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" @click="closePreviewModal">Cerrar</button>
                      <button type="button" class="btn btn-primary" @click="openInNewTab">
                          <i class="fas fa-external-link-alt me-1"></i>
                          Abrir en nueva pestaña
                      </button>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Modal para eliminar módulo -->
      <div v-if="showDeleteModalFlag" class="modal fade show" tabindex="-1" style="display: block;">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title">Borrar módulo <u> {{ moduleToDelete.module_title }} </u></h5>
                        <button type="button" class="close" @click="hideDeleteModal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                  </div>
                  <div class="modal-body">
                      ¿Seguro que desea eliminar el módulo?
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" @click="hideDeleteModal">Cancelar</button>
                      <button type="button" class="btn btn-danger" @click="deleteModule">Eliminar</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Componente Modal para Editar Módulo -->
      <EditModuleModal
          v-if="showEditModuleModal"
          :mini-course-id="miniCourseId"
          :current-module="currentModule"
          :edit-module-data="editModuleData"
          :updating="updating"
          @close="closeEditModuleModal"
          @update="handleModuleUpdate"
          @show-success="showSuccess"
          @show-error="showError"
      />

      <!-- Componente Modal para Agregar Clase -->
      <AddClassModal
          v-if="showAddClassModal"
          :mini-course-id="miniCourseId"
          :current-module="currentModule"
          :class-data="classData"
          :adding-classes="addingClasses"
          @close="closeAddClassModal"
          @add-class="handleAddClass"
          @show-success="showSuccess"
          @show-error="showError"
      />

      <!-- Loading overlay -->
      <div v-if="loading" class="text-center mt-3">
          <div class="spinner-border" role="status">
              <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-2">Cargando módulos...</p>
      </div>
      
      <!-- Error message -->
      <div v-if="error" class="alert alert-danger mt-3" role="alert">
          {{ error }}
      </div>
  </div>
</template>

<script>
// Importar los componentes modales
import EditModuleModal from './EditModuleModal.vue';
import AddClassModal from './AddClassModal.vue';

export default {
    components: {
        EditModuleModal,
        AddClassModal
    },
    props: {
        miniCourseId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            message: 'Este mini curso no cuenta con módulos',
            showDialogAddModule: false,
            showDeleteModalFlag: false,
            showEditModuleModal: false,
            showAddClassModal: false,
            showPreviewModal: false,
            loadingPreview: false,
            previewContent: '',
            previewError: '',
            title: null,
            loading: true,
            error: null,
            updating: false,
            addingClasses: false,
            miniCourse: null,
            modules: [],
            moduleToDelete: {},
            currentModule: {},
            editModuleData: {
                titulo: '',
                contenido: '',
                existingVideos: [],
                newVideos: [],
                newDocuments: [],
                deleteVideos: [],
                deleteDocuments: [],
                videoFiles: [],
                documentFiles: []
            },
            classData: {
                contenido: '',
                duracion: null,
                titulo: '',
                descripcion: '',
                duracion_clase: 1,
                orden: 1,
                documentos: [],
                videoFile: null,
                documentFiles: []
            }
        };
    },
    mounted() {
        this.fetchModules();
    },
    methods: {
        async fetchModules() {
            try {
                this.loading = true;
                this.error = null;
                
                const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/modules/summary`, {
                    headers: {
                        Accept: 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.miniCourse = data.data;
                    this.modules = data.data.modules;
                } else {
                    this.error = data.message || 'Error al cargar módulos';
                }
            } catch (err) {
                this.error = err.message || 'Error de conexión';
            } finally {
                this.loading = false;
            }
        },

        async openPreviewModal() {
            this.showPreviewModal = true;
            this.loadingPreview = true;
            this.previewContent = '';
            this.previewError = '';
            
            try {
                // Intentar cargar el contenido directamente
                const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/module`, {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.previewContent = await response.text();
                } else {
                    throw new Error('Error al cargar la previsualización');
                }
            } catch (error) {
                console.error('Error loading preview:', error);
                this.previewError = 'No se pudo cargar la previsualización. Intente abrir en nueva pestaña.';
            } finally {
                this.loadingPreview = false;
            }
        },

        closePreviewModal() {
            this.showPreviewModal = false;
            this.previewContent = '';
            this.previewError = '';
            this.loadingPreview = false;
        },

        onPreviewLoad() {
            this.loadingPreview = false;
        },

        openInNewTab() {
            window.open(
                `/marketing/mini-course/${this.miniCourseId}/module`, 
                '_blank'
            );
        },

        previewMiniCourse() {
            // Método legacy mantenido por compatibilidad
            this.openPreviewModal();
        },

        showInputModule() {
            this.showDialogAddModule = !this.showDialogAddModule;
            if (this.showDialogAddModule) {
                this.title = null;
            }
        },

        showDeleteModal(module) {
            this.moduleToDelete = module;
            this.showDeleteModalFlag = true;
        },

        hideDeleteModal() {
            this.showDeleteModalFlag = false;
            this.moduleToDelete = {};
        },

        openEditModuleModal(module) {
            this.currentModule = module;
            
            // Preparar datos para el modal de edición
            let existingVideos = [];
            if (module.classes && Array.isArray(module.classes)) {
                module.classes.forEach(clase => {
                    let classDocuments = [];
                    if (clase.documents && Array.isArray(clase.documents)) {
                        classDocuments = clase.documents.map(doc => ({
                            id: doc.document_id,
                            url: doc.document_url,
                            toDelete: false
                        }));
                    }
                
                    existingVideos.push({
                        id: clase.video_id,
                        title: clase.title,
                        description: clase.description || '',
                        duration: clase.duration,
                        order: clase.order || 1,
                        url: clase.video_url,
                        toDelete: false,
                        modified: false,
                        documents: classDocuments
                    });
                });
            }

            this.editModuleData = {
                titulo: module.module_title,
                contenido: module.module_content,
                existingVideos: existingVideos,
                existingDocuments: [],
                newVideos: [],
                newDocuments: [],
                deleteVideos: [],
                deleteDocuments: [],
                videoFiles: [],
                documentFiles: []
            };

            this.showEditModuleModal = true;
        },

        closeEditModuleModal() {
            this.showEditModuleModal = false;
            this.currentModule = {};
            this.resetEditModuleData();
        },

        openAddClassModal(module) {
            this.currentModule = module;

            let nextOrder = 1;
            if (module.classes && Array.isArray(module.classes)) {
                nextOrder = module.classes.length + 1;
            } else if (module.videos_count) {
                nextOrder = module.videos_count + 1;
            }
        
            this.classData = {
                contenido: module.module_content,
                duracion: module.module_duration,
                titulo: '',
                descripcion: '',
                duracion_clase: 1,
                orden: nextOrder,
                documentos: [],
                videoFile: null,
                documentFiles: []
            };

            this.showAddClassModal = true;
        },
        
        closeAddClassModal() {
            this.showAddClassModal = false;
            this.currentModule = {};
            this.resetClassData();
        },

        resetEditModuleData() {
            this.editModuleData = {
                titulo: '',
                contenido: '',
                existingVideos: [],
                existingDocuments: [],
                newVideos: [],
                newDocuments: [],
                deleteVideos: [],
                deleteDocuments: [],
                videoFiles: [],
                documentFiles: []
            };
        },

        resetClassData() {
            this.classData = {
                contenido: '',
                duracion: null,
                titulo: '',
                descripcion: '',
                duracion_clase: 1,
                orden: 1,
                documentos: [],
                videoFile: null,
                documentFiles: []
            };
        },

        // Manejadores de eventos de los modales
        async handleModuleUpdate(updatedData) {
            this.updating = true;
            try {
                // Lógica de actualización del módulo
                const formData = new FormData();
                
                // Datos básicos
                formData.append('titulo', updatedData.titulo);
                formData.append('contenido', updatedData.contenido);

                // Videos a eliminar
                if (updatedData.deleteVideos.length > 0) {
                    updatedData.deleteVideos.forEach((id, index) => {
                        formData.append(`delete_videos[${index}]`, id);
                    });
                }

                // Documentos a eliminar
                if (updatedData.deleteDocuments.length > 0) {
                    updatedData.deleteDocuments.forEach((id, index) => {
                        formData.append(`delete_documents[${index}]`, id);
                    });
                }

                // Videos existentes modificados con sus documentos
                const modifiedVideos = updatedData.existingVideos.filter(v => v.modified && !v.toDelete);
                modifiedVideos.forEach((video, index) => {
                    formData.append(`existing_videos[${index}][id]`, video.id);
                    formData.append(`existing_videos[${index}][title]`, video.title);
                    formData.append(`existing_videos[${index}][description]`, video.description);
                    formData.append(`existing_videos[${index}][duration]`, video.duration);
                    formData.append(`existing_videos[${index}][order]`, video.order);

                    if (video.deleteDocuments && video.deleteDocuments.length > 0) {
                        video.deleteDocuments.forEach((docId, docIndex) => {
                            formData.append(`existing_videos[${index}][delete_documents][${docIndex}]`, docId);
                        });
                    }

                    if (video.newDocumentFiles && video.newDocumentFiles.length > 0) {
                        video.newDocumentFiles.forEach((file, docIndex) => {
                            if (file) {
                                formData.append(`existing_videos[${index}][new_documents][${docIndex}]`, file);
                            }
                        });
                    }
                });

                formData.append('_method', 'PUT');

                const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/${this.currentModule.module_id}/module`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: formData
                });
            
                const data = await response.json();
            
                if (response.ok) {
                    this.showSuccess('Módulo actualizado completamente');
                    this.closeEditModuleModal();
                    await this.fetchModules();
                } else {
                    if (response.status === 413) {
                        this.showError('Los archivos son demasiado grandes. Reduzca el tamaño de los videos o súbalos por separado.');
                    } else {
                        this.showError(data.message || 'Error al actualizar el módulo');
                    }
                }
            } catch (error) {
                console.log('Error al actualizar el módulo', error);
                this.showError('Error al actualizar el módulo');
            } finally {
                this.updating = false;
            }
        },

        async handleAddClass(classDataToAdd) {
            this.addingClasses = true;
            try {
                const formData = new FormData();

                if (classDataToAdd.contenido) {
                    formData.append('contenido', classDataToAdd.contenido);
                }
                if (classDataToAdd.duracion) {
                    formData.append('duracion', classDataToAdd.duracion);
                }

                formData.append('video', classDataToAdd.videoFile);
                formData.append('titulo', classDataToAdd.titulo);
                formData.append('descripcion', classDataToAdd.descripcion || '');
                formData.append('duracion_clase', classDataToAdd.duracion_clase);
                formData.append('orden', classDataToAdd.orden);

                let docIndex = 0;
                classDataToAdd.documentFiles.forEach((file) => {
                    if (file) {
                        formData.append(`documentos[${docIndex}]`, file);
                        docIndex++;
                    }
                });

                const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/${this.currentModule.module_id}/classes`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: formData
                });
            
                const data = await response.json();
            
                if (response.ok) {
                    this.showSuccess('Clase agregada correctamente al módulo');
                    this.closeAddClassModal();
                    await this.fetchModules();
                } else {
                    if (response.status === 413) {
                        this.showError('El archivo de video es demasiado grande. Reduzca el tamaño del video.');
                    } else if (response.status === 422) {
                        const errors = data.errors || {};
                        let errorMessage = 'Errores de validación:\n';
                        Object.keys(errors).forEach(field => {
                            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
                        });
                        this.showError(errorMessage);
                    } else {
                        this.showError(data.message || 'Error al agregar la clase');
                    }
                }
            } catch (error) {
                console.error('Error al agregar clase:', error);
                this.showError('Error de conexión al agregar la clase');
            } finally {
                this.addingClasses = false;
            }
        },

        validateFields() {
            if (!this.title || this.title.trim() === '' || this.title.length === 0) {
                if (this.$message) {
                    this.$message.warning('Titulo esta vacio!');
                } else {
                    alert('Titulo esta vacio!');
                }
                return false;
            }
            if (this.title.length > 255) {
                if (this.$message) {
                    this.$message.warning('Titulo demasiado largo!');
                } else {
                    alert('Titulo demasiado largo!');
                }
                return false;
            }
            return true;
        },

        async storeModule() {
            if (this.validateFields()) {
                try {
                    const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/module/basic`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: JSON.stringify({
                            titulo: this.title
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showSuccess('El módulo ha sido registrado correctamente');
                        this.showDialogAddModule = false;
                        this.title = null;
                        await this.fetchModules();
                    } else {
                        this.showError(data.message || 'Error al validar datos');
                    }
                } catch (error) {
                    console.log(error);
                    this.showError('Error al crear el módulo');
                }
            }
        },

        async deleteModule() {
            try {
                const response = await fetch(`/marketing/mini-course/${this.miniCourseId}/${this.moduleToDelete.module_id}/module/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess('Modulo eliminado correctamente');
                    this.hideDeleteModal();
                    await this.fetchModules();
                } else {
                    this.showError(data.message || 'Error al eliminar el módulo');
                }
            } catch (error) {
                console.log('Error al eliminar el módulo', error);
                this.showError('Error al eliminar el módulo');
            }
        },

        // Métodos auxiliares para mostrar mensajes
        showSuccess(message) {
            if (this.$message) {
                this.$message.success(message);
            } else {
                alert(message);
            }
        },
        
        showError(message) {
            if (this.$message) {
                this.$message.error(message);
            } else {
                alert(message);
            }
        }
    }
};
</script>

<style scoped>
.input-module-title {
    width: 70%;
    margin: 0 10px;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.list-group-item {
    border: 1px solid rgba(0,0,0,.125);
    margin-bottom: 10px;
}

.badge {
    font-size: 0.8em;
}

.fas {
    width: 1rem;
}

.modal {
    background-color: rgba(0,0,0,0.5);
}

.border {
    border-color: #dee2e6 !important;
}

/* Estilos específicos para el modal de previsualización */
.modal-xl {
    max-width: 90%;
}

@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1140px;
    }
}

.preview-content {
    padding: 1rem;
    min-height: 600px;
}

/* Estilos para el contenido de previsualización */
.preview-content h1,
.preview-content h2,
.preview-content h3,
.preview-content h4,
.preview-content h5,
.preview-content h6 {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.preview-content .card {
    margin-bottom: 1rem;
}

.preview-content .btn {
    margin: 0.25rem;
}

.modal-header .close {
    background: none;
    border: none;
    font-size: 1.5rem;
    font-weight: bold;
    color: #000;
    opacity: 0.5;
    cursor: pointer;
}

.modal-header .close:hover {
    opacity: 0.75;
}
</style>