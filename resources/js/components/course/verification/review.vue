<template>
  <div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-6">
          <div class="row">
            <!-- Campo para el título del curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Nombre del Curso</label>
                <input type="text" v-model="course.title" class="form-control" readonly
                  placeholder="Titulo del curso" />
              </div>
            </div>
            <!-- Campo para la categoría del curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Categoría</label>
                <el-select disabled v-model="course.category" placeholder="Seleccione Categoría" class="d-inline">
                </el-select>
              </div>
            </div>
            <!-- Campo para la descripción del curso -->
            <div class="col-lg-12">
              <div class="form-group">
                <label>Descripción</label>
                <textarea readonly class="form-control" v-model="course.description" rows="5" style="resize: none"
                  placeholder="Descripción del curso"></textarea>
              </div>
            </div>
            <!-- Campo para el nivel del curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Nivel del Curso</label>
                <el-select disabled v-model="course.level" placeholder="Seleccione el nivel" class="d-inline">
                </el-select>
              </div>
            </div>
            <!-- Campo para el precio del curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Precio</label>
                <input type="text" class="form-control" v-model="course.price" readonly />
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <label>Portada</label>
          <div class="card" style="width: 22rem">
            <!-- Imagen de la portada del curso -->
            <img class="card-img-top" :src="portada_url" :alt="portada_name" />
          </div>
        </div>

        <div class="row m-2">
          <h3>Información Marketplace</h3>
          <div class="row">
            <!-- Campo para describir más detalles sobre el curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Acerca del curso</label>
                <textarea class="form-control" v-model="course.course_about" rows="5" style="resize: none"
                  placeholder="Detalles acerca del curso" readonly></textarea>
              </div>
            </div>

            <!-- Campo para describir lo que aprenderá el estudiante -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Lo que aprenderá</label>
                <textarea class="form-control" v-model="course.will_learn" rows="5" style="resize: none"
                  placeholder="Lo que aprendera en el curso" readonly></textarea>
              </div>
            </div>

            <!-- Campo para los conocimientos previos requeridos -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Conocimientos previos</label>
                <textarea class="form-control" v-model="course.prev_knowledge" rows="5" style="resize: none"
                  placeholder="Conocimientos previos para llevar el curso" readonly></textarea>
              </div>
            </div>

            <!-- Campo para describir el público al que va destinado el curso -->
            <div class="col-lg-6">
              <div class="form-group">
                <label>Curso destinado para</label>
                <textarea class="form-control" v-model="course.course_for" rows="5" style="resize: none"
                  placeholder="A quienes va destinado el curso" readonly></textarea>
              </div>
            </div>

            <div class="col-lg-6">
              <label>Portada Venta</label>
              <div class="card" style="width: 22rem">
                <!-- Mostrar un video si es que el archivo es un video -->
                <video v-if="isVideo(portada_venta)" class="card-img-top" controls :src="portada_venta"></video>
                <!-- Si no es video, mostrar la imagen -->
                <img v-else class="card-img-top" :src="portada_venta" />
              </div>
            </div>

            <!-- Campo para agregar observaciones -->
            <div class="col-lg-6 mb-2">
              <label>Observación</label>
              <el-input type="textarea" resize="none" rows="6" v-model="observation"
                placeholder="Escriba el motivo de la desaprobación"></el-input>
              <div class="row mt-2">
                <div class="col-lg-12 text-center">
                  <!-- Botón para desaprobar la información -->
                  <button type="button" class="btn btn-warning mr-2" @click="changeStatus(clas.id, 1)">
                    Desaprobar Información
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <h2>Módulos</h2>

      <!-- Árbol de módulos, cargado de manera perezosa -->
      <el-tree :props="props" :load="loadNode" node-key="id" ref="tree" highlight-current lazy
        @node-click="handleNodeClick">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <!-- Color de nodo según el estado del módulo -->
          <div v-if="data.status == '2'">
            <span class="text-success">
              {{ getNodeLabel(node, data) }}
            </span>
          </div>
          <div v-else-if="data.status == '1'">
            <span class="text-warning">
              {{ getNodeLabel(node, data) }}
            </span>
          </div>
          <div v-else>
            <span class="text-danger">
              {{ getNodeLabel(node, data) }}
            </span>
          </div>
        </span>
      </el-tree>
    </div>

    <div class="row">
      <div class="col-md-6 offset-md-6 text-right px-3">
        <div v-if="ready === false" class="alert alert-info text-left">
          Para aprobar el curso primero abre cada clase del árbol y pulsa <strong>Aprobar</strong>.
          Cuando todas las clases estén aprobadas, aparecerá el botón <strong>Aprobar Curso</strong>.
        </div>
        <!-- Botón para aprobar el curso si está listo -->
        <button v-if="ready === true" type="button" class="btn btn-success" @click="approveCourse(course.id)"
          :disabled="isSubmitting">
          Aprobar Curso
        </button>
        <!-- Botón para enviar observaciones si el curso no está listo -->
        <button v-if="ready === false" @click="sendObservations(course.id)" type="button" class="btn btn-warning">
          Enviar Observaciones
        </button>
      </div>
    </div>

    <!-- Modal para ver más detalles de la clase -->
    <custom-modal id="class-modal" size="large" color="primary">
      <template #close>
        <button type="button" class="close" @click="closeModal()" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </template>

      <template #title>
        <b style="text-transform: uppercase"> {{ mutableClass.name }} </b>
      </template>
      <template #body>
        <div class="row">
          <div class="offset-md-6 col-md-6" style="right: 0">
            <!-- Indicadores de estado de la clase -->
            <span v-if="mutableClass.status == 0" class="badge bg-secondary float-right">No revisado</span>
            <span v-if="mutableClass.status == 1" class="badge bg-danger float-right">Desaprobado</span>
            <span v-if="mutableClass.status == 2" class="badge bg-success float-right">Aprobado</span>
          </div>
        </div>

        <class-view ref="classView" :clas="mutableClass" :resources="mutableResources" :video="mutableVideo"
          :course="course" :observations="observations"></class-view>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import ModalComponent from '@/components/common/ModalComponent.vue';
import ViewClass from '../../course/modules/classes/viewClass.vue';
import api from '../../../api/api';

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    ready: {
      type: Boolean,
      required: true,
    },
  },
  components: {
    'custom-modal': ModalComponent,
    'class-view': ViewClass,
  },
  data() {
    return {
      message: 'Este curso no cuenta con módulos',
      title: null,
      portada_url: '',
      portada_venta: '',
      input_visible: false,
      isSubmitting: false,
      portada_name: '',
      observation: '',
      observations: [],
      mutableClass: {},
      mutableResources: [],
      mutableVideo: [],
      props: {
        label: 'name',
        isLeaf: 'leaf',
      },
    };
  },
  mounted() {
    this.castUrl(this.course);
    this.listObservations();
  },
  watch: {
    mutableClass: async function (value) {
      // Carga recursos y video al cambiar la clase seleccionada
      this.mutableResources = [];
      this.mutableVideo = [];
      const { data } = await axios.get(`/course/module/class/${value.id}/details`);
      const { resources, video } = await data.data;

      resources.forEach(async (element) => {
        let obj = await {
          name: element.filename,
          url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${element.resource_file}`,
        };
        this.mutableResources.push(obj);
      });
      if (video && video.length > 0) {
        this.mutableVideo.push({
          name: video[0].filename,
          url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${video[0].path}`,
        });
      }
    },
  },

  methods: {
    closeModal() {
      // Obtener referencia al componente class-view
      const classViewComponent = this.$refs.classView;
      // Si existe el componente y tiene el método stopVideo, llamarlo
      if (classViewComponent && classViewComponent.stopVideo) {
        classViewComponent.stopVideo();
      }
      $('#class-modal').modal('hide');
    },
    isVideo(url) {
      // Verifica si la URL corresponde a un video
      if (!url) return false;
      const videoExtensions = ['.mp4', '.webm', '.ogg'];
      return videoExtensions.some((ext) => url.toLowerCase().endsWith(ext));
    },

    showDescriptionInput() {
      this.input_visible = true;
    },

    getNodeLabel(node, data) {
      // Devuelve el nombre del nodo (módulo o tema)
      if (node.level === 1) {
        return `Módulo ${data.moduleIndex}: ${data.name}`;
      } else if (node.level === 2) {
        return `Tema ${data.classIndex}: ${data.name}`;
      }
      return data.name;
    },

    loadNode(node, resolve) {
      // Carga los nodos según el nivel (módulos o temas)
      if (node.level === 0) {
        this.requestTree(resolve);
      } else if (node.level === 1) {
        this.getThemes(node, resolve);
      }
    },

    listObservations() {
      // Carga las observaciones del curso
      api
        .get(`/course/module/class/${this.course.id}/listObservations`)
        .then((response) => {
          this.observations = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    handleNodeClick(node) {
      // Muestra el modal de clase si se hace clic en un nodo de clase
      if ('id_modules' in node) {
        this.showClassModal(node);
      }
    },

    loadNode(node, resolve) {
      // Carga el árbol de módulos o temas según el nivel del nodo
      if (node.level === 0) {
        this.requestTree(resolve);
      }
      if (node.level >= 1) {
        this.getIndex(node, resolve);
      }
    },

    getIndex(node, resolve) {
      // Obtiene el índice de clases de un módulo
      axios.get(`/course/module/class/${node.data.id}/classList`).then((res) => {
        if (res.status == '200') {
          let data = res.data.data.map((item, index) => ({
            ...item,
            classIndex: index + 1,
            leaf: item.isIndex !== '1',
          }));
          resolve(data);
        }
      });
    },

    requestTree(resolve) {
      // Solicita la lista de módulos del curso
      axios
        .get(`/course/${this.course.id}/modulesList`)
        .then((response) => {
          if (response.status === 200) {
            let data = response.data.data.map((module, index) => ({
              ...module,
              moduleIndex: index + 1,
              leaf: false,
            }));
            resolve(data);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    getThemes(node, resolve) {
      // Solicita la lista de temas de un módulo
      axios.get(`/course/module/${node.data.id}/themeList`).then((res) => {
        if (res.status === 200) {
          let data = res.data.data.map((theme, index) => ({
            ...theme,
            themeIndex: index + 1,
            leaf: true,
          }));
          resolve(data);
        }
      });
    },

    castUrl(course_object) {
      // Asigna las URLs de las portadas del curso
      this.portada_url = `${course_object.url_portada}`;
      this.portada_name = `${course_object.portada}`;
      this.portada_venta = `${course_object.path_url}`;
    },

    showClassModal(class_object) {
      // Muestra el modal de clase
      this.mutableClass = class_object;
      $('#class-modal').modal('show');
    },

    // Enviar observaciones al curso
    async sendObservations(course_id) {
      try {
        const { data, status } = await axios.post(
          `/course/verification/${course_id}/sendObservations`,
          {
            id: course_id,
            observation: this.observation // Enviar el texto de observación
          }
        );
        if (data.status == 'ok') {
          this.$message.success('Revisiones enviadas correctamente');
          window.location.href = ' /course/verification';
        } else if (data.status == 'pending') {
          await this.$message.warning('Debe revisar todas las clases');
        } else {
          await this.$message.warning('Error al validar datos');
        }
      } catch (error) {
        console.log(error);
      }
    },

    // Cambiar el estado de la clase (aprobar o desaprobar)
    async changeStatus(id, status) {
      if (status === 1) {
        if (this.validateFields()) {
          await this.disapprovedClass(id);
        }
      } else {
        await this.approveClass(id);
      }
    },

    // Aprobar el curso
    async approveCourse(course_id) {
      try {
        this.isSubmitting = true;
        const { data, status } = await axios.post(`/course/verification/${course_id}/approved`, {
          id: course_id,
        });
        const response =
          (await status) === 200
            ? this.$message.success('Curso aprobado correctamente')
            : await this.$message.warning('Error al validar datos');

        this.isSubmitting = false;
        document.location.href = `/course/verification`;
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>

<style scoped>
/* Estilos generales */
.card-body {
  padding: 1.5rem;
  background-color: #fff;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

/* Estilos para el formulario */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #80bdff;
  outline: 0;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control:disabled,
.form-control[readonly] {
  background-color: #f8f9fa;
  opacity: 1;
}

/* Estilos para las tarjetas de portada */
.card {
  border: none;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.card-img-top {
  width: 100%;
  height: auto;
  object-fit: cover;
  max-height: 300px;
}

/* Estilos para el árbol de módulos */
.el-tree {
  margin: 1rem 0;
  background-color: transparent;
}

.el-tree-node__content {
  padding: 0.5rem;
  border-radius: 0.25rem;
  margin: 0.25rem 0;
  transition: background-color 0.2s ease;
}

.el-tree-node__content:hover {
  background-color: #f8f9fa;
}

.custom-tree-node {
  flex: 1;
  display: flex;
  align-items: center;
  font-size: 1rem;
  padding: 0.25rem 0;
}

/* Estados de los nodos */
.text-success {
  color: #28a745 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-danger {
  color: #dc3545 !important;
}

/* Badges */
.badge {
  padding: 0.5em 1em;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.375rem;
}

.bg-secondary {
  background-color: #6c757d !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-success {
  background-color: #28a745 !important;
}

/* Botones */
.btn {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 0.375rem;
  transition: all 0.2s ease-in-out;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
  color: #fff;
}

.btn-success:hover {
  background-color: #218838;
  border-color: #1e7e34;
}

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
  border-color: #d39e00;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Modal */
.modal-content {
  border-radius: 0.5rem;
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  padding: 1.5rem;
}

.modal-body {
  padding: 1.5rem;
}

.close {
  font-size: 1.5rem;
  font-weight: 700;
  color: #000;
  opacity: 0.5;
  transition: opacity 0.15s ease-in-out;
}

.close:hover {
  opacity: 1;
}

/* Responsividad */
@media (max-width: 992px) {
  .card {
    width: 100% !important;
    margin-bottom: 1.5rem;
  }

  .offset-md-6 {
    margin-left: 0;
  }

  .text-right {
    text-align: center !important;
  }

  .btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }
}

@media (max-width: 768px) {
  .card-body {
    padding: 1rem;
  }

  .form-group {
    margin-bottom: 1rem;
  }

  .el-tree-node__label {
    font-size: 1.1em;
  }

  .custom-tree-node {
    font-size: 0.9rem;
  }

  .badge {
    font-size: 0.75rem;
  }
}

@media (max-width: 576px) {

  h2,
  h3 {
    font-size: 1.5rem;
  }

  .card-body {
    padding: 0.75rem;
  }

  .form-control {
    padding: 0.5rem;
    font-size: 0.9rem;
  }

  .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
}

/* Animaciones */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-body {
  animation: fadeIn 0.3s ease-out;
}
</style>
