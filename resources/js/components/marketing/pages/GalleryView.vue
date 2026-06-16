<template>
  <div class="container mt-5">
    <div class="mb-4">
      <h1 class="h2 font-weight-bold mb-2">Galería de Plantillas</h1>
      <p class="text-muted">Selecciona una plantilla para personalizar tu página</p>

      <!-- Navegación entre pestañas con estilo de botones -->
      <div class="d-flex gap-3 mt-4">
        <button 
          type="button"
          class="btn"
          :class="currentTab === 'templates' ? 'btn-primary' : 'btn-outline-primary'"
          @click="$emit('tab-change', 'templates')"
        >
          <i class="fas fa-th-large mr-2"></i>
          Plantillas Base
        </button>
        
        <button 
          type="button"
          class="btn"
          :class="currentTab === 'my-templates' ? 'btn-primary' : 'btn-outline-primary'"
          @click="$emit('tab-change', 'my-templates')"
        >
          <i class="fas fa-bookmark mr-2"></i>
          Mis Plantillas
          <span v-if="myTemplates.length > 0" class="badge badge-light ml-2">
            {{ myTemplates.length }}
          </span>
        </button>
      </div>
    </div>

    <!-- Contenido de las pestañas -->
    <section v-if="!initialLoading">
      <!-- Pestaña de Plantillas Base -->
      <div v-if="currentTab === 'templates'">
        <div class="row">
          <div v-for="template in templates" :key="template.id" class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow template-card" @click="$emit('select-template', template)">
              <div class="template-thumbnail">
                <img 
                  :src="template.thumbnail" 
                  :alt="template.name"
                  class="card-img-top"
                />
              </div>
              <div class="card-body">
                <h5 class="card-title">{{ template.name }}</h5>
                <p class="card-text text-muted">{{ template.description }}</p>
                <button class="btn btn-primary btn-block">
                  <i class="fas fa-edit mr-2"></i>
                  Personalizar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pestaña de Mis Plantillas -->
      <div v-else-if="currentTab === 'my-templates'">
        <my-templates-tab 
          :templates="myTemplates"
          :loading="loadingMyTemplates"
          @load-template="$emit('load-template', $event)"
          @delete-template="$emit('delete-template', $event)"
          @switch-tab="handleTabChange"
        />
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner.vue';
import MyTemplatesTab from './MyTemplatesTab.vue';

export default {
  name: 'GalleryView',
  
  components: {
    CustomSpinner,
    MyTemplatesTab,
  },

  props: {
    currentTab: {
      type: String,
      default: 'templates'
    },
    templates: {
      type: Array,
      default: () => []
    },
    myTemplates: {
      type: Array,
      default: () => []
    },
    initialLoading: {
      type: Boolean,
      default: false
    },
    loadingMyTemplates: {
      type: Boolean,
      default: false
    }
  },

  emits: [
    'select-template',
    'load-template', 
    'delete-template',
    'tab-change'
  ],

  methods: {
    handleTabChange(tab) {
      // ⚠️ Este evento se emite al componente padre para que el padre lo maneje
      this.$emit('tab-change', tab);
    }
  }
};
</script>

<style scoped>
.template-card {
  transition: 0.3s;
  border-radius: 10px;
  cursor: pointer;
}

.template-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  transform: translateY(-2px);
}

.template-thumbnail {
  height: 200px;
  overflow: hidden;
}

.template-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Estilos para los botones de navegación */
.btn {
  transition: all 0.3s ease;
  border-radius: 6px;
  font-weight: 500;
  padding: 0.75rem 1.5rem;
}

.btn-outline-primary {
  border: 2px solid #007bff;
  color: #007bff;
  background-color: transparent;
}

.btn-outline-primary:hover {
  background-color: #007bff;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-primary {
  background-color: #007bff;
  border: 2px solid #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
  border-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

/* Espaciado entre botones */
.d-flex.gap-3 {
  gap: 1rem;
}

/* Estilo del badge dentro del botón */
.badge-light {
  background-color: rgba(255, 255, 255, 0.9);
  color: #007bff;
  font-weight: 600;
}

.btn-outline-primary .badge-light {
  background-color: #007bff;
  color: white;
}
</style>