<template>
    <div class="container-fluid mt-4">
        <div class="book-details-card">
            <div class="card-header">
                <div>
                    <h2>{{ course.title }}</h2>
                    <p><span>{{ course.status == 1 ? 'Pendiente de revisión' : course.status == 2 ? 'Aprobado' : 'Con observaciones' }}</span></p>
                </div>
                <div class="d-flex align-items-center" style="gap: 5px;">
                    <UserIcon size="18" />
                    <p class="m-0">Autor: {{ course.author }}</p>
                </div>
            </div>
            <div class="card-body">
                <p></p><strong>Descripción:</strong></p>
                <p>{{ course.description }}</p>
            </div>
        </div>

        <div class="separator"></div>

        <div class="files-container">
            <h3>Archivos del Libro</h3>
            
            <div v-if="isLoading" class="loading-container">
                <p>Cargando archivos...</p>
            </div>

            <div v-else-if="bookFiles.length > 0" class="files-section">
                <div class="storage-info">
                    <div class="storage-header">
                        <span>Almacenamiento utilizado</span>
                        <span class="storage-text">{{ (bookFiles.reduce((total, file) => total + (file.size || 0), 0) / (1024 * 1024)).toFixed(2) }} MB / 250 MB</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: Math.min((bookFiles.reduce((total, file) => total + (file.size || 0), 0) / (1024 * 1024) / 250) * 100, 100) + '%' }"></div>
                    </div>
                </div>

                <div class="files-list">
                    <div v-for="(file, index) in bookFiles" :key="file.id" class="file-row">
                        <div class="file-info">
                            <div class="file-icon">
                                <img :src="getFileIcon(file.file_type)" alt="File Icon">
                            </div>
                            <div class="file-details">
                                <h5 class="file-name">{{ file.file_name }}</h5>
                                <p class="file-meta">{{ (file.size / (1024 * 1024)).toFixed(2) }} MB • {{ file.file_type.toUpperCase() }}</p>
                            </div>
                        </div>
                        <div class="file-actions">
                            <button @click="downloadFile(file)" class="btn-action btn-download" title="Descargar">
                                <DownloadIcon size="16" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="no-files">
                <p>No hay archivos de libro para este curso.</p>
            </div>
        </div>

        <div class="separator"></div>

        <div class="actions-container">
            <button @click="approveCourse(course.id)" class="btn btn-approve">
                Aprobar Publicación
            </button>
            <button @click="openRejectModal" class="btn btn-reject">
                Rechazar con Observaciones
            </button>
        </div>

        <!-- Modal de Rechazo -->
        <div class="modal-overlay" v-if="showRejectModal" @click="closeRejectModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Rechazar Libro</h2>
                    <button @click="closeRejectModal" class="btn-close">×</button>
                </div>
                <div class="modal-body">
                    <p class="modal-description">Proporciona observaciones detalladas para que el usuario pueda corregir los problemas encontrados.</p>
                    
                    <div class="form-group">
                        <label for="observations">Observaciones para el usuario <span class="required">*</span></label>
                        <textarea 
                            id="observations"
                            v-model="observations"
                            class="textarea"
                            placeholder="Describe los problemas encontrados y las correcciones necesarias..."
                            rows="6"
                        ></textarea>
                        <small class="hint">Sé específico y claro para ayudar al usuario a corregir su envío</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeRejectModal" class="btn-cancel">Cancelar</button>
                    <button @click="submitReject" :disabled="!observations.trim()" class="btn-reject-submit">Rechazar</button>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Aprobación -->
        <div class="modal-overlay" v-if="showApproveModal" @click="closeApproveModal">
            <div class="modal-content modal-approve" @click.stop>
                <div class="modal-header">
                    <h2>Confirmar Aprobación</h2>
                    <button @click="closeApproveModal" class="btn-close">×</button>
                </div>
                <div class="modal-body">
                    <p class="modal-description">¿Estás seguro de que deseas aprobar este libro para su publicación en la tienda? Esta acción notificará al usuario y el libro estará disponible para su compra.</p>
                    
                    <div class="book-info-card">
                        <h4>{{ course.title }}</h4>
                        <p class="book-author">por {{ course.author }}</p>
                        <p class="book-files">{{ bookFiles.length }} {{ bookFiles.length === 1 ? 'archivo' : 'archivos' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeApproveModal" class="btn-cancel">Cancelar</button>
                    <button @click="submitApprove" :disabled="isSubmitting" class="btn-approve-submit">Aprobar</button>
                </div>
            </div>
        </div>
        </div>
</template>

<script>
import epubIcon from '../../../../images/icons/file-icons/icon_epub_file.png';
import pdfIcon from '../../../../images/icons/file-icons/pdf_file_icon.png';
import xlsxIcon from '../../../../images/icons/file-icons/xlsx_icon.png';

import { UserIcon, DownloadIcon } from 'vue-feather-icons';

export default {
    components: {
        UserIcon,
        DownloadIcon,
    },

    props: {
        course: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            bookFiles: [],
            selectedFileIndex: null,
            isLoading: false,
            isSubmitting: false,
            observations: '',
            currentAction: null,
            epubIcon,
            pdfIcon,
            xlsxIcon,
            showRejectModal: false,
            showApproveModal: false,
        };
    },

    computed: {
        selectedFile() {
            if (this.selectedFileIndex !== null && this.bookFiles.length > 0) {
                return this.bookFiles[this.selectedFileIndex];
            }
            return null;
        },
    },

    mounted() {
        this.loadBookFiles();
    },

    methods: {
        loadBookFiles() {
            this.isLoading = true;

            axios.get(`/course/${this.course.id}/book-files`)
                .then((response) => {
                    this.bookFiles = response.data.data || [];
                    
                    // Seleccionar el primer archivo automáticamente
                    if (this.bookFiles.length > 0) {
                        this.selectedFileIndex = 0;
                    }

                    this.isLoading = false;
                })
                .catch((error) => {
                    console.error('Error al cargar archivos:', error);
                    this.$message.error('Error al cargar los archivos del libro');
                    this.isLoading = false;
                });
        },

        selectFile(index) {
            this.selectedFileIndex = index;
        },

        downloadFile(file) {
            if (!file.url) return;

            window.open(file.url, '_blank');
        },

        getFileName(fullPath) {
            // Extrae solo el nombre del archivo sin la ruta completa
            return fullPath.split('/').pop();
        },

        getFileNameShort(fileName) {
            // Extrae el nombre del archivo y lo trunca si es muy largo
            const name = fileName.split('/').pop();
            return name.length > 25 ? name.substring(0, 22) + '...' : name;
        },

        getFileExtension(fileName) {
            const ext = fileName.split('.').pop();
            return ext ? ext.toLowerCase() : '';
        },

        getFileIcon(fileType) {
            if (fileType === 'pdf') {
                return this.pdfIcon;
            } else if (fileType === 'epub') {
                return this.epubIcon;
            } else if (['xls', 'xlsx', 'xlsm', 'xlsb', 'csv'].includes(fileType)) {
                return this.xlsxIcon;
            } else {
                return null; // O un icono genérico
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        openObservationsModal(action) {
            this.currentAction = action;
            this.observations = '';
            this.$nextTick(() => {
                $('#observationsModal').modal('show');
            });
        },

        closeObservationsModal() {
            this.observations = '';
            this.currentAction = null;
            $('#observationsModal').modal('hide');
        },

        getObservationsPlaceholder() {
            switch (this.currentAction) {
                case 'approve':
                    return 'Ingresa observaciones opcionales sobre el libro (si las hay)...';
                case 'reject':
                    return 'Explica por qué rechazas el libro (obligatorio)...';
                case 'observe':
                    return 'Ingresa las observaciones sobre el libro...';
                default:
                    return 'Ingresa observaciones...';
            }
        },

        async submitObservations() {
            // Validar que las observaciones sean obligatorias si rechaza
            if (this.currentAction === 'reject' && !this.observations.trim()) {
                this.$message.warning('Las observaciones son obligatorias para rechazar el libro');
                return;
            }

            try {
                this.isSubmitting = true;

                let actionStatus = 'observations';
                if (this.currentAction === 'approve') {
                    actionStatus = 'approved';
                } else if (this.currentAction === 'reject') {
                    actionStatus = 'disapproved';
                }

                console.log(this.observations);
                console.log(this.observations.trim() || null);

                const { data, status } = await axios.post(`/course/verification/book/${this.course.id}/review`, {
                    status: actionStatus,
                    observations: this.observations.trim() || null,
                });

                if (status === 200) {
                    const messageMap = {
                        'approve': 'Libro aprobado correctamente',
                        'disapproved': 'Libro rechazado correctamente',
                        'observe': 'Observaciones guardadas correctamente',
                    };

                    this.$message.success(messageMap[this.currentAction]);
                    
                    this.closeObservationsModal();

                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        document.location.href = `/course/verification`;
                    }, 1500);
                } else {
                    this.$message.warning('Error al validar datos');
                    this.isSubmitting = false;
                }
            } catch (error) {
                console.error('Error en la revisión del libro:', error);
                this.$message.error('Error: ' + (error.response?.data?.message || error.message));
                this.isSubmitting = false;
            }
        },

        async approveCourse(course_id) {
            this.openApproveModal();
        },

        openApproveModal() {
            this.showApproveModal = true;
        },

        closeApproveModal() {
            this.showApproveModal = false;
        },

        async submitApprove() {
            try {
                this.isSubmitting = true;
                const { status } = await axios.post(`/course/verification/book/${this.course.id}/review`, {
                    'status': 'approved'
                });

                if (status === 200) {
                    this.$message.success('Libro aprobado correctamente');
                    this.closeApproveModal();
                    
                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        document.location.href = `/course/verification`;
                    }, 1500);
                }
            } catch (error) {
                console.error('Error al aprobar el libro:', error);
                this.$message.error('Error: ' + (error.response?.data?.message || error.message));
            } finally {
                this.isSubmitting = false;
            }
        },

        openRejectModal() {
            this.showRejectModal = true;
            this.observations = '';
        },

        closeRejectModal() {
            this.showRejectModal = false;
            this.observations = '';
        },

        async submitReject() {
            if (!this.observations.trim()) {
                return;
            }

            try {
                this.isSubmitting = true;
                const { status } = await axios.post(`/course/verification/book/${this.course.id}/review`, {
                    status: 'disapproved',
                    observations: this.observations.trim(),
                });

                if (status === 200) {
                    this.$message.success('Libro rechazado correctamente');
                    this.closeRejectModal();
                    
                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        document.location.href = `/course/verification`;
                    }, 1500);
                }
            } catch (error) {
                console.error('Error al rechazar el libro:', error);
                this.$message.error('Error: ' + (error.response?.data?.message || error.message));
            } finally {
                this.isSubmitting = false;
            }
        },
    },
};
</script>

<style scoped>
.container-fluid {
    max-width: 1200px;
    margin: 0 auto;
}

.book-details-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 30px;
}

.card-header {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.card-header p {
    margin: 8px 0 0 0;
    color: #6b7280;
    font-size: 14px;
}

.card-header span {
    background: #fef08a;
    color: #92400e;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.card-body {
    padding: 24px;
}

.card-body p:first-child {
    margin-top: 0;
}

.card-body strong {
    color: #1f2937;
}

.separator {
    height: 1px;
    background: #e5e7eb;
    margin: 30px 0;
}

/* Files Container */
.files-container {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 30px;
}

.files-container h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.loading-container {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.no-files {
    text-align: center;
    padding: 40px 20px;
    background: #f3f4f6;
    border-radius: 8px;
    color: #6b7280;
}

.files-section {
    width: 100%;
}

.storage-info {
    background-color: #fafbfc;
    border: 1px solid #e0e4e8;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.storage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
}

.storage-text {
    font-size: 13px;
    color: #6b7280;
    font-weight: 400;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: #e0e4e8;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.files-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.file-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    transition: background-color 0.2s ease;
}

.file-row:hover {
    background-color: #f9fafb;
}

.file-row:last-child {
    border-bottom: none;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.file-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.file-icon img {
    height: 48px;
}

.file-icon.icon-pdf {
    background: #fee2e2;
    color: #dc2626;
}

.file-icon.icon-epub {
    background: #dbeafe;
    color: #2563eb;
}

.file-icon.icon-xls,
.file-icon.icon-xlsx,
.file-icon.icon-xlsm,
.file-icon.icon-xlsb,
.file-icon.icon-csv {
    background: #dcfce7;
    color: #16a34a;
}

.file-details {
    flex: 1;
}

.file-name {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
    color: #1f2937;
}

.file-meta {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #9ca3af;
}

.file-actions {
    display: flex;
    gap: 12px;
    margin-left: 16px;
}

.btn-action {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
}

.btn-download {
    background: #f3f4f6;
    color: #374151;
}

.btn-download:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

/* Actions Container */
.actions-container {
    display: flex;
    gap: 16px;
    margin-top: 30px;
}

.btn {
    flex: 1;
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-approve {
    background: #10b981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-reject {
    background: #ef4444;
    color: white;
}

.btn-reject:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    max-width: 500px;
    width: 100%;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.btn-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
}

.btn-close:hover {
    color: #1f2937;
}

.modal-body {
    padding: 24px;
}

.modal-description {
    margin: 0 0 20px 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 8px;
}

.required {
    color: #ef4444;
}

.textarea {
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    color: #1f2937;
    resize: vertical;
    transition: border-color 0.2s ease;
}

.textarea:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.textarea::placeholder {
    color: #9ca3af;
}

.hint {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: #9ca3af;
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 24px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.btn-cancel {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    background: white;
    color: #374151;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.btn-reject-submit {
    flex: 1;
    padding: 12px 20px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-reject-submit:hover:not(:disabled) {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-reject-submit:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Modal Approve Styles */
.modal-approve .modal-body {
    background: #f0fdf4;
}

.book-info-card {
    background: #dcfce7;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
}

.book-info-card h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #15803d;
}

.book-author {
    margin: 8px 0 0 0;
    font-size: 14px;
    color: #4b5563;
    font-weight: 500;
}

.book-files {
    margin: 8px 0 0 0;
    font-size: 14px;
    color: #4b5563;
}

.btn-approve-submit {
    flex: 1;
    padding: 12px 20px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-approve-submit:hover:not(:disabled) {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-approve-submit:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    opacity: 0.6;
}
</style>