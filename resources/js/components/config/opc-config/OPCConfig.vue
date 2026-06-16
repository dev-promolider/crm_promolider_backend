<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table id="data-table-list-payments" class="table table-hover table-striped table-bordered">
            <thead>
              <tr class="text-center bg-light">
                <th class="align-middle">Id</th>
                <th class="align-middle">Nombre</th>
                <th class="align-middle d-none d-md-table-cell">Descripción</th>
                <th class="align-middle">Precio</th>
                <th class="align-middle d-none d-lg-table-cell">Precio Promocional</th>
                <th class="align-middle d-none d-lg-table-cell">Comisión</th>
                <th class="align-middle">Puntos</th>
                <th class="align-middle">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in opc" :key="index" class="text-center">
                <td class="align-middle">{{ row.id }}</td>
                <td class="align-middle">{{ row.name }}</td>
                <td class="align-middle d-none d-md-table-cell">{{ row.descripcion }}</td>
                <td class="align-middle">{{ row.price }}</td>
                <td class="align-middle d-none d-lg-table-cell">{{ row.promotion_prince }}</td>
                <td class="align-middle d-none d-lg-table-cell">{{ row.commission }}</td>
                <td class="align-middle">{{ row.points }}</td>
                <td class="align-middle">
                  <button class="btn btn-primary btn-sm" @click.prevent="openModal(row)">
                    <i class="fas fa-edit me-1"></i>Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <OPCConfigModal v-if="showDialog" :show-dialog="showDialog" :form-data="formData" @closeModal="closeModal"
      @saveOPC="saveOPC" />
  </div>
</template>

<script>
import axios from 'axios';
import OPCConfigModal from './OPCConfigModal.vue';

export default {
  components: {
    OPCConfigModal,
  },
  data() {
    return {
      opc: [],
      showDialog: false,
      formData: {},
    };
  },
  created() {
    this.getOPCData();
  },
  methods: {
    async getOPCData() {
      try {
        const response = await axios.get(`/opc_config/list`);
        this.opc = response.data.data;
      } catch (error) {
        console.error('Error al obtener datos:', error);
        this.$message.error('Error al cargar los datos');
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
    async saveOPC(form) {
      try {
        if (!form.name?.trim()) {
          this.$message.warning('Nombre obligatorio');
          return;
        }
        if (!form.price) {
          this.$message.warning('Precio obligatorio');
          return;
        }
        if (!form.points) {
          this.$message.warning('Puntos obligatorio');
          return;
        }

        const response = await axios.put(`/opc_config/update`, form);
        if (response.data.status === 'success') {
          this.$message.success(response.data.message);
          await this.getOPCData();
          this.closeModal();
        } else {
          this.$message.warning('Ocurrió un error al actualizar');
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
.table {
  font-size: 0.95rem;
  margin-bottom: 0;
}

.table thead th {
  border-bottom-width: 1px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
}

.table td,
.table th {
  padding: 0.75rem;
  vertical-align: middle;
  border: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.075);
  transition: background-color 0.2s ease;
}

.btn-sm {
  padding: 0.4rem 0.8rem;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .table {
    font-size: 0.85rem;
  }

  .table td,
  .table th {
    padding: 0.5rem;
  }

  .btn-sm {
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
  }
}
</style>
