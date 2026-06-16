<template>
  <div>
    <table id="datatable" class="table-hover table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Fecha de solicitud</th>
          <th>Opción</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user, index) in dataUsers" :key="user.id">
          <td>{{ (index = index + 1) }}</td>
          <td>{{ user.name }}</td>
          <td>{{ moment(user.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          <td><button class="btn btn-info" @click="modalUser(user.id_user)">observar</button></td>
        </tr>
      </tbody>
    </table>

    <!-- Modal View Request -->
    <div class="modal fade" id="modalViewRequest" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Solicitud de cambio de rol</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            ¿Aprobar la solicitud?
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" @click="denyAfiliation(userSelected)">
                Denegar
              </button>
              <button type="button" class="btn btn-primary" @click="approveDeny(userSelected)">
                Aprobar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Reject Afiliation -->
    <custom-modal v-bind:id="'viewRejectAfiliation'">
      <template #title>¿Seguro que desea rechazar la afiliación?</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
              <p>Ingrese el motivo del rechazo de cambio de rol</p>
              <div class="form-group">
                <textarea name="mensaje" required="" class="form-control" placeholder="Introduzca el motivo del rechazo"
                  v-model="justification"></textarea>
              </div>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="rejectDeny(userSelected)">
          <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"
            v-if="loading"></span>
          <span class="ml-25 align-middle"> Desautorizar </span>
        </button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  components: {
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      initialLoading: true,
      dataUsers: [],
      justification: '',
      loading: false,
      userSelected: null,
      moment: moment,
    };
  },
  mounted() {
    this.listUsers();
  },
  methods: {
    modalUser(id) {
      $('#modalViewRequest').modal('show');
      this.userSelected = id;
    },
    denyAfiliation() {
      $('#modalViewRequest').modal('hide');
      $(`#viewRejectAfiliation`).modal('show');
    },
    approveDeny(id) {
      const form = {
        id: id,
      };
      axios.patch(`/config/role-request-tools/confirm-change`, form).then((response) => {
        $('#modalViewRequest').modal('hide');
        this.$message.success('Petición aceptada');
      });
      this.listUsers();
    },
    rejectDeny(id) {
      const form = {
        id: id,
        justification: this.justification,
      };
      axios.patch(`/config/role-request-tools/reject-change`, form).then((response) => {
        $('#viewRejectAfiliation').modal('hide');
        this.$message.success('Petición rechazada');
      });
      this.justification = null;
      this.listUsers();
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#datatable').DataTable(language);
      });
    },
    listUsers() {
      this.initialLoading = true;
      api.get(`/config/role-request-tools/list`).then((response) => {
        this.initialLoading = false;
        this.dataUsers = response.data;
        $('#datatable').DataTable().destroy();
        this.loadDataTable();
      });
    },
  },
};
</script>
