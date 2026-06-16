<template>
  <div>
    <div class="row">
      <div class="col-md-2">
        <p>Nota máxima: {{ this.exam.max_score }}</p>
      </div>
      <div class="col-md-4">
        <p>Nota mínima aprobatoria: {{ this.exam.min_passing_score }}</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="col-md-6">
          <h3>
            Lista de Preguntas <span> ({{ this.questions.length }}) </span>
          </h3>
        </div>
        <div class="col-md-6 text-right align-content-right">
          <div class="d-flex flex-row-reverse" >
            <button type="button" class="btn btn-link" @click="preview">Previsualizar</button>
            <button
              type="button"
              class="btn btn-link"
              @click="showInputModule"
              v-if="exam.status != 1"
            >
              (+) Agregar Pregunta
            </button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <ol class="list-group">
          <div v-if="this.questions.length === 0" class="text-center mt-3 mb-3">
            <h4 class="d-inline">
              <span> {{ message }} </span>
            </h4>
          </div>

          <li class="list-group-item p-0" v-if="showDialogAddQuestion == true">
            <div class="row pl-1" style="width: 100%">
              <h3 class="col-sm-12 col-lg-2 m-1 d-inline align-self-center">Pregunta {{ this.questions.length + 1 }}:</h3>
              <input
                style="min-width: 100px;"
                placeholder="Ingrese la pregunta"
                @keyup.enter="storeQuestion"
                type="text"
                class="col-sm-6 col-lg-4 m-1 form-control d-inline"
                aria-label="Default"
                aria-describedby="inputGroup-sizing-default"
                v-model="form.title"
              />
              <button type="button" class="col-sm-3 col-lg-2 m-1 btn btn-primary" @click="storeQuestion">Guardar</button>
            </div>
          </li>

          <!-- question list item  -->
          <div v-if="this.questions.length > 0">
            <exam-question-item
              ref="moduleitems"
              @editQuestion="showEditQuestionModal"
              @deleteQuestion="showDeleteQuestionModal"
              v-for="(question, index) in this.questions"
              :key="question.id"
              :question="question"
              :exam="exam"
              :index="index"
            >
            </exam-question-item>
          </div>
        </ol>
      </div>
    </div>

    <custom-modal id="delete-question-modal" color="danger">
      <template #title
        >Borrar Pregunta <u> {{ mutableQuestion.title }} </u></template
      >
      <template #body>¿Seguro que desea eliminar esta pregunta?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deleteQuestion">Eliminar</button>
      </template>
    </custom-modal>

    <custom-modal id="preview-exam-modal" size="large" color="primary">
      <template #close>
        <button type="button" class="close" @click="closeModal()" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </template>
      <template #title>
        <u>{{ exam.title }}</u>
      </template>
      <template #body>
        <exam-preview :exam="exam"></exam-preview>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import moment from 'moment';
import ModalComponent from '@/components/common/ModalComponent.vue';
import ExamQuestionItem from './examCuestion.vue';
import QuestionDetail from './detailQuestion.vue';
import ExamPreview from '../exam/preview.vue';

export default {
  props: {
    exam: Object,
  },
  components: {
    'custom-modal': ModalComponent,
    'exam-question-item': ExamQuestionItem,
    'question-details': QuestionDetail,
    'exam-preview': ExamPreview,
  },

  data() {
    return {
      message: 'Este examen no cuenta con preguntas',
      moment: moment,
      loading: false,
      form: {},
      showDialogAddQuestion: false,
      mutableQuestion: {},
      isSubmitting: false,
      questions: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
      ],
      exams: null,
      optionSelected: '',
    };
  },
  created() {
    this.listQuestions();
    this.initForm();
  },
  methods: {
    listQuestions() {
      axios
        .get(`/course/exam/question/${this.exam.id}/list`)
        .then((response) => {
          if (response.data.status==='success') {
            this.questions = response.data.data;
          } else {
            this.message = 'Sin preguntas registrados';
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    showInputModule() {
      this.showDialogAddQuestion = true;
    },
    initForm() {
      this.form = {
        title: null,
      };
    },
    validateFields() {
      if (!this.form.title || this.form.title.trim() === '' || this.form.title.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }

      if (this.form.title.length > 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }
      return true;
    },

    showDeleteQuestionModal(value) {
      this.mutableQuestion = value;
      $('#delete-question-modal').modal('show');
    },

    async showEditQuestionModal(value) {
      this.mutableQuestion = value;
      $('#edit-question-modal').modal('show');
    },

    async storeQuestion() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('exam_id', this.exam.id);
        formdata.append('title', this.form.title);
        try {
          this.loading = true;
          const request = await axios({
            url: '/course/exam/question/store',
            method: 'post',
            data: formdata,
          });
          const response =
            request.status == 200
              ? this.$message.success('La pregunta se ha registrado correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.listQuestions();
          this.initForm();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },

    async deleteQuestion() {
      const response = await axios.delete(
        `/course/exam/question/${this.mutableQuestion.id}/delete`
      );
      const { message } =
        (await response.data.status) === 'success'
          ? this.$message.success('Pregunta eliminada correctamente')
          : await this.$message.warning('Error al validar datos');
      this.listQuestions();
      $('#delete-question-modal').modal('hide');
    },

    preview() {
      $('#preview-exam-modal').modal('show');
    },
  },
};
</script>
<style>
.el-checkbox {
  display: block;
  margin-top: 15px;
}
.el-checkbox__input {
  line-height: 4.3;
}
</style>
