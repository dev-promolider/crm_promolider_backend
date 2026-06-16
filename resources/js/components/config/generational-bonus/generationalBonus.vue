<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table id="data-table-list-generational-bonus"
            class="table table-hover table-striped table-bordered custom-table">
            <thead>
              <tr class="text-center bg-primary">
                <th class="align-middle">Rango</th>
                <th class="align-middle">G1%</th>
                <th class="align-middle">G2%</th>
                <th class="align-middle">G3%</th>
                <th class="align-middle">G4%</th>
                <th class="align-middle">G5%</th>
                <th class="align-middle">G6%</th>
                <th class="align-middle">G7%</th>
                <th class="align-middle">G8%</th>
                <th class="align-middle">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in generationalBonus" :key="index" class="text-center">
                <td class="align-middle">{{ row.range_name }}</td>
                <td class="align-middle">{{ row.g_1 }}</td>
                <td class="align-middle">{{ row.g_2 }}</td>
                <td class="align-middle">{{ row.g_3 }}</td>
                <td class="align-middle">{{ row.g_4 }}</td>
                <td class="align-middle">{{ row.g_5 }}</td>
                <td class="align-middle">{{ row.g_6 }}</td>
                <td class="align-middle">{{ row.g_7 }}</td>
                <td class="align-middle">{{ row.g_8 }}</td>
                <td class="align-middle">
                  <button class="btn btn-primary btn-sm" @click.prevent="openModal(row)">
                    <i class="fas fa-edit me-1"></i>
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Indicador de carga -->
        <div v-if="loading" class="text-center my-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <!-- Mensaje cuando no hay datos -->
        <div v-if="!loading && generationalBonus.length === 0" class="alert alert-info text-center">
          No hay datos disponibles
        </div>
      </div>
    </div>

    <GenerationalBonusModal v-if="showDialog" :show-dialog="showDialog" :form-data="formData" @closeModal="closeModal"
      @saveGenerationalBonus="saveGenerationalBonus" />
  </div>
</template>

<script>
import axios from 'axios';
import GenerationalBonusModal from './GenerationalBonusModal.vue';

export default {
  components: {
    GenerationalBonusModal,
  },
  data() {
    return {
      generationalBonus: [],
      showDialog: false,
      formData: {},
      loading: false,
    };
  },
  created() {
    this.getGenerationalBonus();
  },
  methods: {
    async getGenerationalBonus() {
      this.loading = true;
      try {
        const response = await axios.get(`/generational_bonus/list`);
        this.generationalBonus = response.data.data;
      } catch (error) {
        console.error('Error al cargar los datos:', error);
        this.$message.error('Error al cargar los datos');
      } finally {
        this.loading = false;
      }
    },
    openModal(row) {
      this.showDialog = true;
      this.formData = { ...row };
    },
    closeModal() {
      this.showDialog = false;
      this.formData = {};
    },
    async saveGenerationalBonus(form) {
      try {
        const response = await axios.put(`/generational_bonus/update`, form);
        if (response.data.status === 'success') {
          this.$message.success(response.data.message);
          await this.getGenerationalBonus(); // Recargar datos
          this.closeModal();
        } else {
          this.$message.warning(response.data.message || 'Ocurrió un error');
        }
      } catch (error) {
        console.error('Error al guardar:', error);
        this.$message.error('Error al guardar los cambios');
      }
    },
  },
};
</script>

<style scoped>
.custom-table {
  width: 100%;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

/* Estilos responsive para dispositivos pequeños */
@media (max-width: 768px) {
  .custom-table {
    font-size: 0.8rem;
  }

  .custom-table thead th {
    white-space: nowrap;
    padding: 0.5rem;
  }

  .custom-table tbody td {
    padding: 0.5rem;
  }

  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
}

/* Estilos para dispositivos muy pequeños */
@media (max-width: 576px) {
  .custom-table {
    display: block;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table-responsive {
    border: 0;
    margin-bottom: 0;
  }
}

/* Animación hover */
.table-hover tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.05);
  transition: background-color 0.2s ease-in-out;
}

/* Estilos para el encabezado */
.table thead th {
  border-bottom: 2px solid #dee2e6;
  background-color: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 1;
}

/* Estilos para las celdas */
.table td,
.table th {
  vertical-align: middle;
  border: 1px solid #dee2e6;
}

/* Mejoras visuales para el botón */
.btn-primary {
  transition: all 0.2s ease-in-out;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
