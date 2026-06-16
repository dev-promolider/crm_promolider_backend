<template>
    <!-- Modal para editar módulo COMPLETO -->
    <div class="modal fade show custom-modal-scrollable" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header sticky-header">
                    <h5 class="modal-title">Editar Módulo: {{ currentModule.module_title }}</h5>
                    <button type="button" class="close" @click="$emit('close')" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-scrollable">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs sticky-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    :class="{ active: activeTab === 'module' }"
                                    @click="activeTab = 'module'"
                                    type="button">
                                <i class="fas fa-info-circle me-1"></i>
                                Datos del Módulo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    :class="{ active: activeTab === 'classes' }"
                                    @click="activeTab = 'classes'"
                                    type="button">
                                <i class="fas fa-video me-1"></i>
                                Clases
                                <span class="badge bg-secondary ms-1">
                                    {{ localEditData.existingVideos.length }}
                                </span>
                            </button>
                        </li>
                    </ul>
                
                    <div class="tab-content mt-3">
                        <!-- Tab 1: Datos del Módulo -->
                        <div class="tab-pane" :class="{ active: activeTab === 'module' }">
                            <form @submit.prevent="handleUpdate" enctype="multipart/form-data">
                                <!-- Datos básicos del módulo -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label for="editTitle" class="form-label">Título del Módulo</label>
                                        <input type="text" class="form-control" id="editTitle" 
                                               v-model="localEditData.titulo" required>
                                    </div>
                                </div>
                            
                                <div class="mb-4">
                                    <label for="editContent" class="form-label">Contenido</label>
                                    <textarea class="form-control" id="editContent" rows="8" 
                                              v-model="localEditData.contenido" required 
                                              placeholder="Describe detalladamente el contenido del módulo..."></textarea>
                                </div>
                            
                                <!-- Información del módulo -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-1"></i>Información del Módulo
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Clases:</strong> {{ localEditData.existingVideos.length }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Duración Total:</strong> 
                                            {{ calculateModuleDuration() }} min
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Documentos:</strong> 
                                            {{ calculateTotalDocuments() }}
                                        </div>
                                    </div>
                                </div>
                            
                                <!-- Botón para ir a clases -->
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary" 
                                            @click="activeTab = 'classes'">
                                        <i class="fas fa-video me-1"></i>
                                        Administrar Clases del Módulo
                                    </button>
                                </div>
                            </form>
                        </div>
                    
                        <!-- Tab 2: Clases del Módulo -->
                        <div class="tab-pane" :class="{ active: activeTab === 'classes' }">
                            <!-- Header de clases -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-video text-primary me-2"></i>
                                    Clases del Módulo: {{ currentModule.module_title }}
                                </h6>
                                <button type="button" class="btn btn-success btn-sm" @click="addVideoToEdit">
                                    <i class="fas fa-plus"></i> Agregar Nueva Clase
                                </button>
                            </div>
                        
                            <!-- Contenedor de clases con scroll independiente -->
                            <div class="classes-container">
                                <!-- Clases existentes -->
                                <div v-for="(video, index) in localEditData.existingVideos" 
                                     :key="'existing-video-' + video.id" 
                                     class="class-item p-3 border rounded mb-3"
                                     :class="{ 'existing-item': !video.toDelete, 'to-delete': video.toDelete }">
                            
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1 text-primary">
                                                <span class="badge me-2" :class="video.toDelete ? 'badge-delete' : 'badge-existing'">
                                                    {{ video.toDelete ? 'A Eliminar' : 'Clase ' + (index + 1) }}
                                                </span>
                                                <span :class="{ 'text-decoration-line-through': video.toDelete }">
                                                    {{ video.title }}
                                                </span>
                                            </h6>
                                            <small class="text-muted">
                                                ID: {{ video.id }} | Orden: {{ video.order }} | {{ video.duration }} min
                                            </small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn" 
                                                    :class="video.toDelete ? 'btn-outline-success' : 'btn-outline-danger'" 
                                                    @click="markVideoForDeletion(video)"
                                                    :title="video.toDelete ? 'Cancelar eliminación' : 'Marcar para eliminar'">
                                                <i :class="video.toDelete ? 'fas fa-undo' : 'fas fa-trash'"></i>
                                            </button>
                                        </div>
                                    </div>
                                
                                    <div v-if="!video.toDelete">
                                        <!-- Datos de la clase -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Título de la Clase</label>
                                                <input type="text" class="form-control" 
                                                       v-model="video.title" @input="video.modified = true">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Descripción</label>
                                                <textarea class="form-control" rows="2" 
                                                          v-model="video.description" @input="video.modified = true"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Duración (min)</label>
                                                <input type="number" class="form-control" 
                                                       v-model="video.duration" min="1" @input="video.modified = true">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Orden</label>
                                                <input type="number" class="form-control" 
                                                       v-model="video.order" min="1" @input="video.modified = true">
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <a :href="video.url" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                                    <i class="fas fa-play me-1"></i>Ver Video
                                                </a>
                                            </div>
                                        </div>
                                    
                                        <!-- Documentos de esta clase -->
                                        <div class="documents-section">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">
                                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                                    Documentos de esta clase
                                                    <span class="badge bg-secondary ms-1">
                                                        {{ (video.documents ? video.documents.filter(d => !d.toDelete).length : 0) + 
                                                           (video.newDocuments ? video.newDocuments.length : 0) }}
                                                    </span>
                                                </label>
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        @click="addDocumentToClass(video)">
                                                    <i class="fas fa-plus"></i> Agregar
                                                </button>
                                            </div>
                                        
                                            <div class="documents-in-class">
                                                <!-- Documentos existentes -->
                                                <div v-if="video.documents && video.documents.length > 0">
                                                    <div v-for="(doc, docIndex) in video.documents" 
                                                         :key="'doc-' + doc.id"
                                                         class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded"
                                                         :class="{ 'to-delete': doc.toDelete }">
                                                        <div>
                                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                                            <small>
                                                                <span :class="{ 'text-decoration-line-through': doc.toDelete }">
                                                                    Documento {{ doc.id }}
                                                                </span>
                                                                <a v-if="!doc.toDelete" :href="doc.url" target="_blank" class="ms-2">
                                                                    Ver
                                                                </a>
                                                            </small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm" 
                                                                :class="doc.toDelete ? 'btn-outline-success' : 'btn-outline-danger'"
                                                                @click="markClassDocumentForDeletion(video, doc)">
                                                            <i :class="doc.toDelete ? 'fas fa-undo' : 'fas fa-trash'"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            
                                                <!-- Nuevos documentos -->
                                                <div v-if="video.newDocuments && video.newDocuments.length > 0">
                                                    <div v-for="(newDoc, newDocIndex) in video.newDocuments" 
                                                         :key="'new-doc-' + newDocIndex"
                                                         class="d-flex align-items-center mb-2 p-2 border rounded new-item">
                                                        <div class="flex-grow-1 me-2">
                                                            <input type="file" class="form-control form-control-sm" 
                                                                   @change="handleClassDocumentFile($event, video, newDocIndex)"
                                                                   accept=".doc,.docx,.pdf,.xls,.xlsx,.txt">
                                                            <div class="form-text small">DOC, PDF, XLS, TXT | Max: 5MB</div>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                @click="removeNewClassDocument(video, newDocIndex)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            
                                                <!-- Estado vacío -->
                                                <div v-if="(!video.documents || video.documents.filter(d => !d.toDelete).length === 0) && 
                                                           (!video.newDocuments || video.newDocuments.length === 0)" 
                                                     class="text-center text-muted py-2">
                                                    <small>
                                                        <i class="fas fa-file-alt me-1"></i>
                                                        Esta clase no tiene documentos
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div v-else class="text-center py-3">
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Esta clase y todos sus documentos serán eliminados permanentemente.
                                        </p>
                                    </div>
                                </div>
                            
                                <!-- Estado vacío -->
                                <div v-if="localEditData.existingVideos.length === 0" 
                                     class="text-center text-muted py-5">
                                    <i class="fas fa-video fa-4x mb-3 opacity-50"></i>
                                    <h5>No hay clases en este módulo</h5>
                                    <p>Haz clic en "Agregar Nueva Clase" para comenzar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer sticky-footer">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="handleUpdate" :disabled="updating">
                        <i class="fas fa-save me-1"></i>{{ updating ? 'Guardando...' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EditModuleModal',
    props: {
        miniCourseId: {
            type: Number,
            required: true
        },
        currentModule: {
            type: Object,
            required: true
        },
        editModuleData: {
            type: Object,
            required: true
        },
        updating: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'update', 'show-success', 'show-error'],
    data() {
        return {
            activeTab: 'module',
            localEditData: {}
        };
    },
    watch: {
        editModuleData: {
            immediate: true,
            deep: true,
            handler(newData) {
                this.localEditData = JSON.parse(JSON.stringify(newData));
            }
        }
    },
    methods: {
        handleUpdate() {
            this.$emit('update', this.localEditData);
        },

        // Marcar video para eliminar
        markVideoForDeletion(video) {
            video.toDelete = !video.toDelete;
            if (video.toDelete && !this.localEditData.deleteVideos.includes(video.id)) {
                this.localEditData.deleteVideos.push(video.id);
            } else if (!video.toDelete) {
                const index = this.localEditData.deleteVideos.indexOf(video.id);
                if (index > -1) {
                    this.localEditData.deleteVideos.splice(index, 1);
                }
            }
        },

        // Agregar nuevo video en edición
        addVideoToEdit() {
            this.localEditData.newVideos.push({
                title: '',
                description: '',
                duration: 1,
                order: this.localEditData.existingVideos.length + this.localEditData.newVideos.length + 1,
                file: null
            });
        },

        addDocumentToClass(video) {
            if (!video.newDocuments) {
                video.newDocuments = [];
            }
            video.newDocuments.push({
                file: null
            });
        },

        removeNewClassDocument(video, docIndex) {
            if (video.newDocuments) {
                video.newDocuments.splice(docIndex, 1);
            }
        },

        handleClassDocumentFile(event, video, docIndex) {
            const file = event.target.files[0];
            if (file) {
                const maxDocSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxDocSize) {
                    this.$emit('show-error', 'El documento es demasiado grande. Máximo permitido: 5MB');
                    event.target.value = '';
                    return;
                }
            
                if (!video.newDocumentFiles) {
                    video.newDocumentFiles = [];
                }
                while (video.newDocumentFiles.length <= docIndex) {
                    video.newDocumentFiles.push(null);
                }
                video.newDocumentFiles[docIndex] = file;
            }
        },

        markClassDocumentForDeletion(video, document) {
            document.toDelete = !document.toDelete;

            if (!video.deleteDocuments) {
                video.deleteDocuments = [];
            }

            if (document.toDelete && !video.deleteDocuments.includes(document.id)) {
                video.deleteDocuments.push(document.id);
            } else if (!document.toDelete) {
                const index = video.deleteDocuments.indexOf(document.id);
                if (index > -1) {
                    video.deleteDocuments.splice(index, 1);
                }
            }
        },

        // Calcular duración total del módulo
        calculateModuleDuration() {
            return this.localEditData.existingVideos
                .filter(v => !v.toDelete)
                .reduce((total, video) => total + (parseInt(video.duration) || 0), 0);
        },

        // Calcular total de documentos
        calculateTotalDocuments() {
            return this.localEditData.existingVideos
                .filter(v => !v.toDelete)
                .reduce((total, video) => {
                    const existingDocs = video.documents ? video.documents.filter(d => !d.toDelete).length : 0;
                    const newDocs = video.newDocuments ? video.newDocuments.length : 0;
                    return total + existingDocs + newDocs;
                }, 0);
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

/* Contenedores para videos y documentos */
.classes-container {
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 5px;
}

/* Ítems */
.class-item {
    background-color: #fff;
    transition: all 0.2s ease;
}

.class-item:hover {
    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.15);
}

/* Estados */
.existing-item {
    border-left: 4px solid #0d6efd;
}

.new-item {
    border-left: 4px solid #198754;
}

.to-delete {
    background-color: #f8d7da !important;
    border-left: 4px solid #dc3545;
    opacity: 0.7;
}

/* Pestañas pegajosas */
.sticky-tabs {
    position: sticky;
    top: 0;
    z-index: 1040;
    background-color: #fff;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 0;
}

/* Contenido de pestañas */
.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.documents-section {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-top: 1rem;
}

.documents-in-class {
    max-height: 200px;
    overflow-y: auto;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    padding: 0.5rem;
}

/* Badges */
.badge-existing {
    background-color: #0d6efd;
}

.badge-new {
    background-color: #198754;
}

.badge-delete {
    background-color: #dc3545;
}

/* Scrollbar personalizada */
.modal-body-scrollable::-webkit-scrollbar,
.classes-container::-webkit-scrollbar,
.documents-in-class::-webkit-scrollbar {
    width: 8px;
}

.documents-in-class::-webkit-scrollbar {
    width: 6px;
}

.modal-body-scrollable::-webkit-scrollbar-track,
.classes-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-body-scrollable::-webkit-scrollbar-thumb,
.classes-container::-webkit-scrollbar-thumb,
.documents-in-class::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.modal-body-scrollable::-webkit-scrollbar-thumb:hover,
.classes-container::-webkit-scrollbar-thumb:hover,
.documents-in-class::-webkit-scrollbar-thumb:hover {
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
    .classes-container {
        max-height: 60vh;
    }
    
    .modal-dialog-scrollable {
        height: 95vh;
        max-height: 95vh;
        margin: 0.5rem;
    }
    
    .modal-body-scrollable {
        max-height: calc(95vh - 120px);
        padding: 1rem;
    }
}
</style>