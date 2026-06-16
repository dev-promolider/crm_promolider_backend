<template>
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="d-inline">
                    Lista de Modulos <span> ({{ this.mutableModules.length }}) </span>
                </h3>
                <button type="button" class="btn btn-success" @click="openModalPreview()">
                    Previsualizar
                </button>
                <button type="button" class="btn btn-link" @click.prevent="openModalCertificate()"
                    v-if="courses.certificate == 1">
                    Certificado
                </button>
                <button type="button" class="btn btn-link" @click="showInputModule">
                    (+) Agregar Módulo
                </button>
                <button type="button" class="btn btn-link" @click="openModal()">Cambiar orden</button>
                <button type="button" class="btn btn-link" @click="sendRequest(courses.id)"
                    v-if="courses.certificate == 0">
                    Solicitar Revisión
                </button>
            </div>

            <div class="card-body">
                <ol class="list-group">
                    <div v-if="this.mutableModules.length === 0" class="text-center mt-3 mb-3">
                        <h4 class="d-inline">
                            <span> {{ message }} </span>
                        </h4>
                    </div>

                    <li class="list-group-item" v-if="showDialogAddModule == true">
                        <h3 class="d-inline">Módulo {{ this.mutableModules.length + 1 }}:</h3>
                        <input placeholder="Ingrese el título del módulo" @keyup.enter="storeModule" type="text"
                            class="form-control d-inline input-module-title" aria-label="Default"
                            aria-describedby="inputGroup-sizing-default" v-model="title" />
                        <button type="button" class="btn btn-primary" @click="storeModule">Guardar</button>
                    </li>

                    <div v-if="this.mutableModules.length > 0">
                        <course-module-item ref="moduleitems" @delete="showDeleteModal" @addClass="showAddClassModal"
                            @deleteClass="showDeleteClassModal" @editClass="showEditClassModal"
                            v-for="(module, index) in this.mutableModules" :key="module.id" :module="module"
                            :course="courses" :index="index">
                        </course-module-item>
                    </div>
                </ol>
            </div>
        </div>

        <custom-modal id="delete-module-modal" color="danger">
            <template #title>Borrar módulo <u> {{ mutableModule.name }} </u></template>
            <template #body>¿Seguro que desea eliminar el módulo?</template>
            <template #footer>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" @click="deleteModule">Eliminar</button>
            </template>
        </custom-modal>

        <custom-modal id="create-class-modal" size="large" color="primary">
            <template #title>Módulo {{ mutableModule.name }} / Crear Clase</template>
            <template #body>
                <form-add-class :module-id="mutableModule.id"></form-add-class>
            </template>
        </custom-modal>

        <custom-modal id="delete-class-modal" color="danger">
            <template #title>Borrar clase <u> {{ mutableClass.name }} </u></template>
            <template #body>¿Seguro que desea eliminar la clase?</template>
            <template #footer>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" @click="deleteClass">Eliminar</button>
            </template>
        </custom-modal>

        <custom-modal id="edit-class-modal" size="large" color="primary">
            <template #close>
                <button type="button" class="close" @click="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </template>

            <template #title>
                <h3>
                    {{ mutableClass.has_video ? 'Editar Clase' : 'Agregar Video' }} | {{ mutableClass.name }}
                </h3>
            </template>

            <template #body>
                <class-edit :observations="observations" :clas="mutableClass" :resources="mutableResources"
                    :video="mutableVideo">
                </class-edit>
            </template>
        </custom-modal>

        <ConfigureCertificate :course="courses" v-if="visibleConfigure" :visibleConfigure="visibleConfigure"
            @closeModalCertificate="closeModalCertificate" />

        <CoursePreview :courses="courses" v-if="outerVisible" :outerVisible="outerVisible"
            @closeModalPreview="closeModalPreview" />

        <OrderCourse :courses="courses" v-if="orderModal" :orderModal="orderModal" @closeOrderModal="closeOrderModal" />
    </div>
</template>

<script>
import CourseModuleItem from './CourseModuleItem.vue';
import ModalComponent from '@/components/common/ModalComponent.vue';
import ConfigureCertificate from './Certificate.vue';
import CoursePreview from '../CoursePreview.vue';
import OrderCourse from '../OrderCourse.vue';
import AddClass from './classes/addClass.vue';
import EditClass from './classes/editClass.vue';
import api from '../../../api/api';
import draggable from 'vuedraggable';

export default {
    props: {
        modules: {
            type: Array,
            required: true,
        },
        courses: {
            type: Object,
            required: true,
        },
    },
    components: {
        CourseModuleItem,
        'custom-modal': ModalComponent,
        'form-add-class': AddClass,
        'class-edit': EditClass,
        draggable: draggable,
        ConfigureCertificate,
        CoursePreview,
        OrderCourse,
    },
    data() {
        return {
            message: 'Este curso no cuenta con módulos',
            showDialogAddModule: false,
            title: null,
            observations: [],
            mutableModules: this.modules,
            mutableModule: {},
            outerVisible: false,
            visibleConfigure: false,
            mutableClass: {},
            mutableResources: [],
            mutableVideo: [],
            orderModal: false,
            configCertificate: [],
            tabPosition: 'left',
        };
    },
    watch: {
        mutableClass: async function (value) {
            this.mutableResources = [];
            this.mutableVideo = [];
            const { data } = await axios.get(`/course/module/class/${value.id}/details`);
            const { resources, video } = await data.data;

            const objVideo = {
                name: video[0].filename,
                url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${video[0].path}`,
            };

            resources.forEach(async (element) => {
                let obj = await {
                    name: element.filename,
                    url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${element.resource_file}`,
                };
                this.mutableResources.push(obj);
            });
            this.mutableVideo.push(objVideo);
        },
    },
    mounted() {
        this.listObservations();
        this.getConfig();
    },
    methods: {
        closeModalCertificate() {
            this.visibleConfigure = false;
            this.getConfig();
        },

        closeModalPreview() {
            this.outerVisible = false;
        },

        closeOrderModal() {
            this.orderModal = false;
            this.refreshClassData();
            location.reload();
        },

        openModal() {
            this.orderModal = true;
        },

        openModalCertificate() {
            this.visibleConfigure = true;
        },

        openModalPreview() {
            this.outerVisible = true;
        },

        async getConfig() {
            const response = await axios.get(`/course/certificate/configuration/${this.courses.id}`);
            this.configCertificate = response.data.data;
        },

        showDeleteModal(value) {
            this.mutableModule = value;
            $('#delete-module-modal').modal('show');
        },

        showAddClassModal(value) {
            this.mutableModule = value;
            $('#create-class-modal').modal('show');
        },

        showDeleteClassModal(value) {
            this.mutableClass = value;
            $('#delete-class-modal').modal('show');
        },

        async showEditClassModal(value) {
            this.mutableClass = value;
            const { data } = await axios.get(`/course/module/class/${value.id}/details`);
            const { resources, video } = data.data;
            this.mutableClass.has_video = video && video.length > 0;
            this.fileResourcesList = resources.map((element) => ({
                name: element.filename,
                url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${element.resource_file}`,
            }));
            this.fileVideoList =
                video && video.length > 0
                    ? [
                        {
                            name: video[0].filename,
                            url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${video[0].path}`,
                        },
                    ]
                    : [];
            $('#edit-class-modal').modal('show');
        },

        async deleteClass() {
            try {
                const { data } = await axios.delete(`/course/module/class/${this.mutableClass.id}/delete`);
                const { status } = await data;
                if (!!status) {
                    this.$message.success('La clase ha sido eliminada correctamente');
                } else {
                    this.$message.warning('Error al validar datos');
                }
                $('#delete-class-modal').modal('hide');
                this.refreshClassData();
            } catch (error) {
                console.log('Ocurrio un error al eliminar el video', error);
            }
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
        },

        refreshClassData() {
            let array = this.$refs.moduleitems;
            for (let index in array) {
                this.$refs.moduleitems[index].listClasses();
            }
        },

        listObservations() {
            api
                .get(`/course/module/class/${this.courses.id}/listObservations`)
                .then((response) => {
                    this.observations = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        async deleteModule() {
            const { data } = await axios.delete(`/course/module/${this.mutableModule.id}/delete`);
            const { status, modules } = await data.data;

            const response =
                (await status) == 'ok'
                    ? this.$message.success('Modulo eliminado correctamente')
                    : await this.$message.warning('Error al validar datos');
            this.mutableModules = modules;
            $('#delete-module-modal').modal('hide');
        },
        formatResponse(stringifyResponse) {
            return JSON.parse(stringifyResponse.slice(15, -1) + '}');
        },

        showInputModule() {
            this.showDialogAddModule == true
                ? (this.showDialogAddModule = false)
                : (this.showDialogAddModule = true);
        },

        hasEspecialCharacters(text) {
            const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
            return regex.test(text);
        },

        validateFields() {
            if (!this.title || this.title.trim() === '' || this.title.length === 0) {
                this.$message.warning('Titulo esta vacio!');
                return false;
            }
            if (this.title.length > 255) {
                this.$message.warning('Titulo demasiado largo!');
                return false;
            }
            return true;
        },

        async storeModule() {
            if (this.validateFields()) {
                let formData = new FormData();
                formData.append('name', this.title);
                formData.append('course_id', this.courses.id);
                try {
                    const { data } = await axios.post('/course/module/store', formData);
                    const { status, modules } = await data.data;
                    const response =
                        (await status) == 'ok'
                            ? this.$message.success('El módulo ha sido registrado correctamente')
                            : await this.$message.warning('Error al validar datos');

                    this.mutableModules = modules;
                    this.showDialogAddModule = false;
                    this.title = null;
                } catch (error) {
                    console.log(error);
                }
            }
        },
    },
};
</script>
