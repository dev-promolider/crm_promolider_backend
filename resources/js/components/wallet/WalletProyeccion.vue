<!-- <template>
  <div class="card">
    <div class="card-body p-6">
      <div class="user-avatar-section">
        <div class="d-flex align-items-center mb-4">
          <div class="avatar-wrapper me-3">
            <img class="img-fluid rounded" :src="userCurrent.photo" height="110" width="110" alt="User avatar" />
          </div>
          <div class="user-info">
            <h4 class="mb-1">{{ userCurrent.name + ' ' + userCurrent.last_name }}</h4>
            <span class="badge bg-light-secondary">{{ membreshipCurrent.account }}</span>
          </div>
        </div>
      </div>

      <div class="ranking-info text-end mb-4">
        <h3 class="mb-0">#{{ rankingData.rank || '-' }}</h3>
        <small>Prox. Rank: {{ rankingData.proxRank || '-' }}</small>
      </div>

      <div class="progress mb-4">
        <div class="progress-bar" role="progressbar" :style="{ width: progressPercentage + '%' }"
          :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100">
          {{ progressPercentage }}%
        </div>
      </div>

      <h5 class="mb-3">Indicadores</h5>
      <div class="table-responsive mb-4">
        <table class="table">
          <thead>
            <tr>
              <th>Volumen</th>
              <th>Pierna</th>
              <th>University</th>
              <th>Directos Activos</th>
              <th>Frecuencia</th>
              <th>Rango Actual</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ rankingData.volumen || 0 }}</td>
              <td>{{ rankingData.pierna || '-' }}</td>
              <td>{{ universityMembersCount }}</td>
              <td>{{ rankingData.directosActivos || 0 }}</td>
              <td>{{ rankingData.frecuenciaRango || 0 }}</td>
              <td>{{ rankingData.rangoActual || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h5 class="mb-3">Por Obtener:</h5>
      <div class="row g-3">
        <div class="col-md-4" v-for="(bono, index) in bonosConEstado" :key="index">
          <div class="bonus-card">
            <div class="status-icon" :class="{ completed: bono.completed }">
              <i class="fas" :class="bono.completed ? 'fa-check' : 'fa-times'"></i>
            </div>
            <div class="bonus-title">{{ bono.nombre }}</div>
            <img :src="bono.imagen" :alt="bono.nombre" class="bonus-image" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template> -->

<!-- <script>
// import api from '../../api/api';

// export default {
//   name: 'WalletProjeccion',
//   data() {
//     return {
//       universityMembersCount: 0,
//       userCurrent: {},
//       membreshipCurrent: {},
//       rankingData: {
//         rank: 0,
//         proxRank: '',
//         volumen: 0,
//         pierna: '',
//         cantUniversity: 0,
//         directosActivos: 0,
//         frecuenciaRango: 0,
//         rangoActual: '',
//       },
//       progressPercentage: 0,
//       bonos: [
//         { nombre: 'Bono Vehículo', imagen: '../../../images/proyeccion/vehiculo.jpg' },
//         { nombre: 'Bono Vehículo Lujo', imagen: '../../../images/proyeccion/lujo.jpg' },
//         { nombre: 'Bono Viajero', imagen: '../../../images/proyeccion/viaje.jpg' },
//       ],
//     };
//   },
//   computed: {
//     bonosConEstado() {
//       return this.bonos.map((bono) => ({
//         ...bono,
//         completed: this.checkBonusCondition(bono.nombre),
//       }));
//     },
//   },
//   mounted() {
//     this.getDataUserCurrent();
//     this.getRankingData();
//   },
//   methods: {
//     updateUniversityCount(count) {
//       this.universityMembersCount = count;
//     },
//     checkBonusCondition(bonoNombre) {
//       switch (bonoNombre) {
//         case 'Bono Vehículo':
//           return this.rankingData.volumen >= 1000;
//         case 'Bono Vehículo Lujo':
//           return this.rankingData.directosActivos >= 5;
//         case 'Bono Viajero':
//           return this.rankingData.cantUniversity >= 3;
//         default:
//           return false;
//       }
//     },
//     getDataUserCurrent() {
//       api
//         .get(`/users/get-data-currentuser`)
//         .then((response) => {
//           this.userCurrent = response;
//           this.getDataMembershipCurrent();
//         })
//         .catch((error) => {
//           console.error('Error fetching user data:', error);
//         });
//     },
//     getDataMembershipCurrent() {
//       api
//         .get(`/account-type/get-data-id/${this.userCurrent.id_account_type}`)
//         .then((response) => {
//           this.membreshipCurrent = response;
//         })
//         .catch((error) => {
//           console.error('Error fetching membership data:', error);
//         });
//     },
//     getRankingData() {
//       api
//         .get(`/reports/proyeccion/${this.userCurrent.id}`)
//         .then((response) => {
//           this.rankingData = response;
//           this.calculateProgress();
//         })
//         .catch((error) => {
//           console.error('Error fetching ranking data:', error);
//         });
//     },
//     calculateProgress() {
//       // Implementar lógica para calcular el porcentaje de progreso
//       this.progressPercentage = 10;
//     },
//   },
// };
</script> -->

<!-- <style scoped>
/* .card {
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.avatar-wrapper {
  min-width: 110px;
}

.ranking-info {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
}

.table {
  border-collapse: separate;
  border-spacing: 0;
}

.table th {
  font-weight: 600;
  color: #5e5873;
  padding: 1rem;
  text-align: center;
  background-color: #f8f8f8;
}

.table td {
  padding: 1rem;
  text-align: center;
  background-color: #ffffff;
  border-bottom: 1px solid #f0f0f0;
}

.indicator-cell {
  background-color: #f8f8f8;
  border-radius: 0.5rem;
  padding: 1rem !important;
  border: none !important;
}

.indicator-label {
  font-weight: 600;
  color: #5e5873;
  margin-bottom: 0.5rem;
}

.indicator-value {
  font-size: 1.25rem;
  font-weight: 500;
}

.bonus-card {
  position: relative;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.bonus-title {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  padding: 0.5rem;
  background-color: rgba(0, 0, 0, 0.6);
  color: white;
  text-align: center;
  z-index: 1;
}

.bonus-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.status-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #dc3545;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}

.status-icon.completed {
  background-color: #28a745;
} */
</style> -->
