<template>
  <div>
    <custom-spinner v-if="loading"></custom-spinner>
    <template v-else>
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header bg-light py-1">
              <h4 class="card-title mb-0 text-primary">Mis Directos</h4>
            </div>
            <div class="card-body">
              <p class="card-text text-muted">
                En la siguiente tabla encontrará los datos de sus directivos --
              </p>
            </div>
            <div class="table-responsive px-2">
              <table class="table table-hover-animation table-striped table-bordered" id="directosTable">
                <thead class="thead-light">
                  <tr>
                    <th class="align-middle">Nombre</th>
                    <th class="align-middle">Posición</th>
                    <th class="align-middle">Documento</th>
                    <th class="align-middle">Cuenta</th>
                    <th class="align-middle">Teléfono</th>
                    <th class="align-middle">Email</th>
                    <th class="align-middle">Inscripción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in usersMembreship" :key="user.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar mr-2">
                          <div class="avatar-initial rounded-circle bg-light-primary">
                            {{ getInitials(user.name) }}
                          </div>
                        </div>
                        <div class="user-info text-truncate">
                          <span class="d-block font-weight-bold text-truncate">
                            {{ user.name + ' ' + user.last_name }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span :class="getPositionBadgeClass(user.position)">
                        {{
                          user.position === '0'
                            ? 'Izquierda'
                            : user.position === '1'
                              ? 'Derecha'
                              : 'Desconocida'
                        }}
                      </span>
                    </td>
                    <td>
                      <span class="text-truncate">{{
                        user.document_type ? user.document_type.document : 'N/A'
                      }}</span>
                    </td>
                    <td>
                      <span class="text-truncate">{{
                        user.account_type ? user.account_type.account : 'N/A'
                      }}</span>
                    </td>
                    <td>
                      <a :href="'tel:' + user.phone" class="text-body">
                        {{ user.phone }}
                      </a>
                    </td>
                    <td>
                      <a :href="'mailto:' + user.email" class="text-body text-truncate d-block">
                        {{ user.email }}
                      </a>
                    </td>
                    <td>
                      <span class="text-nowrap">{{ formatDate(user.created_at) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import apiUsers from '../../api/api.users';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner';
import moment from 'moment';
import language from '../../api/traduction_es';

export default {
  components: { CustomSpinner },
  created() {
    this.getUsersMembreship();
  },
  data() {
    return {
      loading: true,
      usersMembreship: [],
    };
  },
  methods: {
    formatDate(date) {
      return moment(date).format('DD/MM/YYYY hh:mm A');
    },
    getInitials(name) {
      return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    getPositionBadgeClass(position) {
      return {
        'badge badge-pill px-2 py-1': true,
        'badge-light-success': position === '0',
        'badge-light-primary': position === '1',
        'badge-light-secondary': position !== '0' && position !== '1',
      };
    },
    getUsersMembreship() {
      this.loading = true;
      apiUsers
        .listByUser()
        .then((response) => {
          if (Array.isArray(response)) {
            this.usersMembreship = response;
          } else if (response.data && Array.isArray(response.data)) {
            this.usersMembreship = response.data;
          } else {
            console.error('Unexpected API response structure:', response);
            this.usersMembreship = [];
          }
          this.loading = false;
          this.$nextTick(() => {
            this.initDataTable();
          });
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
          this.loading = false;
        });
    },
    initDataTable() {
      if ($.fn.DataTable.isDataTable('#directosTable')) {
        $('#directosTable').DataTable().destroy();
      }
      $('#directosTable').DataTable({
        ...language,
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, 'Todos'],
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      });
    },
  },
  name: 'BinaryBranch',
};
</script>

<style scoped>
.card {
  padding-top: 6px !important;
  border-radius: 0.428rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
}

.avatar {
  width: 32px;
  height: 32px;
}

.avatar-initial {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.857rem;
  font-weight: 600;
}

.bg-light-primary {
  background-color: rgba(115, 103, 240, 0.12) !important;
  color: #7367f0 !important;
}

.user-info {
  max-width: 150px;
}

.badge-light-success {
  background-color: rgba(40, 199, 111, 0.12);
  color: #28c76f;
}

.badge-light-primary {
  background-color: rgba(115, 103, 240, 0.12);
  color: #7367f0;
}

.badge-light-secondary {
  background-color: rgba(182, 182, 182, 0.12);
  color: #b6b6b6;
}

@media (max-width: 768px) {
  .table-responsive {
    margin: 0;
    padding: 0;
  }

  .card-body {
    padding: 1rem;
  }

  .user-info {
    max-width: 120px;
  }

  td,
  th {
    white-space: nowrap;
    min-width: 120px;
  }

  .avatar {
    width: 28px;
    height: 28px;
  }

  .avatar-initial {
    width: 28px;
    height: 28px;
    font-size: 0.8rem;
  }
}

/* Estilos para dispositivos muy pequeños */
@media (max-width: 576px) {
  .card-header {
    padding: 0.8rem;
  }

  .card-title {
    font-size: 1.2rem;
  }

  .user-info {
    max-width: 100px;
  }

  td,
  th {
    padding: 0.5rem !important;
    font-size: 0.85rem;
  }
}
</style>
