<template>
  <div>
  <custom-modal id="level-modal-warning1" color="primary">
      <template #title>¡ADVERTENCIA!</template>
      <template #body>
        <div class="row">
          <h3>Faltan pocos dias para que termine su estado Activo</h3>
        </div>
      </template>
      <template #footer>
      </template>
  </custom-modal>
  </div>
</template>
<script>
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  name: 'aviso',
  components: {
    'custom-modal': ModalComponent,
  },
  mounted() {
    $('#level-modal-warning1').modal('show');
  },
};
</script>
<style lang="css" scoped></style>