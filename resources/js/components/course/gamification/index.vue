<template>
  <div>
    <div class="card" v-loading="loading" element-loading-text="Cargando..." element-loading-spinner="el-icon-loading"
      element-loading-background="rgba(0, 0, 0, 0.6)">
      <div class="card-body">
        <form @submit.prevent="submit" id="upload" method="post">
          <div class="row text-center">
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
              <table id="data-table-list-exams" class="table table-hover-animation table-striped table-bordered">
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
                  <tr v-for="(game, index) in games" v-bind:key="game.id" v-bind:index="index">
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
    game_type: Array,
  },

  data() {
    return {
      moment: moment,
      loading: false,
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
        {
          value: '3',
          label: 'Pre-visualizar',
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

    listGames() {
      axios
        .get(`/course/game/${this.course.id}/list`)
        .then((response) => {
          if (response.data.error) {
            this.$message.error(response.data.message || 'Error al cargar las dinámicas');
            this.games = [];
          } else {
            console.log(response.data.data);
            this.games = response.data.data;
          }
          this.loadDataTable();
        })
        .catch((error) => {
          console.log(error);
          if (error.response && error.response.status === 403) {
            this.$message.error('No tienes permisos para acceder a este curso');
            // Opcional: redirigir al usuario
            // window.location.href = '/courses';
          } else if (error.response && error.response.status === 404) {
            this.$message.error('Curso no encontrado');
          } else {
            this.$message.error('Error al cargar las dinámicas');
          }
          this.games = [];
          this.loadDataTable();
        });
    },

    loadDataTable() {
      // Verificar si DataTable ya existe y destruirlo
      if ($.fn.DataTable.isDataTable('#data-table-list-exams')) {
        $('#data-table-list-exams').DataTable().destroy();
      }
      
      this.$nextTick(function () {
        if (this.games && this.games.length > 0) {
          $('#data-table-list-exams').DataTable(language);
        }
      });
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
        formdata.append('title', this.form.title);
        formdata.append('game_type_id', this.form.game_type);
        formdata.append('course_id', this.course.id);
        formdata.append('game_for', 'course');
        
        try {
          this.loading = true;
          const request = await axios({
            url: '/course/game/store',
            method: 'post',
            data: formdata,
          });
          
          if (request.status === 200) {
            if (request.data.success) {
              this.$message.success(request.data.message || 'La dinámica ha sido registrada correctamente');
              this.listGames();
              this.initForm();
            } else {
              this.$message.warning(request.data.message || 'Error al validar datos');
            }
          }
          
        } catch (error) {
          console.log(error);
          if (error.response) {
            if (error.response.status === 403) {
              this.$message.error('No tienes permisos para crear dinámicas en este curso');
            } else if (error.response.status === 404) {
              this.$message.error('Curso no encontrado');
            } else if (error.response.data && error.response.data.message) {
              this.$message.error(error.response.data.message);
            } else {
              this.$message.error('Error al crear la dinámica');
            }
          } else {
            this.$message.error('Error de conexión');
          }
        } finally {
          this.isSubmitting = false;
          this.loading = false;
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
        case '3':
          document.location.href = `/course-user/dynamic/${game.id}`;
          break;
        default:
          console.log('Error');
          break;
      }
      this.optionSelected = '';
    },

    async activateGame(id, course_id, lesson_id, module_id, game_type_id) {
      const formdata = new FormData();
      formdata.append('id', id);
      formdata.append('course_id', course_id);
      formdata.append('lesson_id', lesson_id || '');
      formdata.append('module_id', module_id || '');
      formdata.append('game_type_id', game_type_id);
      
      try {
        const request = await axios({
          url: '/course/game/activate',
          method: 'post',
          data: formdata,
        });

        if (request.data.success) {
          this.$message.success(request.data.message || 'El juego ha sido activado correctamente');
        } else {
          this.$message.warning(request.data.message || 'Configure el juego antes de activarlo');
        }
        
        this.listGames();
        
      } catch (error) {
        console.log(error);
        if (error.response) {
          if (error.response.status === 403) {
            this.$message.error('No tienes permisos para activar este juego');
          } else if (error.response.status === 404) {
            this.$message.error('Juego no encontrado');
          } else if (error.response.data && error.response.data.message) {
            this.$message.error(error.response.data.message);
          } else {
            this.$message.error('Error al activar el juego');
          }
        } else {
          this.$message.error('Error de conexión');
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
</style>