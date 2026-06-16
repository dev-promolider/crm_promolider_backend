<template>
    <div class="certificate-preview">
        <!-- Modal para vista previa -->
        <div class="modal fade" id="certificate-preview-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="feather icon-eye"></i>
                            Vista Previa del Certificado
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div v-if="loading" class="text-center p-5">
                            <i class="feather icon-loader rotating"></i>
                            <p class="mt-2">Generando vista previa...</p>
                        </div>
                        
                        <div v-else-if="previewHtml" class="certificate-preview-container">
                            <!-- Barra de herramientas -->
                            <div class="preview-toolbar">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
                                    <div class="certificate-info">
                                        <small class="text-muted">
                                            <strong>Certificado:</strong> {{ certificate?.certificate_code }}
                                            <span class="mx-2">|</span>
                                            <strong>Estado:</strong> {{ getStatusText(certificate?.status) }}
                                        </small>
                                    </div>
                                    <div class="preview-actions">
                                        <button 
                                            class="btn btn-sm btn-outline-primary mr-2"
                                            @click="refreshPreview"
                                            :disabled="loading">
                                            <i class="feather icon-refresh-cw"></i>
                                            Actualizar
                                        </button>
                                        <button 
                                            class="btn btn-sm btn-success"
                                            @click="downloadCertificate"
                                            :disabled="!certificate || certificate.status !== 'issued' || downloading"
                                            v-if="certificate?.status === 'issued'">
                                            <i class="feather icon-download"></i>
                                            <span v-if="downloading">Descargando...</span>
                                            <span v-else>Descargar PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vista previa del certificado -->
                            <div class="preview-content">
                                <div class="certificate-frame">
                                    <div v-html="previewHtml" class="certificate-render"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center p-5">
                            <i class="feather icon-alert-circle text-warning" style="font-size: 2rem;"></i>
                            <h6 class="mt-3">No se pudo generar la vista previa</h6>
                            <p class="text-muted">Verifica que el certificado tenga una plantilla válida</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CertificatePreview',
    props: {
        certificate: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            loading: false,
            downloading: false,
            previewHtml: '',
            templateData: {}
        }
    },
    watch: {
        certificate: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.generatePreview();
                }
            }
        }
    },
    methods: {
        async generatePreview() {
            if (!this.certificate) return;
            
            this.loading = true;
            
            try {
                // Preparar datos para la plantilla
                this.prepareTemplateData();
                
                // Generar HTML procesado
                await this.processTemplate();
                
            } catch (error) {
                console.error('Error al generar vista previa:', error);
                this.$message.error('Error al generar la vista previa del certificado');
            } finally {
                this.loading = false;
            }
        },
        
        prepareTemplateData() {
            this.templateData = {
                recipient_name: this.certificate.user?.name || 'Nombre del Usuario',
                course_name: this.certificate.course?.title || 'Nombre del Curso',
                completion_date: this.formatDate(this.certificate.completion_date),
                certificate_code: this.certificate.certificate_code || 'CERT-XXXXXX',
                issue_date: this.certificate.issued_at ? this.formatDate(this.certificate.issued_at) : null,
                instructor_name: this.certificate.course?.instructor?.name || 'Instructor'
            };
            
            // Agregar datos personalizados si existen
            if (this.certificate.custom_data && typeof this.certificate.custom_data === 'object') {
                Object.assign(this.templateData, this.certificate.custom_data);
            }
        },
        
        async processTemplate() {
            try {
                // Llamar al backend para obtener la vista previa procesada
                const response = await axios.get(`/course-certificates/${this.certificate.id}/preview`);
                
                if (response.data && response.data.html_content) {
                    this.previewHtml = response.data.html_content;
                    this.templateData = response.data.template_data || {};
                } else {
                    // Fallback a plantilla local si el backend no responde
                    this.generateLocalTemplate();
                }
                
            } catch (error) {
                console.error('Error al obtener vista previa del backend:', error);
                // Fallback a plantilla local
                this.generateLocalTemplate();
            }
        },

        generateLocalTemplate() {
            let htmlContent = '';
            
            // Si tiene plantilla personalizada, usarla
            if (this.certificate.template?.html_template) {
                htmlContent = this.certificate.template.html_template;
                
                // Reemplazar variables de plantilla
                Object.keys(this.templateData).forEach(key => {
                    const regex = new RegExp(`{{${key}}}`, 'g');
                    htmlContent = htmlContent.replace(regex, this.templateData[key] || '');
                });
            } else {
                // Generar plantilla por defecto
                htmlContent = this.generateDefaultTemplate();
            }
            
            this.previewHtml = htmlContent;
        },
        
        generateDefaultTemplate() {
            return `
                <div style="
                    width: 794px;
                    height: 600px;
                    margin: 20px auto;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-align: center;
                    position: relative;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    border-radius: 10px;
                    overflow: hidden;
                ">
                    <!-- Código del certificado -->
                    <div style="
                        position: absolute;
                        top: 20px;
                        right: 20px;
                        font-size: 12px;
                        opacity: 0.7;
                        background: rgba(255,255,255,0.1);
                        padding: 5px 10px;
                        border-radius: 15px;
                    ">
                        ${this.templateData.certificate_code}
                    </div>
                    
                    <!-- Encabezado -->
                    <div style="padding: 60px 40px 40px; border-bottom: 3px solid rgba(255,255,255,0.3);">
                        <h1 style="
                            font-size: 48px;
                            font-weight: bold;
                            margin: 0 0 20px 0;
                            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                        ">CERTIFICADO</h1>
                        <p style="
                            font-size: 18px;
                            opacity: 0.9;
                            font-weight: 300;
                            margin: 0;
                        ">de Finalización de Curso</p>
                    </div>
                    
                    <!-- Cuerpo -->
                    <div style="padding: 40px; line-height: 1.8;">
                        <p style="font-size: 16px; margin: 20px 0;">Por medio del presente se certifica que</p>
                        
                        <div style="
                            font-size: 28px;
                            font-weight: bold;
                            margin: 20px 0;
                            padding: 15px;
                            border: 2px solid rgba(255,255,255,0.3);
                            border-radius: 10px;
                            background: rgba(255,255,255,0.1);
                        ">
                            ${this.templateData.recipient_name}
                        </div>
                        
                        <p style="font-size: 16px; margin: 20px 0;">ha completado satisfactoriamente el curso</p>
                        
                        <div style="
                            font-size: 20px;
                            font-weight: 600;
                            margin: 20px 0;
                            color: #ffd700;
                        ">
                            "${this.templateData.course_name}"
                        </div>
                        
                        <p style="font-size: 14px; margin: 20px 0;">
                            Finalizado el ${this.templateData.completion_date}
                        </p>
                        
                        ${this.templateData.issue_date ? `
                            <div style="font-size: 12px; margin-top: 20px; opacity: 0.8;">
                                Emitido el ${this.templateData.issue_date}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        },
        
        formatDate(date) {
            if (!date) return 'N/A';
            const dateObj = new Date(date);
            return dateObj.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        getStatusText(status) {
            const statusMap = {
                'draft': 'Borrador',
                'issued': 'Emitido',
                'revoked': 'Revocado'
            };
            return statusMap[status] || status;
        },
        
        async downloadCertificate() {
            if (!this.certificate || this.certificate.status !== 'issued') {
                this.$message.warning('Solo puedes descargar certificados emitidos');
                return;
            }

            this.downloading = true;

            try {
                const response = await axios.get(
                    `/course-certificates/download/${this.certificate.certificate_code}`, 
                    { responseType: 'blob' }
                );

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `certificado_${this.certificate.certificate_code}.pdf`);
                document.body.appendChild(link);
                link.click();
                
                link.remove();
                window.URL.revokeObjectURL(url);

                this.$message.success('Certificado descargado exitosamente');
                this.$emit('certificate-downloaded', this.certificate);

            } catch (error) {
                console.error('Error al descargar certificado:', error);
                this.$message.error('Error al descargar el certificado');
            } finally {
                this.downloading = false;
            }
        },
        
        async refreshPreview() {
            await this.generatePreview();
        },
        
        showModal() {
            $('#certificate-preview-modal').modal('show');
        },
        
        hideModal() {
            $('#certificate-preview-modal').modal('hide');
        }
    }
}
</script>

<style scoped>
.certificate-preview-container {
    max-height: 80vh;
    overflow-y: auto;
}

.preview-content {
    background: #f8f9fa;
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.certificate-frame {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    max-width: 100%;
    overflow: hidden;
}

.certificate-render {
    transform: scale(0.8);
    transform-origin: center;
    max-width: 100%;
}

.preview-toolbar {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
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

/* Responsividad para certificados */
@media (max-width: 768px) {
    .certificate-render {
        transform: scale(0.5);
    }
    
    .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }
    
    .preview-content {
        padding: 10px;
    }
}

/* Estilos para el HTML del certificado renderizado */
.certificate-render ::v-deep h1,
.certificate-render ::v-deep h2,
.certificate-render ::v-deep h3 {
    margin-top: 0;
}

.certificate-render ::v-deep .certificate {
    max-width: 100%;
    margin: 0 auto;
}
</style>