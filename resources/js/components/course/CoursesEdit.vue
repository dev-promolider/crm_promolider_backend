<template>
  <div class="card" v-if="mutableCourse" v-loading="loading" element-loading-text="Cargando..."
    element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
    <div class="card-body">
      <form @submit.prevent="update">
        <div class="row">
          <div class="col-lg-6">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Nombre del Cursos</label>
                  <input type="text" v-model="mutableCourse.title" class="form-control"
                    placeholder="Titulo del curso" />
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Categoría</label>
                  <el-select v-model="mutableCourse.id_categories" placeholder="Seleccione Categoría" class="d-inline">
                    <el-option v-for="c in categoriesList" :key="c.id" :label="c.name" :value="c.id">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group">
                  <label>Descripción</label>
                  <textarea class="form-control" v-model="mutableCourse.description" rows="5" style="resize: none"
                    placeholder="Descripción del curso"></textarea>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Nivel del Curso</label>
                  <el-select v-model="mutableCourse.course_level_id" @change="handleLevelChange"
                    placeholder="Seleccione un nivel" class="d-inline">
                    <el-option v-for="l in levelsList" :key="l.id" :label="l.description" :value="l.id">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Precio</label>
                  <el-input v-model="mutableCourse.price" placeholder="0.00" clearable
                    :disabled="mutableCourse.is_free"></el-input>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Precio Total</label>
                  <el-input v-model="mutableCourse.price_base" disabled placeholder="0.00" clearable></el-input>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="mr-1">Meses</label>
                  <input type="number" class="form-control" v-model="mutableCourse.months" placeholder="Please input"
                    :disabled="mutableCourse.no_end_date" />
                </div>
              </div>
              <div class="col-lg-6 mb-2">
                <div class="d-flex justify-content-start">
                  <p class="m-0">
                    <strong style="color: #20e404">¿Se entregara certificado?</strong>
                  </p>
                  <input class="mx-2" type="checkbox" v-model="mutableCourse.certificate" />
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group is-free">
                  <label>¿Es un curso gratuito?</label>
                  <input type="checkbox" v-model="mutableCourse.is_free" @change="handleFreeCourse"
                    :disabled="mutableCourse.course_level_id === 1" />
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Sin fecha de fin</label>
                  <input type="checkbox" v-model="mutableCourse.no_end_date" @change="handleNoEndDate" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <label>Subir Portada</label>
            <el-upload class="picture-card" ref="courseUpload" action="" drag
              accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG, .webp, .WEBP" :on-change="onUploadChange" :http-request="update"
              :multiple="false" :limit="1" :auto-upload="false" :file-list="fileList" :on-exceed="handleExceed"
              :on-remove="handleRemoveCoverImage" list-type="picture">
              <div class="el-upload__text" v-if="!imageUrlPreview">
                <i class="el-icon-upload"></i>
                Suelta tu archivo aquí o <em>haz clic para cargar</em>
              </div>
              <div v-else class="upload-preview">
                <img :src="imageUrlPreview" alt="Portada del Curso" class="uploaded-image-preview" @error="handleImageError" />
                <el-button type="danger" icon="el-icon-delete" circle @click.stop="removeCoverImage"
                  class="remove-preview-button"></el-button>
              </div>
              <div slot="tip" class="el-upload__tip">
                Solo archivos jpg/png/webp con un tamaño menor de 100kb
              </div>
            </el-upload>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>Acerca del curso</label>
              <textarea class="form-control" v-model="mutableCourse.course_about" rows="2"
                placeholder="Detalles acerca del curso"></textarea>
            </div>

            <div class="form-group">
              <label>Lo que aprenderá</label>
              <textarea class="form-control" v-model="mutableCourse.will_learn" rows="2"
                placeholder="Lo que aprendera en el curso"></textarea>
            </div>
          </div>

          <div class="col-lg-6">
            <label>Subir video o imagen de venta</label>
            <el-upload 
              class="picture-card" 
              ref="courseUploadVideo" 
              action="" 
              drag
              accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG, .webp, .WEBP, .mp4" 
              :on-change="onUploadChangeVideo"
              :http-request="update" 
              :multiple="false" 
              :limit="1" 
              :auto-upload="false" 
              :file-list="fileListVideo"
              :on-exceed="handleExceedVideo" 
              :on-remove="handleRemoveVideoImage">
              
              <div class="el-upload__text" v-if="!videoUrlPreview">
                <i class="el-icon-upload"></i>
                Suelta tu archivo aquí o <em>haz clic para cargar</em>
              </div>
              
              <div v-else class="upload-preview">
                <!-- Video preview -->
                <video 
                  v-if="isVideoFile" 
                  :src="videoUrlPreview" 
                  controls
                  class="uploaded-video-preview" 
                  @error="handleVideoError"
                  style="max-width: 100%; max-height: 200px;">
                  Tu navegador no soporta la reproducción de video.
                </video>
                
                <!-- Image preview -->
                <img 
                  v-else 
                  :src="videoUrlPreview" 
                  alt="Imagen de Venta" 
                  class="uploaded-image-preview"
                  @error="handleImageError"
                  style="max-width: 100%; max-height: 200px;" />
                
                <!-- Remove button -->
                <el-button 
                  type="danger" 
                  icon="el-icon-delete" 
                  circle 
                  @click.stop.prevent="removeVideoImage"
                  class="remove-preview-button">
                </el-button>
              </div>
              
              <div slot="tip" class="el-upload__tip">
                Solo archivos jpg/png/webp/mp4 con un tamaño menor de 100MB
              </div>
            </el-upload>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>Conocimientos previos</label>
              <textarea class="form-control" v-model="mutableCourse.prev_knowledge" rows="2"
                placeholder="Conocimientos previos para llevar el curso"></textarea>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="form-group">
              <label>Curso destinado para</label>
              <textarea class="form-control" v-model="mutableCourse.course_for" rows="2"
                placeholder="A quienes va destinado el curso"></textarea>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center">
          <button type="submit" class="btn btn-success" :disabled="isSubmitting">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    categoriesList: {
      type: Array,
      required: true,
    },
    levelsList: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      isSubmitting: false,
      loading: false,
      extensions: '.jpeg, .jpg, .png',
      mutableCourse: null,
      fileList: [],
      fileListVideo: [],
      imageUrlPreview: null,
      videoUrlPreview: null,
      isVideoFile: false, // Nueva propiedad para tracking
      showDebugInfo: false, // Cambia a true si quieres ver la info de debug
    };
  },
  watch: {
    course: {
      immediate: true, // Run immediately when component is mounted
      handler(value) {
        if (value) {
          this.mutableCourse = {
            ...value,
            is_free: value.price === 0 && value.price_base === 0,
            // Initialize no_end_date based on the 'months' value from the course object
            no_end_date: value.months === 'Sin definir',
          };
          this.fillFiles();
          this.fillFilesVideo();
        }
      }
    },
    'mutableCourse.price': function (newPrice) {
      if (!this.mutableCourse.is_free && !isNaN(newPrice) && newPrice !== '' && newPrice !== null) {
        newPrice = parseFloat(newPrice);
        this.mutableCourse.price_base = (newPrice + newPrice * 0.18).toFixed(2);
      } else if (this.mutableCourse.is_free) {
        this.mutableCourse.price_base = 0;
      }
    },
  },

  methods: {
    fillFiles() {
      const coverUrl = this.normalizeExistingMediaUrl(this.mutableCourse.url_portada);

      if (!coverUrl) {
        this.fileList = [];
        this.imageUrlPreview = null;
        return;
      }

      // Set the fileList for the El-upload component
      this.fileList = [
        {
          name: this.mutableCourse.portada,
          url: coverUrl,
        },
      ];
      // Set the preview URL for display
      this.imageUrlPreview = coverUrl;
    },

    fillFilesVideo() {
      const mediaUrl = this.normalizeExistingMediaUrl(this.mutableCourse.path_url);

      if (!mediaUrl) {
        this.fileListVideo = [];
        this.videoUrlPreview = null;
        return;
      }

      let name = mediaUrl.split('/');
      let nameFileExtension = name[name.length - 1];
      
      // Determinar si es video basándose en la extensión
      this.isVideoFile = this.isVideoByExtension(nameFileExtension);

      // Set the fileList for the El-upload component
      this.fileListVideo = [
        {
          name: nameFileExtension,
          url: mediaUrl,
          // Agregar información de tipo para compatibilidad
          type: this.isVideoFile ? 'video/mp4' : 'image/jpeg'
        },
      ];
      
      // Set the preview URL for display
      this.videoUrlPreview = mediaUrl;
      
      void ({
        name: nameFileExtension,
        url: this.mutableCourse.path_url,
        isVideo: this.isVideoFile,
        preview: this.videoUrlPreview
      });
    },

    // Nuevo método para detectar videos por extensión
    normalizeExistingMediaUrl(url) {
      if (!url) return null;

      const value = String(url).trim();
      if (!value || value === 'null' || value === 'undefined') return null;

      const duplicatedUrl = value.match(/^https?:\/\/[^/]+\/(https?:\/\/.+)$/);
      return duplicatedUrl ? duplicatedUrl[1] : value;
    },

    isVideoByExtension(filename) {
      if (!filename) return false;
      const extension = filename.toLowerCase().split('.').pop();
      return ['mp4', 'mov', 'avi', 'mkv', 'webm'].includes(extension);
    },

    handleRemoveCoverImage(file, fileList) {
      // Usar nextTick para asegurar que el DOM se actualice correctamente
      this.$nextTick(() => {
        this.fileList = [];
        this.imageUrlPreview = null;
      });
    },
    removeCoverImage() {
      // Verificar que la referencia existe antes de usarla
      if (this.$refs.courseUpload) {
        this.$nextTick(() => {
          this.$refs.courseUpload.clearFiles();
          this.imageUrlPreview = null;
          this.fileList = [];
        });
      } else {
        // Si la referencia no existe, solo limpiar los datos
        this.imageUrlPreview = null;
        this.fileList = [];
      }
    },
    handleRemoveVideoImage(file, fileList) {
      this.$nextTick(() => {
        this.fileListVideo = [];
        this.videoUrlPreview = null;
        this.isVideoFile = false;
      });
    },
    removeVideoImage() {
      // Verificar que la referencia existe antes de usarla
      if (this.$refs.courseUploadVideo) {
        this.$nextTick(() => {
          this.$refs.courseUploadVideo.clearFiles();
          this.videoUrlPreview = null;
          this.fileListVideo = [];
          this.isVideoFile = false;
        });
      } else {
        // Si la referencia no existe, solo limpiar los datos
        this.videoUrlPreview = null;
        this.fileListVideo = [];
        this.isVideoFile = false;
      }
    },
    cleanupObjectURLs() {
      if (this.imageUrlPreview && this.imageUrlPreview.startsWith('blob:')) {
        URL.revokeObjectURL(this.imageUrlPreview);
      }
      if (this.videoUrlPreview && this.videoUrlPreview.startsWith('blob:')) {
        URL.revokeObjectURL(this.videoUrlPreview);
      }
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
    handleExceedVideo(file) {
      if (this.fileListVideo.length >= 1) {
        this.$message.error('Solo puede seleccionar 1 archivo!');
        return false;
      }

      const isLt1M = file.size < 100000000;

      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100MB!');
        return false;
      }
    },
    handleLevelChange(value) {
      if (value == 1) {
        this.mutableCourse.price_base = 0;
        this.mutableCourse.price = 0;
        this.mutableCourse.is_free = true;
      } else if (this.mutableCourse.is_free) {
        // Mantener el estado gratuito si ya estaba marcado
        this.mutableCourse.price_base = 0;
        this.mutableCourse.price = 0;
      } else {
        // Si no es gratuito y no es nivel 1, resetear los precios a null
        this.mutableCourse.price = null;
        this.mutableCourse.price_base = null;
      }
    },
    onUploadChange(file) {
      this.fileList = [];

      const isImage =
        file.raw.type === 'image/jpeg' ||
        file.raw.type === 'image/png' ||
        file.raw.type === 'image/jpg' ||
        file.raw.type === 'image/webp';

      const isLt100KB = file.size < 100000;

      if (!isImage) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png|webp.');
        this.$refs.courseUpload.clearFiles();
        this.imageUrlPreview = null;
        return false;
      }
      if (!isLt100KB) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        this.$refs.courseUpload.clearFiles();
        this.imageUrlPreview = null;
        return false;
      }

      this.fileList.push(file);
      this.imageUrlPreview = URL.createObjectURL(file.raw); // Set preview URL
    },

    onUploadChangeVideo(file) {
      // Limpiar archivos anteriores
      this.fileListVideo = [];

      // Limpiar URL anterior si existe
      if (this.videoUrlPreview && this.videoUrlPreview.startsWith('blob:')) {
        URL.revokeObjectURL(this.videoUrlPreview);
      }

      const isValidFile =
        file.raw.type === 'image/jpeg' ||
        file.raw.type === 'image/png' ||
        file.raw.type === 'image/jpg' ||
        file.raw.type === 'image/webp' ||
        file.raw.type === 'video/mp4';
    
      const isLt100MB = file.size < 100000000;
    
      if (!isValidFile) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png|webp|mp4.');
        if (this.$refs.courseUploadVideo) {
          this.$refs.courseUploadVideo.clearFiles();
        }
        this.videoUrlPreview = null;
        this.isVideoFile = false;
        return false;
      }

      if (!isLt100MB) {
        this.$message.error('El archivo no debe pesar más de 100MB!');
        if (this.$refs.courseUploadVideo) {
          this.$refs.courseUploadVideo.clearFiles();
        }
        this.videoUrlPreview = null;
        this.isVideoFile = false;
        return false;
      }
    
      try {
        this.fileListVideo.push(file);
        this.videoUrlPreview = URL.createObjectURL(file.raw);
        this.isVideoFile = file.raw.type.includes('video');

        console.log('Nuevo archivo seleccionado:', {
          name: file.name,
          type: file.raw.type,
          isVideo: this.isVideoFile,
          size: file.size
        });
      } catch (error) {
        console.error('Error al procesar el archivo:', error);
        this.$message.error('Error al procesar el archivo seleccionado.');
        this.videoUrlPreview = null;
        this.isVideoFile = false;
        this.fileListVideo = [];
      }
    },

    isVideo(file) {
      // Si es un archivo nuevo (con raw)
      if (file && file.raw && file.raw.type) {
        return file.raw.type.includes('video');
      }
      
      // Si es un archivo existente, usar la propiedad isVideoFile
      // o detectar por extensión si tiene name
      if (file && file.name) {
        return this.isVideoByExtension(file.name);
      }
      
      // Fallback: usar la propiedad de estado
      return this.isVideoFile;
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },
    handleFreeCourse() {
      if (this.mutableCourse.is_free) {
        this.mutableCourse.price = 0;
        this.mutableCourse.price_base = 0;
        this.$message.info(
          'Has marcado el curso como gratuito. No obtendrás puntos por subscripciones.'
        );
      } else {
        // Solo permitir cambiar a no gratuito si no es nivel básico
        if (this.mutableCourse.course_level_id !== 1) {
          this.mutableCourse.price = null;
          this.mutableCourse.price_base = null;
        } else {
          this.mutableCourse.is_free = true;
          this.$message.warning('Los cursos de nivel básico deben ser gratuitos');
        }
      }
    },
    handleNoEndDate() {
      if (this.mutableCourse.no_end_date) {
        this.mutableCourse.months = ''; // Clear months when "Sin fecha de fin" is checked
      } else {
        this.mutableCourse.months = 1; // Reset to default if unchecked
      }
    },
    handleVideoError(event) {
      if (event?.target?.src === this.videoUrlPreview) {
        this.videoUrlPreview = null;
        this.fileListVideo = [];
      }
    },

    handleImageError(event) {
      if (event?.target?.src === this.imageUrlPreview) {
        this.imageUrlPreview = null;
        this.fileList = [];
      }

      if (event?.target?.src === this.videoUrlPreview) {
        this.videoUrlPreview = null;
        this.fileListVideo = [];
      }
    },

    // 5. Lifecycle hook para limpiar URLs cuando el componente se destruya
    beforeDestroy() {
      this.cleanupObjectURLs();
    },
    validateFields() {
      if (
        !this.mutableCourse.title ||
        this.mutableCourse.title.trim() === '' ||
        this.mutableCourse.title.length === 0
      ) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }

      if (this.mutableCourse.title.length > 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }

      if (!this.mutableCourse.id_categories || this.mutableCourse.id_categories.length === 0) {
        this.$message.warning('No ha seleccionado la categoría del curso');
        return false;
      }

      if (
        !this.mutableCourse.description ||
        this.mutableCourse.description.trim() === '' ||
        this.mutableCourse.description.length === 0
      ) {
        this.$message.warning('Descripcion esta vacio!');
        return false;
      }

      if (!this.$refs.courseUpload || this.$refs.courseUpload.uploadFiles.length == 0) {
        this.$message.warning('La portada del curso esta vacia!');
        return false;
      }

      if (!this.$refs.courseUploadVideo || this.$refs.courseUploadVideo.uploadFiles.length == 0) {
        this.$message.warning('La imagen o video introductivo esta vacio!');
        return false;
      }

      if (!this.mutableCourse.course_level_id || this.mutableCourse.course_level_id.length === 0) {
        this.$message.warning('No ha seleccionado el nivel del curso');
        return false;
      }

      if (!this.mutableCourse.is_free) {
        if (this.mutableCourse.price === null || this.mutableCourse.price === '' || parseFloat(this.mutableCourse.price) === 0) {
          this.$message.warning('Precio esta vacio o su valor es 0!');
          return false;
        }

        if (this.mutableCourse.price_base === null || this.mutableCourse.price_base === '' || parseFloat(this.mutableCourse.price_base) === 0) {
          this.$message.warning('Precio base esta vacio o su valor es 0!');
          return false;
        }

        if (isNaN(this.mutableCourse.price)) {
          this.$message.warning('Verifique el campo de precio');
          return false;
        }

        if (isNaN(this.mutableCourse.price_base)) {
          this.$message.warning('Verifique el campo de precio base');
          return false;
        }
      }

      // Validación para cursos gratuitos
      if (this.mutableCourse.is_free) {
        if (parseFloat(this.mutableCourse.price) !== 0 || parseFloat(this.mutableCourse.price_base) !== 0) {
          this.$message.warning('Los cursos gratuitos deben tener un precio y precio base de 0.');
          return false;
        }
      }

      // Validación para cursos de nivel básico
      if (this.mutableCourse.course_level_id == 1 && !this.mutableCourse.is_free) {
        this.$message.warning('Los cursos de nivel básico tienen que ser gratuitos');
        return false;
      }

      // Validar `months` solo si `no_end_date` no está marcada
      if (!this.mutableCourse.no_end_date) {
        if (this.mutableCourse.months === null || this.mutableCourse.months === '' || isNaN(this.mutableCourse.months)) {
          this.$message.warning('La duración en meses no puede estar vacía y debe ser un número.');
          return false;
        }
        if (parseInt(this.mutableCourse.months) < 1) {
          this.$message.warning('La duración mínima es 1 mes');
          return false;
        }
        if (parseInt(this.mutableCourse.months) > 12) {
          this.$message.warning('La duración máxima son 12 meses');
          return false;
        }
      }

      if (
        this.mutableCourse.course_about.trim() === '' ||
        this.mutableCourse.course_about.length === 0
      ) {
        this.$message.warning('Acerca del curso esta vacio!');
        return false;
      }

      if (
        !this.mutableCourse.will_learn ||
        this.mutableCourse.will_learn.trim() === '' ||
        this.mutableCourse.will_learn.length === 0
      ) {
        this.$message.warning('Lo que aprenderás del curso esta vacio!');
        return false;
      }

      if (
        !this.mutableCourse.prev_knowledge ||
        this.mutableCourse.prev_knowledge.trim() === '' ||
        this.mutableCourse.prev_knowledge.length === 0
      ) {
        this.$message.warning('Los conocimientos previos del curso esta vacio!');
        return false;
      }

      if (
        !this.mutableCourse.course_for ||
        this.mutableCourse.course_for.trim() === '' ||
        this.mutableCourse.course_for.length === 0
      ) {
        this.$message.warning('El público destino del curso esta vacio!');
        return false;
      }

      return true;
    },
    async update() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();

        formdata.append('title', this.mutableCourse.title);
        formdata.append('id_categories', this.mutableCourse.id_categories);
        formdata.append('description', this.mutableCourse.description);
        formdata.append('id_level', this.mutableCourse.course_level_id);
        formdata.append('price_base', this.mutableCourse.price_base || 0);
        formdata.append('price', this.mutableCourse.price || 0);
        formdata.append('is_free', this.mutableCourse.is_free);
        formdata.append('course_about', this.mutableCourse.course_about);
        formdata.append('will_learn', this.mutableCourse.will_learn);
        formdata.append('prev_knowledge', this.mutableCourse.prev_knowledge);
        formdata.append('course_for', this.mutableCourse.course_for);
        formdata.append('months', this.mutableCourse.no_end_date ? 'Sin definir' : parseInt(this.mutableCourse.months));
        formdata.append('certificate', this.mutableCourse.certificate);

        if (this.fileList.length > 0 && this.fileList[0].raw) { // Check if a new file was selected (raw property exists)
          formdata.append('file', this.fileList[0].raw);
        }

        if (this.fileListVideo.length > 0 && this.fileListVideo[0].raw) { // Check if a new file was selected (raw property exists)
          formdata.append('file_video', this.fileListVideo[0].raw);
        }

        console.log(formdata);

        try {
          this.loading = true;
          const request = await axios({
            url: `course/${this.course.id}/update`,
            method: 'post',
            data: formdata,
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          const response =
            request.status == 200
              ? this.$message.success('El curso ha sido actualizado correctamente')
              : await this.$message.warning('Error al validar datos');

          this.isSubmitting = false;
          this.loading = false;
          // Clear fileList and previews after successful update
          this.fileList = [];
          this.fileListVideo = [];
          this.imageUrlPreview = null;
          this.videoUrlPreview = null;
          $('#edit-course-modal').modal('hide');
          location.reload();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          $('#edit-course-modal').modal('hide');
          console.log(error);
        }
      }
    },
  },
};
</script>

<style>
.course-form-card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border-radius: 0.5rem;
  border: 1px solid #dee2e6;
}

.section-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #dee2e6;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-section {
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 0.375rem;
}

.form-group.is-free .form-control {
  background-color: #f5f5f5;
  border-color: #ddd;
  color: #999;
  cursor: not-allowed;
}

.upload-wrapper {
  background: #fff;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.picture-card .el-upload.el-upload--picture-card {
  width: 100%;
  height: 200px;
  line-height: 200px;
}

.picture-card .el-upload-dragger {
  width: 100%;
  height: 100%;
}

.upload-preview {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  position: relative;
}

.uploaded-image-preview,
.uploaded-video-preview {
  max-width: 100%;
  max-height: 180px;
  object-fit: contain;
}

.remove-preview-button {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .form-section {
    padding: 1rem;
  }

  .section-title {
    font-size: 1.25rem;
  }

  .upload-wrapper {
    padding: 0.75rem;
  }

  .picture-card .el-upload.el-upload--picture-card {
    height: 150px;
    line-height: 150px;
  }

  .uploaded-image-preview,
  .uploaded-video-preview {
    max-height: 130px;
  }
}

@media (max-width: 480px) {
  .picture-card .el-upload.el-upload--picture-card {
    height: 120px;
    line-height: 120px;
  }

  .uploaded-image-preview,
  .uploaded-video-preview {
    max-height: 100px;
  }
}
</style>
