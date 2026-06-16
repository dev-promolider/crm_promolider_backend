<template>
  <div class="card" v-if="question" v-loading="loadingchanges" element-loading-text="Cargando..."
    element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
    <div class="card-body">
      <form @submit.prevent="storeQuestionDetails">
        <div class="row">
          <div class="col-lg-9">
            <label>Pregunta</label>
            <input type="text" v-model="question.title" class="form-control" placeholder="Pregunta" />
          </div>

          <div class="col-lg-3">
            <label>Puntos</label>
            <input type="number" v-model="question.points" class="form-control" placeholder="Puntos" step="any"
              v-on:keypress="validateNumber($event)" />
          </div>

          <div class="col-lg-12">
            <div class="form-group">
              <label>Tipo de pregunta</label>
              <el-select v-model="questionType" placeholder="Seleccione un tipo de pregunta" class="d-inline"
                @change="handleChangeSelect">
                <el-option v-for="et in mutableQuestionTypes" :key="et.id" :label="et.name" :value="et.id">
                </el-option>
              </el-select>
            </div>
          </div>

          <div class="w-100 p-1">
            <div class="row">
              <div class="col-1" v-if="showAll == true">
                <el-checkbox-group v-model="checkList" @change="handleChangeCheckbox" :max="max">
                  <el-checkbox label="0"></el-checkbox>
                  <el-checkbox label="1"></el-checkbox>
                  <el-checkbox label="2" v-if="show == true"></el-checkbox>
                  <el-checkbox label="3" v-if="show == true"></el-checkbox>
                  <el-checkbox label="4" v-if="show == true"></el-checkbox>
                </el-checkbox-group>
              </div>
              <div class="col" v-if="showAll == true">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label>Opcion de respuesta 1</label>
                      <input type="text" v-model="opt_1" class="form-control" placeholder="Opción de respuesta 1" />
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-group">
                      <label>Opcion de respuesta 2</label>
                      <input type="text" v-model="opt_2" class="form-control" placeholder="Opción de respuesta 2" />
                    </div>
                  </div>

                  <div class="col-12" v-if="show == true">
                    <div class="form-group">
                      <label>Opcion de respuesta 3</label>
                      <input type="text" v-model="opt_3" class="form-control" placeholder="Opción de respuesta 3" />
                    </div>
                  </div>

                  <div class="col-12" v-if="show == true">
                    <div class="form-group">
                      <label>Opcion de respuesta 4</label>
                      <input type="text" v-model="opt_4" class="form-control" placeholder="Opción de respuesta 4" />
                    </div>
                  </div>

                  <div class="col-12" v-if="show == true">
                    <div class="form-group">
                      <label>Opcion de respuesta 5</label>
                      <input type="text" v-model="opt_5" class="form-control" placeholder="Opción de respuesta 5" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-center">
          <button type="submit" class="btn btn-success" :disabled="isSubmittingChanges">
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    exam: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: true,
    },
    question_types: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      isSubmittingChanges: false,
      checkList: [],
      questionType: this.question.question_type_id,
      max: 1,
      show: null,
      showAll: null,
      mutableQuestionTypes: null,
      loadingchanges: false,
      mutableQuestion: {},
      opt_1: null,
      opt_2: null,
      opt_3: null,
      opt_4: null,
      opt_5: null,
    };
  },

  created() {
    this.listQuestionTypes();
    this.listQuestion(this.question.question_type_id);
  },

  methods: {
    listQuestionTypes() {
      if (this.exam.time != 59999940) {
        this.question_types.pop();
        this.mutableQuestionTypes = this.question_types;
      } else {
        this.mutableQuestionTypes = this.question_types;
      }
    },
    handleChangeSelect(option) {
      switch (option) {
        case 1:
          this.show = true;
          this.showAll = true;
          this.checkList = [];
          this.max = 1;
          this.opt_1 = '';
          this.opt_2 = '';
          this.opt_3 = '';
          this.opt_4 = '';
          this.opt_5 = '';
          break;
        case 2:
          this.checkList = [];
          this.opt_1 = '';
          this.opt_2 = '';
          this.opt_3 = '';
          this.opt_4 = '';
          this.opt_5 = '';
          this.max = 5;
          this.show = true;
          this.showAll = true;
          break;
        case 3:
          this.show = false;
          this.showAll = true;
          this.checkList = [];
          this.max = 1;
          this.opt_1 = 'Verdadero';
          this.opt_2 = 'Falso';
          this.opt_3 = '';
          this.opt_4 = '';
          this.opt_5 = '';
          break;
        case 4:
          this.checkList = [];
          this.show = true;
          this.showAll = false;
          this.opt_1 = '';
          this.opt_2 = '';
          this.opt_3 = '';
          this.opt_4 = '';
          this.opt_5 = '';
          break;
        default:
          break;
      }
    },
    listQuestion(type) {
      axios
        .get(`/course/exam/question/${this.question.id}/get`)
        .then((response) => {
          this.handleChangeSelect(type);
          if (response.data.error) {
            this.message = 'Sin preguntas registrados';
          } else {
            let options = response.data.data.options;
            let correct = response.data.data.correct;

            if (correct != null) {
              const correct_answers = correct.split(',');
              this.checkList = correct_answers;
            }
            if (options != null) {
              this.opt_1 = options[0];
              this.opt_2 = options[1];
              this.opt_3 = options[2];
              this.opt_4 = options[3];
              this.opt_5 = options[4];
            }
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    handleChangeCheckbox(value) {
      // console.log(value);
    },
    areOnlyNumbers(text) {
      const regex = new RegExp('^[0-9]+([,.][0-9]+)?$');
      return regex.test(text);
    },

    validateFields() {
      if (
        !this.question.title ||
        this.question.title.trim() === '' ||
        this.question.title.length === 0
      ) {
        this.$message.warning('Título esta vacio!');
        return false;
      }
      if (this.question.title.length > 255) {
        this.$message.warning('Título demasiado largo!');
        return false;
      }

      if (Number(this.question.points) >= 999) {
        this.$message.warning('Solo se permite números de 3 dígitos para el campo de puntos');
        return false;
      }

      if (!this.areOnlyNumbers(this.question.points)) {
        this.$message.warning('Solo se permite números para el campo de puntos');
        return false;
      }

      if ((this.questionType == 1 || this.questionType == 3) && this.checkList.length > 1) {
        this.$message.warning('Sólo se permite seleccionar una respuesta correcta para el tipo de pregunta');
        return false;
      }

      if (this.exam.time != 59999940 && this.checkList.length == 0) {
        this.$message.warning('Seleccione una respuesta correcta');
        return false;
      }
      //reccorrer las respuestas marcadas.
      let cont = 0;
      this.checkList.forEach(e => {
        switch (e) {
          case '0':
            //validar que la respuesta marcada como correcta no este vacio
            if (this.validateResponse(this.opt_1)) {
              cont++;
            }
            break;
          case '1':
            if (this.validateResponse(this.opt_2)) {
              cont++;
            }
            break;
          case '2':
            if (this.validateResponse(this.opt_3)) {
              cont++;
            }
            break;
          case '3':
            if (this.validateResponse(this.opt_4)) {
              cont++;
            }
            break;
          case '4':
            if (this.validateResponse(this.opt_5)) {
              cont++;
            }
            break;
        }
      });
      if (cont > 0) {
        return false;
      }

      return true;
    },

    validateResponse(input) {
      if (
        !input ||
        input.trim() === '' ||
        input.length === 0
      ) {
        this.$message.warning('El campo marcado como respuesta correcta está vacío!');
        return true;
      }
      return false;
    },

    restrictChars: function ($event) {
      if ($event.charCode === 0 || /\d/.test(String.fromCharCode($event.charCode))) {
        return true;
      } else {
        $event.preventDefault();
      }
    },

    async storeQuestionDetails() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('exam_question_id', this.question.id);
        formdata.append('title', this.question.title);
        formdata.append('points', this.question.points);
        formdata.append('opt_1', this.opt_1);
        formdata.append('opt_2', this.opt_2);
        formdata.append('opt_3', this.opt_3);
        formdata.append('opt_4', this.opt_4);
        formdata.append('opt_5', this.opt_5);
        formdata.append('correct', this.checkList);
        formdata.append('exam_type', this.questionType);

        let ruta = '';
        // Validar que id tiene disponible
        if (this.exam.course_id != null) {
          formdata.append('exam_for', 'course');
          ruta = `/course/exam/edit/${this.exam.id}`;
        } else if (this.exam.module_id != null) {
          formdata.append('exam_for', 'module');
          ruta = `/course/exam/module/edit/${this.exam.id}`;
        } else if (this.exam.lesson_id != null) {
          formdata.append('exam_for', 'lesson');
          ruta = `/course/exam/lesson/edit/${this.exam.id}`;
        }

        try {
          this.loading = true;
          const request = await axios({
            url: '/course/exam/question/options/store',
            method: 'post',
            data: formdata,
          });
          const response =
            request.status == 200
              ? this.$message.success('La pregunta se ha registrado correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.checkList = [];
          this.opt_1 = null;
          this.opt_2 = null;
          this.opt_3 = null;
          this.opt_4 = null;
          this.opt_5 = null;
          document.location.href = ruta;
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },
    validateNumber(e) {
      let decNum = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', ','];
      if (!decNum.includes(e.key)) {
        e.preventDefault();
        this.$message.warning('Solo se permite números para el campo de puntos');
      }
    },
  },
};
</script>

<style>
.el-checkbox__label {
  font-size: 0;
}
</style>
