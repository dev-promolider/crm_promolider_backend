<template>
    <div>
        <section>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="data-table-list-marketplace"
                            class="table-hover-animation table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Curso</th>
                                    <th>Autor</th>
                                    <th>Precio</th>
                                    <th>Cantidad de Inscritos</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(course, index) in localCourses" v-bind:key="course.id" v-bind:index="index">
                                    <td>
                                        <p style="width: 15px">
                                            {{ (index = index + 1) }}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <img class="mr-3" :src="'https://promolider-storage-user.s3-accelerate.amazonaws.com/' +
                                                course.url_portada
                                                " width="100px" height="50px" alt="Portada del curso" />
                                            <p style="width: 220px">
                                                {{ course.title }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>{{ course.name + ' ' + course.last_name }}</td>
                                    <td>$ {{ course.price }}</td>
                                    <td>{{ course.subscribers ?? 0 }}</td>
                                    <td>
                                        <span class="badge badge-success" v-if="course.marketplace_listed === 1">En
                                            venta</span>
                                        <span class="badge badge-secondary" v-else>Retirado</span>
                                    </td>
                                    <td class="align-content-center">
                                        <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                                            @change="getOptionSelected(course)">
                                            <el-option v-for="item in options" :key="item.value" :label="item.label"
                                                :value="item.value">
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

        <custom-modal id="edit-course-modal" color="primary" size="large" v-if="courseSelected">
            <template #title>Editar curso <u> {{ courseSelected.title }} </u></template>
            <template #body>
                <form-course-edit :course="courseSelected" :categoriesList="categories"
                    :levelsList="levels"></form-course-edit>
            </template>
        </custom-modal>

        <custom-modal id="toggle-marketplace-modal" color="primary">
            <template #title>El curso <u> {{ courseSelected.title }} </u></template>
            <template #body>Ha sido satisfactoriamente
                {{
                    courseSelected.marketplace_listed
                        ? 'publicado en el marketplace.'
                        : 'retirado del marketplace.'
                }}</template>
            <template #footer>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
            </template>
        </custom-modal>

        <CoursePreview :courses="courseSelected" v-if="outerVisible" :outerVisible="outerVisible"
            @closeModalPreview="closeModalPreview" />
    </div>
</template>

<script>
import moment from 'moment';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CoursePreview from '../course/CoursePreview.vue';
import CourseEdit from '../course/CoursesEdit.vue';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import language from '../../api/traduction_es';

export default {
    props: {
        courses: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            moment: moment,
            categories: [],
            localCourses: this.courses,
            levels: [],
            outerVisible: false,
            options: [
                {
                    value: '1',
                    label: 'Integrar/Retirar',
                },
            ],
            optionSelected: '',
            courseSelected: {},
            url: process.env.MIX_FRONTEND_APP_URL + 'redirect/',
        };
    },

    components: {
        'custom-modal': ModalComponent,
        'form-course-edit': CourseEdit,
        'custom-spinner': CustomSpinner,
        CoursePreview,
    },

    mounted() {
        this.loadDataTable();
        this.listCategories();
        this.listLevels();
    },

    methods: {
        loadDataTable() {
            this.$nextTick(function () {
                $('#data-table-list-marketplace').DataTable(language);
            });
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

        async getOptionSelected(course) {
            // La opcion se recupera de la variable optionSelected
            let option = this.optionSelected;

            switch (option) {
                case '1':
                    const toggled = await axios.post(`/marketplace/toggle-sellable/${course.id}`);

                    if ((toggled.status = 200)) {
                        const courseIndex = this.localCourses.findIndex(
                            (localCourse) => localCourse.id === course.id
                        );
                        const localCoursesCopy = this.localCourses;
                        [...localCoursesCopy].splice(courseIndex, 1, {
                            ...course,
                            marketplace_listed: toggled.data.data ? 1 : 0,
                        });
                        this.localCourses = localCoursesCopy;
                        this.courseSelected = { ...course, marketplace_listed: toggled.data.data ? 1 : 0 };
                        this.$set(this.localCourses, courseIndex, this.courseSelected);

                        $('#toggle-marketplace-modal').modal('show');
                    }

                    this.optionSelected = '';
                    break;
                default:
                    console.log('Error');
                    break;
            }
        },

        closeModalPreview() {
            this.outerVisible = false;
        },

        openModalPreview() {
            this.outerVisible = true;
        },

        /**
         * 0 -> registrado e inactivo
         * 1 -> en espera
         * 2 -> activo
         */
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
