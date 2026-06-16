<template>
    <div>
        <section v-if="!loading">
            <div class="card">

                <div class="card-body table-responsive">
                    <table id="students-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombres y Apellidos</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Avance</th>
                                <th>Certificado</th>
                                <th>Inicio / Fin</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(student, index) in students" :key="index">
                                <td>
                                    <img :src="student.photo" alt="foto" width="45" class="rounded-circle">
                                </td>
                                <td>{{ student.fullName }}</td>
                                <td>{{ student.email }}</td>
                                <td>{{ student.phone || '-' }}</td>

                                <!-- Avance -->
                                <td>
                                    <span v-if="student.completed_course === 1" class="badge badge-success">Completado</span>
                                    <span v-else class="badge badge-warning">En progreso</span>
                                </td>

                                <!-- Certificado -->
                                <td>
                                    <span v-if="student.certificate_delivered === 1" class="badge badge-success">Entregado</span>
                                    <span v-else class="badge badge-danger">Pendiente</span>
                                </td>

                                <!-- Inicio / Fin -->
                                <td>
                                    <small>
                                        <strong>Inicio:</strong> {{ formatDate(student.created_at) }}<br>
                                        <strong>Fin:</strong> {{ formatDate(student.expiration_membership_date) }}
                                    </small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <custom-spinner v-else></custom-spinner>
    </div>
</template>

<script>
import moment from 'moment';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';

export default {
    props: ['id', 'user'],

    data() {
        return {
            loading: true,
            students: [],
        };
    },

    components: {
        'custom-spinner': CustomSpinner,
    },

    mounted() {
        this.listStudents();
    },

    methods: {
        listStudents() {
            axios.get(`/course/${this.id}/subscribersList`)
                .then(response => {
                    this.students = response.data.data;
                    this.loading = false;

                    this.$nextTick(() => {
                        $('#students-table').DataTable({
                            responsive: true,
                            language: {
                                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                            }
                        });
                    });
                })
                .catch(() => {
                    this.loading = false;
                    this.$message.error("Error al cargar estudiantes");
                });
        },

        formatDate(date){
            return date ? moment(date).format("DD/MM/YYYY") : "-";
        }
    }
};
</script>

<style scoped>
table img {
    border: 2px solid #eee;
}
</style>