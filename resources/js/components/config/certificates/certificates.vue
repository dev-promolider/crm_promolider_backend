<template>
  <div>
    <section>
      <div class="mb-1">
        <button class="btn btn-primary" @click="changeOptionModal('0')">Crear Certificado</button>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-certificates" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th style="width: 100px">#</th>
                  <th>Plantilla</th>
                  <th style="width: 100px">Accion</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(certificate, index) in this.certificates" v-bind:key="certificate.id" v-bind:index="index">
                  <td>
                    <p style="width: 15px">
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 220px">
                      {{ certificate.name }}
                    </p>
                  </td>
                  <td>
                    <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                      @change="getOptionSelected(certificate)">
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
      <div class="alert alert-info p-2 text-center" role="alert">
        Su firma configurada se utilizará en todas las plantillas de los certificados.
      </div>
    </section>

    <custom-modal id="certificate-modal" color="primary" v-if="formCertificate" size="large">
      <template #title>{{
        optionModal == '0' ? 'Crear Certificado' : 'Editar Certificado ' + formCertificate.name
      }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div class="col-6 form-group">
                <label for="name">Nombre del certificado:</label>
                <input type="text" :class="['form-control', errors_back.name ? 'is-invalid' : '']"
                  v-model="formCertificate.name" id="name" />
                <div v-if="errors_back.name" class="invalid-feedback">
                  {{ errors_back.name[0] }}
                </div>
              </div>

              <div>
                <div class="custom-file" lang="es">
                  <label type="file" :class="['custom-file-input', errors_back.signature ? 'is-invalid' : '']"
                    id="customFile" @change="select_file"></label>
                  <div v-if="errors_back.signature" class="invalid-feedback">
                    {{ errors_back.signature[0] }}
                  </div>
                </div>
              </div>

              <div class="col-6 form-group">
                <label>Subir Firma</label>
                <el-upload class="picture-card" ref="courseUpload" action="" drag
                  accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG" :on-change="onUploadChange" :multiple="false" :limit="1"
                  :auto-upload="false" :file-list="fileList" :on-exceed="handleExceed" :on-remove="handleRemove"
                  list-type="picture">
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
          </div>
          <div class="col-12 mt-2">
            <div class="col-12 form-group">
              <label for="exp">Plantilla:</label>
              <!-- TINYMCE EDITOR COMENTADO -->
              <!-- <editor type="text" :class="['form-control', errors_back.template ? 'is-invalid' : '']"
                v-model="formCertificate.template" id="exp" rows="12"
                api-key="noxug04csuqnyp6la7x4m9w54hjcv62q5frb5s7uhcv8ns48" :init="{
                  height: 500,
                  plugins: [
                    'image code',
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount',
                    'autoresize',
                  ],
                  language: 'es',
                  toolbar:
                    'undo redo | image code | undo redo | formatselect | bold italic backcolor | \
                            alignleft aligncenter alignright alignjustify | \
                            bullist numlist outdent indent | removeformat | help',
                  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                  content_css: `/../css/core.css`,
                }" /> -->
              
              <!-- REEMPLAZO TEMPORAL CON TEXTAREA -->
              <textarea :class="['form-control', errors_back.template ? 'is-invalid' : '']"
                v-model="formCertificate.template" id="exp" rows="12"></textarea>

              <div v-if="errors_back.template" class="invalid-feedback">
                {{ errors_back.template[0] }}
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="saveEditLevel()">
          {{ optionModal == '0' ? 'Crear Certificado' : 'Editar Certificado' }}</el-button>
      </template>
    </custom-modal>

    <custom-modal id="delete-certificate-modal" color="danger">
      <template #title>Eliminar Certificado <u> {{ formCertificate.name }} </u></template>
      <template #body>¿Está seguro de que desea eliminar el certificado?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deleteCertificate(formCertificate.id)">
          Eliminar
        </button>
      </template>
    </custom-modal>
  </div>
</template>
<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../../api/traduction_es';
// IMPORT DE TINYMCE COMENTADO
// import Editor from '@tinymce/tinymce-vue';

export default {
  props: ['path'],
  name: 'user-level-table',
  components: {
    'custom-modal': ModalComponent,
    // COMPONENTE EDITOR DE TINYMCE COMENTADO
    // editor: Editor,
  },

  data() {
    return {
      initialLoading: false,
      requestLoading: false,
      extensions: '.jpeg, .jpg, .png',
      fileList: [],
      optionModal: '0',
      certificates: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Eliminar',
        },
      ],
      optionSelected: '',
      formCertificate: {},
      url_imagen: '',
      errors_back: {},
      img_signature: '',
    };
  },

  /*   watch: {
    course: function (value) {
      this.formCertificate = value;
      this.fillFiles();
    },
  }, */

  mounted() {
    this.initForm();

    this.listCertificates();

    // SOLUCION AL ERROR DE BOOTSTRAP (MODAL) Y TINYMCE - COMENTADO
    /*
    $(document).on('focusin', function (e) {
      if ($(e.target).closest('.tox-tinymce-aux, .moxman-window, .tam-assetmanager-root').length) {
        e.stopImmediatePropagation();
      }
    });
    */
  },

  methods: {
    fillFiles() {
      // if (this.env == 'production') {
      if (this.img_signature != '') {
        this.fileList = [
          {
            name: `${this.getNameSignature(this.img_signature)}`,
            // url: this.path+'/'+this.formCertificate.signature,
            url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${this.img_signature
              }?a=${Math.random()}`,
          },
        ];
      }
      //console.log(this.path+'/'+this.formCertificate.signature);
      // } else {
      //   this.fileList = [
      //     {
      //       name: this.mutableCourse.portada,
      //       url: `${this.url}/storage/${this.mutableCourse.url_portada}`,
      //     },
      //   ];
      // }
    },
    getNameSignature(signature) {
      let rutaSignature = signature.split('/');
      return rutaSignature[rutaSignature.length - 1];
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

    initForm() {
      this.formCertificate = {
        id: '',
        name: '',
        template: '',
        signature: null,
      };
      this.errors_back = {};
    },

    select_file(event) {
      this.url_imagen = event.target.files[0];
    },

    loadDataTable() {
      $('#data-table-list-certificates').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-certificates').DataTable(language);
      });
    },

    listCertificates: function () {
      this.initialLoading = true;
      api
        .get(`/config/certificates/list`)
        .then((response) => {
          this.certificates = response.data[0];

          this.loadDataTable();
          this.initialLoading = false;

          if (response.data[1][0] != undefined) {
            this.img_signature = response.data[1][0].value;
          }
          this.fillFiles();
        })
        .catch()
        .finally();
    },

    changeOptionModal(option) {
      this.initForm();
      this.optionModal = option;
      if (this.optionModal == '0') {
        this.initForm();
        $('#certificate-modal').modal('show');
      }
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },

    validateFields() {
      if (
        !this.formCertificate.name ||
        this.formCertificate.name.trim() === '' ||
        this.formCertificate.name.length === 0
      ) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }
      if (this.formCertificate.name.length > 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }

      if (!this.hasEspecialCharacters(this.formCertificate.name)) {
        this.$message.warning('El título no puede contener caracteres especiales');
        return false;
      }

      if (
        !this.formCertificate.template ||
        this.formCertificate.template.trim() === '' ||
        this.formCertificate.template.length === 0
      ) {
        this.$message.warning('Plantilla esta vacio!');
        return false;
      }
      return true;
    },

    saveEditLevel() {
      let file_signature = undefined;
      if (this.fileList[0] != undefined) {
        file_signature = this.fileList[0].raw;
      }
      if (this.validateFields()) {
        this.requestLoading = true;
        const formdata = new FormData();
        formdata.append('id', this.formCertificate.id);
        formdata.append('name', this.formCertificate.name);
        formdata.append('template', this.formCertificate.template);
        formdata.append('signature', file_signature);

        api
          .post('/config/certificates/add', formdata)
          .then((response) => {
            if (response == 'ok') {
              this.listCertificates();
              this.requestLoading = false;
              $('#certificate-modal').modal('hide');
              this.$message.success('Acción Procesada');
            } else if (response == 'error_img') {
              this.$message.warning('Ingrese una imagen');
              this.requestLoading = false;
            } else {
              this.$message.warning('Error al realizar la acción');
              this.requestLoading = false;
              console.log(response);
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
    deleteCertificate(id) {
      api
        .delete('/config/certificates/' + id)
        .then((response) => {
          if (response == 'ok') {
            this.listCertificates();
            this.requestLoading = false;
            this.$message.success('Acción Procesada');
          } else {
            this.requestLoading = false;
            this.$message.warning('Error al realizar la acción');
          }
        })
        .catch((error) => {
          console.log(error);
        });
      $('#delete-certificate-modal').modal('hide');
    },
    //obtener la opcion seccionada
    getOptionSelected(user_level) {
      this.changeOptionModal('1');
      let option = this.optionSelected;

      switch (option) {
        case '1':
          document.getElementById('customFile').value = '';
          this.formCertificate = user_level;
          $('#certificate-modal').modal('show');
          this.optionSelected = '';
          this.fillFiles();
          break;
        case '2':
          this.formCertificate = user_level;
          $('#delete-certificate-modal').modal('show');
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
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

/* ESTILOS DE TINYMCE COMENTADOS */
/*
.tox-notification {
  display: none !important;
}
*/
</style>