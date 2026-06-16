<template>
    <!-- Modal para agregar clases - MEJORADO CON SCROLL -->
    <div class="modal fade show custom-modal-scrollable" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header sticky-header">
                    <h5 class="modal-title">Agregar Clase al Módulo: {{ currentModule.module_title }}</h5>
                    <button type="button" class="close" @click="$emit('close')" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-scrollable">
                    <form @submit.prevent="handleAddClass" enctype="multipart/form-data">                        
                        <!-- UNA SOLA CLASE -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-video text-primary me-2"></i>Datos de la Clase
                            </h6>

                            <div class="class-item p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Archivo de Video *</label>
                                        <input type="file" class="form-control" 
                                               @change="handleVideoFile($event)" 
                                               accept="video/*" required>
                                        <div class="form-text">MP4, MOV, AVI, WEBM | Max: 500MB</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Título de la Clase *</label>
                                        <input type="text" class="form-control" 
                                               v-model="localClassData.titulo" 
                                               placeholder="Título de la clase" required>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Descripción</label>
                                        <textarea class="form-control" 
                                                 v-model="localClassData.descripcion" 
                                                 rows="2" 
                                                 placeholder="Descripción de la clase"></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Duración de la Clase (min) *</label>
                                        <input type="number" class="form-control" 
                                               v-model="localClassData.duracion_clase" 
                                               min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden *</label>
                                        <input type="number" class="form-control" 
                                               v-model="localClassData.orden" 
                                               min="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <hr>
                    
                        <!-- Documentos (múltiples opcionales) -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-alt text-info me-2"></i>Documentos de la Clase (Opcional)
                                </h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" @click="addDocument">
                                    <i class="fas fa-plus"></i> Agregar Documento
                                </button>
                            </div>

                            <div class="documents-container">
                                <div v-for="(doc, index) in localClassData.documentos" :key="'doc-' + index" 
                                     class="document-item mb-2 p-2 border rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <label :for="'doc-file-' + index" class="form-label small mb-1">
                                                Documento {{ index + 1 }}
                                            </label>
                                            <input type="file" class="form-control" :id="'doc-file-' + index"
                                                   @change="handleDocumentFile($event, index)" 
                                                   accept=".doc,.docx,.pdf,.xls,.xlsx,.txt">
                                            <div class="form-text small">DOC, PDF, XLS, TXT | Max: 5MB</div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm" @click="removeDocument(index)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="localClassData.documentos.length === 0" class="text-center text-muted py-4">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <p class="mb-0">No hay documentos agregados. Los documentos son opcionales.</p>
                                    <small>Haz clic en "Agregar Documento" si deseas incluir documentos.</small>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Resumen de la clase -->
                        <div class="alert alert-info" v-if="localClassData.titulo || localClassData.videoFile">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-1"></i>Resumen de la clase
                            </h6>
                            <ul class="mb-0 small">
                                <li v-if="localClassData.titulo">
                                    Título: <strong>{{ localClassData.titulo }}</strong>
                                </li>
                                <li v-if="localClassData.duracion_clase">
                                    Duración: <strong>{{ localClassData.duracion_clase }} minutos</strong>
                                </li>
                                <li v-if="localClassData.orden">
                                    Orden: <strong>{{ localClassData.orden }}</strong>
                                </li>
                                <li v-if="localClassData.videoFile">
                                    Video: <strong>{{ localClassData.videoFile.name }}</strong> 
                                    ({{ formatFileSize(localClassData.videoFile.size) }})
                                </li>
                                <li v-if="localClassData.documentFiles.filter(f => f).length > 0">
                                    Documentos: <strong>{{ localClassData.documentFiles.filter(f => f).length }}</strong>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer sticky-footer">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="handleAddClass" 
                            :disabled="addingClasses || !localClassData.titulo || !localClassData.videoFile">
                        <i class="fas fa-save me-1"></i>
                        {{ addingClasses ? 'Agregando...' : 'Agregar Clase' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AddClassModal',
    props: {
        miniCourseId: {
            type: Number,
            required: true
        },
        currentModule: {
            type: Object,
            required: true
        },
        classData: {
            type: Object,
            required: true
        },
        addingClasses: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'add-class', 'show-success', 'show-error'],
    data() {
        return {
            localClassData: {},
            maxFileSize: 500 * 1024 * 1024, // 500MB
            allowedVideoTypes: ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm']
        };
    },
    watch: {
        classData: {
            immediate: true,
            deep: true,
            handler(newData) {
                this.localClassData = JSON.parse(JSON.stringify(newData));
            }
        }
    },
    methods: {
        handleAddClass() {
            // Validaciones
            if (!this.localClassData.videoFile) {
                this.$emit('show-error', 'Debe seleccionar un video para la clase');
                return;
            }

            if (!this.localClassData.titulo || !this.localClassData.titulo.trim()) {
                this.$emit('show-error', 'Debe ingresar un título para la clase');
                return;
            }

            if (!this.localClassData.duracion_clase || this.localClassData.duracion_clase < 1) {
                this.$emit('show-error', 'Debe especificar la duración de la clase');
                return;
            }

            if (!this.localClassData.orden || this.localClassData.orden < 1) {
                this.$emit('show-error', 'Debe especificar el orden de la clase');
                return;
            }

            this.$emit('add-class', this.localClassData);
        },

        handleVideoFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validar tipo de archivo
            if (!this.allowedVideoTypes.includes(file.type)) {
                this.$emit('show-error', 'Tipo de archivo no permitido. Use: MP4, AVI, MOV, WMV, WEBM');
                event.target.value = '';
                return;
            }
            
            // Validar tamaño
            if (file.size > this.maxFileSize) {
                const sizeMB = (this.maxFileSize / (1024 * 1024)).toFixed(0);
                this.$emit('show-error', `El archivo es demasiado grande. Máximo permitido: ${sizeMB}MB`);
                event.target.value = '';
                return;
            }
            
            this.localClassData.videoFile = file;
            this.getVideoDuration(file);
        },

        // Obtener duración del video (opcional)
        getVideoDuration(file) {
            const video = document.createElement('video');
            video.preload = 'metadata';

            video.onloadedmetadata = () => {
                window.URL.revokeObjectURL(video.src);
                const duration = Math.ceil(video.duration / 60); // en minutos

                // Actualizar duración automáticamente si no se ha establecido
                if (!this.localClassData.duracion_clase || this.localClassData.duracion_clase === 1) {
                    this.localClassData.duracion_clase = duration;
                }

                console.log(`Duración del video detectada: ${duration} minutos`);
            };

            video.src = URL.createObjectURL(file);
        },

        addDocument() {
            this.localClassData.documentos.push({
                file: null
            });
        },

        removeDocument(index) {
            this.localClassData.documentos.splice(index, 1);
            this.localClassData.documentFiles.splice(index, 1);
        },

        handleDocumentFile(event, index) {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño del documento
                const maxDocSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxDocSize) {
                    this.$emit('show-error', 'El documento es demasiado grande. Máximo permitido: 5MB');
                    event.target.value = '';
                    return;
                }

                // Asegurar que el array tenga el tamaño correcto
                while (this.localClassData.documentFiles.length <= index) {
                    this.localClassData.documentFiles.push(null);
                }
                this.localClassData.documentFiles[index] = file;
            }
        },

        // Método auxiliar para formatear tamaño de archivos
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
};
</script>

<style scoped>
/* Estilos mejorados para el modal con scroll */
.custom-modal-scrollable {
    overflow: hidden;
}

.modal-dialog-scrollable {
    height: 90vh;
    max-height: 90vh;
}

.modal-dialog-scrollable .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.sticky-header {
    position: sticky;
    top: 0;
    z-index: 1050;
    background-color: #fff;
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

.modal-body-scrollable {
    flex: 1;
    overflow-y: auto;
    max-height: calc(90vh - 120px);
    padding: 1.5rem;
}

.sticky-footer {
    position: sticky;
    bottom: 0;
    z-index: 1050;
    background-color: #fff;
    border-top: 1px solid #dee2e6;
    flex-shrink: 0;
}

/* Contenedores para documentos */
.documents-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

/* Ítems */
.document-item,
.class-item {
    background-color: #fff;
    transition: box-shadow 0.15s ease-in-out;
}

.document-item:hover,
.class-item:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Scrollbar personalizada */
.modal-body-scrollable::-webkit-scrollbar,
.documents-container::-webkit-scrollbar {
    width: 8px;
}

.modal-body-scrollable::-webkit-scrollbar-track,
.documents-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-body-scrollable::-webkit-scrollbar-thumb,
.documents-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.modal-body-scrollable::-webkit-scrollbar-thumb:hover,
.documents-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Estados vacíos */
.text-center.text-muted {
    padding: 2rem;
    background-color: rgba(108, 117, 125, 0.1);
    border-radius: 0.375rem;
    border: 2px dashed #dee2e6;
}

.text-center.text-muted i {
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-dialog-scrollable {
        height: 95vh;
        max-height: 95vh;
        margin: 0.5rem;
    }
    
    .modal-body-scrollable {
        max-height: calc(95vh - 120px);
        padding: 1rem;
    }
    
    .documents-container {
        max-height: 300px;
    }
}

/* Alertas y elementos específicos */
.alert {
    margin-bottom: 1rem;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>