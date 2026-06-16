<template>
  <div>
    <!-- Sección de Créditos Disponibles -->
    <section>
      <div class="row">
        <div class="col-md-12 col-12">
          <div class="card bg-gradient-primary">
            <div class="card-body text-center">
              <h3 class="text-white mb-1">Créditos Disponibles</h3>
              <h1 class="text-white font-weight-bolder">
                {{ formatMoney(availableCredits) }}
              </h1>
              <p class="text-white mb-0">Canjea tus créditos por increíbles premios</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección de Premios -->
    <section>
      <div class="row">
        <div class="col-12">
          <h4 class="mb-2">Premios Disponibles</h4>
        </div>
      </div>

      <!-- Cargando -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Cargando...</span>
        </div>
      </div>

      <!-- Grid de Premios -->
      <div v-else class="row">
        <div 
          v-for="reward in rewards" 
          :key="reward.id" 
          class="col-md-4 col-sm-6 col-12 mb-3"
        >
          <div class="card reward-card h-100">
            <div class="card-img-top-wrapper">
              <img 
                :src="reward.image" 
                class="card-img-top" 
                :alt="reward.name"
                @error="handleImageError"
              />
              <span 
                v-if="!canAfford(reward)" 
                class="badge badge-warning position-absolute"
                style="top: 10px; right: 10px;"
              >
                Créditos insuficientes
              </span>
            </div>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">{{ reward.name }}</h5>
              <p class="card-text text-muted flex-grow-1">{{ reward.description }}</p>
              
              <div class="reward-info mb-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Costo:</span>
                  <span class="font-weight-bold text-primary h5 mb-0">
                    {{ formatMoney(reward.cost) }}
                  </span>
                </div>
                <div v-if="reward.stock !== null" class="d-flex justify-content-between align-items-center mt-1">
                  <span class="text-muted">Stock:</span>
                  <span 
                    class="badge" 
                    :class="reward.stock > 0 ? 'badge-success' : 'badge-danger'"
                  >
                    {{ reward.stock > 0 ? `${reward.stock} disponibles` : 'Agotado' }}
                  </span>
                </div>
              </div>

              <button 
                class="btn btn-block"
                :class="canRedeem(reward) ? 'btn-primary' : 'btn-secondary'"
                :disabled="!canRedeem(reward) || redeeming"
                @click="redeemReward(reward)"
              >
                <span v-if="!redeeming">
                  {{ getButtonText(reward) }}
                </span>
                <span v-else class="spinner-border spinner-border-sm" role="status"></span>
              </button>
            </div>
          </div>
        </div>

        <!-- Sin Premios -->
        <div v-if="rewards.length === 0" class="col-12">
          <div class="card">
            <div class="card-body text-center py-5">
              <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-3">
                <path d="M20 7h-9"></path>
                <path d="M14 17H5"></path>
                <circle cx="17" cy="17" r="3"></circle>
                <circle cx="7" cy="7" r="3"></circle>
              </svg>
              <h5 class="text-muted">No hay premios disponibles</h5>
              <p class="text-muted">Pronto habrá nuevos premios para canjear</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="modalConfirmRedeem" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar Canje</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body" v-if="selectedReward">
            <div class="text-center mb-3">
              <img 
                :src="selectedReward.image" 
                :alt="selectedReward.name"
                class="img-fluid rounded"
                style="max-height: 200px;"
              />
            </div>
            <h5 class="text-center">{{ selectedReward.name }}</h5>
            <p class="text-center text-muted">{{ selectedReward.description }}</p>
            
            <div class="alert alert-info">
              <div class="d-flex justify-content-between">
                <span>Costo del premio:</span>
                <strong>{{ formatMoney(selectedReward.cost) }}</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Créditos disponibles:</span>
                <strong>{{ formatMoney(availableCredits) }}</strong>
              </div>
              <hr>
              <div class="d-flex justify-content-between">
                <span>Créditos restantes:</span>
                <strong class="text-primary">
                  {{ formatMoney(availableCredits - selectedReward.cost) }}
                </strong>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              Cancelar
            </button>
            <button 
              type="button" 
              class="btn btn-primary"
              @click="confirmRedeem"
              :disabled="redeeming"
            >
              <span v-if="!redeeming">Confirmar Canje</span>
              <span v-else class="spinner-border spinner-border-sm" role="status"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

// Configuración base de axios
const api = axios.create({
  baseURL: '/',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Agregar token CSRF si existe
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  api.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Agregar token de autenticación si existe
const authToken = localStorage.getItem('access_token');
if (authToken) {
  api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

export default {
  name: 'RewardsRedemption',
  data() {
    return {
      availableCredits: 0,
      rewards: [],
      loading: false,
      redeeming: false,
      selectedReward: null
    };
  },
  mounted() {
    this.loadCredits();
    this.loadRewards();
  },
  methods: {
    async loadCredits() {
      try {
        const response = await api.get('/rewards/credits');
        if (response.data.success) {
          this.availableCredits = response.data.data.credits || 0;
        }
      } catch (error) {
        console.error('Error al cargar créditos:', error);
        this.$message.error('Error al cargar los créditos disponibles');
      }
    },

    async loadRewards() {
      this.loading = true;
      try {
        const response = await api.get('/rewards');
        if (response.data.success) {
          this.rewards = response.data.data;
        }
      } catch (error) {
        console.error('Error al cargar premios:', error);
        this.$message.error('Error al cargar los premios disponibles');
      } finally {
        this.loading = false;
      }
    },

    canAfford(reward) {
      return this.availableCredits >= reward.cost;
    },

    canRedeem(reward) {
      return this.canAfford(reward) && (reward.stock === null || reward.stock > 0);
    },

    getButtonText(reward) {
      if (reward.stock !== null && reward.stock === 0) {
        return 'Agotado';
      }
      if (!this.canAfford(reward)) {
        return 'Créditos insuficientes';
      }
      return 'Canjear';
    },

    redeemReward(reward) {
      this.selectedReward = reward;
      $('#modalConfirmRedeem').modal('show');
    },

    async confirmRedeem() {
      if (!this.selectedReward) return;

      this.redeeming = true;
      try {
        const response = await api.post('/rewards/redeem', {
          reward_id: this.selectedReward.id
        });

        if (response.data.success) {
          this.$message.success('¡Premio canjeado exitosamente!');
          $('#modalConfirmRedeem').modal('hide');
          
          // Recargar datos
          await this.loadCredits();
          await this.loadRewards();
          
          this.selectedReward = null;
        } else {
          this.$message.error(response.data.message || 'Error al procesar el canje');
        }
      } catch (error) {
        console.error('Error al canjear premio:', error);
        const errorMessage = error.response?.data?.message || 'Error al procesar el canje. Intenta nuevamente.';
        this.$message.error(errorMessage);
      } finally {
        this.redeeming = false;
      }
    },

    formatMoney(amount) {
      return amount.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
      });
    },

    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/300x200?text=Sin+Imagen';
    }
  }
};
</script>

<style scoped>
.reward-card {
  transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid #e0e0e0;
}

.reward-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.card-img-top-wrapper {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: #f8f9fa;
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #10b10b 0%, #10b10b 100%);
}

.reward-info {
  border-top: 1px solid #e0e0e0;
  padding-top: 1rem;
}
</style>