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
                      <label class="form-label">Nombre de la Masterclass</label>
                      <input type="text" v-model="form.title" class="form-control" placeholder="Titulo de la masterclass" />
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
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
                                placeholder="Descripción de la masterclass"></textarea>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-label">Objetivo</label>
                      <textarea class="form-control" v-model="form.objective" rows="2"
                                placeholder="Objetivo de la masterclass"></textarea>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="form-label">Fecha</label>
                      <input type="date" class="form-control" v-model="form.date" placeholder="Seleccione una fecha" />
                    </div>
                  </div>

                  <!-- hora -->
                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="form-label">Hora</label>
                      <input type="time" class="form-control" v-model="form.hour" placeholder="Seleccione la hora" />
                    </div>
                  </div>

                  <!-- Duracion --- cada unidad es 30 min -->
                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="form-label">Duracion</label>
                      <el-select type="select" v-model="form.duration" placeholder="Seleccione la duracion"
                                 class="w-100">
                        <el-option v-for="i in 10" :key="i" :label="i * 30 + ' min'" :value="i">
                        </el-option>
                      </el-select>
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                   <!---  mejorar ia -->
                    <a href="" class="btn btn-link">Mejorar con IA</a>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="form-group" >
                      <label class="form-label">Correo Electrónico</label>
                      <input type="email" class="form-control" v-model="form.email" placeholder="Ingrese su correo" />
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="form-group" >
                      <label class="form-label">Número de Celular</label>
                      <input type="tel" class="form-control" v-model="form.phone" placeholder="Ingrese su número de celular" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="upload-wrapper">
                  <label class="form-label">Subir Imágenes</label>
                  <el-upload class="upload-container"
                    ref="imagesUpload"
                    action=""
                    drag
                    accept=".jpg,.jpeg,.png,.webp"
                    :on-change="onImageUploadChange"
                    :http-request="submit"
                    :multiple="true"
                    :limit="5"
                  :auto-upload="false"
                  :file-list="imageFileList"
                  :on-exceed="handleImageExceed"
                  >
                  <div class="upload-content">
                    <i class="el-icon-upload upload-icon"></i>
                    <div class="upload-text">
                      <span class="upload-main-text">Suelta tus imágenes aquí o</span>
                      <em class="upload-action">haz clic para cargar</em>
                    </div>
                    <div class="upload-tip">
                      Solo archivos jpg/png/webp con un tamaño menor de 500kb
                    </div>
                  </div>
                  </el-upload>
                </div>
              </div>


              <div class="col-12 col-md-6">
                <div class="upload-wrapper">
                  <label class="form-label">Subir Documentos</label>
                  <el-upload class="upload-container"
                    ref="documentsUpload"
                    action=""
                    drag
                    accept=".doc,.docx,.pdf,.xls,.xlsx,.txt"
                    :on-change="onDocumentUploadChange"
                    :http-request="submit"
                    :multiple="true"
                    :limit="5"
                  :auto-upload="false"
                  :file-list="documentFileList"
                  :on-exceed="handleDocumentExceed"
                  >
                  <div class="upload-content">
                    <i class="el-icon-upload upload-icon"></i>
                    <div class="upload-text">
                      <span class="upload-main-text">Suelta tus documentos aquí o</span>
                      <em class="upload-action">haz clic para cargar</em>
                    </div>
                    <div class="upload-tip">
                      Solo archivos .doc, .pdf, .xls, etc., con un tamaño menor de 1MB
                    </div>
                  </div>
                  </el-upload>
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
export default {
  props: {
    categoriesList: Array,
    route: String,
  },
  data() {
    return {
      loading: false,
      form: {
        title: '',
        id_categories: null,
        description: '',
        objective: '',
        date: null,
        email: '',
        phone: '',
      },
      imageFileList: [],
      documentFileList: [],
      isSubmitting: false,
    };
  },
  methods: {
    validateFields() {
      if (!this.form.title.trim()) {
        this.$message.warning('El nombre de la masterclass es obligatorio.');
        return false;
      }
      if (!this.form.id_categories) {
        this.$message.warning('Debe seleccionar una categoría.');
        return false;
      }
      if (!this.form.description.trim()) {
        this.$message.warning('La descripción es obligatoria.');
        return false;
      }
      if (!this.form.objective.trim()) {
        this.$message.warning('El objetivo de la masterclass es obligatorio.');
        return false;
      }
      if (!this.form.date) {
        this.$message.warning('Debe seleccionar una fecha.');
        return false;
      }
      if (!this.form.hour) {
        this.$message.warning('Debe seleccionar una hora.');
        return false;
      }
      if (!this.form.duration) {
        this.$message.warning('Debe seleccionar una duración.');
        return false;
      }
      if (!this.form.email.trim() || !this.validateEmail(this.form.email)) {
        this.$message.warning('Debe ingresar un correo electrónico válido.');
        return false;
      }
      if (!this.form.phone.trim() || !this.validatePhone(this.form.phone)) {
        this.$message.warning('Debe ingresar un número de celular válido.');
        return false;
      }
      if (this.imageFileList.length === 0) {
        this.$message.warning('Debe subir al menos una imagen.');
        return false;
      }
      return true;
    },
    validateEmail(email) {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return regex.test(email);
    },
    validatePhone(phone) {
      const regex = /^[0-9]{8,15}$/;
      return regex.test(phone);
    },
    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formData = new FormData();
        formData.append('title', this.form.title);
        formData.append('id_categories', this.form.id_categories);
        formData.append('description', this.form.description);
        formData.append('objective', this.form.objective);
        formData.append('date', this.form.date);
        formData.append('hour', this.form.hour);
        formData.append('duration', this.form.duration);
        formData.append('email_contact', this.form.email);
        formData.append('phone_contact', this.form.phone);

        // Agregar archivos de imágenes
        this.imageFileList.forEach((file) => {
          formData.append('images[]', file.raw);
        });

        // Agregar documentos
        this.documentFileList.forEach((file) => {
          formData.append('documents[]', file.raw);
        });

        try {
          const response = await axios({
            url: 'store-masterclass',
            method: 'post',
            data: formData,
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          if (response.status === 200) {
            this.$message.success('La masterclass se ha registrado correctamente.');
            this.resetForm();
          } else {
            this.$message.warning('Hubo un problema al guardar la masterclass.');
          }
        } catch (error) {
          console.error(error);
          this.$message.error('Ocurrió un error en el servidor.');
        } finally {
          this.isSubmitting = false;
        }
      }
    },
    resetForm() {
      this.form = {
        title: '',
        id_categories: null,
        description: '',
        objective: '',
        date: null,
        email: '',
        phone: '',
      };
      this.imageFileList = [];
      this.documentFileList = [];
    },
    onImageUploadChange(file) {
      const isLt500kb = file.size / 1024 < 500;
      if (!isLt500kb) {
        this.$message.error('Cada imagen debe pesar menos de 500kb.');
        this.$refs.imagesUpload.clearFiles();
        return false;
      }
      this.imageFileList.push(file);
    },
    onDocumentUploadChange(file) {
      const isLt1MB = file.size / 1024 / 1024 < 1;
      if (!isLt1MB) {
        this.$message.error('Cada documento debe pesar menos de 1MB.');
        this.$refs.documentsUpload.clearFiles();
        return false;
      }
      this.documentFileList.push(file);
    },
    handleImageExceed() {
      this.$message.warning('Solo puedes subir hasta 5 imágenes.');
    },
    handleDocumentExceed() {
      this.$message.warning('Solo puedes subir hasta 5 documentos.');
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

/* Ajustes para el select de Element UI */
.el-select {
  width: 100%;
}

/* Botón de guardado */
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
</style>
