<template>
  <div class="row">
    <div class="col-12">
      <div class="table-responsive">
        <table id="data-table-list-payments" class="table table-hover-animation table-striped table-bordered">
          <thead>
            <tr class="text-center">
              <th class="th-sm">Id</th>
              <th>Rango</th>
              <th>Insignia</th>
              <th>Volumen de puntos</th>
              <th>Pack University</th>
              <th>Activos Directos</th>
              <th>Maximo pagos</th>
              <th>Bono Mensual</th>
              <th class="th-sm">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in rankBonus" :key="index" class="text-center">
              <td data-label="Id">{{ row.id }}</td>
              <td data-label="Rango">{{ row.name }}</td>
              <td data-label="Insignia">
                <img :src="`https://promolider-storage-user.s3-accelerate.amazonaws.com/${row.icon}`" alt="Insignia"
                  class="badge-image" />
              </td>
              <td data-label="Volumen de puntos">{{ row.vol_min }}</td>
              <td data-label="Pack University">{{ row.pack_max }}</td>
              <td data-label="Activos Directos">{{ row.active_direct }}</td>
              <td data-label="Maximo pagos">{{ row.max_pay }}</td>
              <td data-label="Bono Mensual">{{ row.monthly_bonus }}</td>
              <td data-label="Acción">
                <button class="btn btn-primary btn-sm" @click.prevent="openModal(row)">
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <RankModal v-if="showDialog" :show-dialog="showDialog" :form-data="formData" @closeModal="closeModal"
      @saveRankBonus="saveRankBonus" />
  </div>
</template>

<script>
import axios from 'axios';
import RankModal from './RankModal.vue';

export default {
  components: {
    RankModal,
  },
  data() {
    return {
      rankBonus: [],
      showDialog: false,
      formData: {},
    };
  },
  created() {
    this.getRankBonus();
  },
  methods: {
    async getRankBonus() {
      try {
        const response = await axios.get(`/rank_bonus/list`);
        this.rankBonus = response.data.data;
      } catch (error) {
        console.log('ocurrio un error: ' + error);
      }
    },
    openModal(row) {
      this.showDialog = true;
      this.formData = row;
    },
    closeModal() {
      this.showDialog = false;
    },
    async saveRankBonus(form) {
      try {
        if (!form.name.trim()) {
          this.$message.warning('Nombre obligatorio');
        } else if (form.vol_min === undefined || form.vol_min === null || form.vol_min === '') {
          this.$message.warning('Volumen de puntos obligatorio');
        } else if (form.pack_max === undefined || form.pack_max === null || form.pack_max === '') {
          this.$message.warning('Pack University obligatorio');
        } else if (
          form.active_direct === undefined ||
          form.active_direct === null ||
          form.active_direct === ''
        ) {
          this.$message.warning('Activos Directos obligatorio');
        } else if (form.max_pay === undefined || form.max_pay === null || form.max_pay === '') {
          this.$message.warning('Máximo pagos obligatorio');
        } else if (
          form.monthly_bonus === undefined ||
          form.monthly_bonus === null ||
          form.monthly_bonus === ''
        ) {
          this.$message.warning('Bono Mensual obligatorio');
        } else {
          const response = await axios.put(`/rank_bonus/update`, form);
          if (response.data.status == 'success') {
            this.$message.success(response.data.message);
            this.closeModal();
          } else {
            this.$message.warning('Ocurrio un error');
          }
        }
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>

<style scoped>
/* Estilos generales de la tabla */
.table-responsive {
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.table {
  margin-bottom: 0;
  background-color: white;
}

/* Estilos para el encabezado */
thead tr {
  background-color: #f8f9fa;
  font-weight: bold;
}

/* Estilos para la imagen de insignia */
.badge-image {
  width: 40px;
  height: 40px;
  object-fit: contain;
  transition: transform 0.2s;
}

.badge-image:hover {
  transform: scale(1.2);
}

/* Estilos para el botón */
.btn-primary {
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Estilos responsivos */
@media screen and (max-width: 768px) {
  table {
    border: 0;
  }

  table thead {
    display: none;
  }

  table tr {
    margin-bottom: 1rem;
    display: block;
    border: 1px solid #ddd;
    border-radius: 8px;
  }

  table td {
    display: block;
    text-align: right;
    padding: 0.75rem;
    border-bottom: 1px solid #ddd;
  }

  table td::before {
    content: attr(data-label);
    float: left;
    font-weight: bold;
  }

  table td:last-child {
    border-bottom: 0;
  }

  .badge-image {
    margin: 0 auto;
    display: block;
  }

  td[data-label='Acción'] {
    text-align: center;
  }

  .btn-primary {
    width: 100%;
    margin-top: 5px;
  }
}

/* Animación para hover en las filas */
.table-hover-animation tbody tr {
  transition: all 0.2s ease;
}

.table-hover-animation tbody tr:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  background-color: #f8f9fa;
}
</style>
