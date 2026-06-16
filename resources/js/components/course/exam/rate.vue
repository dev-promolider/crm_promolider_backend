<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table
              id="data-table-list-courses"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>#</th>
                  <th>Estudiante</th>
                  <th>F. Registro</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(exam, index) in this.user_exams"
                  v-bind:key="exam.id"
                  v-bind:index="index"
                >
                  <td>
                    <p style="width: 15px">
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>{{ exam.name }} {{ exam.last_name }}</td>
                  <td>{{ moment(exam.created_at).format('DD/MM/YYYY hh:mm A') }}</td>

                  <td class="align-content-center">
                    <el-select
                      id="customize_select"
                      size="mini"
                      clearable
                      v-model="optionSelected"
                      @change="getOptionSelected(exam)"
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
    </section>
    <custom-spinner v-else></custom-spinner>

    <custom-modal id="rate-exam" color="primary" size="large" v-if="examSelected">
      <template #title
        >Calificar Exámen <u>{{ currentExam.title }} </u></template
      >
      <template #body>
        <form @submit.prevent="submit" id="upload" method="post">
          <div class="p-1 d-flex justify-content-between">
            <div>
              <p>
                Estudiante: <b> {{ examSelected.name }} {{ examSelected.last_name }}</b>
              </p>
            </div>
            <div>
              <p>
                <b> Fecha {{ moment(examSelected.created_at).format('DD/MM/YYYY hh:mm A') }}</b>
              </p>
            </div>
          </div>

          <div class="col-lg-12" v-for="(detail, index) in currentDetail" :key="detail.id">
            <div class="p-1 d-flex justify-content-between">
              <div>
                <p>
                  <b>{{ index + 1 }}) {{ currentQuestions[index].title }} </b>
                </p>
              </div>
              <div v-if="currentQuestions[index].question_type_id != 4">
                <p>
                  <b>{{ detail.points_gained }} / {{ currentQuestions[index].points }}</b>
                </p>
              </div>

              <div v-else>
                <p>
                  <b>
                    <el-input-number
                      v-model="rate_variable[index]"
                      :precision="2"
                      size="mini"
                      :step="0.5"
                      :max="currentQuestions[index].points"
                    ></el-input-number>
                    / {{ currentQuestions[index].points }}</b
                  >
                </p>
              </div>
            </div>

            <div
              class="row justify-content-center"
              v-if="currentQuestions[index].question_type_id != 4"
            >
              <div
                class="col-lg-5 col-md-5 option"
                :class="matched(subindex, detail.options_selected)"
                v-for="(option, subindex) in buildArray(currentQuestions[index].options)"
                :key="subindex"
              >
                {{ option }}
              </div>
            </div>

            <div class="row justify-content-center" v-else>
              {{ detail.options_selected.response }}
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 text-right">
              <button type="submit" class="btn btn-success ml-2" :disabled="isSubmitting">
                Calificar
              </button>
            </div>
          </div>
        </form>
      </template>
    </custom-modal>
  </div>
</template>
<script>
import moment from 'moment';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner.vue';
import language from '../../../api/traduction_es';

export default {
  props: {
    user: Object,
  },
  data() {
    return {
      initialLoading: false,
      moment: moment,
      user_exams: [],
      isSubmitting: false,
      options: [
        {
          value: '1',
          label: 'Calificar',
        },
      ],
      optionSelected: '',
      examSelected: {},
      currentExam: '',
      currentDetail: '',
      currentQuestions: '',
      rate_variable: [],
    };
  },
  components: {
    'custom-modal': ModalComponent,
    'custom-spinner': CustomSpinner,
  },

  mounted() {
    this.listPendingExams();
  },
  methods: {
    matched(optionIndex, selected) {
      if (Array.isArray(selected)) {
        let string = selected.toString();
        if (string.includes(optionIndex)) {
          return 'matched';
        } else {
          return 'option';
        }
      } else {
        if (selected == optionIndex) {
          return 'matched';
        } else {
          return 'option';
        }
      }
    },
    buildArray(data) {
      let array = data.filter(function (f) {
        return f !== null;
      });
      let clearArray = array.filter(function (f) {
        return f !== 'undefined';
      });
      return clearArray;
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#data-table-list-courses').DataTable(language);
      });
    },
    validateFields() {
      let index_array = [];
      // get index of open questions
      for (let [index, val] of this.currentQuestions.entries()) {
        if (val.question_type_id == 4) {
          index_array.push(index);
        }
      }

      let index_undefined = [];
      // compare if every index had been rate

      this.rate_variable.forEach(function (valor, indice, array) {
        index_array.forEach(function (valor2, indice2, array2) {
          if (valor2 == indice && valor == undefined) {
            // sumamos 1 para saltar la posicion 0
            index_undefined.push(valor2 + 1);
          }
        });
      });

      let message = `Las siguientes preguntas necesitan tener una nota: ${index_undefined}`;
      if (index_undefined.length > 0) {
        this.$message.warning(`${message}`);
        return false;
      }
      return true;
    },

    listPendingExams: function () {
      this.initialLoading = true;
      axios.get(`/course/exam/rate/${this.user.id}/list`).then((response) => {
        this.user_exams = response.data.data;
        this.initialLoading = false;
        this.loadDataTable();
      });
    },

    /**
     * Return data about the option selected in the table
     * @param {object} exam - The exam object selected
     */
    getOptionSelected(exam) {
      let option = this.optionSelected;

      switch (option) {
        case '1':
          this.listDetail(exam.id);
          this.examSelected = exam;
          $('#rate-exam').modal('show');
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
      }
    },

    listDetail(exam_id) {
      axios.get(`/course/exam/rate/${exam_id}/detailList`).then((response) => {
        this.currentDetail = response.data.details;
        this.currentQuestions = response.data.questions;
        this.currentExam = response.data.exam;
      });
    },

    parseObjectToArray(object) {
      let string = JSON.stringify(object);
      let clean_string = string.slice(1, -1);
      let array = clean_string.split(',');
      return array;
    },
    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        let array = this.parseObjectToArray(this.rate_variable);
        formdata.append('exam_id', this.examSelected.id);
        formdata.append('rate', array);
        try {
          this.loading = true;
          const request = await axios({
            url: '/course/exam/rate/update',
            method: 'post',
            data: formdata,
          });

          const response =
            request.status == 200
              ? this.$message.success('El exámen se califico correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.rate_variable = [];
          $('#rate-exam').modal('hide');
          this.listPendingExams();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
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

.option {
  margin: 10px;
  padding: 10px;
  background-color: #c9d3d3;
  color: #000000;
  text-align: center;
}
.matched {
  margin: 10px;
  padding: 10px;
  text-align: center;
  background-color: #45c77f;
  color: #000000;
}
.modal-footer {
  border-top: none;
}
</style>
