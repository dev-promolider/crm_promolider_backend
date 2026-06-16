<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-payments"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Posición</th>
                  <th>Patrocinador</th>
                  <th>País</th>
                  <th>Membresía</th>
                  <th>Email</th>
                  <th>Fecha de inscripción</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tempUsers in this.usersMembreship" v-bind:key="tempUsers.id">
                  <td class="td-ellipsis">
                    <p>
                      {{ tempUsers.name + ' ' + tempUsers.last_name }}
                    </p>
                  </td>
                  <td>
                    {{ tempUsers.position === '0' ? 'Izquierda' : 'Derecha' }}
                  </td>
                  <td>{{ tempUsers.binary_sponsor }}</td>
                  <td>{{ tempUsers.country.name }}</td>
                  <td>{{ tempUsers.account_type.account }}</td>
                  <td>{{ tempUsers.email }}</td>
                  <td translate="no">{{ moment(tempUsers.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                  <td>
                      <div v-if="tempUsers.active">
                          <el-tag size="mini" type="success">Activo</el-tag>
                      </div>
                      <div v-else>
                          <el-tag size="mini" type="danger">No activo</el-tag>
                      </div>
                      <div v-if="tempUsers.membershipActive">
                          <el-tag size="mini" type="success">Activo</el-tag>
                      </div>
                      <div v-else>
                          <el-tag size="mini" type="danger">No activo</el-tag>
                      </div>
                      <div v-if="tempUsers.qualified">
                          <el-tag size="mini" type="success">Calificado</el-tag>
                      </div>
                      <div v-else>
                          <el-tag size="mini" type="danger">No calificado</el-tag>
                      </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import apiUsers from '../../api/api.users';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  components: { CustomSpinner },
  data() {
    return {
      initialLoading: false,
      moment: moment,
      usersMembreship: [],
    };
  },
  mounted() {
    this.getUsersMembreship();
  },
  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $('#data-table-list-payments').DataTable(language);
      });
    },
    getUsersMembreship: function () {
      this.initialLoading = true;
      apiUsers.listUsers().then((response) => {
        this.usersMembreship = response.data;
        this.initialLoading = false;
        this.loadDataTable();
        console.log(this.usersMembreship);
      });
    },
  },
  name: 'List',
};
</script>

<style scoped>
.td-ellipsis {
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
