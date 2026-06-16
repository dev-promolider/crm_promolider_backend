<template>
  <div>
    <section>
      <div class="mb-1">
        <button class="btn btn-primary" @click="changeOptionModal('0')">Crear logro</button>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-badges" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Descripción</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(badge, index) in this.badges" v-bind:key="badge.id" v-bind:index="index">
                  <td>
                    <p style="width: 15px">
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 220px">
                      {{ badge.description }}
                    </p>
                  </td>
                  <td>
                    <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                      @change="getOptionSelected(badge)">
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

    <custom-modal id="level-modal-badges" color="primary" v-if="formBadge">
      <template #title>{{
        optionModal == '0' ? 'Crear Logro' : 'Editar Logro ' + formBadge.description
      }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div class="col-12 form-group">
                <label for="name">Nombre del Logro:</label>
                <input type="text" :class="['form-control', errors_back.name ? 'is-invalid' : '']"
                  v-model.trim="formBadge.name" id="name" />
                <div v-if="errors_back.name" class="invalid-feedback">
                  {{ errors_back.name[0] }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 form-group">
            <label for="description">Descripción:</label>
            <input type="text" :class="['form-control', errors_back.description ? 'is-invalid' : '']"
              v-model.trim="formBadge.description" id="name" />
            <div v-if="errors_back.description" class="invalid-feedback">
              {{ errors_back.description[0] }}
            </div>
          </div>

          <div class="col-lg-12">
            <div class="form-group">
              <label for="level">Nivel :</label>

              <el-select v-model="formBadge.level" placeholder="Seleccione un nivel" class="d-inline" id="combovalue">
                <el-option v-for="l in levelArray" :key="l.id" :label="l.desc" :value="l.id">
                </el-option>
              </el-select>
            </div>
          </div>

          <div class="col-12 form-group">
            <label for="description">Condicion:</label>
            <input type="number" :class="['form-control', errors_back.condition ? 'is-invalid' : '']"
              v-model.trim="formBadge.condition" id="condition" />
            <div v-if="errors_back.condition" class="invalid-feedback">
              {{ errors_back.condition[0] }}
            </div>
          </div>

          <div class="col-12 form-group">
            <label for="credits">Créditos otorgados:</label>
            <input
              type="number"
              :class="['form-control', errors_back.credits ? 'is-invalid' : '']"
              v-model.number="formBadge.credits"
              id="credits"
            />
            <div v-if="errors_back.credits" class="invalid-feedback">
              {{ errors_back.credits[0] }}
            </div>
          </div>

          <div class="col-12 form-group">
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
          {{ optionModal == '0' ? 'Crear Logro' : 'Editar Logro' }}</el-button>
      </template>
    </custom-modal>

    <custom-modal id="modal-canje" color="warning" v-if="modalCanjeVisible">
      <template #title>
        Modificar Canje - {{ badgeSelected.description }}
      </template>
    
      <template #body>
        <div class="form-group">
          <label>Crédito otorgado por este logro:</label>
          <input
            type="number"
            class="form-control"
            v-model.number="badgeSelected.credit_exchange"
          />
        </div>
      </template>
    
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger" @click="closeCanjeModal">Cancelar</el-button>
      
        <el-button class="btn btn-primary" :loading="requestLoading" @click="saveCanje">
          Guardar Canje
        </el-button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../../api/traduction_es';

export default {
  name: 'badges-table',
  components: {
    'custom-modal': ModalComponent,
  },

  data() {
    return {
      initialLoading: false,
      requestLoading: false,
      modalCanjeVisible: false,
      badgeSelected: {},
      optionModal: '0',
      badges: [],
      fileList: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Modificar canje', // 👈 nueva opción
        },
      ],
      optionSelected: '',
      formBadge: {},
      url_imagen: '',
      errors_back: {},
      logo1: '',
      levelArray: [
        {
          id: 1,
          desc: 'Level 1',
        },
        {
          id: 2,
          desc: 'Level 2',
        },
        {
          id: 3,
          desc: 'Level 3',
        },
      ],
    };
  },

  mounted() {
    this.listUserLevels();
    this.initForm();
  },

  methods: {
    fillFiles(icon) {
      // if (this.env == 'production') {
      this.fileList = [
        {
          name: icon,
          url: `${icon}`,
        },
      ];
      // } else {
      //   this.fileList = [
      //     {
      //       name: this.mutableCourse.portada,
      //       url: `${this.url}/storage/${this.mutableCourse.url_portada}`,
      //     },
      //   ];
      // }
    },
    loadDataTable() {
      $('#data-table-list-badges').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-badges').DataTable(language);
      });
    },
    initForm() {
      this.formBadge = {
        id: '',
        name: '',
        description: '',
        level: '',
        condition: '',
        credits: 0,   // 👈 AQUI
        experience_required: '',
        logo: null,
      };
    },

    select_file(event) {
      this.url_imagen = event.target.files[0];
    },
    listUserLevels: function () {
      this.initialLoading = true;
      api.get(`/badges/list-all`).then((response) => {
        this.badges = response.data;
        this.initialLoading = false;
        this.loadDataTable();
      });
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

    changeOptionModal(option) {
      this.initForm();
      this.optionModal = option;
      if (this.optionModal == '0') {
        $('#level-modal-badges').modal('show');
      }
    },

    saveEditLevel() {
      if (this.validateFields()) {
        this.requestLoading = true;
        const formdata = new FormData();
      
        formdata.append('id', this.formBadge.id);
        formdata.append('name', this.formBadge.name);
        formdata.append('description', this.formBadge.description);
        formdata.append('level', this.formBadge.level);
        formdata.append('condition', this.formBadge.condition);
        formdata.append('credits', this.formBadge.credits); // 👈 NUEVO
      
        if (this.fileList.length > 0) {
          formdata.append('file', this.fileList[0].raw);
        }
      
        var ruta = this.optionModal == 0 ? '/badges/store' : '/badges/update';
        api
          .post(ruta, formdata)
          .then((response) => {
            if (response.status == 'ok') {
              this.listUserLevels();
              this.requestLoading = false;
              $('#level-modal-badges').modal('hide');
              this.$message.success('Acción Procesada');
            } else if (response.status == 'error_imagen') {
              this.requestLoading = false;
              this.$message.warning('Ingrese una imagen');
            } else {
              this.requestLoading = false;
              this.$message.warning(response.status);
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

    closeCanjeModal() {
      this.modalCanjeVisible = false;
      $('#modal-canje').modal('hide');
    },
    
    saveCanje() {
      this.requestLoading = true;
    
      api.post('/badges/update-canje', {
        id: this.badgeSelected.id,
        credit_exchange: this.badgeSelected.credit_exchange,
      })
      .then((response) => {
        this.requestLoading = false;
        if (response.status == 'ok') {
          this.$message.success('Canje actualizado correctamente');
          this.listUserLevels();
          this.closeCanjeModal();
        } else {
          this.$message.error('Error al actualizar el canje');
        }
      })
      .catch(() => {
        this.requestLoading = false;
        this.$message.error('Error del servidor');
      });
    },

    getOptionSelected(badge) {
      let option = this.optionSelected;
    
      switch (option) {
        case '1': // Editar
          this.changeOptionModal('1');
          this.formBadge = { ...badge };
          this.fillFiles(badge.icon);
          $('#level-modal-badges').modal('show');
          this.optionSelected = '';
          break;
      
        case '2': // 👈 Modificar canje
          this.badgeSelected = { ...badge };
          this.modalCanjeVisible = true;
          $('#modal-canje').modal('show');
          this.optionSelected = '';
          break;
      
        default:
          console.log('Opción no válida');
          break;
      }
    },
    validateFields() {
      if (!this.formBadge.name || this.formBadge.name.length === 0) {
        this.$message.warning('El campo Nombre del Logro está vacío!');
        return false;
      }
      if (!this.formBadge.description || this.formBadge.description.length === 0) {
        this.$message.warning('El campo Descripción está vacío!');
        return false;
      }
      if (!this.formBadge.level || this.formBadge.level.length === 0) {
        this.$message.warning('El campo Nivel está vacío!');
        return false;
      }
      if (!this.formBadge.condition || this.formBadge.condition.length === 0) {
        this.$message.warning('El campo Condicion está vacío!');
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
