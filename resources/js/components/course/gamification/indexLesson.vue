<template>
  <div>
    <div class="card" v-loading="loading" element-loading-text="Cargando..." element-loading-spinner="el-icon-loading"
      element-loading-background="rgba(0, 0, 0, 0.6)">
      <div class="card-body">
        <form @submit.prevent="submit" id="upload" method="post">
          <div class="row text-center">
            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Módulo</label>
                <el-select v-model="mutableModule" placeholder="Seleccione el módulo" class="d-inline"
                  @input="handleChangeModule">
                  <el-option v-for="m in modules" :key="m.id" :label="m.name" :value="m.id">
                  </el-option>
                </el-select>
              </div>
            </div>

            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Clase</label>
                <el-select v-model="mutableLesson" placeholder="Seleccione la clase" class="d-inline"
                  @input="handleChangeLesson">
                  <el-option v-for="l in lessons" :key="l.id" :label="l.name" :value="l.id">
                  </el-option>
                </el-select>
              </div>
            </div>

            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Título de la dinámica</label>
                <input type="text" v-model="form.title" class="form-control" placeholder="Titulo de la dinámica" />
              </div>
            </div>

            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Tipo de juego</label>
                <el-select v-model="form.game_type" placeholder="Seleccione el tipo de juego" class="d-inline">
                  <el-option v-for="g in game_type" :key="g.id" :label="g.title" :value="g.id">
                  </el-option>
                </el-select>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success" :disabled="isSubmitting">Guardar</button>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <h3 class="mb-2">Lista de dinámicas</h3>
            <div class="table-responsive">
              <table id="table-module-games" class="table table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Juego</th>
                    <th>Título</th>
                    <th>Fecha creación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(game, index) in this.games" v-bind:key="game.id" v-bind:index="index">
                    <td>
                      <p style="width: 15px">
                        {{ (index = index + 1) }}
                      </p>
                    </td>
                    <td>
                      <p style="width: 140px">
                        {{ game.game_type }}
                      </p>
                    </td>
                    <td>
                      <p style="width: 140px">
                        {{ game.title }}
                      </p>
                    </td>

                    <td>{{ moment(game.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                    <td>
                      <span class="badge badge-success" v-if="game.status === 1">Activo</span>
                      <span class="badge badge-secondary" v-else>Inactivo</span>
                    </td>
                    <td>
                      <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                        @change="getOptionSelected(game)">
                        <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                        </el-option>
                      </el-select>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-md-12 text-center">
            <div class="alert alert-primary py-1" role="alert">
              Solo puede activar una dinámica por tipo de juego
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import language from '../../../api/traduction_es';

export default {
  props: {
    course: Object,
    modules: Array,
    game_type: Array,
  },

  data() {
    return {
      moment: moment,
      loading: false,
      isEmpty: true,
      mutableLesson: null,
      mutableModule: null,
      lessons: null,
      form: {},
      isSubmitting: false,
      options: [
        {
          value: '1',
          label: 'Configurar',
        },
        {
          value: '2',
          label: 'Activar/Desactivar',
        },
      ],
      games: null,
      optionSelected: '',
    };
  },
  created() {
    this.initForm();
  },
  mounted() {
    this.listGames();
  },

  methods: {
    initForm() {
      this.form = {
        title: null,
        game_type: null,
      };
    },

    listLessons(module_id) {
      this.mutableLesson = null;
      axios
        .get(`/course/module/class/${module_id}/classList`)
        .then((response) => {
          console.log(response.data.data);
          this.lessons = response.data.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    handleChangeModule: function (value) {
      this.listLessons(value);
    },

    handleChangeLesson: function (value) {
      this.isEmpty = false;
      this.listGames(value);
    },

    listGames(lesson_id) {
      axios
        .get(`/course/game/lesson/${lesson_id}/list`)
        .then((response) => {
          if (response.data.error) {
            this.message = 'Sin dinámicas registradas';
          } else {
            this.games = response.data;
          }
          this.loadDataTable();
        })
        .catch((error) => {
          console.log(error);
        });
    },

    loadDataTable() {
      $('#table-module-games').DataTable().destroy();
      this.$nextTick(function () {
        $('#table-module-games').DataTable(language);
      });
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

      if (!this.form.game_type || this.form.game_type.length === 0) {
        this.$message.warning('No ha seleccionado el tipo de juego');
        return false;
      }

      return true;
    },

    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('lesson_id', this.mutableLesson);
        formdata.append('title', this.form.title);
        formdata.append('game_type_id', this.form.game_type);
        formdata.append('course_id', this.course.id);
        formdata.append('game_for', 'lesson');
        try {
          this.loading = true;
          const request = await axios({
            url: '/course/game/store',
            method: 'post',
            data: formdata,
          });
          const response =
            request.status == 200
              ? this.$message.success('La dinámica ha sido registrado correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.listGames(this.mutableLesson);
          this.initForm();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },

    getOptionSelected(game) {
      let option = this.optionSelected;
      switch (option) {
        case '1':
          document.location.href = `/course/game/edit/${game.id}`;
          break;
        case '2':
          this.activateGame(
            game.id,
            game.course_id,
            game.lesson_id,
            game.module_id,
            game.game_type_id
          );
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
      }
    },

    async activateGame(id, course_id, lesson_id, module_id, game_type_id) {
      const formdata = new FormData();
      formdata.append('id', id);
      formdata.append('course_id', course_id);
      formdata.append('lesson_id', lesson_id);
      formdata.append('module_id', module_id);
      formdata.append('game_type_id', game_type_id);
      try {
        const request = await axios({
          url: '/course/game/activate',
          method: 'post',
          data: formdata,
        });

        if ((await request.data) == 'success') {
          this.$message.success('El juego ha sido activado correctamente');
        } else {
          this.$message.warning('Configure el juego antes de activarlo');
        }
        this.listGames();
      } catch (error) {
        console.log(error);
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
