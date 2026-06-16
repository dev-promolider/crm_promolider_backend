<template>
  <div class="card">
    <div class="card-body">
      <h5 class="mb-1">Ajuste de pierna</h5>

      <div class="row d-flex justify-content-center">
        <div class="col-6 col-md-12 col-lg-6">
          <button
            class="btn btn-sm mb-1"
            :class="selectedLeg === 'izquierda'
              ? 'btn-primary'
              : 'btn-light'"
            @click="changePosition('izquierda')"
          >
            Izquierda
          </button>
        </div>

        <div class="col-6 col-md-12 col-lg-6">
          <button
            class="btn btn-sm mb-1"
            :class="selectedLeg === 'derecha'
              ? 'btn-primary'
              : 'btn-light'"
            @click="changePosition('derecha')"
          >
            Derecha
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api';
export default {
  name: 'AdjustLeg',

  props: {
    selectedLeg: {
      type: String,
      default: 'izquierda',
    },
  },

  methods: {
    changePosition(position) {
      this.$emit('change-leg', position);
    },
  },
};
</script>