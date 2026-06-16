<template>
  <div class="rewards-admin">
    <div class="row">
      <div class="col-12">
        <h4>Gestión de Premios</h4>
        
        <!-- Tabla de premios -->
        <div class="card">
          <div class="card-header">
            <h5>Premios Disponibles</h5>
            <button class="btn btn-primary" @click="openAddModal">
              <i class="fas fa-plus"></i> Agregar Premio
            </button>
          </div>
          
          <div class="card-body">
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
              </div>
            </div>

            <div class="table-responsive">
              <table id="rewards-table" class="table table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Costo</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="(reward, index) in rewards" 
                    :key="reward.id"
                    :class="{ 'reward-deleted': reward.deleted_at }"
                  >
                    <td>
                      <p style="width: 15px">{{ index + 1 }}</p>
                    </td>
                    <td>
                      <img 
                        :src="reward.image" 
                        :alt="reward.name"
                        class="reward-thumbnail"
                        :class="{ 'img-deleted': reward.deleted_at }"
                        @error="handleImageError"
                      />
                    </td>
                    <td>
                      <p style="width: 180px">
                        {{ reward.name }}
                        <span v-if="reward.deleted_at" class="badge badge-danger ml-2">
                          <i class="fas fa-trash-alt"></i> Eliminado
                        </span>
                      </p>
                    </td>
                    <td>
                      <p style="width: 200px">{{ truncateText(reward.description, 50) }}</p>
                    </td>
                    <td class="font-weight-bold text-primary">
                      {{ formatMoney(reward.cost) }}
                    </td>
                    <td>
                      <span 
                        v-if="reward.stock !== null"
                        :class="reward.stock > 0 ? 'badge badge-success' : 'badge badge-danger'"
                      >
                        {{ reward.stock }}
                      </span>
                      <span v-else class="badge badge-info">Ilimitado</span>
                    </td>
                    <td>
                      <span 
                        v-if="reward.deleted_at" 
                        class="badge badge-danger"
                      >
                        Eliminado
                      </span>
                      <span 
                        v-else
                        :class="reward.active ? 'badge badge-success' : 'badge badge-secondary'"
                      >
                        {{ reward.active ? 'Activo' : 'Inactivo' }}
                      </span>
                      <div v-if="reward.deleted_at" class="text-muted small mt-1">
                        {{ formatDate(reward.deleted_at) }}
                      </div>
                    </td>
                    <td class="align-content-center">
                      <el-select 
                        id="customize_select" 
                        size="mini" 
                        clearable 
                        v-model="optionSelected"
                        @change="getOptionSelected(reward)"
                      >
                        <el-option 
                          v-for="option in getAvailableOptions(reward)" 
                          :key="option.value"
                          :label="option.label" 
                          :value="option.value">
                        </el-option>
                      </el-select>
                    </td>
                  </tr>
                  <tr v-if="rewards.length === 0">
                    <td colspan="8" class="text-center py-4">
                      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-3">
                        <path d="M20 7h-9"></path>
                        <path d="M14 17H5"></path>
                        <circle cx="17" cy="17" r="3"></circle>
                        <circle cx="7" cy="7" r="3"></circle>
                      </svg>
                      <p class="text-muted mb-0">No hay premios registrados</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="card mt-3">
          <div class="card-header">
            <h5>Estadísticas de Canjes</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="stats-box">
                  <h3>{{ stats.total_rewards }}</h3>
                  <p>Total Premios</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-box">
                  <h3>{{ stats.active_rewards }}</h3>
                  <p>Premios Activos</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-box">
                  <h3>{{ stats.total_redemptions }}</h3>
                  <p>Total Canjes</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-box">
                  <h3>{{ formatMoney(stats.total_credits_used) }}</h3>
                  <p>Créditos Canjeados</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de Canjes Pendientes -->
        <div class="card mt-3">
          <div class="card-header">
            <h5>Canjes Pendientes de Procesar</h5>
          </div>
          <div class="card-body">
            <div v-if="loadingRedemptions" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
              </div>
            </div>
            
            <div v-else class="table-responsive">
              <table class="table table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Premio</th>
                    <th>Costo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="redemption in pendingRedemptions" :key="redemption.id">
                    <td>{{ redemption.id }}</td>
                    <td>{{ redemption.user ? redemption.user.name : 'N/A' }}</td>
                    <td>{{ redemption.reward ? redemption.reward.name : 'N/A' }}</td>
                    <td class="font-weight-bold text-primary">{{ formatMoney(redemption.cost) }}</td>
                    <td>{{ formatDate(redemption.created_at) }}</td>
                    <td>
                      <span class="badge badge-warning">{{ redemption.status }}</span>
                    </td>
                    <td>
                      <button 
                        class="btn btn-sm btn-success mr-1"
                        @click="processRedemption(redemption.id, 'processed')"
                        title="Aprobar"
                      >
                        <i class="fas fa-check"></i>
                      </button>
                      <button 
                        class="btn btn-sm btn-danger"
                        @click="processRedemption(redemption.id, 'cancelled')"
                        title="Cancelar"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="pendingRedemptions.length === 0">
                    <td colspan="7" class="text-center py-4">
                      <p class="text-muted mb-0">No hay canjes pendientes</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Agregar/Editar -->
    <custom-modal 
        id="reward-modal" 
        color="primary" 
        size="large"
    >
      <template #title>{{ editingReward ? 'Editar Premio' : 'Agregar Premio' }}</template>
      <template #body>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="name">Nombre del Premio *</label>
              <input 
                type="text" 
                id="name" 
                class="form-control" 
                v-model="form.name"
                placeholder="Ej: Membresía School"
              />
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="cost">Costo (Créditos) *</label>
              <input 
                type="number" 
                id="cost" 
                class="form-control" 
                v-model.number="form.cost"
                placeholder="Ej: 50"
                min="0"
                step="0.01"
              />
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label for="description">Descripción *</label>
              <textarea 
                id="description" 
                class="form-control" 
                v-model="form.description"
                placeholder="Describe el premio..."
                rows="3"
              ></textarea>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="stock">Stock</label>
              <input 
                type="number" 
                id="stock" 
                class="form-control" 
                v-model.number="form.stock"
                placeholder="Dejar vacío para ilimitado"
                min="0"
              />
              <small class="form-text text-muted">
                Deja vacío para stock ilimitado
              </small>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="image">URL de Imagen *</label>
              <input 
                type="text" 
                id="image" 
                class="form-control" 
                v-model="form.image"
                placeholder="/images/premio.png"
              />
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input 
                  type="checkbox" 
                  class="custom-control-input" 
                  id="active"
                  v-model="form.active"
                />
                <label class="custom-control-label" for="active">
                  Premio Activo
                </label>
              </div>
              <small class="form-text text-muted">
                Los premios inactivos no serán visibles para los usuarios
              </small>
            </div>
          </div>

          <!-- Preview de la imagen -->
          <div class="col-12" v-if="form.image">
            <div class="form-group">
              <label>Vista Previa</label>
              <div class="image-preview">
                <img 
                  :src="form.image" 
                  alt="Preview"
                  @error="handleImageError"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
        <button 
          class="btn btn-primary" 
          @click="saveReward"
          :disabled="saving"
        >
          <span v-if="!saving">
            <i class="fas fa-save mr-1"></i>
            {{ editingReward ? 'Actualizar' : 'Guardar' }}
          </span>
          <span v-else>
            <span class="spinner-border spinner-border-sm mr-1"></span>
            Guardando...
          </span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal de confirmación para eliminar -->
    <custom-modal id="delete-reward-modal" color="danger" v-if="selectedReward">
      <template #title>Eliminar Premio <u>{{ selectedReward.name }}</u></template>
      <template #body>¿Seguro que desea eliminar este premio?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="confirmDeleteReward">
          Eliminar
        </button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import axios from 'axios';
import ModalComponent from '@/components/common/ModalComponent.vue';

// Configuración de axios
const api = axios.create({
  baseURL: '/',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Token CSRF
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  api.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Token de autenticación
const authToken = localStorage.getItem('access_token');
if (authToken) {
  api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

export default {
  name: 'RewardsAdmin',
  
  components: {
    'custom-modal': ModalComponent,
  },

  data() {
    return {
      rewards: [],
      pendingRedemptions: [],
      stats: {
        total_rewards: 0,
        active_rewards: 0,
        total_redemptions: 0,
        total_credits_used: 0,
        pending_redemptions: 0,
      },
      form: {
        name: '',
        description: '',
        cost: 0,
        stock: null,
        image: '/images/pickle-icon.png',
        active: true,
      },
      // Opciones del menú desplegable
      options: [
        {
          value: '1', 
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Eliminar',
        },
        {
          value: '3',
          label: 'Restaurar',
        },
      ],
      optionSelected: '',
      selectedReward: null,
      editingReward: null,
      showModal: false,
      loading: false,
      loadingRedemptions: false,
      saving: false,
    };
  },

  mounted() {
    this.loadRewards();
    this.loadStats();
    this.loadPendingRedemptions();
  },

  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        if ($.fn.DataTable.isDataTable('#rewards-table')) {
          $('#rewards-table').DataTable().destroy();
        }
        $('#rewards-table').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
          }
        });
      });
    },

    async loadRewards() {
      this.loading = true;
      try {
        const response = await api.get('/ad/rewards');
        if (response.data.success) {
          this.rewards = response.data.data;
          this.loading = false;
          this.loadDataTable();
        }
      } catch (error) {
        console.error('Error loading rewards:', error);
        this.$message.error('Error al cargar los premios');
        this.loading = false;
      }
    },

    async loadStats() {
      try {
        const response = await api.get('/ad/rewards/stats');
        if (response.data.success) {
          this.stats = response.data.data;
        }
      } catch (error) {
        console.error('Error loading stats:', error);
      }
    },

    async loadPendingRedemptions() {
      this.loadingRedemptions = true;
      try {
        const response = await api.get('/ad/rewards/redemptions', {
          params: { status: 'pending' }
        });
        if (response.data.success) {
          this.pendingRedemptions = response.data.data.data || response.data.data;
        }
      } catch (error) {
        console.error('Error loading redemptions:', error);
      } finally {
        this.loadingRedemptions = false;
      }
    },

    // Obtener opciones disponibles según el estado del premio
    getAvailableOptions(reward) {
      return this.options.filter((option) => {
        // Restaurar solo para premios eliminados
        if (option.value === '3') {
          return reward.deleted_at !== null;
        }
        // Editar y Eliminar solo para premios no eliminados
        if (option.value === '1' || option.value === '2') {
          return reward.deleted_at === null;
        }
        return true;
      });
    },

    // Manejar la selección de opciones
    getOptionSelected(reward) {
      let option = this.optionSelected;
      
      switch (option) {
        case '1': // Editar
          this.editReward(reward);
          break;
        case '2': // Eliminar
          this.selectedReward = reward;
          $('#delete-reward-modal').modal('show');
          break;
        case '3': // Restaurar
          this.restoreReward(reward.id);
          break;
        default:
          console.log('Opción no válida');
          break;
      }
      
      // Limpiar selección
      this.optionSelected = '';
    },

    openAddModal() {
      this.editingReward = null;
      this.resetForm();
      this.showModal = true;
    
      this.$nextTick(() => {
        $('#reward-modal').modal('show');
      });
    },

    editReward(reward) {
      this.editingReward = reward.id;
      this.form = {
        name: reward.name,
        description: reward.description,
        cost: reward.cost,
        stock: reward.stock,
        image: reward.image,
        active: reward.active,
      };
      this.showModal = true;

      this.$nextTick(() => {
        $('#reward-modal').modal('show');
      });
    },

    async saveReward() {
      if (!this.validateForm()) return;

      this.saving = true;
      try {
        let response;
        if (this.editingReward) {
          response = await api.put(`/ad/rewards/${this.editingReward}`, this.form);
          this.$message.success('Premio actualizado correctamente');
        } else {
          response = await api.post('/ad/rewards', this.form);
          this.$message.success('Premio agregado correctamente');
        }

        this.closeModal();
        await this.loadRewards();
        await this.loadStats();
      } catch (error) {
        console.error('Error saving reward:', error);
        const errorMessage = error.response?.data?.message || 'Error al guardar el premio';
        this.$message.error(errorMessage);
      } finally {
        this.saving = false;
      }
    },

    confirmDeleteReward() {
      if (this.selectedReward) {
        this.deleteReward(this.selectedReward.id);
        $('#delete-reward-modal').modal('hide');
      }
    },

    async deleteReward(id) {
      try {
        const response = await api.delete(`/ad/rewards/${id}`);
        if (response.data.success) {
          this.$message.success('Premio eliminado correctamente');
          await this.loadRewards();
          await this.loadStats();
        }
      } catch (error) {
        console.error('Error deleting reward:', error);
        const errorMessage = error.response?.data?.message || 'Error al eliminar el premio';
        this.$message.error(errorMessage);
      }
    },

    async restoreReward(id) {
      try {
        const response = await api.post(`/ad/rewards/${id}/restore`);
        if (response.data.success) {
          this.$message.success('Premio restaurado correctamente');
          await this.loadRewards();
          await this.loadStats();
        }
      } catch (error) {
        console.error('Error restoring reward:', error);
        const errorMessage = error.response?.data?.message || 'Error al restaurar el premio';
        this.$message.error(errorMessage);
      }
    },

    async processRedemption(redemptionId, status) {
      const action = status === 'processed' ? 'aprobar' : 'cancelar';
      if (!confirm(`¿Está seguro de ${action} este canje?`)) {
        return;
      }

      try {
        const response = await api.post(`/ad/rewards/redemptions/${redemptionId}/process`, {
          status: status,
          notes: status === 'cancelled' ? 'Cancelado por el administrador' : 'Aprobado por el administrador'
        });

        if (response.data.success) {
          this.$message.success(`Canje ${status === 'processed' ? 'aprobado' : 'cancelado'} correctamente`);
          await this.loadPendingRedemptions();
          await this.loadStats();
        }
      } catch (error) {
        console.error('Error processing redemption:', error);
        const errorMessage = error.response?.data?.message || 'Error al procesar el canje';
        this.$message.error(errorMessage);
      }
    },

    validateForm() {
      if (!this.form.name || this.form.name.trim() === '') {
        this.$message.warning('El nombre del premio es obligatorio');
        return false;
      }

      if (!this.form.description || this.form.description.trim() === '') {
        this.$message.warning('La descripción es obligatoria');
        return false;
      }

      if (this.form.cost <= 0) {
        this.$message.warning('El costo debe ser mayor a 0');
        return false;
      }

      if (!this.form.image || this.form.image.trim() === '') {
        this.$message.warning('La URL de la imagen es obligatoria');
        return false;
      }

      if (this.form.stock !== null && this.form.stock < 0) {
        this.$message.warning('El stock no puede ser negativo');
        return false;
      }

      return true;
    },

    resetForm() {
      this.form = {
        name: '',
        description: '',
        cost: 0,
        stock: null,
        image: '/images/pickle-icon.png',
        active: true,
      };
    },

    closeModal() {
      this.showModal = false;
    
      this.$nextTick(() => {
        $('#reward-modal').modal('hide');
      });
    },

    formatMoney(amount) {
      return (amount || 0).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
      });
    },

    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength 
        ? text.substring(0, maxLength) + '...' 
        : text;
    },

    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/300x200?text=Sin+Imagen';
    },
  },
};
</script>

<style scoped>
/* Estilos para el selector tipo puntitos */
.el-select {
  width: 80%;
}

.el-select .el-input .el-select__caret::before {
  content: '\e794';
}

#customize_select {
  background-color: #ffffff !important;
  border: none !important;
  width: 0px !important;
  font-size: 0px !important;
  justify-content: center;
  text-align: center;
  padding: 0 !important;
}

/* Estilos para filas eliminadas */
.reward-deleted {
  background-color: #fff5f5 !important;
  opacity: 0.7;
  transition: all 0.3s ease;
}

.reward-deleted:hover {
  opacity: 0.9;
}

.reward-deleted td {
  color: #6c757d;
  text-decoration: line-through;
}

.reward-deleted .badge,
.reward-deleted .btn {
  text-decoration: none;
}

.img-deleted {
  filter: grayscale(100%);
  opacity: 0.6;
}

.reward-thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #e9ecef;
  transition: all 0.3s ease;
}

/* Estilos de estadísticas */
.stats-box {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  border: 1px solid #dee2e6;
  transition: transform 0.2s;
}

.stats-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stats-box h3 {
  color: #7367f0;
  margin-bottom: 5px;
  font-weight: bold;
  font-size: 2rem;
}

.stats-box p {
  color: #6c757d;
  margin: 0;
  font-size: 14px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
}

.card-header h5 {
  margin: 0;
}

.image-preview {
  width: 100%;
  max-width: 300px;
  height: 200px;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: #f8f9fa;
}

.image-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.table-hover-animation tbody tr {
  transition: background-color 0.2s;
}

.table-hover-animation tbody tr:hover:not(.reward-deleted) {
  background-color: #f8f9fa;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.badge {
  padding: 0.35em 0.65em;
  font-size: 0.875rem;
}
</style>