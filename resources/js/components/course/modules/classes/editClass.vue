<template>
  <div>
    <form @submit.prevent="updateClass">
      <div class="row" v-loading="loading"
        element-loading-text="Tu video se está procesando. Por favor, espera a que termine."
        element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
        <div class="col-lg-12" v-if="hasObservation(clas.id) == true">
          <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Observación</h4>
            <p class="p-2">
              {{ observationData.description }}
            </p>
          </div>
        </div>

        <div class="col-lg-12 mb-2">
          <label>Titulo</label>
          <el-input v-model="clas.name" placeholder="Título de la clase"></el-input>
        </div>
        <div class="col-lg-12 mb-2">
          <label>Descripción</label>
          <el-input type="textarea" placeholder="Descripcion de la clase" v-model="clas.description" resize="none"
            rows="6">
          </el-input>
        </div>
        <div class="col-lg-6 col-md-12 text-center">
          <label>Clase Video</label>
          <el-upload class="upload-demo" ref="uploadClassVideo" action="" :on-change="onUploadVideoChange"
            :http-request="updateClassS3" drag :limit="1" :auto-upload="true" :file-list="fileVideoList"
            :on-exceed="handleExceedVideo" :on-remove="handleRemoveVideo" accept=".mp4" list-type="text">
            <i class="el-icon-upload"></i>
            <div class="el-upload__text">
              Suelta tu archivo aquí o <em>haz clic para cargar</em>
            </div>
            <div slot="tip" class="el-upload__tip">Solo archivos mp4</div>
          </el-upload>
        </div>
        <div class="col-lg-6 col-md-12 text-center">
          <label>Recursos</label>
          <el-upload class="upload-demo" drag ref="uploadResources" action="" :on-change="onUploadResourceChange"
            :http-request="updateClass" multiple :limit="3" :auto-upload="false" :file-list="fileResourcesList"
            :on-exceed="handleExceedResources" :on-remove="handleRemoveResource" list-type="picture">
            <i class="el-icon-upload"></i>
            <div class="el-upload__text">
              Suelta tu archivo aquí o <em>haz clic para cargar</em>
            </div>
            <div slot="tip" class="el-upload__tip">
              Máximo 50 MB - La carga de recursos es opcional
            </div>
          </el-upload>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <div class="row">
          <div class="col-lg-12 text-center mt-2" v-if="!this.loadingVideo">
            <el-button data-dismiss="modal">Cancelar</el-button>
            <el-button type="primary" native-type="submit">Guardar</el-button>
            <div class="alert alert-warning" role="alert" style="padding: 5px; margin-top: 10px">
              Al editar la clase, deberás solicitar una nueva revisión!
            </div>
          </div>
          <div class="col-lg-12 text-center mt-2" v-else>
            <span class="loader"></span>
          </div>
        </div>
      </span>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    clas: {
      type: undefined,
      required: true,
    },
    resources: {
      type: undefined,
      required: true,
    },
    video: {
      type: undefined,
      required: true,
    },
    observations: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      fileVideoList: [],
      observationData: null,
      fileResourcesList: [],
      fileResourcesRemoved: [],
      isSubmitting: false,
      loading: false,
      loadingVideo: false,
      firmaUrl: null,
    };
  },
  watch: {
    video: function (value) {
      this.fileVideoList = value;
    },
    resources: function (value) {
      this.fileResourcesList = value;
    },
  },
  mounted() {
    if (this.resources && this.resources.length > 0) {
      this.fileResourcesList = this.resources;
    }
  },
  methods: {
    async updateClassS3() {
      try {
        this.loadingVideo = true;
        const videoFile = this.fileVideoList[0];
        const config = {
          headers: { 'Content-Type': videoFile.raw.type },
        };

        const { data } = await axios.get(
          `/course/module/class/update-video-url/${this.clas.id}/${videoFile.raw.name}`
        );

        await axios.put(data.data, videoFile.raw, config);
        this.$message.success('Video subido con exito');
        this.loadingVideo = false;
        $('#edit-class-modal').modal('hide');
      } catch (error) {
        this.$message.error('Ocurrio un error al subir el video');
        this.loadingVideo = false;
        $('#edit-class-modal').modal('hide');
      }
    },

    hasObservation(class_id) {
      let result = this.observations.map((observation) => observation.id_class);
      if (result.includes(class_id)) {
        let obj = this.observations.find((observation) => observation.id_class === class_id);
        this.observationData = obj;
        return true;
      } else {
        return false;
      }
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },

    validateFields() {
      if (!this.clas.name || this.clas.name.trim() === '' || this.clas.name.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }

      if (this.clas.name.length >= 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }
      if (
        !this.clas.description ||
        this.clas.description.trim() === '' ||
        this.clas.description.length === 0
      ) {
        this.$message.warning('Descripcion esta vacio!');
        return false;
      }

      if (!this.$refs.uploadClassVideo || this.$refs.uploadClassVideo.uploadFiles.length == 0) {
        this.$message.warning('¡El video de la clase esta vacio!');
        return false;
      }
      return true;
    },

    handleExceedVideo(video) {
      this.$message.warning(`El límite es 1 video`);
    },

    handleRemoveVideo(fileremoved, fileVideo) {
      this.fileVideoList = fileVideo;
    },

    onUploadVideoChange(video) {
      this.fileVideoList = [];
      const isVideo = video.raw.type === 'video/mp4';
      // 630mb
      const fortymb = 662144000;
      const isLt3MB = video.size < fortymb;

      if (!isVideo) {
        this.$message.error('Solo puede cargar archivos en formato .mp4');
        return false;
      }

      if (!isLt3MB) {
        this.$message.error('El archivo no debe pesar más de 250 mb!');
        return false;
      }
      this.fileVideoList.push(video);
    },

    onUploadResourceChange(documents) {
      const isLt50MB = documents.size < 50 * 1024 * 1024; // 50 MB
      if (!isLt50MB) {
        this.$message.error('El archivo no debe pesar más de 50 MB!');
        this.$refs.uploadResources.clearFiles();
        return false;
      }
      this.fileResourcesList.push(documents);
    },

    handleRemoveResource(fileremoved, fileList) {
      this.fileResourcesList = fileList;
      this.fileResourcesRemoved.push(fileremoved);
    },

    handleExceedResources(documents) {
      this.$message.warning(`El límite es 3 recursos`);
    },

    resetAll() {
      this.isSubmitting = false;
      this.form = {};
      this.fileVideoList = [];
      this.fileResourcesList = [];
      this.isSubmitting = false;
    },

    isNewVideo() {
      if (this.fileVideoList.length > 0 && this.fileVideoList[0].raw !== undefined) {
        return true;
      }
      return false;
    },

    areNewResources() {
      if (this.fileResourcesList.length > 0) {
        for (let i = 0; i < this.fileResourcesList.length; i++) {
          return true;
        }
      }
      return false;
    },

    isKeyExists(obj, key) {
      return obj.hasOwnProperty(key);
    },

    async updateClass() {
      if (this.validateFields()) {
        const formdata = new FormData();
        formdata.append('title', this.clas.name);
        formdata.append('description', this.clas.description);
        formdata.append('module_id', this.clas.id);

        if (this.isNewVideo()) {
          formdata.append('video', this.fileVideoList[0].raw);
        }

        if (this.areNewResources()) {
          for (let i = 0; i < this.fileResourcesList.length; i++) {
            if (this.isKeyExists(this.fileResourcesList[i], 'raw')) {
              let file = this.fileResourcesList[i].raw;
              formdata.append('resources[]', file);
            }
          }
        }

        if (this.fileResourcesRemoved.length > 0) {
          for (let i = 0; i < this.fileResourcesRemoved.length; i++) {
            let fileRemoved = this.fileResourcesRemoved[i].url;
            formdata.append('resourcesRemoved[]', fileRemoved);
          }
        }

        try {
          this.loading = true;
          this.isSubmitting = true;
          const config = {
            headers: { 'content-type': 'multipart/form-data' },
          };
          const { data } = await axios.post(
            `/course/module/class/${this.clas.id}/update`,
            formdata,
            config
          );
          const { status } = await data.data;

          if (status === 'ok') {
            this.$message.success('La clase ha sido actualizada correctamente');
            this.loading = false;
            $('#edit-class-modal').modal('hide');
          } else {
            this.$message.warning('Error al validar datos');
          }
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

<style>
.observation-text {
  border: 1px solid #ff9f43 !important;
}

.loader {
  display: block;
  position: relative;
  height: 12px;
  width: 80%;
  border: 1px solid #fff;
  border-radius: 10px;
  overflow: hidden;
}

.loader::after {
  content: '';
  width: 40%;
  height: 100%;
  background: #1ae600;
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: border-box;
  animation: animloader 2s linear infinite;
}

@keyframes animloader {
  0% {
    left: 0;
    transform: translateX(-100%);
  }

  100% {
    left: 100%;
    transform: translateX(0%);
  }
}
</style>
