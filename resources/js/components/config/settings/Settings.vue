<template>
    <div>
        <section id="basic-horizontal-layouts">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 col-12">
                            <el-tabs type="border-card" v-model="activeName">
                                <el-tab-pane label="General" name="first">
                                    <template-certificate-setting></template-certificate-setting>
                                </el-tab-pane>
                                <el-tab-pane label="Puntos" name="second">
                                    <div class="row" >
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="passed_exam">Examen Aprobado</label>
                                                <input type="number" v-model="formPoints.passed_exam" id="passed_exam"
                                                    class="form-control" autocomplete="false" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="exam_timer">Examen con temporizador</label>
                                                <input type="number" v-model="formPoints.exam_timer" id="exam_timer"
                                                    class="form-control" autocomplete="false" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="dynamics_1">Dinámica 1</label>
                                                <input type="number" v-model="formPoints.dynamics_1" id="dynamics_1"
                                                    class="form-control" autocomplete="false" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="dynamics_2">Dinámica 2</label>
                                                <input type="number" v-model="formPoints.dynamics_2" id="dynamics_2"
                                                    class="form-control" autocomplete="false" />
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="d-flex justify-content-center">
                                                <button @click="savePoints()"
                                                    class="btn btn-success">Actualizar</button>
                                            </div>
                                        </div>

                                    </div>

                                </el-tab-pane>
                            </el-tabs>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
</template>

<script>
import CertificateSetting from './CertificateSetting';
import api from '../../../api/api';

export default {
    name: 'Settings',
    components: {
        'template-certificate-setting': CertificateSetting
    },
    data() {
        return {
            formPoints: {
                passed_exam: '',
                exam_timer: '',
                dynamics_1: '',
                dynamics_2: ''
            },
            activeName: 'first'
        }
    },
    mounted() {
        this.getPoints();
    },
    methods: {
        getPoints() {
            api.get(`/config/settings/points`).then((response) => {
                try {
                    this.formPoints.passed_exam = response[0].value;
                    this.formPoints.exam_timer = response[1].value;
                    this.formPoints.dynamics_1 = response[2].value;
                    this.formPoints.dynamics_2 = response[3].value;
                } catch (error) {
                }
            });
        },

        savePoints() {
            if (this.validateFields()) {
                const formdata = new FormData();
                formdata.append('passed_exam', this.formPoints.passed_exam);
                formdata.append('exam_timer', this.formPoints.exam_timer);
                formdata.append('dynamics_1', this.formPoints.dynamics_1);
                formdata.append('dynamics_2', this.formPoints.dynamics_2);

                api.post(`/config/settings/points`, formdata).then((response) => {

                    if (response == 'ok') {
                        this.$message.success('Registro guardado correctamente');
                    } else {
                        this.$message.warning('Error al realizar la acción');
                    }

                }).catch((error) => {
                    this.$message.warning('Error al realizar la acción');
                });
            }
        },
        validateFields() {
            if (
                this.formPoints.passed_exam.toString().trim() === '' ||
                this.formPoints.passed_exam == 0 ||
                isNaN(this.formPoints.passed_exam)
            ) {
                this.$message.warning('El campo de Examen Aprobado esta vacio o su valor es 0!');
                return false;
            }
            
            if (
                this.formPoints.exam_timer.toString().trim() === '' ||
                this.formPoints.exam_timer == 0 ||
                isNaN(this.formPoints.exam_timer)
            ) {
                this.$message.warning('El campo de Examen con temporizador esta vacio o su valor es 0!');
                return false;
            }

            if (
                this.formPoints.dynamics_1.toString().trim() === '' ||
                this.formPoints.dynamics_1 == 0 ||
                isNaN(this.formPoints.dynamics_1)
            ) {
                this.$message.warning('El campo de Dinámica 1 esta vacio o su valor es 0!');
                return false;
            }

            if (
                this.formPoints.dynamics_2.toString().trim() === '' ||
                this.formPoints.dynamics_2 == 0 ||
                isNaN(this.formPoints.dynamics_2)
            ) {
                this.$message.warning('El campo de Dinámica 2 esta vacio o su valor es 0!');
                return false;
            }


            return true;
        },

    },
}
</script>