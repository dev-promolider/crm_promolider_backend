<template>
  <div class="container mt-5">
    <!-- 🔹 Tabs de Filtros -->
    <section v-if="!initialLoading" class="mb-4">
      <ul class="nav nav-tabs justify-content-center filter-tabs">
        <li class="nav-item" @click="filterCampaigns('all')">
          <a class="nav-link" :class="{ active: filterType === 'all' }" href="#">
            <i class="feather icon-grid"></i> Todas
          </a>
        </li>
        <li class="nav-item" @click="filterCampaigns('Masterclass')">
          <a class="nav-link text-success" :class="{ active: filterType === 'Masterclass' }" href="#">
            <i class="feather icon-video"></i> Masterclasses
          </a>
        </li>
        <li class="nav-item" @click="filterCampaigns('E-book')">
          <a class="nav-link text-info" :class="{ active: filterType === 'E-book' }" href="#">
            <i class="feather icon-book"></i> E-books
          </a>
        </li>
        <li class="nav-item" @click="filterCampaigns('Mini Curso')">
          <a class="nav-link text-warning" :class="{ active: filterType === 'Mini Curso' }" href="#">
            <i class="feather icon-monitor"></i> Mini Cursos
          </a>
        </li>
      </ul>
    </section>

    <!-- 🔹 Cards -->
    <section v-if="!initialLoading">
      <div v-if="filteredCampaigns.length === 0" class="alert alert-info text-center">
        No se encontraron resultados para "{{ filterType === 'all' ? 'todas las campañas' : filterType }}"
      </div>

      <div class="row">
        <div
          v-for="campaign in filteredCampaigns"
          :key="`${campaign.tipo}-${campaign.id}`"
          class="col-md-4 mb-4"
        >
          <div
            class="card shadow campaign-card"
            @click="redirectToDetail(campaign)"
            style="cursor: pointer; position: relative;"
          >
            <!-- Imagen -->
            <div class="card-img-wrapper">
              <img
                :src="campaign.imagen || 'https://via.placeholder.com/400x250'"
                class="card-img-top"
                :alt="`Imagen de ${campaign.nombre}`"
              />
            </div>

            <!-- Cuerpo -->
            <div class="card-body">
              <h5 class="card-title">{{ campaign.nombre }}</h5>
              <p class="card-text">
                <strong>Categoría:</strong> {{ campaign.category_name || 'General' }} <br>

                <template v-if="campaign.tipo === 'Masterclass' && campaign.fecha_hora_evento">
                  <strong>Evento:</strong> {{ campaign.fecha_hora_evento }} <br>
                </template>

                <template v-if="campaign.tipo === 'Mini Curso' && campaign.modulos">
                  <strong>Módulos:</strong> {{ campaign.modulos }} <br>
                </template>

                <template v-if="campaign.tipo === 'E-book' && campaign.capitulos">
                  <strong>Capítulos:</strong> {{ campaign.capitulos }} <br>
                </template>
              </p>

              <!-- Badge -->
              <span
                class="badge position-absolute campaign-badge"
                :class="getTypeBadgeClass(campaign.tipo)"
              >
                <i :class="getTypeIcon(campaign.tipo)"></i> {{ campaign.tipo }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import axios from 'axios';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';

export default {
  name: 'CampaignsCard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  components: {
    'custom-spinner': CustomSpinner,
  },
  data() {
    return {
      campaigns: [],
      initialLoading: true,
      filterType: 'all',
    };
  },
  computed: {
    filteredCampaigns() {
      if (this.filterType === 'all') {
        return this.campaigns;
      }
      return this.campaigns.filter(campaign => campaign.tipo === this.filterType);
    },
  },
  mounted() {
    this.fetchCampaigns();
  },
  methods: {
    async fetchCampaigns() {
      this.initialLoading = true;
      try {
        const response = await axios.get('/marketing/campaigns/list');
        if (response.data.success) {
          this.campaigns = response.data.data;
        }
      } catch (error) {
        console.error('Error al obtener campañas:', error);
      } finally {
        this.initialLoading = false;
      }
    },

    filterCampaigns(type) {
      this.filterType = type;
    },

    redirectToDetail(campaign) {
      const typeMap = {
        'Masterclass': 'masterclass',
        'E-book': 'ebook',
        'Mini Curso': 'minicourse',
      };
      const route = typeMap[campaign.tipo];
      if (route) {
        window.location.href = `/marketing/${campaign.id}/${route}`;
      }
    },

    getTypeBadgeClass(tipo) {
      const classes = {
        'Masterclass': 'badge-success',
        'E-book': 'badge-info',
        'Mini Curso': 'badge-warning',
      };
      return classes[tipo] || 'badge-secondary';
    },

    getTypeIcon(tipo) {
      const icons = {
        'Masterclass': 'feather icon-video',
        'E-book': 'feather icon-book',
        'Mini Curso': 'feather icon-monitor',
      };
      return icons[tipo] || 'feather icon-box';
    },
  },
};
</script>

<style scoped>
/* 🔹 Tabs */
.filter-tabs {
  border-bottom: 2px solid #dee2e6;
}
.filter-tabs .nav-item {
  margin: 0 0.5rem;
}
.filter-tabs .nav-link {
  font-weight: 500;
  color: #6c757d;
  border: none;
  border-bottom: 3px solid transparent;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}
.filter-tabs .nav-link:hover {
  color: #7367f0;
}
.filter-tabs .nav-link.active {
  color: #7367f0;
  border-color: #7367f0;
  background-color: transparent;
}
.filter-tabs i {
  margin-right: 6px;
}

/* 🔹 Cards */
.card {
  transition: 0.3s;
  border-radius: 10px;
  min-height: 420px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}
.card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Imagen con separación */
.card-img-wrapper {
  padding: 16px;
  background-color: transparent;
}
.card-img-top {
  height: 320px !important;
  object-fit: cover;
  width: 100%;
  border: 20px solid #fff;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
  border-radius: 10px 10px 0 0;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Cuerpo */
.card-body {
  flex-grow: 1;
  background-color: #fff;
  padding: 1.25rem;
  position: relative;
}
.card-title {
  font-size: 1.1rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}
.card-text {
  font-size: 0.9rem;
  color: #555;
}

/* Badge */
.campaign-badge {
  bottom: 10px;
  right: 15px;
}
.badge-success {
  background-color: #28a745;
}
.badge-info {
  background-color: #17a2b8;
}
.badge-warning {
  background-color: #ffc107;
  color: #333;
}
.badge-secondary {
  background-color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
  .filter-tabs {
    flex-wrap: wrap;
  }
  .filter-tabs .nav-item {
    margin-bottom: 0.5rem;
  }
  .col-md-4 {
    margin-bottom: 1rem;
  }
  .card {
    min-height: auto;
  }
}
</style>
