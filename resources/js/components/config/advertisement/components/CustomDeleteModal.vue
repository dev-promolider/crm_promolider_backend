<template>
  <div class="d-inline-block">
    <div
      class="modal fade text-left modal-danger"
      id="delete-modal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="myModalLabel140"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5
              class="modal-title"
              :class="advertisement.status === '0' ? 'text-danger' : 'text-success'"
              id="myModalLabel140"
            >
              {{ advertisement.status === '1' ? 'Activar' : 'Deshabilitar' }} Anuncio
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Usted quiere {{ advertisement.status === '1' ? 'Activar' : 'Deshabilitar' }} el anuncio
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
            <button
              type="button"
              class="btn"
              :class="advertisement.status === '0' ? 'btn-danger' : 'btn-success'"
              @click="onDelete"
            >
              <span
                class="spinner-border spinner-border-sm text-danger"
                role="status"
                aria-hidden="true"
                v-if="loading"
              ></span>
              <span class="ml-25 align-middle"> Aceptar </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiAdvertisement from '../../../../api/api.advertisement';

export default {
  props: {
    advertisement: {
      type: Object,
      require: true,
    },
  },
  emits: {
    'confirm-delete': function (bool, status) {
      /*Recibe un campo boolean y un campo status '1' o '0'*/
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    onDelete() {
      if (this.advertisement) {
        this.loading = true;
        const params = {
          status: this.advertisement.status === '1' ? '0' : '1',
        };
        apiAdvertisement.delete(this.advertisement.id, params).then((response) => {
          this.loading = false;
          $('#delete-modal').modal('hide');
          this.$emit('confirm-delete', true, response.data.status);
        });
      }
    },
  },
  name: 'CustomDeleteModal',
};
</script>

<style scoped></style>
