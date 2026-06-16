<template>
  <div class="container-fluid py-1">
    <div class="card course-form-card">
      <div class="card-body" v-loading="loading" element-loading-text="Cargando..."
        element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
        <form @submit.prevent="submit" id="upload" enctype="multipart/form-data" method="post" class="needs-validation">
          <div class="form-section mb-1">
            <h3 class="section-title">Información General</h3>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="row g-3">
                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="form-label">{{ productTypeId === '2' ? 'Nombre del Libro' : 'Nombre del Curso' }}</label>
                      <input type="text" v-model="form.title" class="form-control" :placeholder="productTypeId === '2' ? 'Titulo del libro' : 'Titulo del curso'" />
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-group">
                      <label class="form-label">Categoría</label>
                      <el-select type="select" v-model="form.id_categories" placeholder="Seleccione Categoría"
                        class="w-100">
                        <el-option v-for="c in categoriesList" :key="c.id" :label="c.name" :value="c.id">
                        </el-option>
                      </el-select>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-label">Descripción</label>
                      <textarea class="form-control" v-model="form.description" rows="2"
                        placeholder="Descripción del curso"></textarea>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-group">
                      <label class="form-label">Nivel del Curso</label>
                      <el-select v-model="form.id_level" placeholder="Seleccione el nivel" class="w-100"
                        @change="handleLevelChange">
                        <el-option v-for="l in levelsList" :key="l.id" :label="l.description" :value="l.id">
                        </el-option>
                      </el-select>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="form-group" :class="{ 'is-free': form.is_free }">
                      <label class="form-label">Precio</label>
                      <input type="number" min="0" class="form-control" v-model="form.price" placeholder="0.00"
                        :disabled="form.is_free" />
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="form-group" :class="{ 'is-free': form.is_free }">
                      <label class="form-label">Precio Total (IVA: {{ calculateIVA }}) </label>
                      <input type="number" min="0" disabled class="form-control" v-model="form.price_base"
                        placeholder="0.00" />
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-group">
                      <label class="form-label">Meses de duración</label>
                      <input v-model="form.months" class="form-control" type="number" :disabled="form.no_end_date" />
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-check mb-1">
                      <input class="form-check-input" type="checkbox" v-model="form.certificate"
                        id="certificateCheck" />
                      <label class="form-check-label" for="certificateCheck" style="color: #20e404; font-weight: bold">
                        ¿Se entregará certificado?
                      </label>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-check mb-1">
                      <input class="form-check-input" type="checkbox" v-model="form.is_free" @change="handleFreeCourse"
                        id="freeCheck" />
                      <label class="form-check-label" for="freeCheck" style="color: #20e404; font-weight: bold">
                        ¿Es un curso gratuito?
                      </label>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6" v-if="productTypeId !== '2'">
                    <div class="form-check mb-1">
                      <input class="form-check-input" type="checkbox" v-model="form.no_end_date"
                        @change="handleNoEndDate" id="noEndDateCheck" />
                      <label class="form-check-label" for="noEndDateCheck" style="color: #20e404; font-weight: bold">
                        Sin fecha de fin
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            <div class="col-12 col-md-6">
              <div class="upload-wrapper">
                <label class="form-label">Subir Portada</label>
                <div class="upload-requirements mb-2">
                  <small class="text-muted">Dimensiones recomendadas: 1280x720 píxeles</small>
                </div>
                <el-upload 
                  class="upload-container" 
                  ref="courseUpload" 
                  action="" 
                  drag
                  accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG, .webp, .WEBP" 
                  :on-change="onUploadChange"
                  :http-request="submit" 
                  :multiple="false" 
                  :limit="1" 
                  :auto-upload="false" 
                  :file-list="fileList"
                  :on-exceed="handleExceed" 
                  :on-remove="handleCoverRemove"
                  :show-file-list="true">
                  <div v-if="imagePreviewUrl" class="preview-container">
                    <img :src="imagePreviewUrl" class="upload-preview-image" />
                  </div>
                  <div v-else class="upload-content"> 
                    <i class="el-icon-upload upload-icon"></i>
                    <div class="upload-text">
                      <span class="upload-main-text">Suelta tu archivo aquí o</span>
                      <em class="upload-action">haz clic para cargar</em>
                    </div>
                  </div>
                
                  <div class="el-upload__tip" slot="tip">
                    Solo archivos jpg/png con un tamaño menor de 500kb
                  </div>
                </el-upload>

                <!-- Información adicional del archivo -->
                <div v-if="fileList.length > 0" class="mt-2">
                  <small class="text-muted d-block">
                    <strong>Archivo:</strong> {{ fileList[0].name }}
                  </small>
                  <small class="text-muted d-block">
                    <strong>Tamaño:</strong> {{ formatFileSize(fileList[0].size) }}
                  </small>
                </div>

                <div v-if="currentImageDimensions.width" class="mt-2">
                  <small class="text-muted d-block">
                    <strong>Dimensiones:</strong> {{ currentImageDimensions.width }}x{{ currentImageDimensions.height }} píxeles
                  </small>
                </div>
              </div>
            </div>
            </div>
          </div>

          <div class="form-section mb-1">
            <h3 class="section-title">Información Marketplace</h3>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="form-group mb-1">
                  <label class="form-label">{{ productTypeId === '2' ? 'Acerca del libro' : 'Acerca del curso' }}</label>
                  <textarea class="form-control" v-model="form.course_about" rows="2"
                    :placeholder="productTypeId === '2' ? 'Detalles acerca del libro' : 'Detalles acerca del curso'"></textarea>
                </div>
                <div class="form-group mb-1">
                  <label class="form-label">Lo que aprenderá</label>
                  <textarea class="form-control" v-model="form.will_learn" rows="2"
                    placeholder="Lo que aprenderá en el curso"></textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="upload-wrapper">
                  <label class="form-label">Subir video o imagen de venta</label>
                  <div class="upload-requirements mb-2">
                    <small class="text-muted">Dimensiones recomendadas para video: 1920x1080 píxeles</small>
                    <br />
                    <small class="text-muted">Dimensiones recomendadas para imagen: 1280x720 píxeles</small>
                  </div>
                  <el-upload 
                    class="upload-container" 
                    ref="courseUploadVideo" 
                    action="" 
                    drag
                    accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG, .webp, .WEBP, .mp4" 
                    :on-change="onUploadChangeVideo"
                    :http-request="submit" 
                    :multiple="false" 
                    :limit="1" 
                    :auto-upload="false" 
                    :file-list="fileListVideo"
                    :on-exceed="handleExceedVideo" 
                    :on-remove="handleVideoRemove"
                    :show-file-list="true">
                    <div v-if="videoImagePreviewUrl" class="preview-container">
                      <video v-if="isVideoPreview" :src="videoImagePreviewUrl" class="upload-preview-image" controls></video>
                      <img v-else :src="videoImagePreviewUrl" class="upload-preview-image" />
                    </div>
                    <div v-else class="upload-content"> 
                      <i class="el-icon-upload upload-icon"></i>
                      <div class="upload-text">
                        <span class="upload-main-text">Suelta tu archivo aquí o</span>
                        <em class="upload-action">haz clic para cargar</em>
                      </div>
                    </div>
                  </el-upload>

                  <!-- Información adicional del archivo -->
                  <div v-if="fileListVideo.length > 0" class="mt-2">
                    <small class="text-muted d-block">
                      <strong>Archivo:</strong> {{ fileListVideo[0].name }}
                    </small>
                    <small class="text-muted d-block">
                      <strong>Tamaño:</strong> {{ formatFileSize(fileListVideo[0].size) }}
                    </small>
                    <small class="text-muted d-block">
                      <strong>Tipo:</strong> {{ isVideoPreview ? 'Video' : 'Imagen' }}
                    </small>
                  </div>

                  <div v-if="currentVideoImageDimensions.width" class="mt-2">
                    <small class="text-muted d-block">
                      <strong>Dimensiones:</strong> {{ currentVideoImageDimensions.width }}x{{ currentVideoImageDimensions.height }} píxeles
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6" v-if="productTypeId !== '2'">
                <div class="form-group mb-1">
                  <label class="form-label">Conocimientos previos</label>
                  <textarea class="form-control" v-model="form.prev_knowledge" rows="2"
                    placeholder="Conocimientos previos para llevar el curso"></textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group mb-1">
                  <label class="form-label">{{ productTypeId === '2' ? 'Libro destinado para' : 'Curso destinado para' }}</label>
                  <textarea class="form-control" v-model="form.course_for" rows="2"
                    :placeholder="productTypeId === '2' ? 'A quienes va destinado el libro' : 'A quienes va destinado el curso'"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-success btn-lg px-4" :disabled="isSubmitting">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
const FILE_LIMITS = {
  IMAGE_SIZE: 500 * 1024,         // 500KB
  VIDEO_SIZE: 90 * 1024 * 1024,   // 90MB (aumentado de 80MB a 90MB)
  TOTAL_PAYLOAD: 100 * 1024 * 1024 // 100MB
};
export default {
  props: {
    categoriesList: Array,
    levelsList: Array,
    route: String,
    productTypeId: String,
  },
  data() {
    return {
      loading: false,
      extensions: '.jpeg, .jpg, .png',
      categories: [],
      form: {},
      fileList: [],
      fileListVideo: [],
      isSubmitting: false,
      iva_rate: 0,
      currentImageDimensions: {
        width: null,
        height: null,
      },
      currentVideoImageDimensions: {
        width: null,
        height: null,
      },
      imagePreviewUrl: null,
      videoImagePreviewUrl: null,
      isVideoPreview: false,
    };
  },
  computed: {
    isBasicLevel() {
      return this.form.id_level === 1;
    },
    calculateIVA() {
      if (!this.form.price || isNaN(this.form.price)) return '0.00';
      return (parseFloat(this.form.price) * this.iva_rate).toFixed(2);
    },
  },
  async mounted() {
    try {
      const response = await axios.get('/api/config/settings');
      const ivaOption = response.data.find((option) => option.description === 'iva_rate');
      if (ivaOption) {
        this.iva_rate = parseFloat(ivaOption.value) / 100;
      }
      console.log('IVA cargado:', this.iva_rate);
    } catch (error) {
      console.error('Error al cargar el IVA:', error);
    }
  },
  watch: {
    'form.price': function (newPrice) {
      if (!isNaN(newPrice) && newPrice !== '') {
        const priceBase = parseFloat(newPrice);
        const iva = priceBase * this.iva_rate;
        this.form.price_base = (priceBase + iva).toFixed(2);
      }
    },
    isBasicLevel: function (newValue) {
      if (newValue) {
        this.form.price = null;
        this.form.price_base = null;
        this.form.is_free = true;
      }
    },
  },
  created() {
    this.initForm();
  },
  methods: {
    handleExceed(files, fileList) {
      // Si se intenta subir más de un archivo, reemplazar el actual
      this.$refs.courseUpload.clearFiles();

      // Tomar solo el primer archivo de los nuevos
      const file = files[0];

      // Crear un objeto file compatible con el-upload
      const fileObj = {
        name: file.name,
        size: file.size,
        type: file.type,
        raw: file,
        status: 'ready',
        uid: Date.now() + Math.random()
      };

      // Simular el comportamiento de onChange con el nuevo archivo
      this.onUploadChange(fileObj, [fileObj]);

      // Actualizar la lista de archivos del componente
      this.$nextTick(() => {
        this.$refs.courseUpload.uploadFiles = [fileObj];
      });

      this.$message.info('Archivo anterior reemplazado por el nuevo.');
    },

    calculateTotalPayloadSize() {
      let totalSize = 0;

      // Agregar tamaño de archivos
      if (this.fileList.length > 0) {
        totalSize += this.fileList[0].raw.size;
      }

      if (this.fileListVideo.length > 0) {
        totalSize += this.fileListVideo[0].raw.size;
      }

      // Agregar estimación de datos de formulario (textos, etc.)
      const formDataEstimate = JSON.stringify(this.form).length * 2; // Factor de seguridad
      totalSize += formDataEstimate;

      return totalSize;
    },

    handleCoverRemove() {
      this.clearCoverUpload();
    },

    handleVideoRemove() {
      this.clearVideoUpload();
    },

    handleExceedVideo(files, fileList) {
      // Si se intenta subir más de un archivo, reemplazar el actual
      this.$refs.courseUploadVideo.clearFiles();

      // Tomar solo el primer archivo de los nuevos
      const file = files[0];

      // Crear un objeto file compatible con el-upload
      const fileObj = {
        name: file.name,
        size: file.size,
        type: file.type,
        raw: file,
        status: 'ready',
        uid: Date.now() + Math.random()
      };

      // Simular el comportamiento de onChange con el nuevo archivo
      this.onUploadChangeVideo(fileObj, [fileObj]);

      // Actualizar la lista de archivos del componente
      this.$nextTick(() => {
        this.$refs.courseUploadVideo.uploadFiles = [fileObj];
      });

      this.$message.info('Archivo anterior reemplazado por el nuevo.');
    },

    async onUploadChangeVideo(file, fileList) {
      if (this.videoImagePreviewUrl) {
        URL.revokeObjectURL(this.videoImagePreviewUrl);
        this.videoImagePreviewUrl = null;
      }

      const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'video/mp4'];
      const isValidFile = validTypes.includes(file.raw.type);
      const isVideo = file.raw.type === 'video/mp4';
      const isImage = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.raw.type);

      // Validar tipo
      if (!isValidFile) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png|webp|mp4.');
        this.clearVideoUpload();
        return false;
      }

      // Validar tamaño según tipo
      let sizeLimit = isVideo ? FILE_LIMITS.VIDEO_SIZE : FILE_LIMITS.IMAGE_SIZE;
      let sizeLimitText = isVideo ? '90MB' : '900KB';

      if (file.raw.size > sizeLimit) {
        const sizeMB = (file.raw.size / (1024 * 1024)).toFixed(2);
        this.$message.error(`El archivo pesa ${sizeMB}MB. Debe ser menor a ${sizeLimitText}.`);
        this.clearVideoUpload();
        return false;
      }

      // Validar tamaño total del payload
      this.fileListVideo = [file];
      const totalSize = this.calculateTotalPayloadSize();

      if (totalSize > FILE_LIMITS.TOTAL_PAYLOAD) {
        const totalMB = (totalSize / (1024 * 1024)).toFixed(2);
        this.$message.error(`El tamaño total de archivos (${totalMB}MB) excede el límite permitido de 60MB.`);
        this.clearVideoUpload();
        return false;
      }
    
      try {
        this.isVideoPreview = isVideo;
        this.videoImagePreviewUrl = URL.createObjectURL(file.raw);
        this.currentVideoImageDimensions = await this.getVideoDimensions(file.raw);

        this.$nextTick(() => {
          if (this.$refs.courseUploadVideo.uploadFiles.length === 0 || 
              this.$refs.courseUploadVideo.uploadFiles[0].uid !== file.uid) {
            this.$refs.courseUploadVideo.uploadFiles = [file];
          }
        });
      } catch (error) {
        console.error('Error al procesar el archivo:', error);
        this.$message.error('Error al procesar el archivo');
        this.clearVideoUpload();
      }
    },

    async getImageDimensions(file) {
      return new Promise((resolve) => {
        const img = new Image();
        img.onload = function () {
          resolve({
            width: this.width,
            height: this.height,
          });
        };
        img.src = URL.createObjectURL(file);
      });
    },

    async getVideoDimensions(file) {
      if (file.type.includes('video')) {
        return new Promise((resolve) => {
          const video = document.createElement('video');
          video.preload = 'metadata';
          video.onloadedmetadata = function () {
            resolve({
              width: this.videoWidth,
              height: this.videoHeight,
            });
            URL.revokeObjectURL(video.src);
          };
          video.src = URL.createObjectURL(file);
        });
      } else {
        return this.getImageDimensions(file);
      }
    },

    async onUploadChange(file, fileList) {
      if (this.imagePreviewUrl) {
        URL.revokeObjectURL(this.imagePreviewUrl);
        this.imagePreviewUrl = null;
      }
    
      const isImage = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.raw.type);
      const isLt500K = file.raw.size <= FILE_LIMITS.IMAGE_SIZE;
    
      if (!isImage) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png|webp.');
        this.clearCoverUpload();
        return false;
      }
    
      if (!isLt500K) {
        const sizeMB = (file.raw.size / (1024 * 1024)).toFixed(2);
        this.$message.error(`El archivo pesa ${sizeMB}MB. Debe ser menor a 500KB.`);
        this.clearCoverUpload();
        return false;
      }
    
      // Validar tamaño total del payload
      this.fileList = [file];
      const totalSize = this.calculateTotalPayloadSize();
    
      if (totalSize > FILE_LIMITS.TOTAL_PAYLOAD) {
        const totalMB = (totalSize / (1024 * 1024)).toFixed(2);
        this.$message.error(`El tamaño total de archivos (${totalMB}MB) excede el límite permitido de 100MB.`);
        this.clearCoverUpload();
        return false;
      }
    
      try {
        this.imagePreviewUrl = URL.createObjectURL(file.raw);
        this.currentImageDimensions = await this.getImageDimensions(file.raw);
      
        this.$nextTick(() => {
          if (this.$refs.courseUpload.uploadFiles.length === 0 || 
              this.$refs.courseUpload.uploadFiles[0].uid !== file.uid) {
            this.$refs.courseUpload.uploadFiles = [file];
          }
        });
      } catch (error) {
        console.error('Error al procesar la imagen:', error);
        this.$message.error('Error al procesar la imagen');
        this.clearCoverUpload();
      }
    },

    clearCoverUpload() {
      this.fileList = [];
      this.currentImageDimensions = { width: null, height: null };

      if (this.$refs.courseUpload) {
        this.$refs.courseUpload.clearFiles();
        this.$refs.courseUpload.uploadFiles = [];
      }

      if (this.imagePreviewUrl) {
        URL.revokeObjectURL(this.imagePreviewUrl);
        this.imagePreviewUrl = null;
      }
    },

    clearVideoUpload() {
      this.fileListVideo = [];
      this.currentVideoImageDimensions = { width: null, height: null };
      this.isVideoPreview = false;

      if (this.$refs.courseUploadVideo) {
        this.$refs.courseUploadVideo.clearFiles();
        this.$refs.courseUploadVideo.uploadFiles = [];
      }

      if (this.videoImagePreviewUrl) {
        URL.revokeObjectURL(this.videoImagePreviewUrl);
        this.videoImagePreviewUrl = null;
      }
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';

      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));

      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    createFileObject(rawFile) {
      return {
        name: rawFile.name,
        size: rawFile.size,
        type: rawFile.type,
        raw: rawFile,
        status: 'ready',
        uid: Date.now() + Math.random()
      };
    },

    handleLevelChange(value) {
      if (value == 1) {
        this.form.price = null;
        this.form.price_base = null;
      }
    },

    handleFreeCourse() {
      if (this.form.is_free) {
        this.form.price = null;
        this.form.price_base = null;
        this.$message.info('Has creado un curso gratuito. No obtendrás puntos por subscripciones.');
      }
    },

    handleNoEndDate() {
      if (this.form.no_end_date) {
        this.form.months = '';
      } else {
        this.form.months = 1;
      }
    },

    initForm() {
      this.form = {
        id: null,
        title: null,
        id_categories: null,
        description: null,
        id_level: null,
        price: null,
        certificate: false,
        price_base: null,
        course_about: null,
        will_learn: null,
        prev_knowledge: 'Ninguno',
        course_for: null,
        months: 1,
        is_free: false,
        no_end_date: false,
      };

      // Usar los métodos auxiliares para limpiar
      this.clearCoverUpload();
      this.clearVideoUpload();
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },

    validateFields(productTypeId) {
      if (!this.form.title || this.form.title.trim() === '' || this.form.title.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }

      if (this.form.title.length > 255) {
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

      if (!this.$refs.courseUpload || this.$refs.courseUpload.uploadFiles.length == 0) {
        this.$message.warning('La portada del curso esta vacia!');
        return false;
      }

      if (!this.$refs.courseUploadVideo || this.$refs.courseUploadVideo.uploadFiles.length == 0) {
        this.$message.warning('La imagen o video introductivo esta vacio!');
        return false;
      }

      if (!this.form.is_free) {
        if (this.form.price === null || this.form.price === '' || this.form.price == 0) {
          this.$message.warning('Precio esta vacio o su valor es 0!');
          return false;
        }

        if (this.form.price_base === null || this.form.price_base === '' || this.form.price_base == 0) {
          this.$message.warning('Precio Base esta vacio o su valor es 0!');
          return false;
        }

        if (isNaN(this.form.price) == true) {
          this.$message.warning('Verifique el campo de precio');
          return false;
        }

        if (isNaN(this.form.price_base) == true) {
          this.$message.warning('Verifique el campo de precio base');
          return false;
        }
      }

      if (this.form.price < 0) {
        this.$message.warning('El precio asignado al curso no es correcto');
        return false;
      }

      if (this.form.price_base < 0) {
        this.$message.warning('El precio base asignado al curso no es correcto');
        return false;
      }
      if (this.form.price !== null && this.form.price !== '' && String(this.form.price).length > 7) {
        this.$message.warning('El campo de precio es muy largo');
        return false;
      }
      if (this.form.price_base !== null && this.form.price_base !== '' && String(this.form.price_base).length > 7) {
        this.$message.warning('El campo de precio base es muy largo');
        return false;
      }

      if (this.form.is_free && (this.form.price !== null && this.form.price !== 0 || this.form.price_base !== null && this.form.price_base !== 0)) {
        this.$message.warning('Los cursos gratuitos deben tener un precio y precio base de 0.');
        return false;
      }

      if (!this.form.no_end_date) {
        if (this.form.months < 1) {
          this.$message.warning('La duración mínima es 1 mes');
          return false;
        }

        if (this.form.months > 12) {
          this.$message.warning('La duración máxima son 12 meses');
          return false;
        }
      }

      if (
        !this.form.course_about ||
        this.form.course_about.trim() === '' ||
        this.form.course_about.length === 0
      ) {
        this.$message.warning('Acerca del curso esta vacio!');
        return false;
      }

      if (
        !this.form.will_learn ||
        this.form.will_learn.trim() === '' ||
        this.form.will_learn.length === 0
      ) {
        this.$message.warning('Lo que aprenderás del curso esta vacio!');
        return false;
      }

      if (
        !this.form.prev_knowledge ||
        this.form.prev_knowledge.trim() === '' ||
        this.form.prev_knowledge.length === 0
      ) {
        this.$message.warning('Los conocimientos previos del curso esta vacio!');
        return false;
      }

      if (
        !this.form.course_for ||
        this.form.course_for.trim() === '' ||
        this.form.course_for.length === 0
      ) {
        this.$message.warning('El público destino del curso esta vacio!');
        return false;
      }

      // Validaciones solo para cursos, no para libros
      if (productTypeId === '1') {
        if (!this.form.id_categories || this.form.id_categories.length === 0) {
          this.$message.warning('No ha seleccionado la categoría del curso');
          return false;
        }

        if (!this.form.id_level || this.form.id_level.length === 0) {
          this.$message.warning('No ha seleccionado el nivel del curso');
          return false;
        }
      }

      return true;
    },

    async submit() {
      if (this.validateFields(this.productTypeId)) {
        this.isSubmitting = true;
      
        // Verificar tamaño antes de enviar
        const totalSize = this.calculateTotalPayloadSize();
        const totalMB = (totalSize / (1024 * 1024)).toFixed(2);

        if (totalSize > FILE_LIMITS.TOTAL_PAYLOAD) {
          this.$message.error(`El tamaño total (${totalMB}MB) excede el límite de 100MB.`);
          this.isSubmitting = false;
          return;
        }
      
        console.log(`Enviando ${totalMB}MB de datos...`);

        const formdata = new FormData();
        formdata.append('title', this.form.title);
        formdata.append('id_categories', this.form.id_categories);
        formdata.append('description', this.form.description);
        formdata.append('id_level', this.form.id_level);
        formdata.append('price_base', this.form.price_base || 0);
        formdata.append('price', this.form.price || 0);
        formdata.append('course_about', this.form.course_about);
        formdata.append('will_learn', this.form.will_learn);
        formdata.append('prev_knowledge', this.form.prev_knowledge);
        formdata.append('course_for', this.form.course_for);
        formdata.append('months', this.form.no_end_date ? 'Sin definir' : parseInt(this.form.months));
        formdata.append('certificate', this.form.certificate);
        formdata.append('is_free', this.form.is_free);
        formdata.append('product_type_id', this.productTypeId);
      
        if (this.fileList.length > 0) {
          formdata.append('file', this.fileList[0].raw);
        }
      
        if (this.fileListVideo.length > 0) {
          formdata.append('file_video', this.fileListVideo[0].raw);
        }

        console.log('FormData preparado para envío:', formdata);
      
        try {
          this.loading = true;

          // Configurar timeout más largo para archivos grandes
          const request = await axios({
            url: '/course/store-course',
            method: 'post',
            data: formdata,
            headers: { 
              'Content-Type': 'multipart/form-data',
            },
            timeout: 300000, // 5 minutos
            onUploadProgress: (progressEvent) => {
              const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
              console.log(`Progreso de subida: ${percentCompleted}%`);
              // Opcional: mostrar progreso al usuario
              // this.$message.info(`Subiendo archivo: ${percentCompleted}%`);
            }
          });

          if (request.status == 200) {
            this.$message.success('El curso ha sido registrado correctamente');
            this.initForm();
            this.fileList = [];
            this.fileListVideo = [];
            window.location.href = this.route;
          } else {
            this.$message.warning('Error al validar datos');
          }

        } catch (error) {
          console.error('Error al guardar el curso:', error);

          // Manejo específico de errores
          if (error.response?.status === 413) {
            this.$message.error('El archivo es demasiado grande. Reduce el tamaño del video o configura el servidor para archivos más grandes.');
          } else if (error.response?.status === 408 || error.code === 'ECONNABORTED') {
            this.$message.error('Tiempo de espera agotado. El archivo es muy grande o la conexión es lenta.');
          } else if (error.response?.status >= 500) {
            this.$message.error('Error del servidor. Contacta al administrador.');
          } else {
            this.$message.error('Error al guardar el curso: ' + (error.response?.data?.message || error.message));
          }
        } finally {
          this.isSubmitting = false;
          this.loading = false;
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

.upload-container {
  width: 100% !important;
  height: auto !important;
  border: 2px dashed #dcdfe6;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
}

.upload-container:hover {
  border-color: #409eff;
}

.upload-content {
  padding: 20px;
  text-align: center;
}

.upload-icon {
  font-size: 28px;
  color: #8c939d;
  margin-bottom: 10px;
}

.upload-text {
  color: #606266;
  font-size: 14px;
  margin-bottom: 10px;
}

.upload-main-text {
  display: block;
  margin-bottom: 5px;
}

.upload-action {
  color: #409eff;
  font-style: normal;
}

.upload-tip {
  font-size: 12px;
  color: #909399;
}

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

  .upload-content {
    padding: 15px 10px;
  }

  .upload-icon {
    font-size: 24px;
  }

  .upload-text {
    font-size: 13px;
    line-height: 1.4;
  }

  .upload-main-text,
  .upload-action {
    display: inline;
  }

  .upload-tip {
    font-size: 11px;
    margin-top: 8px;
  }

  .el-upload-dragger {
    width: 100% !important;
    height: auto !important;
    min-height: 120px !important;
  }

  .el-upload--picture-card {
    width: 100% !important;
    height: auto !important;
  }

  .el-upload-list--picture-card .el-upload-list__item {
    width: 100% !important;
    height: auto !important;
    aspect-ratio: 16/9;
  }
}

@media (max-width: 480px) {
  .upload-content {
    padding: 12px 8px;
  }

  .upload-icon {
    font-size: 20px;
  }

  .upload-text {
    font-size: 12px;
  }

  .el-upload-dragger {
    min-height: 100px !important;
  }
}

.el-upload-list--picture-card {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  width: 100%;
}

.el-upload-list--picture-card .el-upload-list__item {
  margin: 0 !important;
  float: none;
}

.el-upload-list--picture-card .el-upload-list__item-thumbnail {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.el-select {
  width: 100%;
}

.btn-success {
  background-color: #4caf50;
  border-color: #4caf50;
}

.btn-success:hover {
  background-color: #45a049;
  border-color: #45a049;
}

.btn-success:disabled {
  background-color: #4caf50;
  border-color: #4caf50;
  opacity: 0.65;
}

.preview-container {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.upload-container>>>.el-upload {
  width: 100%;
}

.upload-container>>>.el-upload-dragger {
  width: 100%;
  height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
}
</style>