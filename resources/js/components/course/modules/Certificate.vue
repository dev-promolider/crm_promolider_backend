<template>
  <el-dialog title="" top="1vh" :visible="visibleConfigure" class="full-screen-dialog" @close="close()" append-to-body
    :close-on-modal="false" :close-on-click-modal="false">
    <div class="row">
      <div class="col-md-12 col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Entrega del Certificado</h4>
          </div>
          <div class="card-body">
            <form @submit.prevent="store" class="form">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label class="d-block">Seleccione la condición que se tiene que cumplir para entregar un
                      certificado</label>
                    <div class="d-inline py-2">
                      <div>
                        <input type="radio" v-model="conditionSelected" value="0" @click="showVideos = true"
                          name="showVideo" id="radio1" />
                        <label for="radio1">Visualizar videos</label>
                      </div>
                      <div>
                        <input type="radio" v-model="conditionSelected" value="1" @click="showVideos = false"
                          name="showVideo" id="radio2" />
                        <label for="radio2">Aprobar exámenes</label>
                      </div>
                      <div>
                        <input type="radio" v-model="conditionSelected" value="2" @click="showVideos = true"
                          name="showVideo" id="radio3" />
                        <label for="radio3">Visualizar videos y aprobar exámenes</label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group" v-if="showVideos">
                    <label>Determine el límite de videos que se tendrá que visualizar</label>
                    <el-select v-model="form.type" placeholder="Seleccione cuando entregar el certificado"
                      class="d-inline" @input="handleChangeCourse">
                      <el-option v-for="t in typeList" :key="t.value" :label="t.label" :value="t.value">
                      </el-option>
                    </el-select>
                  </div>
                </div>

                <div v-if="form.type == 2 || form.type == 3" class="col-lg-3 col-md-3 col-sm-6">
                  <div class="form-group">
                    <label>Seleccione el módulo:</label>
                    <el-select v-model="form.module" placeholder="Seleccione cuando entregar el certificado"
                      class="d-inline" @input="handleChangeModule">
                      <el-option v-for="m in modules" :key="m.id" :label="m.name" :value="m.id">
                      </el-option>
                    </el-select>
                  </div>
                </div>

                <div v-if="form.type == 3" class="col-lg-3 col-md-3 col-sm-6">
                  <div class="form-group">
                    <label>Seleccione la clase:</label>
                    <el-select v-model="form.lesson" placeholder="Seleccione cuando entregar el certificado"
                      class="d-inline">
                      <el-option v-for="m in lessons" :key="m.id" :label="m.name" :value="m.id">
                      </el-option>
                    </el-select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group">
                    <label>¿El certificado será personalizado?</label>
                    <select v-model="form.customized_certificate" class="form-control">
                      <option value="" disabled selected>Seleccione una opción</option>
                      <option v-for="option in options" :value="option.label">
                        {{ option.value }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex justify-content-start">
                    <p class="m-0">¿Se pagará por el certificado?</p>
                    <input class="mx-2" type="checkbox" v-model="form.type_certificate_check" />
                  </div>
                </div>

                <div class="col-3" v-if="form.type_certificate_check == true">
                  <div class="form-group py-2">
                    <label>Precio de certificado:</label>
                    <el-input placeholder="Escriba el precio" v-model="form.certificate_price"></el-input>
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                      Guardar
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>
<script>
import CertificateSetting from './CertificateSetting.vue';
import axios from 'axios';
export default {
  components: {
    CertificateSetting,
  },
  props: {
    visibleConfigure: {
      type: Boolean,
      required: true,
    },
    course: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      isSubmitting: false,
      form: {},
      loading: false,
      typeList: [
        {
          value: '1',
          label: 'Curso',
        },
        {
          value: '2',
          label: 'Módulo',
        },
      ],
      options: [
        {
          label: 0,
          value: 'No',
        },
        {
          label: 1,
          value: 'Si',
        },
      ],
      mutableModuleId: null,
      modules: null,
      lessons: null,
      module: null,
      courseConfiguration: {},
      showVideos: false,
      conditionSelected: null,
    };
  },
  created() {
    this.initForm();
    this.listModules();
    this.getConfiguration();
  },
  methods: {
    close() {
      this.$emit('closeModalCertificate');
    },
    initForm() {
      this.form = {
        type: null,
        lesson: null,
        module: null,
        type_certificate_check: false,
        type_certificate: 1,
        certificate_price: 0,
      };
    },
    handleChangeCourse: function (value) {
      this.form.module = null;
      this.form.lesson = null;
    },

    handleChangeModule: function (value) {
      this.lessons = null;
      this.form.lesson = null;
      this.listLessons(value);
    },

    listModules() {
      axios
        .get(`/course/${this.course.id}/modulesList`)
        .then((response) => {
          this.modules = response.data.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    listLessons(module_id) {
      axios
        .get(`/course/module/class/${module_id}/classList`)
        .then((response) => {
          this.lessons = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    validateFields() {
      if (this.conditionSelected == null) {
        this.$message.warning('Seleccione una condición para entregar un certificado');
        return false;
      }
      if (this.conditionSelected != 1) {
        if (this.form.type == null) {
          this.$message.warning('Seleccione cuando entregar el certificado');
          return false;
        }
        if (this.form.type != null && this.form.type != 1 && this.form.module == null) {
          this.$message.warning('Seleccione un módulo');
          return false;
        }
        if (
          this.form.type != null &&
          this.form.type != 2 &&
          this.form.module != null &&
          this.form.lesson == null
        ) {
          this.$message.warning('Seleccione una clase');
          return false;
        }
      }
      if (this.form.type_certificate_check) {
        if (
          !this.form.certificate_price ||
          this.form.certificate_price.trim() === '' ||
          this.form.certificate_price.length === 0
        ) {
          this.$message.warning('Ingrese un precio adecuado');
          return false;
        }
        if (this.form.certificate_price <= 0) {
          this.$message.warning('El precio asignado al certificado no es correcto');
          return false;
        }
      }
      if (
        this.form.customized_certificate == null ||
        this.form.customized_certificate == undefined
      ) {
        this.$message.warning('Seleccione si el certificado será personalizado');
        return false;
      }

      return true;
    },
    async store() {
      if (this.validateFields()) {
        this.isSubmitting = true;

        if (this.form.type_certificate_check == true) {
          this.form.type_certificate = 0;
        }
        const formdata = new FormData();
        formdata.append('type', this.form.type);
        formdata.append('course', this.course.id);
        formdata.append('module', this.form.module);
        formdata.append('lesson', this.form.lesson);
        formdata.append('type_certificate', this.form.type_certificate);
        formdata.append('certificate_price', this.form.certificate_price);
        formdata.append('condition_to_certificate', this.conditionSelected);
        formdata.append('customized_certificate', this.form.customized_certificate);

        try {
          this.loading = true;
          const request = await axios.post(`/course/certificate/store/configuration`, formdata);
          const response = request.status;

          if (response == 200) {
            this.$message.success('El curso ha sido actualizado correctamente');
            this.close();
          } else {
            await this.$message.warning('Error al validar datos');
          }
          this.isSubmitting = false;
          this.loading = false;
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },
    async getConfiguration() {
      try {
        this.loading = true;
        const request = await axios.get(`/course/certificate/configuration/${this.course.id}`);
        this.courseConfiguration = request.data;
        switch (this.courseConfiguration.validated_by) {
          case 'course':
            this.form.type = '1';
            break;
          case 'module':
            this.form.type = '2';
            this.form.module = parseInt(this.courseConfiguration.data.module);
            break;
          case 'lesson':
            this.form.type = '3';
            this.form.module = parseInt(this.courseConfiguration.data.module);
            this.listLessons(this.form.module);
            this.form.lesson = parseInt(this.courseConfiguration.data.lesson);
            break;
        }
      } catch (error) {
        console.log(error);
      }
      this.isSubmitting = false;
      this.loading = false;
    },
  },
};
</script>
