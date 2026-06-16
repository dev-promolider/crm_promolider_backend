<template>
    <div class="certificate-generator">
        <form @submit.prevent="generateCertificate">
            <div class="row">
                <!-- Selección de plantilla -->
                <div class="col-12 mb-3">
                    <label for="template_id" class="form-label">
                        <strong>Plantilla de certificado *</strong>
                    </label>
                    <el-select 
                        v-model="form.template_id" 
                        placeholder="Selecciona una plantilla"
                        class="w-100"
                        size="medium"
                        :disabled="templates.length === 0">
                        <el-option
                            v-for="template in templates"
                            :key="template.id"
                            :label="template.name"
                            :value="template.id">
                        </el-option>
                    </el-select>

                    <!-- Vista previa de la plantilla seleccionada -->
                    <div v-if="selectedTemplate" class="text-center my-3">
                      <div class="template-preview-container">
                        <img 
                          :src="selectedTemplate.preview_image_url" 
                          :alt="selectedTemplate.name" 
                          class="template-preview-image"
                          @click="openImageInNewTab"
                          @error="handleImageError"
                        >
                        <div class="zoom-overlay" @click="openImageInNewTab">
                          <i class="feather icon-external-link"></i>
                          <span>Click para ver en tamaño completo</span>
                        </div>
                      </div>
                      <p class="mt-2 text-muted">{{ selectedTemplate.name }}</p>
                    </div>

                    <small class="text-muted" v-if="templates.length === 0">
                        No hay plantillas disponibles. Contacta al administrador.
                    </small>
                    <small class="text-muted" v-else>
                        Selecciona la plantilla que se utilizará para generar el certificado
                    </small>
                </div>

                <!-- Subida de firma digital -->
                <div class="col-12 mb-3">
                    <label class="form-label">
                        <strong>Firma digital del instructor</strong>
                    </label>
                    <div class="card">
                        <div class="card-body">
                            <div v-if="currentInstructorSignature" class="mb-3">
                                <p class="text-success mb-2">
                                    <i class="feather icon-check-circle"></i> 
                                    Ya tienes una firma cargada
                                </p>
                                <img :src="currentInstructorSignature" 
                                     alt="Firma actual" 
                                     style="max-height: 60px; border: 1px solid #ddd; padding: 5px;">
                                <div class="mt-2">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            @click="showSignatureUpload = true">
                                        Cambiar firma
                                    </button>
                                </div>
                            </div>

                            <div v-if="!currentInstructorSignature || showSignatureUpload">
                                <input type="file" 
                                       ref="signatureFile"
                                       @change="handleSignatureFile"
                                       accept="image/png,image/jpg,image/jpeg"
                                       class="form-control">
                                <small class="text-muted">
                                    Sube una imagen PNG o JPG de tu firma (máximo 2MB)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fecha de finalización -->
                <div class="col-md-6 mb-3">
                    <label for="completion_date" class="form-label">
                        <strong>Fecha de finalización *</strong>
                    </label>
                    <el-date-picker
                      v-model="form.completion_date"
                      type="date"
                      placeholder="Selecciona la fecha"
                      format="dd/MM/yyyy"
                      value-format="yyyy-MM-dd"
                      class="w-100"
                      size="medium">
                    </el-date-picker>
                </div>

                <!-- Auto-emitir certificado -->
                <div class="col-md-6 mb-3 d-flex align-items-center">
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            v-model="form.auto_issue" 
                            id="auto_issue">
                        <label class="form-check-label" for="auto_issue">
                            <strong>Emitir automáticamente</strong>
                        </label>
                        <small class="form-text text-muted d-block">
                            El certificado se emitirá inmediatamente y estará disponible para descarga
                        </small>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" @click="closeModal">
                    Cancelar
                </button>
                <button 
                    type="submit" 
                    class="btn btn-success" 
                    :disabled="!canGenerate || loading">
                    <span v-if="loading">
                        <i class="feather icon-loader rotating"></i> Generando...
                    </span>
                    <span v-else>
                        <i class="feather icon-award"></i> Generar Certificado
                    </span>
                </button>
            </div>
        </form>

        <!-- Alertas de validación -->
        <div v-if="errors.length > 0" class="alert alert-danger mt-3">
            <h6>Por favor, corrige los siguientes errores:</h6>
            <ul class="mb-0">
                <li v-for="error in errors" :key="error">{{ error }}</li>
            </ul>
        </div>

        <!-- Alerta de course/templates faltantes -->
        <div v-if="!course || !course.id" class="alert alert-warning mt-3">
            <strong>Advertencia:</strong> No se pudo cargar la información del curso. Intenta recargar la página.
        </div>

        <div v-if="templates.length === 0" class="alert alert-warning mt-3">
            <strong>Advertencia:</strong> No hay plantillas de certificados disponibles. Contacta al administrador del sistema.
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    name: 'CertificateGenerator',
    props: {
        course: {
            type: Object,
            required: true,
            default: () => ({})
        },
        templates: {
            type: Array,
            default: () => []
        }
    },
    data() {
      return {
        loading: false,
        errors: [],
        showDebug: process.env.NODE_ENV === 'development',
        signatureFile: null,
        currentInstructorSignature: null,
        showSignatureUpload: false,
        uploadingSignature: false,
        form: {
          template_id: null,
          course_id: null,
          completion_date: null, 
          auto_issue: true,
          custom_data: {}
        },
        customFields: [
          { key: '', value: '' }
        ]
      }
    },
    computed: {
        canGenerate() {
            return this.form.template_id && 
                   this.form.completion_date && 
                   this.form.course_id && 
                   !this.loading &&
                   this.templates.length > 0;
        },
        selectedTemplate() {
            return this.templates.find(t => t.id === this.form.template_id) || null;
        },
        debugData() {
            return {
                form: this.form,
                customFields: this.customFields,
                templates: this.templates,
                course: this.course,
                propsValidation: {
                    courseExists: !!this.course,
                    courseHasId: !!(this.course && this.course.id),
                    templatesCount: this.templates.length
                }
            };
        }
    },
    methods: {
        // 👈 Método para abrir imagen en nueva pestaña
        openImageInNewTab() {
            if (this.selectedTemplate && this.selectedTemplate.preview_image_url) {
                window.open(this.selectedTemplate.preview_image_url, '_blank');
            }
        },

        // Método para manejar errores de carga de imagen
        handleImageError(event) {
            console.error('Error cargando imagen:', event.target.src);
            // Opcionalmente puedes mostrar una imagen de placeholder
            // event.target.src = '/path/to/placeholder-image.jpg';
        },

        addCustomField() {
            this.customFields.push({ key: '', value: '' });
        },
        
        removeCustomField(index) {
            if (this.customFields.length > 1) {
                this.customFields.splice(index, 1);
            }
        },

        formatDate(date) {
            if (!date) return '';
            return moment(date).format('DD/MM/YYYY');
        },

        validateForm() {
            this.errors = [];

            if (!this.course) {
                this.errors.push('Error: No se ha proporcionado información del curso');
                return false;
            }

            if (!this.course.id) {
                this.errors.push('Error: El curso no tiene un ID válido');
                return false;
            }

            if (this.templates.length === 0) {
                this.errors.push('No hay plantillas de certificados disponibles');
                return false;
            }

            if (!this.form.template_id) {
                this.errors.push('Debes seleccionar una plantilla de certificado');
            }

            if (!this.form.completion_date) {
                this.errors.push('Debes seleccionar una fecha de finalización');
            } else {
                const completionDate = moment(this.form.completion_date);
                const today = moment();
                
                if (completionDate.isAfter(today, 'day')) {
                    this.errors.push('La fecha de finalización no puede ser futura');
                }
            }

            return this.errors.length === 0;
        },

        buildCustomData() {
            const customData = {};
            
            this.customFields.forEach(field => {
                if (field.key && field.key.trim() && field.value && field.value.trim()) {
                    customData[field.key.trim()] = field.value.trim();
                }
            });

            return Object.keys(customData).length > 0 ? customData : {};
        },

        async generateCertificate() {
            if (!this.validateForm()) {
                return;
            }

            this.loading = true;

            try {
                // Preparar datos personalizados
                const customData = this.buildCustomData();
                
                const payload = {
                    template_id: parseInt(this.form.template_id),
                    course_id: parseInt(this.form.course_id),
                    completion_date: this.form.completion_date,
                    auto_issue: Boolean(this.form.auto_issue),
                    custom_data: customData
                };

                const response = await axios.post('/course-certificates', payload);

                if (response.status === 201) {
                    const certificate = response.data;
                    
                    this.$message.success(
                        certificate.status === 'issued' 
                            ? 'Certificado generado y emitido exitosamente'
                            : 'Certificado generado como borrador'
                    );

                    this.$emit('certificate-generated', certificate);
                } else {
                    throw new Error('Error inesperado al generar el certificado');
                }

            } catch (error) {
                console.error('Error al generar certificado:', error);
                
                if (error.response && error.response.status === 422) {
                    const errorData = error.response.data;
                    
                    if (errorData.errors) {
                        const validationErrors = [];
                        Object.keys(errorData.errors).forEach(field => {
                            errorData.errors[field].forEach(msg => {
                                validationErrors.push(`${field}: ${msg}`);
                            });
                        });
                        this.errors = validationErrors;
                    } else if (errorData.message) {
                        this.$message.error(errorData.message);
                    } else {
                        this.$message.error('Ya tienes un certificado para este curso');
                    }
                } else if (error.response && error.response.status === 404) {
                    this.$message.error('Recurso no encontrado. Verifica que el curso y la plantilla existan.');
                } else {
                    this.$message.error('Error al generar el certificado. Inténtalo de nuevo.');
                }
            } finally {
                this.loading = false;
            }
        },

        closeModal() {
            this.$emit('close-modal');
        },

        initializeCourseId() {
            if (this.course && this.course.id) {
                this.form.course_id = parseInt(this.course.id);
            }
        },

        handleSignatureFile(event) {
            this.signatureFile = event.target.files[0];
            if (this.signatureFile) {
                this.uploadInstructorSignature();
            }
        },

        async uploadInstructorSignature() {
            if (!this.signatureFile) return;

            this.uploadingSignature = true;
            const formData = new FormData();
            formData.append('signature', this.signatureFile);

            try {
                const response = await axios.post(
                    `/instructor-signature/${this.course.id}`, 
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                );

                this.currentInstructorSignature = response.data.signature_url;
                this.showSignatureUpload = false;
                this.$message.success('Firma subida exitosamente');
            } catch (error) {
                this.$message.error('Error al subir la firma');
            } finally {
                this.uploadingSignature = false;
            }
        },

        async loadCurrentSignature() {
            if (!this.course || !this.course.id) {
                return;
            }

            try {
                const response = await axios.get(`/courses/${this.course.id}`);
                if (response.data.instructor_signature_url) {
                    this.currentInstructorSignature = response.data.instructor_signature_url;
                }
            } catch (error) {
                console.error('Error cargando firma actual');
            }
        }
    },
    
    created() {
        // Inicializar en created() antes del mounted()
        this.initializeCourseId();
    },

    mounted() {
        // No mostrar errores en mounted, solo logs informativos
        this.loadCurrentSignature();
        if (!this.course) {
        } else if (!this.course.id) {
        }
        
        if (!this.templates || this.templates.length === 0) {
        }
    },

    watch: {
        course: {
            immediate: true,
            deep: true,
            handler(newCourse, oldCourse) {
                if (newCourse && newCourse.id && newCourse.id !== this.form.course_id) {
                    this.form.course_id = parseInt(newCourse.id);
                }
            }
        },
        templates: {
            immediate: true,
            handler(newTemplates) {
                if (newTemplates && newTemplates.length > 0) {
                    // Auto-seleccionar la primera plantilla si no hay ninguna seleccionada
                    if (!this.form.template_id) {
                        this.form.template_id = newTemplates[0].id;
                    }
                }
            }
        }
    }
}
</script>

<style scoped>
.certificate-generator {
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

.form-label {
    margin-bottom: 0.5rem;
}

.card {
    border: 1px solid #e3ebf0;
}

.alert {
    border-radius: 8px;
}

.btn {
    border-radius: 6px;
}

.form-control {
    border-radius: 6px;
}

pre {
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
}

/* 👈 Nuevos estilos para la vista previa de imagen */
.template-preview-container {
    position: relative;
    display: inline-block;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: transform 0.2s ease;
}

.template-preview-container:hover {
    transform: scale(1.02);
}

.template-preview-image {
    max-width: 300px;
    height: auto;
    display: block;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.zoom-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    cursor: pointer;
    font-size: 14px;
}

.template-preview-container:hover .zoom-overlay {
    opacity: 1;
}

.zoom-overlay i {
    font-size: 24px;
    margin-bottom: 8px;
}

.image-preview-content {
    padding: 20px;
    max-height: 70vh;
    overflow: auto;
}

.preview-modal-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Personalización del modal de Element UI */
.image-preview-dialog .el-dialog {
    border-radius: 12px;
}

.image-preview-dialog .el-dialog__header {
    border-bottom: 1px solid #f0f0f0;
    padding: 20px 24px 16px;
}

.image-preview-dialog .el-dialog__body {
    padding: 0;
}

.dialog-footer {
    padding: 16px 24px;
    border-top: 1px solid #f0f0f0;
}
</style>
