<template>
  <div>
    <custom-modal v-bind:id="'pointsLog'" size="large">
      <template #title>{{ total_points }} Puntos</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
              <tr>
                <th>Puntos</th>
                <th>Motivo</th>
              </tr>
              <tr v-for="userPoint in userPoints" :key="userPoint.id">
                <td>{{ userPoint.points }}</td>
                <td>{{ userPoint.reason }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </custom-modal>
  </div>
</template>
<script>
import ModalComponent from '@/components/common/ModalComponent.vue';
export default {
  name: 'ModalPoints',
  components: {
    'custom-modal': ModalComponent,
  },
  props: {
    userPoints: {
      type: Array,
      required: true,
    },
    total_points: {},
  },
};
</script>
