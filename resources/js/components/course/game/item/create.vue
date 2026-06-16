<template>
  <div>
    <form @submit.prevent="createItem">
      <div
        class="row"
        v-loading="loading"
        element-loading-text="Cargando..."
        element-loading-spinner="el-icon-loading"
        element-loading-background="rgba(0, 0, 0, 0.6)"
      >
        <div class="col-lg-12 mb-2">
          <label>Titulo</label>
          <el-input v-model="form.name" placeholder="Nombre de la imagen"></el-input>
        </div>

        <div class="col-lg-12 text-center">
          <label>Subir Imagen</label>
          <el-upload
            class="picture-card"
            ref="courseUpload"
            action=""
            drag
            accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG"
            :on-change="onUploadChange"
            :http-request="createItem"
            :multiple="false"
            :limit="1"
            :auto-upload="false"
            :file-list="fileList"
            :on-exceed="handleExceed"
          >
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

      <span slot="footer" class="dialog-footer">
        <div class="row">
          <div class="col-lg-12 text-center mt-2">
            <el-button data-dismiss="modal">Cancelar</el-button>
            <el-button type="primary" native-type="submit">Guardar</el-button>
          </div>
        </div>
      </span>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    game: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      fileList: [],
      isSubmitting: false,
      loading: false,
      form: {},
    };
  },
  created() {
    this.initForm();
  },
  methods: {
    validateFields() {
      if (!this.form.name || this.form.name.trim() === '' || this.form.name.length === 0) {
        this.$message.warning('El nombre de la imagen esta vacio!');
        return false;
      }

      if (this.form.name.length >= 51) {
        this.$message.warning('El nombre de la imagen es demasiado largo!');
        return false;
      }

      if (!this.$refs.courseUpload || this.$refs.courseUpload.uploadFiles.length == 0) {
        this.$message.warning('La imagen esta vacio!');
        return false;
      }
      return true;
    },

    handleExceed(file) {
      const isLt1M = file.size > 100000;

      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        this.$refs.courseUpload.clearFiles();
        return false;
      }
    },
    onUploadChange(file) {
      this.fileList = [];

      const isImage =
        file.raw.type === 'image/jpeg' ||
        file.raw.type === 'image/png' ||
        file.raw.type === 'image/jpg';

      // 100kb
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
      // let reader = new FileReader();
      // reader.readAsDataURL(file.raw);
      // reader.onload = function (e) {
      //   console.log(this.result);
      // };
      this.fileList.push(file);
    },

    initForm() {
      this.form = {
        name: '',
      };
    },

    async createItem() {
      if (this.validateFields()) {
        const formdata = new FormData();
        formdata.append('name', this.form.name);
        formdata.append('game_id', this.game.id);
        if (this.fileList.length > 0) {
          formdata.append('file', this.fileList[0].raw);
        }
        try {
          this.loading = true;
          this.isSubmitting = true;
          const config = {
            headers: { 'content-type': 'multipart/form-data' },
          };
          const data = await axios.post(`/course/game/item/store`, formdata, config);
          if (data.status == 200) {
            this.$message.success('Item guardado correctamente');
            this.$emit('listenStore');
          } else {
            await this.$message.warning('Error al validar datos');
          }
          this.form.name = '';
          this.fileList = [];
          this.loading = false;
          $('#create-item-modal').modal('hide');
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          $('#create-item-modal').modal('hide');
          console.log(error);
        }
      }
    },
  },
};
</script>
