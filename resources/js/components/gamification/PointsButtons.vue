<template>
  <div>
    <div class="d-flex justify-content-around my-5">
      <button class="btn btn-primary" @click="getPoints(0)">Volumen Izquierdo</button>
      <button class="btn btn-primary" @click="getPoints(1)">Volumen Derecho</button>
    </div>
    <modal-points :userPoints="userPoints" :total_points="total_points"></modal-points>
  </div>
</template>

<script>
import api from '@/api/api';
import ModalPoints from '@/components/gamification/ModalPoints.vue';
export default {
  name: 'PointsButtons',
  components: {
    'modal-points': ModalPoints,
  },
  data() {
    return {
      userPoints: [],
      total_points: 0
    };
  },
  methods: {
    sum_points(arrayPoints){
      this.total_points = 0
      arrayPoints.forEach(element => {
        this.total_points += element.points 
      });
    },
    getPoints(side) {
      api
        .get(`/mypointslog?side=${side}`)
        .then((response) => {
          this.userPoints = response.data;
          this.sum_points(response.data);
          $(`#pointsLog`).modal('show');
        })
        .catch((error) => {
          // console.log(error);
        });
    },
  },
};
</script>

<style>
</style>