<template>
  <div>
    <div class="card text-white bg-white border border-white rounded-sm shadow">
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-10 mb-2">
            <div class="media">
              <img :src="user.photo" alt="John Doe" class="rounded mr-1" style="width: 100px" />
              <div class="media-body">
                <h3>
                  {{ user.name + ' ' + user.last_name }}
                  <!-- <small><i>Posted on February 19, 2016 dad asd a das asda asda sda</i></small> -->
                </h3>
                <p class="text-primary font-weight-bolder">Nivel: {{ level.description }}</p>

                <h3 class="points text-warning font-weight-bolder">
                  <svg style="width: 25px; height: 25px; margin-bottom: 0.5rem" viewBox="0 0 24 24">
                    <path fill="currentColor"
                      d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" />
                  </svg>
                  <span>{{ points }} Puntos Totales</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-2 mb-2">
            <div class="text-primary row text-center">
              <div class="col-3 col-md-12">
                <h2 class="text-success font-weight-bold">{{ n_ranking }}</h2>
              </div>
              <div class="col-3 col-md-12">
                <p>#RANK</p>
              </div>
              <div class="col-6 col-md-12">
                <button class="btn btn-primary">Ver top</button>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex justify-content-md-end justify-content-center">
              <h3 class="">Prox. nivel {{ nextLevel.description }}</h3>
            </div>
            <div class="progress">
              <div class="progress-bar progress-bar-striped progress-bar-animated" :style="{ width: porcentaje + '%' }">
              </div>
            </div>
            <div class="d-flex justify-content-center mt-1">
              <h3 class="d-flex justify-items-center">{{ porcentaje + '%' }}</h3>
            </div>
          </div>

          <div class="col-12">
            <hr style="border-color: #f8f8f8" />
            <h3>Logros</h3>
            <div class="row mt-2" v-if="size_badge > 0">
              <div class="col-4 col-md-2" v-for="(badge, index) in this.listBadges" v-bind:key="badge.id"
                v-bind:index="index">
                <img :src="imagePathTable(badge.icon)" style="width: 100px; margin: auto; display: block"
                  alt="Cinque Terre" data-toggle="tooltip" data-placement="bottom"
                  v-bind:title="badge.name + ' nivel ' + badge.level" />
                <p class="text-primary text-center font-weight-bold mt-1">
                  {{ badge.name }} nivel {{ badge.level }}
                </p>
              </div>
            </div>
            <div class="row mt-2" v-else>
              <div class="col-12">
                <p class="text-center text-primary">No tiene logros</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  name: 'user-view-badges',
  components: {
    'custom-modal': ModalComponent,
  },

  data() {
    return {
      points: 0,
      level: {},
      nextLevel: {},
      porcentaje: 0,
      user: {},
      listBadges: [],
      n_ranking: 0,
      size_badge: 0,
    };
  },

  mounted() {
    this.getInfoUser();
    this.getBadges();
    this.getPositionRanking();
  },

  methods: {
    imagePathTable(link) {
      return `https://promolider-storage-user.s3-accelerate.amazonaws.com/images/badges/${link}`;
    },
    getInfoUser() {
      api.get('/user-levels/list-my-info').then((response) => {
        this.points = response.points;
        this.level = response.level;
        this.nextLevel = response.nextLevel;
        this.porcentaje = response.porcentaje;
        this.user = response.user;
      });
    },
    getBadges() {
      api.get('/badges/get-my-badges').then((response) => {
        this.listBadges = response;
        this.size_badge = response.length;
      });
    },

    getPositionRanking() {
      api.get('/user-levels/num-ranking').then((response) => {
        this.n_ranking = response;
      });
    },
  },
};
</script>

<style>
@media (max-width: 576px) {
  .media {
    text-align: center;
  }

  .progress {
    height: 20px;
  }

  .icon-svg {
    width: 20px;
    height: 20px;
  }

  .points {
    font-size: 1rem;
  }

  .d-flex.justify-content-md-end {
    justify-content: center !important;
  }
}
</style>
