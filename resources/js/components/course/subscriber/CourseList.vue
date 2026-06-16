<template>
  <div class="course-list-container">
    <div class="row g-4">
      <!-- Lista de cursos -->
      <template v-if="courses.length != 0">
        <div class="col-12 col-md-4 col-lg-3">
          <div class="courses-sidebar">
            <div class="course-item card mb-1" v-for="(course, index) in courses" :key="index"
              @click="listSubscribers(course.id)">
              <div class="course-content d-flex align-items-center p-2">
                <img class="course-thumbnail me-2" :src="course.url_portada" :alt="course.portada" />
                <span class="course-title">
                  {{ course.title }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de suscriptores -->
        <div class="col-12 col-md-8 col-lg-9">
          <template v-if="show">
            <div v-if="subs_qty == 0 && show_sub" class="alert alert-info text-center">
              <strong>Aún no hay suscriptores</strong>
            </div>

            <template v-else>
              <div class="row g-4">
                <div class="col-12 col-md-6" v-for="(subscriber, i) in subscribers" :key="i">
                  <div class="subscriber-card card h-100" @click="openDeliverCertificateModal(subscriber)">
                    <div class="card-body d-flex align-items-center">
                      <img :src="subscriber.photo" :alt="subscriber.name" class="subscriber-photo me-3" />
                      <div class="subscriber-info">
                        <h5 class="card-title mb-1">{{ subscriber.name }}</h5>
                        <p class="card-text text-muted mb-1">{{ subscriber.email }}</p>
                        <p v-if="
                          subscriber.completed_course == 1 &&
                          subscriber.certificate_delivered == 0
                        " class="certificate-status text-danger mb-0">
                          Certificado pendiente
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </template>

          <div v-else class="alert alert-info text-center">
            <strong>Seleccione un curso para ver los suscriptores</strong>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="col-12">
          <div class="alert alert-warning text-center">
            <strong>No tiene cursos registrados</strong>
          </div>
        </div>
      </template>
    </div>

    <!-- Modal sin cambios... -->
    <el-dialog title="Subir certificado" :visible.sync="deliverCertificateModal" width="90%" max-width="600px"
      class="certificate-modal">
      <div class="certificate-info mb-4">
        <div class="mb-2"><strong>Nombre: </strong>{{ subscriberSelected.fullName }}</div>
        <div class="mb-2"><strong>Correo: </strong>{{ subscriberSelected.email }}</div>
        <div class="mb-2"><strong>Teléfono: </strong>{{ subscriberSelected.phone }}</div>
      </div>

      <div class="upload-section">
        <label class="form-label"><strong>Subir Certificado: </strong></label>
        <el-upload class="upload-container" ref="certificateUpload" action="" drag
          accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG,.webp,.WEBP" :on-change="onUploadChange"
          :http-request="saveCertificate" :multiple="false" :limit="1" :auto-upload="false" :file-list="fileList"
          :on-exceed="handleExceed">
          <i class="el-icon-upload"></i>
          <div class="upload-text">Suelta tu archivo aquí o <em>haz clic para cargar</em></div>
          <div class="upload-tip">Solo archivos jpg/png con un tamaño menor de 500kb</div>
        </el-upload>
      </div>

      <div class="modal-footer">
        <el-button @click="deliverCertificateModal = false">Cancelar</el-button>
        <el-button type="primary" @click="saveCertificate()">Confirmar</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  props: {
    courses: {
      type: Array,
      required: true,
    },
    path: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      message: '',
      subscribers: '',
      question: {},
      options: null,
      show: false,
      subs_qty: 0,
      loading: false,
      show_sub: true,
      deliverCertificateModal: false,
      subscriberSelected: [],
      fileList: [],
    };
  },
  methods: {
    async saveCertificate() {
      try {
        const formdata = new FormData();
        formdata.append('file', this.fileList[0].raw);
        formdata.append('purchased_course_id', this.subscriberSelected.purchased_course_id);

        await axios.post('/course/certificate/deliver-certificate', formdata);
        this.deliverCertificateModal = false;
        this.$message.success('El certificado se cargó correctamente');

        // Actualizar lista de suscriptores
        await this.listSubscribers(this.subscriberSelected.course_id);
      } catch (error) {
        this.$message.error('Error al cargar el certificado');
      }
    },

    handleExceed(file) {
      if (file.size > 5000000) {
        this.$message.error('El archivo no debe pesar más de 5Mb!');
        this.$refs.certificateUpload.clearFiles();
        return false;
      }
    },

    onUploadChange(file) {
      this.fileList = [];

      const isImage = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(
        file.raw.type
      );
      const isLt5M = file.size < 5000000;

      if (!isImage) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png|webp.');
        this.$refs.certificateUpload.clearFiles();
        return false;
      }

      if (!isLt5M) {
        this.$message.error('El archivo no debe pesar más de 5Mb!');
        this.$refs.certificateUpload.clearFiles();
        return false;
      }

      this.fileList.push(file);
    },

    openDeliverCertificateModal(subscriber) {
      this.subscriberSelected = subscriber;
      if (subscriber.completed_course == 1 && subscriber.certificate_delivered == 0) {
        this.deliverCertificateModal = true;
      }
    },

    async listSubscribers(id) {
      try {
        this.show = true;
        this.show_sub = false;
        this.loading = true;

        const { data } = await axios.get(`/course/${id}/subscribersList`);
        this.subscribers = data.data;
        this.subs_qty = this.subscribers.length;
        this.show_sub = true;
      } catch (error) {
        this.$message.error('Error al cargar los suscriptores');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style>
/* Estilos anteriores mantenidos... */
.course-list-container {
  padding: 1rem;
  margin-top: 20px;
}

.course-item {
  margin-bottom: 15px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.course-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.course-thumbnail {
  width: 70px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
  margin-right: 10px;
}

.course-title {
  color: #2c3e50;
  text-decoration: none;
  font-weight: 500;
}

.course-item:hover .course-title {
  color: #3498db;
}

.subscriber-card {
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}

.subscriber-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.subscriber-photo {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  margin-right: 10px;
}

/* Media queries actualizados */
@media (max-width: 768px) {
  .courses-sidebar {
    margin-bottom: 2rem;
  }

  .subscriber-card .card-body {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem;
  }

  .subscriber-photo {
    margin-bottom: 1rem;
    margin-right: 0 !important;
    width: 80px;
    height: 80px;
  }

  .row.g-4 {
    --bs-gutter-y: 1.5rem !important;
  }

  .subscriber-info {
    width: 100%;
  }
}

@media (max-width: 576px) {
  .course-list-container {
    padding: 0.5rem;
  }

  .course-thumbnail {
    width: 50px;
    height: 40px;
  }

  .row.g-4 {
    gap: 5px;
    --bs-gutter-y: 2rem !important;
  }

  .subscriber-card {
    margin-bottom: 1.5rem;
  }
}
</style>
