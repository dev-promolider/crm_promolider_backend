<template>
  <div class="content-wrapper">
    <!-- Observación previa -->
    <div v-if="hasObservation(clas.id)" class="alert-section">
      <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Observación Anterior</h4>
        <p>{{ observationData.description }}</p>
      </div>
    </div>

    <!-- Descripción -->
    <div class="form-group">
      <label for="description">Descripción</label>
      <el-input id="description" type="textarea" placeholder="Descripción de la clase" v-model="clas.description"
        resize="none" readonly rows="6"></el-input>
    </div>

    <!-- Video -->
    <div class="video-wrapper">
      <div class="video-container">
        <video-player class="video-player" ref="videoPlayer" :options="playerOptions" :playsinline="true"
          customEventName="customstatechangedeventname">
        </video-player>
        <div class="video-overlay" @click="togglePlay">
          <div v-if="!isPlaying" class="play-button">
            <i class="fas fa-play"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Archivos -->
    <div class="files-section">
      <label for="files">Archivos</label>
      <ul v-if="resources.length > 0" class="file-list">
        <li v-for="resource in resources" :key="resource.id">
          <a :href="resource.url" class="file-link">{{ resource.name }}</a>
        </li>
      </ul>
      <p v-else class="no-files">No hay archivos cargados</p>
    </div>

    <!-- Observación -->
    <div v-if="input_visible" class="form-group">
      <label for="observation">Observación</label>
      <el-input id="observation" type="textarea" resize="none" rows="6" v-model="observation"
        placeholder="Escriba el motivo de la desaprobación"></el-input>
    </div>

    <!-- Botones -->
    <div class="button-group">
      <button v-if="!input_visible" type="button" class="btn btn-warning" @click="showDescriptionInput">
        Escribir Observación
      </button>
      <button v-if="input_visible" type="button" class="btn btn-danger" @click="changeStatus(clas.id, 1)">
        Desaprobar
      </button>
      <button type="button" class="btn btn-success" @click="changeStatus(clas.id, 2)">
        Aprobar
      </button>
    </div>
  </div>
</template>

<script>
import 'video.js/dist/video-js.css';
import { videoPlayer } from 'vue-video-player';

export default {
  components: {
    'video-player': videoPlayer,
  },

  props: {
    course: {
      type: Object,
      required: true,
    },
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
      isPlaying: false,
      playerOptions: {
        language: 'es',
        muted: false,
        fluid: true,
        playbackRates: [0.7, 1.0, 1.5, 2.0],
        sources: [
          {
            type: 'video/mp4',
            src: '',
          },
        ],
        aspectRatio: '16:9',
        responsive: true,
        controlBar: {
          children: [
            'playToggle',
            'volumePanel',
            'currentTimeDisplay',
            'timeDivider',
            'durationDisplay',
            'progressControl',
            'playbackRateMenuButton',
            'fullscreenToggle',
          ],
        },
      },
      input_visible: false,
      observation: '',
      observationData: '',
      filevideo: '',
      fileResourcesList: '',
    };
  },

  mounted() {
    this.initializeVideoPlayer();
  },

  beforeDestroy() {
    this.stopVideo();
  },

  watch: {
    video: function (value) {
      this.playerOptions.sources[0].src = value[0]?.url || '';
      this.playerOptions.poster = this.course.url_portada || '';
    },
    resources: function (value) {
      this.fileResourcesList = value;
    },
  },

  methods: {
    stopVideo() {
      const player = this.$refs.videoPlayer?.player;
      if (player) {
        player.pause();
        this.isPlaying = false;
      }
    },
    initializeVideoPlayer() {
      const player = this.$refs.videoPlayer?.player;
      if (!player) {
        return;
      }

      const overlay = document.querySelector('.video-overlay');

      // Actualizar estado de reproducción
      player.on('play', () => {
        this.isPlaying = true;
        if (overlay) overlay.classList.add('hidden');
      });

      player.on('pause', () => {
        this.isPlaying = false;
        if (overlay) overlay.classList.remove('hidden');
      });

      player.on('ended', () => {
        this.isPlaying = false;
        if (overlay) overlay.classList.remove('hidden');
      });
    },

    togglePlay() {
      const player = this.$refs.videoPlayer?.player;
      if (!player || !this.playerOptions.sources[0].src) {
        return;
      }

      if (player.paused()) {
        player.play();
      } else {
        player.pause();
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

    showDescriptionInput() {
      this.input_visible = true;
    },

    async changeStatus(id, status) {
      if (status === 1) {
        if (this.validateFields()) {
          await this.disapprovedClass(id);
        }
      } else {
        await this.approveClass(id);
      }
    },

    async disapprovedClass(id) {
      try {
        const { data, status } = await axios.post('/course/verification/class/changeStatus', {
          id: id,
          status: 1,
          observation: this.observation,
        });
        const response =
          (await status) === 200
            ? this.$message.success('Observación enviada correctamente')
            : await this.$message.warning('Error al validar datos');

        this.input_visible = false;
        this.observation = '';
        $('#class-modal').modal('hide');
        window.location.reload();
      } catch (error) {
        console.log(error);
      }
    },

    async approveClass(id) {
      try {
        const { data, status } = await axios.post('/course/verification/class/changeStatus', {
          id: id,
          status: 2,
        });
        const response =
          (await status) === 200
            ? this.$message.success('Clase aprobada correctamente')
            : await this.$message.warning('Error al validar datos');
        $('#class-modal').modal('hide');
        window.location.reload();
      } catch (error) {
        console.log(error);
      }
    },

    validateFields() {
      if (!this.observation || this.observation.trim() === '' || this.observation.length === 0) {
        this.$message.warning('La observación esta vacio!');
        return false;
      }
      return true;
    },
  },
};
</script>

<style scoped>
/* Contenedor principal */
.content-wrapper {
  max-width: 800px;
  margin: 0 auto;
  padding: 1rem;
}

/* Video container con aspect ratio correcto */
.video-wrapper {
  margin-bottom: 1.5rem;
}

.video-container {
  position: relative;
  width: 100%;
  background: #000;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Mantener aspect ratio 16:9 */
.video-container::before {
  content: '';
  display: block;
  padding-top: 56.25%;
  /* 16:9 Aspect Ratio */
}

.video-player {
  position: absolute !important;
  top: 0;
  left: 0;
  width: 100% !important;
  height: 100% !important;
}

/* Estilos para el player de video */
:deep(.video-js) {
  width: 100% !important;
  height: 100% !important;
  position: absolute;
  top: 0;
  left: 0;
}

/* Overlay y botón de play */
.video-overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  z-index: 1;
  opacity: 1;
  /* Overlay visible por defecto */
  visibility: visible;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.video-overlay.hidden {
  opacity: 0;
  /* Desaparece suavemente */
  visibility: hidden;
  /* Oculta completamente después de la transición */
}

.play-button {
  width: 80px;
  height: 80px;
  background-color: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease;
}

.play-button i {
  color: white;
  font-size: 32px;
  margin-left: 5px;
}

.video-overlay:hover .play-button {
  background-color: rgba(0, 0, 0, 0.8);
  transform: scale(1.1);
}

/* Barra de controles del video */
:deep(.video-js .vjs-control-bar) {
  background-color: rgba(0, 0, 0, 0.7);
  height: 4em;
}

:deep(.video-js .vjs-big-play-button) {
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  border: none;
  background-color: rgba(0, 0, 0, 0.6);
  width: 80px;
  height: 80px;
  line-height: 80px;
}

:deep(.video-js:hover .vjs-big-play-button) {
  background-color: rgba(0, 0, 0, 0.8);
}

/* Cursor personalizado */
.video-overlay,
:deep(.video-js) {
  cursor: pointer;
}

/* Mejoras en controles de video */
:deep(.video-js .vjs-control) {
  width: 4em;
}

:deep(.video-js .vjs-time-control) {
  display: block;
  min-width: 2em;
}

:deep(.video-js .vjs-progress-control:hover) {
  height: 0.8em;
}

:deep(.video-js .vjs-progress-control .vjs-play-progress) {
  background-color: #20e404;
}

/* Estilos para las secciones */
.alert-section {
  margin-bottom: 1.5rem;
}

.alert-warning {
  background-color: #fff3cd;
  border: 1px solid #ffeeba;
  border-radius: 0.375rem;
  padding: 1.25rem;
  margin-bottom: 1rem;
}

.alert-heading {
  color: #856404;
  margin-bottom: 0.75rem;
  font-size: 1.25rem;
}

/* Formularios */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #212529;
}

/* Archivos */
.files-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
}

.file-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.file-list li {
  padding: 0.5rem 0;
  border-bottom: 1px solid #dee2e6;
}

.file-list li:last-child {
  border-bottom: none;
}

.file-link {
  text-decoration: none;
  color: #0d6efd;
  display: inline-block;
  padding: 0.25rem 0;
  transition: color 0.2s ease-in-out;
}

.file-link:hover {
  color: #0a58ca;
  text-decoration: underline;
}

.no-files {
  color: #6c757d;
  font-style: italic;
  margin: 0;
  padding: 0.5rem 0;
}

/* Botones */
.button-group {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn {
  flex: 1;
  padding: 0.75rem 1.25rem;
  font-size: 1rem;
  font-weight: 500;
  border: none;
  border-radius: 0.375rem;
  transition: all 0.2s ease-in-out;
  text-align: center;
  cursor: pointer;
}

.btn-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #ffca2c;
}

.btn-danger {
  background-color: #dc3545;
  color: #fff;
}

.btn-danger:hover {
  background-color: #bb2d3b;
}

.btn-success {
  background-color: #198754;
  color: #fff;
}

.btn-success:hover {
  background-color: #157347;
}

/* Input y Textarea */
.el-input__inner,
.el-textarea__inner {
  width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #212529;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out;
}

.el-textarea__inner:focus,
.el-input__inner:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
