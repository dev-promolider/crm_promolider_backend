<template>
    <div>
        <section v-if="!initialLoading">
            <div class="row mb-3">
                <div class="col-12 col-md-4">
                    <label for="filter-type" class="form-label">Filtrar por tipo:</label>
                    <el-select id="filter-type" v-model="filterType" placeholder="Seleccionar tipo" clearable @change="applyFilter">
                        <el-option label="Todos" value=""></el-option>
                        <el-option label="Cursos" value="1"></el-option>
                        <el-option label="Libros" value="2"></el-option>
                    </el-select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="data-table-list-courses" class="table-hover-animation table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Curso</th>
                                    <th>Infoproducto</th>
                                    <th>Precio</th>
                                    <th>Fecha de registro</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(course, index) in this.filteredCourses" v-bind:key="course.id" v-bind:index="index">
                                    <td>
                                        <p style="width: 15px">
                                            {{ (index = index + 1) }}
                                        </p>
                                    </td>
                                    <td>
                                        <p style="width: 220px">
                                            {{ course.title }}
                                        </p>
                                    </td>
                                    <td>{{ course.infoproduct }}</td>
                                    <td>$ {{ course.price }}</td>
                                    <td>{{ moment(course.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                                    <td>
                                        <span class="badge badge-success" v-if="course.status === 2">Activo</span>
                                        <span class="badge badge-warning" v-else-if="course.status === 1">En
                                            espera</span>
                                        <span class="badge badge-warning" v-else-if="course.status === 3">Con
                                            observaciones</span>
                                        <span class="badge badge-warning" v-else-if="course.status === 4">Nueva
                                            revisión</span>
                                        <span class="badge badge-danger" v-else-if="course.status === 0">Inactivo</span>
                                        <span class="badge badge-secondary" v-else>Desconocido</span>
                                    </td>
                                    <td class="align-content-center">
                                        <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                                            @change="getOptionSelected(course)">
                                            <el-option v-for="item in getAvailableOptions(course)" :key="item.value"
                                                :label="item.label" :value="item.value"
                                                :disabled="item.value === '7' && !course.certificate">
                                            </el-option>
                                        </el-select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <custom-spinner v-else></custom-spinner>

        <custom-modal id="edit-course-modal" color="primary" size="large" v-if="isEditModalOpen && courseSelected">
            <template #title>Editar cursos <u> {{ courseSelected.title }} </u></template>
            <template #body>
                <form-course-edit :course="courseSelected" :categoriesList="categories"
                    :levelsList="levels"></form-course-edit>
            </template>
        </custom-modal>

        <custom-modal id="delete-course-modal" color="danger" v-if="isDeleteModalOpen && courseSelected">
            <template #title>Borrar curso <u> {{ courseSelected.title }} </u></template>
            <template #body>¿Seguro que desea eliminar este curso?</template>
            <template #footer>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" @click="deleteCourse(courseSelected.id)">
                    Eliminar
                </button>
            </template>
        </custom-modal>

        <!-- Modal para generar certificado - VERSIÓN CORREGIDA -->
        <custom-modal id="generate-certificate-modal" color="success" size="medium" v-if="isCertificateModalOpen && courseSelected && courseSelected.id && certificateTemplates.length > 0">
            <template #title>Generar Certificado - <u>{{ courseSelected.title }}</u></template>
            <template #body>
                <certificate-generator 
                    :key="`cert-gen-${courseSelected.id}-${certificateTemplates.length}`"
                    :course="courseSelected" 
                    :templates="certificateTemplates"
                    @certificate-generated="onCertificateGenerated"
                    @close-modal="closeCertificateModal">
                </certificate-generator>
            </template>
        </custom-modal>

        <!-- Agregar este modal de carga mientras se preparan los datos -->
        <custom-modal id="loading-certificate-modal" color="info" size="small" v-if="isPreparingCertificate">
            <template #title>Preparando certificado...</template>
            <template #body>
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando plantillas y datos del curso...</p>
                </div>
            </template>
        </custom-modal>

        <!-- Modal para mostrar certificados existentes -->
        <custom-modal id="view-certificates-modal" color="info" size="large" v-if="isCertificatesModalOpen && courseSelected">
            <template #title>Certificados - <u>{{ courseSelected.title }}</u></template>
            <template #body>
                <certificate-viewer 
                    :course="courseSelected" 
                    :certificates="userCertificates"
                    @close-modal="closeCertificatesModal"
                    @certificate-downloaded="onCertificateDownloaded">
                </certificate-viewer>
            </template>
        </custom-modal>

        <CoursePreview :courses="courseSelected" v-if="outerVisible" :outerVisible="outerVisible"
            @closeModalPreview="closeModalPreview" />
    </div>
</template>

<script>
import moment from 'moment';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CoursePreview from './CoursePreview.vue';
import CourseEdit from './CoursesEdit.vue';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import CertificateGenerator from './CertificateGenerator.vue';
import CertificateViewer from './CertificateViewer.vue';
import language from '../../api/traduction_es';

export default {
    props: {
        user: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            initialLoading: false,
            moment: moment,
            courses: [],
            filteredCourses: [],
            filterType: '',
            categories: [],
            levels: [],
            certificateTemplates: [],
            isPreparingCertificate: false,
            isEditModalOpen: false,
            isDeleteModalOpen: false,
            isCertificatesModalOpen: false,
            isCertificateModalOpen: false,
            userCertificates: [],
            outerVisible: false,
            options: [
                {
                    value: '1',
                    label: 'Módulos y clases',
                },
                {
                    value: '2',
                    label: 'Solicitar revisión',
                },
                {
                    value: '3',
                    label: 'Editar',
                },
                {
                    value: '4',
                    label: 'Eliminar',
                },
                {
                    value: '5',
                    label: 'Exámenes',
                },
                {
                    value: '6',
                    label: 'Dinámicas',
                },
                {
                    value: '7',
                    label: 'Ver certificados',
                },
                {
                    value: '8',
                    label: 'Previsualizar curso',
                },
                {
                    value: '9',
                    label: 'Generar certificado',
                },
                {
                    value: '10',
                    label: 'Lista de Alumnos',
                },
                {
                    value: '11',
                    label: 'Gestionar contenido',
                },
            ],
            optionSelected: '',
            courseSelected: {},
            url: process.env.MIX_FRONTEND_APP_URL + 'redirect/',
            bookFile: null,
            isUploadingBook: false,
            // Para AddBookModal
            loadingBook: false,
            isAddBookModalOpen: false,
            bookFiles: null,
        };
    },
    components: {
        'custom-modal': ModalComponent,
        'form-course-edit': CourseEdit,
        'custom-spinner': CustomSpinner,
        'certificate-generator': CertificateGenerator,
        'certificate-viewer': CertificateViewer,
        CoursePreview,
    },

    mounted() {
        this.listCourses();
        this.listCategories();
        this.listLevels();

        // Cargar plantillas con manejo de errores mejorado
        this.loadCertificateTemplates();
    },

    methods: {
        loadDataTable() {
            this.$nextTick(function () {
                $('#data-table-list-courses').DataTable(language);
            });
        },

        listCourses: function () {
            this.initialLoading = true;
            axios.get(`/course/${this.user.id}/courseList`).then((response) => {
                this.courses = response.data.data;
                this.applyFilter();
                this.initialLoading = false;
                this.loadDataTable();
            });
        },

        applyFilter: function () {
            if (this.filterType === '') {
                this.filteredCourses = this.courses;
            } else {
                this.filteredCourses = this.courses.filter(course => {
                    return course.product_type_id == this.filterType;
                });
            }
        },

        listCategories() {
            axios
                .get(`/course/categories`)
                .then((response) => {
                    if (response.data.error) {
                        this.message = 'Sin categorias registrados';
                    } else {
                        Object.keys(response.data).map((key) => {
                            this.categories.push(response.data[key]);
                        });
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        listLevels() {
            axios
                .get(`/course/levels`)
                .then((response) => {
                    if (response.data.error) {
                        this.message = 'Sin niveles registrados';
                    } else {
                        Object.keys(response.data).map((key) => {
                            this.levels.push(response.data[key]);
                        });
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        // Cargar plantillas de certificados disponibles
        loadCertificateTemplates() {
            axios.get('/certificate-templates')
                .then((response) => {
                    let templates = [];

                    // Manejar diferentes formatos de respuesta
                    if (Array.isArray(response.data)) {
                        templates = response.data;
                    } else if (response.data && Array.isArray(response.data.data)) {
                        templates = response.data.data;
                    } else if (response.data && response.data.templates && Array.isArray(response.data.templates)) {
                        templates = response.data.templates;
                    } else {
                        templates = [];
                    }

                    // Filtrar solo plantillas activas
                    this.certificateTemplates = templates.filter(template => {
                        return template.is_active === true || template.is_active === 1;
                    });

                    if (this.certificateTemplates.length === 0) {
                        this.$message.info('No hay plantillas de certificados disponibles en este momento');
                    }
                })
                .catch((error) => {
                    console.error('=== ERROR CARGANDO PLANTILLAS ===');
                    console.error('Error:', error);
                    console.error('Response:', error.response);
                    console.error('Status:', error.response?.status);
                    console.error('Data:', error.response?.data);
                    console.error('Headers:', error.response?.headers);

                    this.certificateTemplates = [];

                    // Manejar diferentes tipos de errores
                    if (!error.response) {
                        // Error de red
                        console.error('Error de red o CORS');
                        this.$message.error('Error de conexión. Verifica tu conexión a internet.');
                    } else {
                        switch (error.response.status) {
                            case 404:
                                console.error('Endpoint no encontrado');
                                this.$message.warning('El servicio de plantillas no está disponible. Contacta al administrador.');
                                break;
                            case 401:
                                console.error('No autorizado');
                                this.$message.error('No tienes permisos para acceder a las plantillas.');
                                break;
                            case 403:
                                console.error('Prohibido');
                                this.$message.error('Acceso denegado a las plantillas.');
                                break;
                            case 500:
                                console.error('Error interno del servidor');
                                this.$message.error('Error interno del servidor. Intenta más tarde.');
                                break;
                            default:
                                console.error(`Error HTTP ${error.response.status}`);
                                this.$message.error(`Error del servidor (${error.response.status}). Intenta más tarde.`);
                        }
                    }
                });
        },

        // Método para verificar si las plantillas están disponibles
        checkTemplatesAvailable() {
            return this.certificateTemplates && this.certificateTemplates.length > 0;
        },
        
        // Método para recargar plantillas manualmente
        reloadCertificateTemplates() {
            this.certificateTemplates = [];
            this.loadCertificateTemplates();
        },

        // Cargar certificados del usuario para un curso específico
        loadUserCertificates(courseId) {
            axios.get('/course-certificates', {
                params: { course_id: courseId }
            })
                .then((response) => {
                    this.userCertificates = response.data.data;
                })
                .catch((error) => {
                    console.error('Error al cargar certificados:', error);
                    this.$message.error('Error al cargar los certificados');
                });
        },

        async deleteCourse(course_id) {
            try {
                const { data } = await axios.delete(`/course/delete/${course_id}`);

                if (data.status === 'inactive') {
                    this.$message({
                        message: data.message,
                        type: 'warning',
                        duration: 5000,
                    });
                } else if (data.status === 'ok') {
                    this.$message({
                        message: data.message,
                        type: 'success',
                        duration: 3000,
                    });
                }

                if (data.courses) {
                    this.courses = data.courses;
                    $('#data-table-list-courses').DataTable().destroy();
                    this.$nextTick(() => {
                        this.loadDataTable();
                    });
                }

                $('#delete-course-modal').modal('hide');
            } catch (error) {
                this.$message({
                    message: 'Error al procesar la solicitud. Por favor, inténtelo de nuevo.',
                    type: 'error',
                    duration: 5000,
                });
                console.error('Error:', error);
            }
        },

        getOptionSelected(course) {
            let option = this.optionSelected;
            this.closeCourseActionModals();
        
            switch (option) {
                case '1':
                    document.location.href = `/course/module/${course.id}/editModule`;
                    break;
                case '2':
                    this.sendRequest(course.id);
                    this.optionSelected = '';
                    break;
                case '3':
                    this.courseSelected = course;
                    this.isEditModalOpen = true;
                    this.$nextTick(() => $('#edit-course-modal').modal('show'));
                    this.optionSelected = '';
                    break;
                case '4':
                    this.courseSelected = course;
                    this.isDeleteModalOpen = true;
                    this.$nextTick(() => $('#delete-course-modal').modal('show'));
                    this.optionSelected = '';
                    break;
                case '5':
                    document.location.href = `/course/exam/${course.id}/create`;
                    break;
                case '6':
                    document.location.href = `/course/game/${course.id}/`;
                    break;
                case '7':
                    // Ver certificados existentes
                    this.courseSelected = course;
                    this.isCertificatesModalOpen = true;
                    this.loadUserCertificates(course.id);
                    this.$nextTick(() => $('#view-certificates-modal').modal('show'));
                    this.optionSelected = '';
                    break;
                case '8':
                    this.courseSelected = course;
                    this.openModalPreview();
                    break;
                case '9':
                    // Generar nuevo certificado - VERSIÓN CORREGIDA
                    this.handleCertificateGeneration(course);
                    break;
                case '10':
                    document.location.href = `/course/students-list/${course.id}`;
                    break;
                case '11':
                    // Agregar libro
                    document.location.href = `/course/${course.id}/book-content`;
                    //this.openAddBookModal(course);
                    break;
                default:
                    break;
            }
            this.optionSelected = '';
        },

        // Función para abrir el modal de agregar libro y cargar los archivos existentes
        async openAddBookModal(course) {
            this.courseSelected = course;
            this.isAddBookModalOpen = true;
            this.loadingBook = true;
            this.bookFiles = null;
            this.optionSelected = '';

            try {
                const response = await axios.get(`/course/${course.id}/book-files`);
                this.bookFiles = response.data.data;
                console.log('Archivos del libro cargados:', this.bookFiles);
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar los archivos del libro. Por favor, inténtelo de nuevo.');
                this.isAddBookModalOpen = false;
            } finally {
                this.loadingBook = false;
            }
        },

        async handleCertificateGeneration(course) {
            this.isCertificateModalOpen = false;

            // Validaciones iniciales
            if (!course.certificate) {
                this.$message.warning('Este curso no tiene la opción de certificado habilitada.');
                return;
            }

            if (!course.id) {
                this.$message.error('Error: El curso no tiene un ID válido');
                return;
            }

            // Mostrar modal de carga
            this.isPreparingCertificate = true;
            $('#loading-certificate-modal').modal('show');

            try {
                // Si no hay plantillas, intentar cargarlas
                if (this.certificateTemplates.length === 0) {
                    await this.loadCertificateTemplatesAsync();
                }

                // Verificar que ahora sí tenemos plantillas
                if (this.certificateTemplates.length === 0) {
                    throw new Error('No se pudieron cargar las plantillas');
                }

                // Preparar datos del curso
                this.courseSelected = {
                    ...course,
                    // Asegurar que todos los campos necesarios estén presentes
                    id: parseInt(course.id),
                    title: course.title || 'Sin título',
                    certificate: Boolean(course.certificate)
                };

                // Cerrar modal de carga
                $('#loading-certificate-modal').modal('hide');
                this.isPreparingCertificate = false;

                // Esperar un poco para que se cierre el modal anterior
                await this.sleep(300);

                // Abrir modal de generación
                this.isCertificateModalOpen = true;
                await this.$nextTick();
                $('#generate-certificate-modal').modal('show');

            } catch (error) {
                console.error('Error preparando certificado:', error);

                // Cerrar modal de carga
                $('#loading-certificate-modal').modal('hide');
                this.isPreparingCertificate = false;

                // Mostrar error al usuario
                this.$message.error('Error al preparar la generación del certificado: ' + error.message);
            }
        },

        loadCertificateTemplatesAsync() {
            return new Promise((resolve, reject) => {
                axios.get('/certificate-templates')
                    .then((response) => {
                        if (response.data && Array.isArray(response.data)) {
                            this.certificateTemplates = response.data;
                            resolve(this.certificateTemplates);
                        } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
                            this.certificateTemplates = response.data.data;
                            resolve(this.certificateTemplates);
                        } else {
                            this.certificateTemplates = [];
                            reject(new Error('Formato de respuesta inesperado'));
                        }
                    })
                    .catch((error) => {
                        console.error('Error al cargar plantillas:', error);
                        this.certificateTemplates = [];

                        if (error.response?.status === 404) {
                            reject(new Error('El endpoint de plantillas no está disponible'));
                        } else if (error.response?.status === 500) {
                            reject(new Error('Error interno del servidor al cargar plantillas'));
                        } else {
                            reject(new Error('Error de conexión al cargar plantillas'));
                        }
                    });
            });
        },

        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },

        getAvailableOptions(course) {
            let options = this.options.map(option => ({ ...option }));
            
            // Si es un libro, hacer ajustes específicos
            if (course.product_type_id == 2) {
                // Excluir módulos, exámenes, previsualizacion y dinámicas, pero mantener "Gestionar contenido"
                options = options.filter(option => {
                    if (option.value === '1' || option.value === '5' || option.value === '6' || option.value === '8') {
                        return false;
                    }
                    return true;
                });
                
                // Cambiar nombres de opciones para libros
                options = options.map(option => {
                    if (option.value === '10') {
                        return { ...option, label: 'Lista de Compradores' };
                    }
                    return option;
                });
            } else {
                // Para cursos, excluir "Gestionar contenido"
                options = options.filter(option => option.value !== '11');
            }
            
            // Solo mostrar opciones de certificado si el curso tiene certificado habilitado
            options = options.filter(option => {
                if (option.value === '7' || option.value === '9') {
                    return course.certificate;
                }
                return true;
            });
            
            return options;
        },

        closeCourseActionModals() {
            this.isEditModalOpen = false;
            this.isDeleteModalOpen = false;
            this.isCertificatesModalOpen = false;
            this.isCertificateModalOpen = false;
            this.isAddBookModalOpen = false;
        },

        closeModalPreview() {
            this.outerVisible = false;
        },

        openModalPreview() {
            this.outerVisible = true;
        },

        closeCertificateModal() {
            $('#generate-certificate-modal').modal('hide');
            this.isCertificateModalOpen = false;
        },

        closeCertificatesModal() {
            $('#view-certificates-modal').modal('hide');
            this.isCertificatesModalOpen = false;
        },

        onCertificateGenerated(certificate) {
            this.$message.success('Certificado generado exitosamente');
            this.closeCertificateModal();
            // Opcional: recargar los certificados si es necesario
        },

        onCertificateDownloaded() {
            this.$message.success('Certificado descargado exitosamente');
        },

        async sendRequest(id) {
            const data = await axios.post(`/course/${id}/sendRequest`);

            switch (data.data.data) {
                case 'ok':
                    this.$message.success('Curso enviado a revisión correctamente');
                    break;
                case 'request':
                    this.$message.warning('Ya se ha enviado el curso para su revisión');
                    break;
                case 'empty':
                    this.$message.warning('El curso debe tener al menos un módulo y una clase');
                    break;
                case 'misconfigured':
                    this.$message.warning('Configure la entrega del certificado');
                    break;
                case 'signaturetemplate':
                    this.$message.warning('Seleccione la plantilla y firma para el certificado');
                    break;
                default:
                    this.$message.warning('Error al validar datos');
                    break;
            }
            this.listCourses();
        },

        onBookFileSelected(event) {
            const file = event.target.files[0];
            
            if (!file) {
                this.bookFile = null;
                return;
            }

            // Validar extensión
            const allowedExtensions = ['pdf', 'epub'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                this.$message.error('Solo se permiten archivos PDF o EPUB');
                event.target.value = '';
                this.bookFile = null;
                return;
            }

            // Validar tamaño (250MB = 250 * 1024 * 1024 bytes)
            const maxSize = 250 * 1024 * 1024;
            if (file.size > maxSize) {
                this.$message.error(`El archivo no puede exceder 250MB. Tamaño actual: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
                event.target.value = '';
                this.bookFile = null;
                return;
            }

            this.bookFile = file;
        },

        async uploadBookFile() {
            if (!this.bookFile || !this.courseSelected.id) {
                this.$message.error('Error: archivo o curso inválido');
                return;
            }

            this.isUploadingBook = true;

            try {
                const formData = new FormData();
                formData.append('file', this.bookFile);
                formData.append('course_id', this.courseSelected.id);

                const response = await axios.post(
                    `/course/${this.courseSelected.id}/store-book-file`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    }
                );

                if (response.status === 200) {
                    this.$message.success('Archivo de libro guardado exitosamente');
                    this.closeAddBookModal();
                    this.listCourses();
                } else {
                    this.$message.error('Error al guardar el archivo');
                }
            } catch (error) {
                console.error('Error al subir archivo:', error);
                
                if (error.response?.status === 413) {
                    this.$message.error('El archivo es demasiado grande. Máximo: 250MB');
                } else if (error.response?.data?.message) {
                    this.$message.error(error.response.data.message);
                } else {
                    this.$message.error('Error al guardar el archivo del libro');
                }
            } finally {
                this.isUploadingBook = false;
            }
        },

        closeAddBookModal() {
            this.isAddBookModalOpen = false;
            this.bookFile = null;
        },
    },
};
</script>

<style>
.el-select {
    width: 80%;
}

.el-select .el-input .el-select__caret::before {
    content: '\e794';
}

#customize_select {
    background-color: #ffffff !important;
    border: none !important;
    width: 0px !important;
    font-size: 0px !important;
    justify-content: center;
    text-align: center;
    padding: 0 !important;
}
</style>
