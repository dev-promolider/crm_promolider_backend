<script>
import epubIcon from '../../../images/icons/file-icons/icon_epub_file.png';
import pdfIcon from '../../../images/icons/file-icons/pdf_file_icon.png';
import xlsxIcon from '../../../images/icons/file-icons/xlsx_icon.png';
import { Trash2Icon, UploadIcon, DownloadIcon } from 'vue-feather-icons';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';

import Swal from 'sweetalert2';

export default {
    name: 'EditBookContent',

    components: {
        UploadIcon,
        Trash2Icon,
        DownloadIcon,
        CustomSpinner,
    },

    props: {
        course: {
            type: Object,
            required: true
        },
        observations: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            bookFiles: [],
            loading: true,
            error: null,
            maxFiles: 20,
            maxTotalSize: 250 * 1024 * 1024, // 250MB en bytes
            allowedFormats: ['pdf', 'epub', 'xls', 'xlsx', 'xlsm', 'xlsb', 'csv'],
            epubIcon,
            pdfIcon,
            xlsxIcon,
        }
    },

    computed: {
        usedMB() {
            return this.bookFiles.reduce((total, file) => total + (file.size || 0), 0) / (1024 * 1024);
        },
        usagePercentage() {
            return (this.usedMB / 250) * 100;
        }
    },

    mounted() {
        this.fetchBookFiles();
    },

    methods: {
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

        downloadFile(file) {
            if (!file.url) return;

            window.open(file.url, '_blank');
        },

        async fetchBookFiles() {
            try {
                this.loading = true;
                this.error = null;
                const response = await axios.get(`/course/${this.course.id}/book-files`);
                this.bookFiles = response.data.data || response.data;
            } catch (err) {
                this.error = err.message || 'Error al cargar los archivos del libro';
                console.error('Error fetching book files:', err);
            } finally {
                this.loading = false;
            }
        },

        triggerFileInput() {
            this.$refs.fileInput.click();
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            
            if (!file) return;

            // Validar formato
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!this.allowedFormats.includes(fileExtension)) {
                this.error = `El formato ${fileExtension.toUpperCase()} no está permitido`;
                this.$refs.fileInput.value = '';
                return;
            }

            // Validar tamaño individual
            if (file.size > this.maxTotalSize) {
                const maxMB = this.maxTotalSize / (1024 * 1024);
                this.error = `El archivo no puede superar ${maxMB}MB`;
                this.$refs.fileInput.value = '';
                return;
            }

            this.error = null;
            this.uploadFiles([file]);
        },

        async uploadFiles(files) {
            const formData = new FormData();
            formData.append('course_id', this.course.id);
            
            for (let file of files) {
                formData.append('file', file);
            }

            try {
                this.loading = true;
                const response = await axios.post(`/course/${this.course.id}/store-book-file`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                // Recargar la lista de archivos
                await this.fetchBookFiles();
                this.error = null;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al subir los archivos';
                console.error('Error uploading files:', err);
            } finally {
                this.loading = false;
                // Limpiar el input
                this.$refs.fileInput.value = '';
            }
        },

        async confirmDelete(file) {
            const result = await Swal.fire({
                title: '¿Eliminar archivo?',
                text: `Se eliminará "${file.file_name}". Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            });

            if (result.isConfirmed) {
                this.deleteBookFile(file.id);
            }
        },

        async deleteBookFile(fileId) {
            try {
                this.loading = true;
                await axios.delete(`/course/book-file/${fileId}`);
                // Recargar la lista de archivos
                await this.fetchBookFiles();
                this.error = null;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al eliminar el archivo';
                console.error('Error deleting file:', err);
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return '';
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return new Date(date).toLocaleDateString('es-ES', options);
        }
    },
}

</script>

<template>
    <div class="edit-book-content">

        <div class="book-files-container">
            <h3>Archivos del Libro</h3>
            
            <div class="upload-section">
                <button @click="triggerFileInput" class="btn-upload" :disabled="loading">
                    <span><UploadIcon size="24" /> Subir Archivo</span>
                </button>
                <input 
                    ref="fileInput" 
                    type="file" 
                    @change="handleFileUpload"
                    :accept="allowedFormats.map(f => '.' + f).join(',')"
                    style="display: none;"
                >
                <p class="upload-info">
                    <strong>Formatos permitidos:</strong> {{ allowedFormats.map(f => f.toUpperCase()).join(', ') }} <br>
                    <strong>Límite:</strong> Máximo {{ maxFiles }} archivos, {{ maxTotalSize / (1024 * 1024) }} MB totales
                </p>
            </div>

            <div v-if="loading" class="loading">
                <CustomSpinner />
            </div>

            <div v-else-if="error" class="alert alert-danger">
                <p>{{ error }}</p>
            </div>

            <div v-else>   
                <div v-if="bookFiles.length > 0" class="files-with-observations">
                    <div class="files-section">
                        <div class="storage-info">
                            <div class="storage-header">
                                <span>Almacenamiento utilizado</span>
                                <span class="storage-text">{{ usedMB.toFixed(2) }} MB / 250 MB</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" :style="{ width: Math.min(usagePercentage, 100) + '%' }"></div>
                            </div>
                        </div>
                        <div class="files-list">
                            <div v-for="file in bookFiles" :key="file.id" class="file-item card">
                                <div class="card-body">
                                    <div class="file-icon">
                                        <img :src="getFileIcon(file.file_type)" alt="Epub Icon">
                                    </div>
                                    <div class="card-details">
                                        <h5 class="card-title">{{ file.file_name }}</h5>
                                        <p class="card-text">
                                            {{ (file.size / (1024 * 1024)).toFixed(2) }} MB • {{ file.file_type.toUpperCase() }}
                                        </p>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <div class="file-actions">
                                        <button
                                            class="btn-action btn-download"
                                            @click="downloadFile(file)"
                                        >
                                            <DownloadIcon size="18" />
                                            <span>Descargar</span>
                                        </button>

                                        <button
                                            class="btn-action btn-delete"
                                            @click="confirmDelete(file)"
                                        >
                                            <Trash2Icon size="18" />
                                            <span>Eliminar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="course.status === 3" class="observations-section">
                        <div class="observations-panel">
                            <h4>Observaciones</h4>
                            <div
                                v-if="observations && observations.length"
                                class="observations-list"
                            >
                                <div
                                    v-for="observation in observations"
                                    :key="observation.id"
                                    class="observation-item"
                                >
                                    <div class="observation-header">
                                        <span class="observation-author">
                                            {{ observation.analyst?.name || 'Analista no asignado' }}
                                            {{ observation.analyst?.last_name || '' }}
                                        </span>

                                        <span class="observation-date">
                                            {{ formatDate(observation.created_at) }}
                                        </span>
                                    </div>

                                    <p class="observation-text">
                                        {{ observation.observations }}
                                    </p>
                                </div>
                            </div>

                            <div v-else class="no-observations">
                                <p>No hay observaciones registradas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="alert alert-info">
                    <p>No hay archivos de libro para este curso.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.edit-book-content {
    padding: 20px;
}

.book-files-container {
    margin-top: 20px;
}

.upload-section {
    margin-bottom: 30px;
    border-bottom: 3px solid lime;
    padding-bottom: 30px;
}

.btn-upload {
    display: inline-block;
    padding: 12px 24px;
    background: #20e404;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 12px;
}

.btn-upload:hover:not(:disabled) {
    transform: translateY(-2px);
}

.btn-upload:active:not(:disabled) {
    transform: translateY(0);
}

.btn-upload:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-action {
    flex: 1;

    border: none;
    border-radius: 10px;

    padding: 12px 16px;

    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    font-weight: 600;
    cursor: pointer;

    transition: all .2s ease;
}

.btn-download {
    background: #f3f4f6;
    color: #1f2937;
}

.btn-download:hover {
    background: #e5e7eb;
}

.btn-delete {
    background: #fef2f2;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fee2e2;
}

.upload-info {
    background-color: #edffeb;
    border-left: 4px solid #8cff7d;
    padding: 12px 16px;
    margin: 0;
    font-size: 14px;
    color: #333;
    line-height: 1.6;
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
    color: #333;
}

.storage-text {
    font-size: 13px;
    color: #666;
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
    background: linear-gradient(90deg, #98d390 0%, #20e404 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.files-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.file-item.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.file-item.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.file-item.card:hover .btn-delete {
    opacity: 1;
}

.file-actions {
    display: flex;
    gap: 14px;
}

.card-body {
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.file-icon {
    padding: 5px;
}

.file-icon img {
    height: 48px;
}

.card-title {
    margin: 0 0 12px 0;
    font-size: 16px;
    font-weight: 600;
    word-break: break-word;
}

.card-text {
    margin: 8px 0;
    font-size: 14px;
    color: #868686;
}

.card-text strong {
    color: #333;
}

/* Files with Observations Layout */
.files-with-observations {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 24px;
    align-items: start;
}

.files-section {
    width: 100%;
}

.observations-section {
    width: 100%;
}

.observations-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    position: sticky;
    top: 20px;
}

.observations-panel h4 {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.observations-panel h4::before {
    content: '💬';
    margin-right: 8px;
}

.observations-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.observation-item {
    padding: 12px;
    background: #f9fafb;
    border-left: 3px solid #f59e0b;
    border-radius: 4px;
}

.observation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.observation-author {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.observation-date {
    font-size: 12px;
    color: #9ca3af;
}

.observation-text {
    margin: 0;
    font-size: 13px;
    color: #4b5563;
    line-height: 1.4;
    border: none !important;
}

.no-observations {
    text-align: center;
    padding: 20px 10px;
    color: #9ca3af;
    font-size: 13px;
}

@media (max-width: 1024px) {
    .files-with-observations {
        grid-template-columns: 1fr;
    }

    .observations-panel {
        position: static;
    }
}
</style>