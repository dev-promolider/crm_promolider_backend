<template>
  <el-dialog width="80%" top="1vh" title="Previsualización Curso" :visible="outerVisible" @close="close()"
    class="full-screen-dialog">
    <div v-if="items.length > 0" class="row course-container">
      <div class="col-md-7 col-lg-8 video-section" v-if="!!urlvideo">
        <h4 class="course-title">
          <strong>{{ nameCourse }}</strong>
        </h4>

        <div class="video-container">
          <video-player class="video-player-box" ref="videoPlayer" :options="playerOptions" :playsinline="true"
            customEventName="customstatechangedeventname">
          </video-player>
        </div>

        <ul class="nav nav-pills mt-3" id="pills-tab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-toggle="pill" data-target="#pills-home"
              type="button" role="tab" aria-controls="pills-home" aria-selected="true">
              <strong class="bt-title">RESUMEN</strong>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-toggle="pill" data-target="#pills-profile"
              type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
              <strong class="bt-title">RECURSOS</strong>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-toggle="pill" data-target="#pills-contact"
              type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
              <strong class="bt-title">EXAMEN</strong>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-dynamics-tab" data-toggle="pill" data-target="#pills-dynamics"
              type="button" role="tab" aria-controls="pills-dynamics" aria-selected="false">
              <strong class="bt-title">DINÁMICAS</strong>
            </button>
          </li>
          <el-tooltip placement="right-end">
            <div slot="content">
              Fecha de inicio: 2023-11-27
              <br />
              Fecha de finalización: 2024-11-27
            </div>
            <el-button class="nav-link active">
              <strong class="bt-title"> DÍAS RESTANTES DEL CURSO: 30</strong>
            </el-button>
          </el-tooltip>
        </ul>

        <div class="tab-content" id="pills-tabContent">
          <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="content-box">
              <h4 class="content-title">{{ nameCourse }}</h4>
              <div class="content-body"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-5 col-lg-4">
        <div class="temary-container">
          <div class="card card-temary">
            <h2 class="title-temary">Temario</h2>
            <div class="modules-list">
              <div v-for="(element, index) in items" :key="index" class="module-class">
                <div v-if="element.type == 'module'" class="module-header">
                  <strong class="title-module">
                    {{ getModuleNumber(index) }}. {{ element.name }}
                  </strong>
                </div>
                <div v-else class="class-item">
                  <input type="checkbox" :checked="element.completed" />
                  <a href="javascript:void(0)" class="class-link" @click="previewVideo(element)">
                    {{ element.name }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">
      <strong>No hay módulos disponibles</strong>
    </div>
  </el-dialog>
</template>

<script>
import 'video.js/dist/video-js.css';
import { videoPlayer } from 'vue-video-player';

export default {
  props: {
    courses: {
      type: Object,
      required: true,
    },
    outerVisible: {
      type: Boolean,
      required: true,
    },
  },
  components: {
    videoPlayer,
  },
  data() {
    return {
      nameCourse: null,
      urlvideo: null,
      items: [],
      playerInitialized: false,
      playerOptions: {
        responsive: true,
        fluid: true,
        aspectRatio: '16:9',
        preload: 'auto',
        autoplay: false,
        muted: false,
        language: 'es',
        playbackRates: [0.7, 1.0, 1.5, 2.0],
        sources: [
          {
            type: 'video/mp4',
            src: '',
          },
        ],
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
    };
  },
  mounted() {
    this.getModulesData();
  },
  methods: {
    initializeVideoPlayer() {
      const player = this.$refs.videoPlayer?.player;
      if (!player || this.playerInitialized) {
        return;
      }

      const videoContainer = player.el_;
      if (!videoContainer) {
        return;
      }

      this.playerInitialized = true;
      videoContainer.style.cursor = 'pointer';

      videoContainer.addEventListener('click', (event) => {
        const techElement = player.tech_?.el_;

        if (event.target === videoContainer || event.target === techElement) {
          if (player.paused()) {
            player.play();
          } else {
            player.pause();
          }
        }
      });
    },
    getModuleNumber(index) {
      return this.items.slice(0, index + 1).filter((item) => item.type === 'module').length;
    },
    close() {
      this.$emit('closeModalPreview');
    },
    async getModulesData() {
      try {
        const response = await axios.get(`/course/${this.courses.id}/orders`);
        this.items = response.data;

        const firstClass = this.items.find((item) => item.type === 'class' && item.videos?.[0]?.path)
          || this.items.find((item) => item.type === 'class');
        if (firstClass) {
          this.previewVideo(firstClass);
        }
      } catch (error) {
        console.error('Error loading modules:', error);
      }
    },
    async previewVideo(video) {
      if (!video || video.type !== 'class') {
        return;
      }

      this.nameCourse = video.name;
      const videoPath = video.videos?.[0]?.path;

      if (!videoPath) {
        this.urlvideo = null;
        this.playerInitialized = false;
        this.$set(this.playerOptions.sources, 0, {
          type: 'video/mp4',
          src: '',
        });
        return;
      }

      this.urlvideo = videoPath.startsWith('http')
        ? videoPath
        : `https://promolider-storage-user.s3-accelerate.amazonaws.com/${videoPath}`;

      this.$set(this.playerOptions.sources, 0, {
        type: 'video/mp4',
        src: this.urlvideo,
      });

      this.$nextTick(() => {
        this.initializeVideoPlayer();
      });
    },
  },
};
</script>

<style scoped>
/* Contenedor principal */
.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
  background-color: #f8f9fa;
}

/* Video */
.video-wrapper {
  position: relative;
  width: 100%;
  background: #000;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.video-player {
  position: relative;
  width: 100%;
}

.video-player .video-js {
  width: 100% !important;
  min-height: 400px !important;
  border-radius: 10px;
}

/* Controles del video */
.video-js .vjs-control-bar {
  background-color: rgba(0, 0, 0, 0.7);
  height: 4em;
  border-radius: 0 0 10px 10px;
}

.video-js .vjs-button {
  height: 3em;
  width: 3em;
}

.video-js .vjs-time-control {
  font-size: 1.2em;
  padding: 0 0.5em;
}

.video-js .vjs-progress-control {
  height: 0.6em;
}

.video-js .vjs-progress-control:hover {
  height: 1em;
}

/* Descripción y campos de texto */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.75rem;
  font-weight: 500;
  color: #2c3e50;
  font-size: 1.1rem;
}

.el-input__inner,
.el-textarea__inner {
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.el-textarea__inner:focus,
.el-input__inner:focus {
  border-color: #20e404;
  box-shadow: 0 0 0 0.2rem rgba(32, 228, 4, 0.25);
}

/* Archivos */
.files-section {
  background-color: #fff;
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.file-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.file-list li {
  padding: 0.75rem 0;
  border-bottom: 1px solid #e9ecef;
}

.file-list li:last-child {
  border-bottom: none;
}

.file-link {
  display: flex;
  align-items: center;
  color: #2c3e50;
  text-decoration: none;
  transition: color 0.3s ease;
}

.file-link:hover {
  color: #20e404;
}

.file-link:before {
  content: '📎';
  margin-right: 0.5rem;
  font-size: 1.2rem;
}

/* Observaciones */
.alert-section {
  background-color: #fff3cd;
  border: 1px solid #ffeeba;
  border-radius: 10px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.alert-heading {
  color: #856404;
  margin-bottom: 0.75rem;
  font-size: 1.25rem;
}

/* Botones */
.button-group {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  border: none;
  border-radius: 50px;
  transition: all 0.3s ease;
  cursor: pointer;
  text-align: center;
}

.btn-warning {
  background-color: #ffc107;
  color: #000;
}

.btn-warning:hover {
  background-color: #ffca2c;
  transform: translateY(-1px);
}

.btn-danger {
  background-color: #dc3545;
  color: #fff;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  transform: translateY(-1px);
}

.btn-success {
  background-color: #20e404;
  color: #fff;
}

.btn-success:hover {
  background-color: #1cc204;
  transform: translateY(-1px);
}

/* Scroll personalizado */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #60d950;
  border-radius: 10px;
}

/* Responsividad */
@media (max-width: 992px) {
  .content-wrapper {
    padding: 1rem;
  }

  .video-js {
    min-height: 300px !important;
  }

  .button-group {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .video-js {
    min-height: 250px !important;
  }

  .video-js .vjs-control-bar {
    height: 3em;
  }

  .video-js .vjs-button {
    height: 2.5em;
    width: 2.5em;
  }

  .form-group label {
    font-size: 1rem;
  }

  .files-section {
    padding: 1rem;
  }
}

@media (max-width: 576px) {
  .content-wrapper {
    padding: 0.75rem;
  }

  .video-js {
    min-height: 200px !important;
  }

  .btn {
    padding: 0.625rem 1.25rem;
    font-size: 0.95rem;
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

.content-wrapper {
  animation: fadeIn 0.3s ease-out;
}
</style>
