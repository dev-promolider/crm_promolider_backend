<template>
  <div>
    <div
      class="card"
      v-loading="loading"
      element-loading-text="Cargando..."
      element-loading-spinner="el-icon-loading"
      element-loading-background="rgba(0, 0, 0, 0.6)"
    >
      <div class="card-body">
        <form @submit.prevent="submit" id="upload" method="post">
          <div class="row">
            <div class="col-lg-3">
              <div class="form-group">
                <label>Módulo</label>
                <el-select
                  v-model="mutableModule"
                  placeholder="Seleccione el módulo"
                  class="d-inline"
                  @input="handleChangeModule"
                >
                  <el-option v-for="m in modules" :key="m.id" :label="m.name" :value="m.id">
                  </el-option>
                </el-select>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label>Clase</label>
                <el-select
                  v-model="mutableLesson"
                  placeholder="Seleccione la clase"
                  class="d-inline"
                  @input="handleChangeLesson"
                >
                  <el-option v-for="l in lessons" :key="l.id" :label="l.name" :value="l.id">
                  </el-option>
                </el-select>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label>Título del Examen</label>
                <input
                  type="text"
                  v-model="form.title"
                  class="form-control"
                  placeholder="Titulo del examen"
                />
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label>Duración</label>
                <el-time-select
                  id="duration_time"
                  style="width:100%"
                  v-model="form.duration"
                  :picker-options="{
                    start: '00:05',
                    step: '00:05',
                    end: '02:00',
                  }"
                  placeholder="Horas : Minutos"
                >
                </el-time-select>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label>Nota máxima</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.max_score"
                  placeholder="100 pts"
                  v-on:keypress="restrictChars($event)"
                />
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label>Nota aprobatoria mínima</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.min_passing_score"
                  placeholder="80 pts"
                  v-on:keypress="restrictChars($event)"
                />
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success" :disabled="isSubmitting">Guardar</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-12">
          <h3 class="mb-2">Lista de clases</h3>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Clase</th>
                  <th scope="col">Nombre de examen</th>
                  <th scope="col">Estado</th>
                  <th scope="col">Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="clas in classes" :key="clas.class_id">
                  <td scope="row"  @click="classSelect(clas)" >{{ clas.class_name }}</td>
                  <td scope="row">{{ clas.exam_name }}</td>
                  <td>
                    <el-tag
                    v-if="clas.exam_status == true"
                    type="success"
                    class="mx-1"
                    effect="dark"
                    >
                      Publicado
                    </el-tag>
                    <el-tag
                      v-else-if="clas.exam_status == false"
                      type="warning"
                      class="mx-1"
                      effect="dark"
                    >
                      No publicado
                    </el-tag>
                    <div v-else>{{ clas.exam_status }}</div>
                  </td>
                  <td>
                    <el-select
                      v-if="clas.exam_exist"
                      id="customize_select"
                      size="mini"
                      clearable
                      v-model="optionSelected"
                      @change="getOptionSelected(clas)"
                    >
                      <el-option
                        v-for="item in options"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      >
                      </el-option>
                    </el-select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <custom-modal id="advice" color="danger">
      <template #title> <u> Advertencia </u></template>
      <template #body
        >Al activar el examen no podrá eliminar ni editar el examen. ¿Desea continuar con la
        operación?</template
      >
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button
          type="button"
          class="btn btn-danger"
          @click="activateExam(mutableExamId, mutableCourseId)"
        >
          Confirmar
        </button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import moment from 'moment';
import language from '../../../../api/traduction_es';
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  props: {
    modules: Array,
    course_id: Number,
  },
  components: {
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      infinite: 999999,
      mutableExamId: '',
      mutableCourseId: '',
      moment: moment,
      loading: false,
      form: {},
      isEmpty: true,
      lessons: null,
      mutableLesson: null,
      mutableModule: null,
      isSubmitting: false,
      classes: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Activar/Desactivar',
        },
      ],
      exams: null,
      optionSelected: '',
    };
  },
  created() {
    this.initForm();
  },
  mounted() {
    $("#duration_time").keypress(
      (e) => {
        let decNum = ['0','1','2','3','4','5','6','7','8','9','.',','];
        if( !decNum.includes(e.key) ){
          e.preventDefault();
          this.$message.warning('Solo se permite números para el campo de duración');
        }
      }
    );
    this.getClassList();
  },
  methods: {
    initForm() {
      this.form = {
        title: null,
        duration: null,
        max_score: null,
        min_passing_score: null,
      };
    },

    classSelect(clas){
      if(clas.exam_name == "---"){
        this.classSelected = clas
        this.mutableModule = clas.module_id
        this.handleChangeModule(clas.module_id)
        // this.listLessons(clas.module_id)
        this.mutableLesson = clas.class_id
        this.handleChangeLesson(clas.class_id)
        this.$message.success('Clase y módulo seleccionados correctamente');
      }
    },

    listLessons(module_id) {
      axios
        .get(`/course/module/class/${module_id}/classList`)
        .then((response) => {
          this.lessons = response.data.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    handleChangeModule: function (value) {
      this.mutableLesson = '';
      this.listLessons(value);
    },

    handleChangeLesson: function (value) {
      this.isEmpty = false;
      this.listExams(value);
    },

    async getClassList(){
      await axios.get(`/course/exam/lesson/all?course_id=${this.course_id}`)
      .then((response) => {
        this.classes = response.data.data;
      })
    },

    listExams(lesson) {
      axios
        .get(`/course/exam/lesson/${lesson}/list`)
        .then((response) => {
          if (response.data.error) {
            this.message = 'Sin examenes registrados';
          } else {
            this.exams = response.data.data;
          }
          this.loadDataTable();
        })
        .catch((error) => {
          console.log(error);
        });
    },

    loadDataTable() {
      $('#data-table-list-exams').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-exams').DataTable(language);
      });
    },

    areOnlyNumbers(text) {
      const regex = new RegExp('^[0-9]+$');
      return regex.test(text);
    },

    secondsToMinutes(secs) {
      const HOUR = 3600;
      let response;
      let duration = moment.duration(secs, 's');
      let minutes = duration.get('m');
      if (secs >= HOUR) {
        let hours = duration.get('h');
        response = `${hours} h ${minutes} m`;
      } else {
        response = `${minutes} m`;
      }
      return response;
    },
    validateFields() {
      if (!this.mutableModule || this.mutableModule.length === 0) {
        this.$message.warning('No ha seleccionado un módulo');
        return false;
      }
      if (!this.mutableLesson || this.mutableLesson.length === 0) {
        this.$message.warning('No ha seleccionado la clase');
        return false;
      }

      if (!this.form.title || this.form.title.trim() === '' || this.form.title.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }

      if (this.form.title.length > 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }

      if (
        !this.form.max_score ||
        this.form.max_score.trim() === '' ||
        this.form.max_score.length === 0
      ) {
        this.$message.warning('Nota máxima no debe estar vacío');
        return false;
      }

      if (!this.areOnlyNumbers(this.form.max_score)) {
        this.$message.warning('Solo se permite números para el campo de nota máxima');
        return false;
      }

      if (Number(this.form.max_score) >= 999) {
        this.$message.warning('Solo se permite números de 3 dígitos para el campo de nota máxima');
        return false;
      }

      if (
        !this.form.min_passing_score ||
        this.form.min_passing_score.trim() === '' ||
        this.form.min_passing_score.length === 0
      ) {
        this.$message.warning('La nota mínima aprobatoria no debe estar vacío');
        return false;
      }

     if (this.form.min_passing_score == 0) {
        this.$message.warning('La nota mínima aprobatoria no puede ser 0');
        return false;
      }

      if (!this.areOnlyNumbers(this.form.min_passing_score)) {
        this.$message.warning('Solo se permite números para el campo de nota mínima aprobatoria');
        return false;
      }

      if (Number(this.form.min_passing_score) >= 999) {
        this.$message.warning(
          'Solo se permite números de 3 dígitos para el campo de nota mínima aprobatoria'
        );
        return false;
      }

      if (Number(this.form.min_passing_score) >= Number(this.form.max_score)) {
        this.$message.warning('La nota mínima debe ser menor que la nota máxima');
        return false;
      }

     /*  if (this.form.duration == null || this.form.duration == 'null') {
        this.$message.warning('El campo de duración esta vacio, ingrese la duración en horas y minutos');
        return false;
      } */
      
      return true;
    },

    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('lesson_id', this.mutableLesson);
        formdata.append('title', this.form.title);
        if (this.form.duration != null && this.form.duration != 'null') {
          formdata.append('time', this.form.duration);
        }
        else{
          formdata.append('time', this.infinite);
        }

        formdata.append('max_score', this.form.max_score);
        formdata.append('min_passing_score', this.form.min_passing_score);
        formdata.append('exam_for', 'lesson');
        try {
          this.loading = true;
          const request = await axios({
            url: '/course/exam/store',
            method: 'post',
            data: formdata,
          });
          console.log(request, request.data, request.status)
          const response =
            request.status == 200
              ? this.$message.success('El examen ha sido registrado correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.listExams(this.mutableLesson);
          this.initForm();
          await this.getClassList();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },

    getOptionSelected(exam) {
      // La opcion se recupera de la variable optionSelected
      let option = this.optionSelected;

      switch (option) {
        case '1':
          document.location.href = `/course/exam/lesson/edit/${exam.exam_id}`;
          break;
        case '2':
          if (exam.status == 1) {
            this.activateExam(exam.exam_id, exam.course_id);
          } else {
            this.mutableExamId = exam.exam_id;
            this.mutableCourseId = exam.course_id;
            $('#advice').modal('show');
          }

          break;
        default:
          console.log('Error');
          break;
      }
      this.optionSelected = '';
    },

    async activateExam(id, course_id) {
      const formdata = new FormData();
      formdata.append('id', id); // exam id
      formdata.append('course_id', course_id); // course id

      try {
        const request = await axios({
          url: '/course/exam/activate',
          method: 'post',
          data: formdata,
        });

        if ((await request.data.data) == 'success') {
          this.$message.success('El examen ha sido activado/desactivado correctamente');
        } else if (request.data.data == 'failed') {
          this.$message.warning('La puntuación total no coincide con la nota máxima');
        } else {
          this.$message.warning('Error al validar datos');
        }
        this.listExams(this.mutableLesson);
        await this.getClassList();
        $('#advice').modal('hide');
      } catch (error) {
        console.log(error);
      }
    },
    restrictChars: function ($event) {
      if ($event.charCode === 0 || /\d/.test(String.fromCharCode($event.charCode))) {
        return true;
      } else {
        $event.preventDefault();
      }
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
