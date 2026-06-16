<template>
  <div>
    <div class="mb-1">
      <el-button type="primary" class="btn btn-primary" native-type="submit" disabled>Crear curso</el-button>
    </div>
    <template v-if="chargeData">
      <template v-if="data.status == 1">
        <div class="text-center my-5">
          <p>Su solicitud de permisos está en evaluación</p>
        </div>
      </template>
      <template v-else-if="!data.status">
        <div class="text-center my-5">
          <p>Se debe solicitar permisos para crear masterclass</p>
          <button type="button" @click="showModal()" class="btn btn-primary" native-type="submit">
            Solicitar permisos
          </button>
        </div>
      </template>
      <template v-if="data.status == 3">
        <div class="text-center my-5">
          <p>Su solicitud fue rechazada</p>
          <div v-if="data.reason != null">
            <p>Motivo: {{ data.reason }}</p>
          </div>
        </div>
      </template>
      <div class="modal fade" id="modalViewRequest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Solicitud de permisos</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">¿Desea solicitar los permisos para crear masterclass?</div>
            <div class="modal-footer">
              <div>
                <button data-dismiss="modal" class="btn btn-light">Cancelar</button>
                <button data-dismiss="modal" type="button" class="btn btn-primary"
                        @click="changeRole()">
                  Aceptar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  data() {
    return {
      chargeData: false,
      data: [],
    };
  },
  methods: {
    showModal() {
      $('#modalViewRequest').modal('show');
    },
    async getRequest() {
      await axios.get('/config/role-request/get').then((response) => {
        this.data = response.data;
      });
      this.chargeData = true;
    },
    async changeRole() {
      await axios.post('/users/change-role').then((response) => {
        this.status = response;
        this.getRequest();
        this.$message.success('La solicitud ha sido enviada');
      });
    },
  },
  created() {
    this.getRequest();
  },
};
</script>
