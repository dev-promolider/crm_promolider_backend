<template>
    <div class="certificate-viewer">
        <div v-if="loading" class="text-center p-4">
            <i class="feather icon-loader rotating"></i>
            <p>Cargando certificados...</p>
        </div>

        <div v-else-if="certificates.length === 0" class="text-center p-4">
            <div class="empty-state">
                <i class="feather icon-award" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3 text-muted">No tienes certificados para este curso</h5>
                <p class="text-muted">Genera tu primer certificado usando la opción "Generar certificado"</p>
                <button type="button" class="btn btn-primary" @click="closeModal">
                    Cerrar
                </button>
            </div>
        </div>

        <div v-else>
            <!-- Información del curso -->
            <div class="course-info mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-1">
                            <i class="feather icon-book-open"></i> {{ course.title }}
                        </h6>
                        <small class="text-muted">Certificados disponibles: {{ certificates.length }}</small>
                    </div>
                </div>
            </div>

            <!-- Lista de certificados -->
            <div class="certificates-list">
                <div v-for="certificate in certificates" :key="certificate.id" class="certificate-card mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="certificate-info">
                                <h6 class="mb-1">
                                    <i class="feather icon-award"></i>
                                    Certificado #{{ certificate.certificate_code }}
                                </h6>
                                <small class="text-muted">
                                    Plantilla: {{ certificate.template ? certificate.template.name : 'N/A' }}
                                </small>
                            </div>
                            <div class="certificate-status">
                                <span 
                                    :class="getStatusBadgeClass(certificate.status)"
                                    class="badge">
                                    {{ getStatusText(certificate.status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="certificate-details">
                                        <p class="mb-2">
                                            <strong>Fecha de finalización:</strong><br>
                                            <small>{{ formatDate(certificate.completion_date) }}</small>
                                        </p>
                                        <p class="mb-2" v-if="certificate.issued_at">
                                            <strong>Fecha de emisión:</strong><br>
                                            <small>{{ formatDate(certificate.issued_at) }}</small>
                                        </p>
                                        <p class="mb-0" v-if="certificate.custom_data && Object.keys(certificate.custom_data).length > 0">
                                            <strong>Datos personalizados:</strong><br>
                                            <small v-for="(value, key) in certificate.custom_data" :key="key">
                                                {{ key }}: {{ value }}<br>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="certificate-actions d-flex flex-column gap-2">
                                        <!-- Botón vista previa -->
                                        <button 
                                            type="button"
                                            class="btn btn-info btn-sm"
                                            @click="previewCertificate(certificate)">
                                            <i class="feather icon-eye"></i> Vista Previa
                                        </button>

                                        <!-- Botón descargar -->
                                        <button 
                                            type="button"
                                            class="btn btn-success btn-sm"
                                            @click="downloadCertificate(certificate)"
                                            :disabled="certificate.status !== 'issued' || downloading === certificate.id"
                                            v-if="certificate.status === 'issued'">
                                            <span v-if="downloading === certificate.id">
                                                <i class="feather icon-loader rotating"></i> Descargando...
                                            </span>
                                            <span v-else>
                                                <i class="feather icon-download"></i> Descargar PDF
                                            </span>
                                        </button>

                                        <!-- Botón emitir -->
                                        <button 
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            @click="issueCertificate(certificate)"
                                            :disabled="certificate.status === 'issued' || issuing === certificate.id"
                                            v-if="certificate.status === 'draft'">
                                            <span v-if="issuing === certificate.id">
                                                <i class="feather icon-loader rotating"></i> Emitiendo...
                                            </span>
                                            <span v-else>
                                                <i class="feather icon-send"></i> Emitir certificado
                                            </span>
                                        </button>

                                        <!-- Botón editar -->
                                        <button 
                                            type="button"
                                            class="btn btn-warning btn-sm"
                                            @click="editCertificate(certificate)"
                                            :disabled="certificate.status === 'issued'"
                                            v-if="certificate.status === 'draft'">
                                            <i class="feather icon-edit"></i> Editar
                                        </button>

                                        <!-- Estado revocado -->
                                        <div v-if="certificate.status === 'revoked'" class="text-muted">
                                            <small>
                                                <i class="feather icon-x-circle"></i>
                                                Este certificado ha sido revocado
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones principales -->
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" @click="closeModal">
                    <i class="feather icon-x"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" @click="refreshCertificates">
                    <i class="feather icon-refresh-cw"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- Modal para editar certificado -->
        <div class="modal fade" id="edit-certificate-modal" tabindex="-1" v-if="editingCertificate">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Certificado</h5>
                        <button type="button" class="close" @click="closeEditModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateCertificate">
                            <div class="mb-3">
                                <label class="form-label">Fecha de finalización</label>
                                <el-date-picker
                                    v-model="editForm.completion_date"
                                    type="date"
                                    placeholder="Selecciona la fecha"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                    class="w-100">
                                </el-date-picker>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Plantilla</label>
                                <el-select v-model="editForm.template_id" class="w-100">
                                    <el-option
                                        v-for="template in availableTemplates"
                                        :key="template.id"
                                        :label="template.name"
                                        :value="template.id">
                                    </el-option>
                                </el-select>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="closeEditModal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary" :disabled="updatingCertificate">
                                    <span v-if="updatingCertificate">
                                        <i class="feather icon-loader rotating"></i> Actualizando...
                                    </span>
                                    <span v-else>
                                        Actualizar
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Componente de vista previa -->
        <certificate-preview 
            ref="certificatePreview"
            :certificate="selectedCertificate"
            @certificate-downloaded="onCertificateDownloaded">
        </certificate-preview>
    </div>
</template>

<script>
import moment from 'moment';
import CertificatePreview from './CertificatePreview.vue';

export default {
    name: 'CertificateViewer',
    components: {
        CertificatePreview
    },
    props: {
        course: {
            type: Object,
            required: true
        },
        certificates: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            loading: false,
            downloading: null,
            issuing: null,
            updatingCertificate: false,
            editingCertificate: null,
            selectedCertificate: null,
            editForm: {
                completion_date: '',
                template_id: ''
            },
            availableTemplates: []
        }
    },
    mounted() {
        this.loadTemplates();
    },
    methods: {
        formatDate(date) {
            if (!date) return 'N/A';
            return moment(date).format('DD/MM/YYYY');
        },

        getStatusText(status) {
            const statusMap = {
                'draft': 'Borrador',
                'issued': 'Emitido',
                'revoked': 'Revocado'
            };
            return statusMap[status] || status;
        },

        getStatusBadgeClass(status) {
            const classMap = {
                'draft': 'badge-warning',
                'issued': 'badge-success',
                'revoked': 'badge-danger'
            };
            return classMap[status] || 'badge-secondary';
        },

        async loadTemplates() {
            try {
                const response = await axios.get('/certificate-templates');
                this.availableTemplates = response.data;
            } catch (error) {
                console.error('Error al cargar plantillas:', error);
            }
        },

        previewCertificate(certificate) {
            this.selectedCertificate = certificate;
            this.$nextTick(() => {
                this.$refs.certificatePreview.showModal();
            });
        },

        async downloadCertificate(certificate) {
            if (certificate.status !== 'issued') {
                this.$message.warning('Solo puedes descargar certificados emitidos');
                return;
            }

            this.downloading = certificate.id;

            try {
                const response = await axios.get(
                    `/course-certificates/download/${certificate.certificate_code}`, 
                    { 
                        responseType: 'blob' 
                    }
                );

                // Crear un enlace temporal para descargar el archivo
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `certificado_${certificate.certificate_code}.pdf`);
                document.body.appendChild(link);
                link.click();
                
                // Limpiar
                link.remove();
                window.URL.revokeObjectURL(url);

                this.$emit('certificate-downloaded', certificate);

            } catch (error) {
                console.error('Error al descargar certificado:', error);
                this.$message.error('Error al descargar el certificado');
            } finally {
                this.downloading = null;
            }
        },

        async issueCertificate(certificate) {
            this.issuing = certificate.id;

            try {
                const response = await axios.post(`/course-certificates/${certificate.id}/issue`);
                
                if (response.status === 200) {
                    // Actualizar el certificado en la lista
                    const index = this.certificates.findIndex(c => c.id === certificate.id);
                    if (index !== -1) {
                        this.$set(this.certificates, index, response.data.certificate);
                    }

                    this.$message.success('Certificado emitido exitosamente');
                }

            } catch (error) {
                console.error('Error al emitir certificado:', error);
                
                if (error.response && error.response.data.message) {
                    this.$message.error(error.response.data.message);
                } else {
                    this.$message.error('Error al emitir el certificado');
                }
            } finally {
                this.issuing = null;
            }
        },

        editCertificate(certificate) {
            this.editingCertificate = certificate;
            this.editForm = {
                completion_date: certificate.completion_date.split('T')[0],
                template_id: certificate.template_id
            };
            $('#edit-certificate-modal').modal('show');
        },

        async updateCertificate() {
            this.updatingCertificate = true;

            try {
                const response = await axios.put(
                    `/course-certificates/${this.editingCertificate.id}`, 
                    this.editForm
                );

                if (response.status === 200) {
                    // Actualizar el certificado en la lista
                    const index = this.certificates.findIndex(c => c.id === this.editingCertificate.id);
                    if (index !== -1) {
                        this.$set(this.certificates, index, response.data);
                    }

                    this.$message.success('Certificado actualizado exitosamente');
                    this.closeEditModal();
                }

            } catch (error) {
                console.error('Error al actualizar certificado:', error);
                this.$message.error('Error al actualizar el certificado');
            } finally {
                this.updatingCertificate = false;
            }
        },

        closeEditModal() {
            this.editingCertificate = null;
            $('#edit-certificate-modal').modal('hide');
        },

        async refreshCertificates() {
            this.loading = true;
            try {
                const response = await axios.get('/course-certificates', {
                    params: { course_id: this.course.id }
                });
                
                // Emitir evento para actualizar los certificados en el componente padre
                this.$emit('certificates-updated', response.data.data);
                
            } catch (error) {
                console.error('Error al actualizar certificados:', error);
                this.$message.error('Error al actualizar los certificados');
            } finally {
                this.loading = false;
            }
        },

        onCertificateDownloaded(certificate) {
            this.$emit('certificate-downloaded', certificate);
        },

        closeModal() {
            this.$emit('close-modal');
        }
    }
}
</script>

<style scoped>
.certificate-viewer {
    max-width: 100%;
}

.rotating {
    animation: rotation 2s infinite linear;
}

@keyframes rotation {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(359deg);
    }
}

.empty-state {
    padding: 2rem;
}

.certificate-card {
    transition: box-shadow 0.3s ease;
}

.certificate-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.certificate-actions .btn {
    width: 100%;
}

.gap-2 > * + * {
    margin-top: 0.5rem;
}

.course-info {
    border-left: 4px solid #007bff;
}

.badge {
    font-size: 0.75rem;
}
</style>