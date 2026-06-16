<template>
  <div>
    <div class="alert alert-success p-1" style="display: none" role="alert" id="alertRemove">
      <strong>Role removed</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="card">
      <div class="card-body">
        <table
          id="data-table-list-payments"
          class="table table-hover-animation table-striped table-bordered"
        >
          <thead>
            <tr class="text-center">
              <th>Id</th>
              <th>Roles</th>
              <th colspan="1">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(role, index) in roles" :key="index" class="text-center">
              <td width="10px">{{ role.id }}</td>
              <td>{{ role.name }}</td>
              <td width="10px">
                <button
                  class="btn btn-primary"
                  @click="openDialogRoles(role)"
                  v-if="can('action-edit-role')"
                >
                  Editar
                </button>
              </td>
              <!-- <td width="10px">
                <button
                  class="btn btn-danger"
                  v-on:click="removeRole(role.id)"
                  v-if="can('action-remove-role')"
                >
                  Remove
                </button>
              </td> -->
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <dialog-roles :show.sync="showDialogRoles" :rol="rol"> </dialog-roles>
  </div>
</template>
<script>
import axios from 'axios';
import dialogRoles from './dialogRoles';
import language from '@/api/traduction_es';
export default {
  components: {
    dialogRoles,
  },
  data() {
    return {
      roles: [],
      rol: {},
      showDialogRoles: false,
    };
  },
  created() {
    this.listRoles();
  },
  methods: {
    loadDataTable() {
      $('#data-table-list-payments').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-payments').DataTable(language);
      });
    },

    listRoles() {
      axios.get('/admin/role/show').then((response) => {
        this.roles = response.data;
      });
    },
    listAction: function (submodul) {
      this.actions = [];
      axios
        .post('/admin/role/actions', { submodulo: submodul, role: this.idRole })
        .then((response) => {
          this.actions = response.data;
          this.loadDataTable();
        });
      this.showActions = true;
    },
    AddorRemove: function (event) {
      this.selectAllModules = false;
      if (event.target.checked) {
        axios
          .post('/admin/role/add/permission', { role: this.idRole, permission: event.target.id })
          .then((response) => {
            //console.log(response);
          });
      } else {
        axios
          .post('/admin/role/remove/permission', { role: this.idRole, permission: event.target.id })
          .then((response) => {
            //console.log(response);
          });
      }
    },
    removeRole: function (role) {
      axios.get('/admin/role/remove/' + role).then((response) => {
        this.roles = [];
        this.roles = response.data;
        $('#alertRemove').show();
      });
    },
    onSelectAllModules(permissions) {
      // console.log(permissions);
      (this.selectAllModules = true),
        permissions.forEach((p) => {
          axios
            .post('/admin/role/add/permission', { role: this.idRole, permission: p.id })
            .then((response) => {
              //console.log(response);
            });
        });
    },
    onSelectAllSubmodules(submodule) {
      submodule.forEach((p) => {
        axios
          .post('/admin/role/add/permission', { role: this.idRole, permission: p.id })
          .then((response) => {
            //console.log(response);
          });
      });
    },
    onSelectAllActions(actions) {
      actions.forEach((p) => {
        axios
          .post('/admin/role/add/permission', { role: this.idRole, permission: p.id })
          .then((response) => {
            //console.log(response);
          });
      });
    },
    closeModal() {
      this.showSubmodules = false;
      this.showActions = false;
    },
    openDialogRoles(rol) {
      this.rol = rol;
      this.showDialogRoles = true;
    },
  },
};
</script>
