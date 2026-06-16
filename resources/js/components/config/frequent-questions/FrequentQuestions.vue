<template>
  <div>
    <section>
      <div class="mb-3">
        <button class="btn btn-primary" @click="changeOptionModal('0')">Crear Pregunta frecuente</button>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-frequent-questions"
              class="table table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th class="col-1">#</th>
                  <th>Pregunta</th>
                  <th class="col-1">Estado</th>
                  <th class="col-3">Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(frequentQuestion, index) in this.frequentQuestions" v-bind:key="frequentQuestion.id"
                  v-bind:index="index">
                  <td>
                    <p>
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p>
                      {{ frequentQuestion.question }}
                    </p>
                  </td>

                  <td>
                    <span class="badge badge-success" v-if="frequentQuestion.status === 1">Activo</span>
                    <span class="badge badge-secondary" v-else>Inactivo</span>
                  </td>

                  <td>
                    <el-select size="mini" clearable v-model="optionSelected"
                      @change="getOptionSelected(frequentQuestion)">
                      <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
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

    <custom-modal id="level-modal" color="primary" v-if="formFrequentQuestions" size="large">
      <template #title>{{
          optionModal == '0' ? 'Crear Pregunta Frecuente' : 'Editar Pregunta Frecuente '
      }}</template>
      <template #body>
        <div class="row">

          <div class="col-12 mt-2">
            <div class="col-12 form-group">
              <label for="exp">Pregunta:</label>
              <textarea class="form-control" v-model="formFrequentQuestions.question" type="text"
                :class="['form-control', errors_back.template ? 'is-invalid' : '']"></textarea>

            </div>
          </div>

          <div class="col-12">
            <div class="col-12 form-group">
              <label for="exp">Respuesta:</label>
              <textarea class="form-control" style="min-height:120px" v-model="formFrequentQuestions.answer" type="text"
                :class="['form-control', errors_back.template ? 'is-invalid' : '']"></textarea>

            </div>
          </div>

        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="saveOrUpdateFrecuentQuestions()">
          {{ optionModal == '0' ? 'Crear Pregunta Frecuente' : 'Editar Pregunta Frecuente' }}</el-button>
      </template>
    </custom-modal>

    <custom-modal id="delete-frequent-question-modal" color="danger">
      <template #title
        >Eliminar Pregunta Frecuente</template
      >
      <template #body><strong>¿Seguro que desea eliminar esta pregunta?</strong><br>{{ formFrequentQuestions.question }} </template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deleteFrequentQuestion(formFrequentQuestions.id)">
          Eliminar
        </button>
      </template>
    </custom-modal>

  </div>
</template>

<style>
.tox-notification {
  display: none !important
}
</style>

<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../../api/traduction_es';

export default {
  name: 'user-level-table',
  components: {
    'custom-modal': ModalComponent,
  },

  data() {
    return {
      initialLoading: false,
      requestLoading: false,
      optionModal: '0',
      frequentQuestions: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Eliminar',
        },
        {
          value: '3',
          label: 'Activar/Desactivar',
        },
      ],
      optionSelected: '',
      formFrequentQuestions: {},
      url_imagen: '',
      errors_back: {},
    };
  },
  mounted() {
    this.initForm();
    this.listFrequentQuestions();
  },

  methods: {
    initForm() {
      this.formFrequentQuestions = {
        id: '',
        question: '',
        answer: '',
        status: '',
      };
      this.errors_back = {};
    },

    loadDataTable() {
      $('#data-table-list-frequent-questions').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-frequent-questions').DataTable(language);
      });
    },

    listFrequentQuestions: function () {
      this.initialLoading = true;
      api.get(`/config/frequent-questions/list`).then((response) => {
        this.frequentQuestions = response.data;
        this.loadDataTable();
        this.initialLoading = false;
      }).catch().finally();
    },

    changeOptionModal(option) {
      this.initForm();
      this.optionModal = option;
      if (this.optionModal == '0') {
        this.initForm();
        $('#level-modal').modal('show');
      }
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ?),.;:$/-]+$/g);
      return regex.test(text);
    },

    getOptionSelected(frequentQuestion) {
      this.changeOptionModal('1');
      let option = this.optionSelected;

      switch (option) {
        case '1':
          this.formFrequentQuestions = frequentQuestion;
          $('#level-modal').modal('show');
          this.optionSelected = '';
          break;
        case '2':
          this.formFrequentQuestions = frequentQuestion;
          $('#delete-frequent-question-modal').modal('show');
          this.optionSelected = '';
          break;
        case '3':
          this.changeStatus(frequentQuestion);
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
      }
    },
    validateFields() {
      if (
        !this.formFrequentQuestions.question ||
        this.formFrequentQuestions.question.trim() === '' ||
        this.formFrequentQuestions.question.length === 0
      ) {
        this.$message.warning('El campo de pregunta esta vacío!');
        return false;
      }

      if(!this.hasEspecialCharacters(this.formFrequentQuestions.question)){
        this.$message.warning('Ingrese solo caracteres válidos!');
        return false;
      }

      if (
        !this.formFrequentQuestions.answer ||
        this.formFrequentQuestions.answer.trim() === '' ||
        this.formFrequentQuestions.answer.length === 0
      ) {
        this.$message.warning('El campo de respuesta esta vacío!');
        return false;
      }
      if(!this.hasEspecialCharacters(this.formFrequentQuestions.answer)){
        this.$message.warning('Ingrese solo caracteres válidos!');
        return false;
      }
      
      return true;
    },
    saveOrUpdateFrecuentQuestions() {
      if (this.optionModal == 0) {
        this.saveFrecuentQuestions()
      } else if (this.optionModal == 1) {
        this.updateFrecuentQuestions();
      }
    },
    saveFrecuentQuestions() {
      if (this.validateFields()) {
        this.requestLoading = true;
        const formdata = new FormData();
        formdata.append('id', this.formFrequentQuestions.id);
        formdata.append('question', this.formFrequentQuestions.question);
        formdata.append('answer', this.formFrequentQuestions.answer);
        api
          .post('/config/frequent-questions/add', formdata)
          .then((response) => {
            if (response == 'ok') {
              this.listFrequentQuestions();
              this.requestLoading = false;
              $('#level-modal').modal('hide');
              this.$message.success('Registro guardado correctamente');

            } else if (response == 'error') {
              this.$message.warning('Error al realizar la acción');
            } else {
              this.requestLoading = false;
              this.$message.warning('Error al realizar la acción');
            }
          })
          .catch((error) => {
            if (error.response.status == 422) {
              this.requestLoading = false;
              this.errors_back = error.response.data.errors;
            }
          });
      }
    },
    updateFrecuentQuestions() {
      if (this.validateFields()) {
        
        this.requestLoading = true;

        const formdata = new FormData();
        formdata.append('id', this.formFrequentQuestions.id);
        formdata.append('question', this.formFrequentQuestions.question);
        formdata.append('answer', this.formFrequentQuestions.answer);

        api
          .post('/config/frequent-questions/update', formdata)
          .then((response) => {
            if (response == 'ok') {
              this.listFrequentQuestions();
              this.requestLoading = false;
              $('#level-modal').modal('hide');
              this.$message.success('Registro actualizado correctamente');

            } else if (response == 'error') {
              this.$message.warning('Error al realizar la acción');
            } else {
              this.requestLoading = false;
              this.$message.warning('Error al realizar la acción');
            }
          })
          .catch((error) => {
            if (error.response.status == 422) {
              this.requestLoading = false;
              this.errors_back = error.response.data.errors;
            }
          });
      }
    },

    changeStatus(frequentQuestion) {
      this.requestLoading = true;

      this.formFrequentQuestions = frequentQuestion;

      const formdata = new FormData();
      formdata.append('id', this.formFrequentQuestions.id);
      formdata.append('status', this.formFrequentQuestions.status);

      api
        .post('/config/frequent-questions/status', formdata)
        .then((response) => {
          if (response == 'ok') {
            this.listFrequentQuestions();
            this.requestLoading = false;
            this.$message.success('Registro actualizado correctamente');

          } else if (response == 'error') {
            this.$message.warning('Error al realizar la acción');
          } else {
            this.requestLoading = false;
            this.$message.warning('Error al realizar la acción');
          }
        })
        .catch((error) => {
          if (error.response.status == 422) {
            this.requestLoading = false;
            this.errors_back = error.response.data.errors;
          }
        });
    },

    deleteFrequentQuestion(id) {
      api.delete('/config/frequent-questions/' + id)
        .then((response) => {
          if (response == 'ok') {
            this.listFrequentQuestions();
            this.requestLoading = false;
            $('#delete-frequent-question-modal').modal('hide');
            this.$message.success('Registro eliminado correctamente');
          } else {
            this.requestLoading = false;
            this.$message.warning('Error al realizar la acción');
          }
        })
        .catch((error) => {
          console.log(error);
        });
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

