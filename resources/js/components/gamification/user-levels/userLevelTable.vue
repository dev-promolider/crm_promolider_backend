<template>
  <div>
    <section>
      <div class="mb-1">
        <button class="btn btn-primary" @click="changeOptionModal('0')">Crear Nivel</button>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-level" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th style="width: 30px">#</th>
                  <th>Descripción</th>
                  <th style="width: 100px">Experiencia Requerida</th>
                  <th style="width: 60px">Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(user_level, index) in this.user_levels" v-bind:key="user_level.id" v-bind:index="index">
                  <td>
                    <p>
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p>
                      {{ user_level.description }}
                    </p>
                  </td>
                  <td>
                    <p>
                      {{ user_level.experience_required }}
                    </p>
                  </td>
                  <td>
                    <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                      @change="getOptionSelected(user_level)">
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

    <custom-modal id="level-modal-level" color="primary" v-if="formLevel">
      <template #title>{{
        optionModal == '0' ? 'Crear Nivel' : 'Editar Nivel' + formLevel.description
      }}</template>
      <template #body>
        <div class="row">
          <div class="col-md-6 col-12 form-group">
            <label for="description">Nombre del nivel:</label>
            <input type="text" :class="['form-control', errors_back.description ? 'is-invalid' : '']"
              v-model.trim="formLevel.description" id="description" />
            <div v-if="errors_back.description" class="invalid-feedback">
              {{ errors_back.description[0] }}
            </div>
          </div>
          <div class="col-md-6 col-12 form-group">
            <label for="experience">Experiencia Requerida:</label>
            <input type="number" :class="['form-control', errors_back.experience_required ? 'is-invalid' : '']"
              v-model.trim="formLevel.experience_required" id="experience" />
            <div v-if="errors_back.experience_required" class="invalid-feedback">
              {{ errors_back.experience_required[0] }}
            </div>
          </div>

          <div class="col-md-12 col-12 form-group">
            <label>Subir Icono</label>
            <el-upload class="picture-card" ref="courseUpload" action="" drag accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG"
              :on-change="onUploadChange" :multiple="false" :limit="1" :auto-upload="false" :file-list="fileList"
              :on-exceed="handleExceed" :on-remove="handleRemove" list-type="picture">
              <i class="el-icon-upload"></i>
              <div class="el-upload__text">
                Suelta tu archivo aquí o <em>haz clic para cargar</em>
              </div>
              <div slot="tip" class="el-upload__tip">
                Solo archivos jpg/png con un tamaño menor de 100kb
              </div>
            </el-upload>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="saveEditLevel()">
          {{ optionModal == '0' ? 'Crear Nivel' : 'Editar Nivel' }}</el-button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner.vue';
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
      user_levels: [],
      fileList: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
      ],
      optionSelected: '',
      formLevel: {},
      url_imagen: '',
      errors_back: {},
    };
  },

  mounted() {
    this.listUserLevels();
    this.initForm();
  },

  methods: {
    fillFiles(icon) {
      this.fileList = [
        {
          name: icon,
          url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/images/levels/${icon}`,
        },
      ];
    },
    handleRemove(fileremoved, file) {
      this.fileList = file;
    },

    handleExceed(file) {
      if (this.fileList.length >= 1) {
        this.$message.error('Solo puede seleccionar 1 archivo!');
        return false;
      }

      const isLt1M = file.size < 100000;

      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        return false;
      }
    },

    onUploadChange(file) {
      this.fileList = [];

      const isImage =
        file.raw.type === 'image/jpeg' ||
        file.raw.type === 'image/png' ||
        file.raw.type === 'image/jpg';

      const isLt1M = file.size < 100000;

      if (!isImage) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png.');
        this.$refs.courseUpload.clearFiles();
        return false;
      }
      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        this.$refs.courseUpload.clearFiles();
        return false;
      }

      this.fileList.push(file);
    },
    loadDataTable() {
      $('#data-table-list-level').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-level').DataTable(language);
      });
    },
    initForm() {
      this.formLevel = {
        id: '',
        description: '',
        experience_required: '',
        url_icon: null,
      };
    },

    select_file(event) {
      this.url_imagen = event.target.files[0];
    },

    listUserLevels: function () {
      this.initialLoading = true;
      api.get(`/user-levels/list`).then((response) => {
        this.user_levels = response.data;
        this.initialLoading = false;
        this.loadDataTable();
      });
    },

    changeOptionModal(option) {
      this.initForm();
      this.optionModal = option;
      if (this.optionModal == '0') {
        $('#level-modal-level').modal('show');
      }
    },

    saveEditLevel() {
      if (this.validateFields()) {
        this.requestLoading = true;
        const formdata = new FormData();
        formdata.append('id', this.formLevel.id);
        formdata.append('description', this.formLevel.description);
        formdata.append('experience_required', this.formLevel.experience_required);

        if (this.fileList.length > 0) {
          formdata.append('file', this.fileList[0].raw);
        }

        api
          .post('/user-levels/createUpdate', formdata)
          .then((response) => {
            if (response == 'ok') {
              this.listUserLevels();
              this.requestLoading = false;
              $('#level-modal-level').modal('hide');
              this.$message.success('Acción Procesada');
            } else if (response == 'error_imagen') {
              this.requestLoading = false;
              this.$message.warning('Ingrese una imagen');
            } else if (response == 'error') {
              this.requestLoading = false;
              this.$message.warning('Error al procesar la acción');
            } else {
              this.requestLoading = false;
              //console.log(response);
              this.$message.warning(response);
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

    getOptionSelected(user_level) {
      this.changeOptionModal('1');
      let option = this.optionSelected;

      switch (option) {
        case '1':
          this.formLevel = user_level;
          this.fillFiles(user_level.url_icon);
          $('#level-modal-level').modal('show');
          this.optionSelected = '';
          break;

        default:
          console.log('Error');
          break;
      }
    },
    validateFields() {
      if (!this.formLevel.description || this.formLevel.description.length === 0) {
        this.$message.warning('El campo descripción está vacío!');
        return false;
      }
      if (!this.formLevel.experience_required || this.formLevel.experience_required.length === 0) {
        this.$message.warning('El campo Experiencia Requerida está vacío!');
        return false;
      }
      return true;
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
