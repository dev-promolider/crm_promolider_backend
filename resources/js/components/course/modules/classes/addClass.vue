<template>
  <div>
    <form @submit.prevent="storeClass">
      <div class="row" v-loading="loading" element-loading-text="Creando clase"
        element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
        <div class="col-lg-12 mb-2">
          <label>Titulo</label>
          <el-input v-model="form.name" placeholder="Título de la clase" type="text" class="w-100"></el-input>
        </div>

        <div class="col-lg-12 mb-2">
          <label>Descripción</label>
          <el-input type="textarea" placeholder="Descripcion de la clase" v-model="form.description" resize="none"
            rows="6" class="w-100"></el-input>
        </div>

        <div class="col-lg-12 mb-2">
          <label>Recursos (Opcional)</label>
          <el-upload class="upload-demo" drag ref="uploadResources" action="" :on-change="onUploadResourceChange"
            :http-request="storeClass" multiple :limit="3" :auto-upload="false" :file-list="fileResourcesList"
            :on-exceed="handleExceedResources" :on-remove="handleRemoveResource" list-type="picture">
            <i class="el-icon-upload"></i>
            <div class="el-upload__text">
              Suelta tu archivo aquí o <em>haz clic para cargar</em>
            </div>
            <div slot="tip" class="el-upload__tip">
              Máximos 50mb - La carga de recursos es opcional
            </div>
          </el-upload>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12 text-center mt-2">
          <el-button data-dismiss="modal">Cancelar</el-button>
          <el-button type="primary" native-type="submit" :disabled="isSubmitting">
            Guardar
          </el-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    moduleId: {
      type: undefined,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      form: {},
      fileResourcesList: [],
      isSubmitting: false,
    };
  },
  methods: {
    validateFields() {
      if (!this.form.name || this.form.name.trim() === '' || this.form.name.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }
      if (this.form.name.length >= 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }
      if (
        !this.form.description ||
        this.form.description.trim() === '' ||
        this.form.description.length === 0
      ) {
        this.$message.warning('Descripcion esta vacio!');
        return false;
      }
      return true;
    },

    onUploadResourceChange(documents) {
      const isLt50MB = documents.size < 50000000; // 50 MB
      if (!isLt50MB) {
        this.$message.error('El archivo no debe pesar más de 50 MB!');
        this.$refs.uploadResources.clearFiles();
        return false;
      }
      this.fileResourcesList.push(documents);
    },

    handleRemoveResource(fileremoved, fileList) {
      this.fileResourcesList = fileList;
    },

    handleExceedResources(documents) {
      this.$message.warning(`El límite es 3, haz seleccionado ${documents.length} archivos`);
      this.$refs.uploadResources.clearFiles();
      this.fileResourcesList = [];
    },

    resetAll() {
      this.isSubmitting = false;
      this.form = {};
      this.fileResourcesList = [];
    },

    async storeClass() {
      if (this.validateFields()) {
        const formdata = new FormData();
        formdata.append('title', this.form.name);
        formdata.append('description', this.form.description);
        formdata.append('module_id', this.moduleId);

        if (this.fileResourcesList.length > 0) {
          this.fileResourcesList.forEach((resource) => {
            if (resource.raw) {
              formdata.append('resources[]', resource.raw);
            }
          });
        }

        try {
          this.loading = true;
          this.isSubmitting = true;
          const config = {
            headers: { 'content-type': 'multipart/form-data' },
          };
          const { data } = await axios.post(
            `/course/module/class/${this.moduleId}/save`,
            formdata,
            config
          );
          const { status } = await data.data;
          const response =
            (await status) == 'ok'
              ? this.$message.success('Su clase ha sido registrada')
              : await this.$message.warning('Error al validar datos');
          this.resetAll();
          this.loading = false;
          $('#create-class-modal').modal('hide');
          this.$parent.$parent.refreshClassData();
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
