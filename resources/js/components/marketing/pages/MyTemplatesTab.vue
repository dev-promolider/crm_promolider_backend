<template>
  <div class="my-templates-tab">
    <!-- Estado de carga -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando plantillas...</p>
    </div>

    <!-- Sin plantillas -->
    <div v-else-if="templates.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-bookmark"></i>
      </div>
      <h3>No tienes plantillas guardadas</h3>
      <p>Cuando personalices y guardes una plantilla, aparecerá aquí.</p>
      <button class="btn-primary" @click="$emit('switch-tab', 'templates')">
        Explorar Plantillas
      </button>
    </div>

    <!-- Lista de plantillas -->
    <div v-else>
      <!-- Header minimalista -->
      <div class="header">
        <div class="header-left">
          <h2>Mis Plantillas</h2>
          <span class="count">{{ templates.length }}</span>
        </div>
        
        <div class="filters">
          <select v-model="sortBy" class="select">
            <option value="updated_at">Recientes</option>
            <option value="created_at">Más antiguas</option>
            <option value="title">A-Z</option>
          </select>
          
          <select v-model="filterStatus" class="select">
            <option value="all">Todas</option>
            <option value="draft">Borradores</option>
            <option value="published">Publicadas</option>
          </select>
        </div>
      </div>

      <!-- Grid de plantillas -->
      <div class="templates-grid">
        <div 
          v-for="template in filteredAndSortedTemplates" 
          :key="template.id" 
          class="template-card"
        >
          <!-- Imagen/Preview -->
          <div class="template-image">
            <img 
              v-if="template.template && template.template.thumbnail" 
              :src="template.template.thumbnail" 
              :alt="template.title"
            />
            <div v-else class="placeholder">
              <i class="fas fa-file-alt"></i>
            </div>
          </div>

          <!-- Contenido -->
          <div class="template-content">
            <div class="template-header">
              <h3 class="template-title">{{ template.title }}</h3>
              <span 
                class="status"
                :class="{ published: template.status === 'published' }"
              >
                {{ template.status === 'published' ? 'Publicada' : 'Borrador' }}
              </span>
            </div>

            <p class="template-base">{{ template.template ? template.template.name : 'Plantilla base' }}</p>

            <!-- Link público si está publicada -->
            <div v-if="template.status === 'published' && template.slug" class="public-link">
              <a :href="getPublicUrl(template.slug)" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                Ver página
              </a>
            </div>

            <!-- Información adicional -->
            <div class="template-meta">
              <span class="meta-item">
                <i class="fas fa-calendar"></i>
                {{ formatDate(template.updated_at) }}
              </span>
              <span v-if="getEditedFieldsCount(template) > 0" class="meta-item">
                <i class="fas fa-edit"></i>
                {{ getEditedFieldsCount(template) }} campos
              </span>
            </div>

            <!-- Acciones -->
            <div class="actions">
              <button 
                @click="$emit('load-template', template)"
                class="btn-secondary"
              >
                Editar
              </button>

              <button 
                @click="confirmDelete(template)"
                class="btn-outline btn-delete"
              >
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MyTemplatesTab',
  
  props: {
    templates: {
      type: Array,
      required: true,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      sortBy: 'updated_at',
      filterStatus: 'all',
    };
  },

  computed: {
    filteredAndSortedTemplates() {
      let filtered = this.templates;

      if (this.filterStatus !== 'all') {
        filtered = filtered.filter(template => template.status === this.filterStatus);
      }

      return filtered.sort((a, b) => {
        switch (this.sortBy) {
          case 'title':
            return a.title.localeCompare(b.title);
          case 'created_at':
            return new Date(b.created_at) - new Date(a.created_at);
          case 'updated_at':
          default:
            return new Date(b.updated_at) - new Date(a.updated_at);
        }
      });
    }
  },

  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 1) return 'Ayer';
      if (diffDays < 7) return `Hace ${diffDays} días`;
      
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short'
      });
    },

    getEditedFieldsCount(template) {
      if (!template.edited_fields) return 0;
      
      try {
        const fields = JSON.parse(template.edited_fields);
        return Object.keys(fields).length;
      } catch (error) {
        return 0;
      }
    },

    getPublicUrl(slug) {
      return `${window.location.origin}/pages/${slug}`;
    },

    openPublishedPage(slug) {
      const url = this.getPublicUrl(slug);
      window.open(url, '_blank');
    },

    confirmDelete(template) {
      if (confirm(`¿Eliminar "${template.title}"?`)) {
        this.$emit('delete-template', template.id);
      }
    },

    duplicateTemplate(template) {
      this.$emit('duplicate-template', template);
    }
  }
};
</script>

<style scoped>
.my-templates-tab {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 0;
  color: #666;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #666;
}

.empty-icon {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #333;
}

.empty-state p {
  margin-bottom: 2rem;
  font-size: 1rem;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-left h2 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
}

.count {
  background: #f8f9fa;
  color: #666;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
}

.filters {
  display: flex;
  gap: 0.75rem;
}

.select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  font-size: 0.875rem;
  color: #666;
  cursor: pointer;
  transition: border-color 0.2s;
}

.select:hover,
.select:focus {
  outline: none;
  border-color: #007bff;
}

/* Templates Grid */
.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.template-card {
  background: white;
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.template-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: #ddd;
}

/* Template Image */
.template-image {
  position: relative;
  height: 160px;
  overflow: hidden;
  background: #f8f9fa;
}

.template-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #ccc;
  font-size: 2.5rem;
}

/* Template Content */
.template-content {
  padding: 1.25rem;
}

.template-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.template-title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #333;
  line-height: 1.3;
  flex: 1;
}

.status {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  background: #f8f9fa;
  color: #666;
  white-space: nowrap;
}

.status.published {
  background: #d4edda;
  color: #155724;
}

.template-base {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  color: #666;
}

/* Public Link */
.public-link {
  margin-bottom: 1rem;
}

.public-link a {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #28a745;
  text-decoration: none;
  font-weight: 500;
}

.public-link a:hover {
  text-decoration: underline;
}

/* Template Meta */
.template-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.25rem;
  font-size: 0.875rem;
  color: #666;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.meta-item i {
  font-size: 0.75rem;
}

/* Actions */
.actions {
  display: flex;
  gap: 0.5rem;
}
.actions button {
  flex: 1;
}

.btn-primary,
.btn-secondary,
.btn-outline {
  padding: 0.5rem 1rem;
  border: 1px solid;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #007bff;
  border-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  border-color: #0056b3;
}

.btn-secondary {
  background: #6c757d;
  border-color: #6c757d;
  color: white;
  flex: 1;
}

.btn-secondary:hover {
  background: #545b62;
  border-color: #545b62;
}

.btn-outline {
  background: transparent;
  border-color: #ddd;
  color: #666;
  padding: 0.5rem;
}

.btn-outline:hover {
  background: #f8f9fa;
  border-color: #007bff;
  color: #007bff;
}

.btn-delete:hover {
  background: #f8f9fa;
  border-color: #dc3545;
  color: #dc3545;
}

.actions .btn-outline {
  width: auto;  /* Elimina el ancho fijo */
}

/* Responsive */
@media (max-width: 768px) {
  .my-templates-tab {
    padding: 1rem;
  }

  .header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-left {
    justify-content: center;
    text-align: center;
  }

  .filters {
    justify-content: center;
  }

  .templates-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .template-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }

  .status {
    align-self: flex-start;
  }

  .template-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  
}

@media (max-width: 480px) {
  .actions {
    flex-wrap: wrap;
  }

  .btn-secondary {
    flex: none;
    width: 100%;
    margin-bottom: 0.5rem;
  }
}
</style>