<template>
  <div class="template-gallery">
    <!-- Vista de Galería -->
    <gallery-view 
      v-if="currentView === 'gallery'"
      :current-tab="currentTab"
      :templates="templates"
      :my-templates="myTemplates"
      :initial-loading="initialLoading"
      :loading-my-templates="loadingMyTemplates"
      @select-template="selectTemplate"
      @load-template="loadMyTemplate"
      @delete-template="deleteMyTemplate"
      @tab-change="handleTabChange"
    />

    <!-- Vista de Editor -->
    <editor-view
      v-else-if="currentView === 'editor'"
      :selected-template="selectedTemplate"
      :edited-content="editedContent"
      :template-title="templateTitle"
      :has-unsaved-changes="hasUnsavedChanges"
      :saved-template-id="savedTemplateId"
      :is-saving="isSaving"
      :show-preview="showPreview"
      :preview-device="previewDevice"
      :auto-save-enabled="autoSaveEnabled"
      :published-url="publishedUrl"
      :show-url-modal="showUrlModal"
      :is-published="isTemplatePublished()"
      @back-to-gallery="currentView = 'gallery'"
      @refresh-templates="refreshAllTemplates"
      @update-content="updateEditedContent"
      @update-title="updateTemplateTitle"
      @toggle-preview="showPreview = $event"
      @toggle-auto-save="autoSaveEnabled = $event"
      @change-preview-device="previewDevice = $event"
      @open-full-preview="currentView = 'preview'"
      @save-template="savePage"
      @update-template="updateExistingTemplate"
      @publish-template="publishPage"
      @copy-url="copyUrlToClipboard"
      @open-published="openPublishedPage"
      @close-url-modal="showUrlModal = false"
    />

    <!-- Vista de Preview Completa -->
    <preview-view
      v-else-if="currentView === 'preview'"
      :live-preview-content="livePreviewContent"
      @back-to-editor="currentView = 'editor'"
      @save-template="savePage"
    />
  </div>
</template>

<script>
import axios from 'axios';
import GalleryView from './GalleryView.vue';
import EditorView from './EditorView.vue';
import PreviewView from './PreviewView.vue';

export default {
  name: 'TemplateGallery',
  
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  
  components: {
    GalleryView,
    EditorView,
    PreviewView,
  },

  data() {
    return {
      currentView: 'gallery', // 'gallery', 'editor', 'preview'
      selectedTemplate: null,
      editedContent: {},
      initialLoading: false,
      showPreview: true,
      previewDevice: 'desktop',
      livePreviewContent: '',
      updateTimeout: null,
      templates: [],
      savedTemplateId: null,
      isSaving: false,
      hasUnsavedChanges: false,
      autoSaveEnabled: false,
      autoSaveTimeout: null,
      myTemplates: [],
      loadingMyTemplates: false,
      currentTab: 'templates',
      publishedUrl: null,
      showUrlModal: false,
      templateTitle: '',
    };
  },

  watch: {
    previewDevice() {
      this.updatePreview();
    },

    templateTitle() {
      this.hasUnsavedChanges = true;

      if (this.autoSaveEnabled && this.savedTemplateId) {
        clearTimeout(this.autoSaveTimeout);
        this.autoSaveTimeout = setTimeout(() => {
          this.updateExistingTemplate();
        }, 3000);
      }
    },

    showPreview(newVal) {
      if (newVal) {
        this.$nextTick(() => {
          this.updatePreview();
        });
      }
    },

    editedContent: {
      handler(newVal, oldVal) {
        if (oldVal && Object.keys(oldVal).length > 0) {
          this.hasUnsavedChanges = true;

          if (this.autoSaveEnabled && this.savedTemplateId) {
            clearTimeout(this.autoSaveTimeout);
            this.autoSaveTimeout = setTimeout(() => {
              this.updateExistingTemplate();
            }, 3000);
          }
        }
      },
      deep: true
    },

    autoSaveEnabled(newVal) {
      if (!newVal) {
        clearTimeout(this.autoSaveTimeout);
      }
    },

    myTemplates: {
      handler(newTemplates, oldTemplates) {
        if (this.savedTemplateId && oldTemplates && newTemplates) {
          const oldTemplate = oldTemplates.find(t => t.id === this.savedTemplateId);
          const newTemplate = newTemplates.find(t => t.id === this.savedTemplateId);

          // Si cambió de published a draft, mostrar mensaje
          if (oldTemplate && newTemplate && 
              oldTemplate.status === 'published' && 
              newTemplate.status === 'draft') {
            console.log('La plantilla cambió de publicada a borrador - botón de publicar habilitado');
          }
        }
      },
      deep: true
    }
  },

  mounted() {
    this.fetchTemplates();
    this.fetchMyTemplates(); // Agregar esta línea
  },

  methods: {
    async fetchTemplates() {
      try {
        this.initialLoading = true;
        const response = await axios.get('/api/v1/templates');
        if (response.status === 200) {
          this.templates = response.data.data;
        }
      } catch (error) {
        console.error('Error al obtener las plantillas:', error);
      } finally {
        this.initialLoading = false;
      }
    },

    // Nuevo método para refrescar todas las plantillas
    async refreshAllTemplates() {
      try {
        // Recargar plantillas base
        await this.fetchTemplates();

        // Recargar mis plantillas
        await this.fetchMyTemplates();

        console.log('Plantillas actualizadas exitosamente');
      } catch (error) {
        console.error('Error al actualizar plantillas:', error);
      }
    },

    async fetchMyTemplates() {
      try {
        this.loadingMyTemplates = true;
        const response = await axios.get(`/marketing/pages/user/${this.user.id}/edit-templates`);
        if (response.status === 200) {
          this.myTemplates = response.data.data;
        }
      } catch (error) {
        if (error.response && error.response.status === 404) {
          this.myTemplates = [];
        } else {
          console.error('Error al obtener mis plantillas:', error);
        }
      } finally {
        this.loadingMyTemplates = false;
      }
    },

    handleTabChange(tab) {
      this.currentTab = tab;
    },

    selectTemplate(template) {
      this.selectedTemplate = this.cleanTemplateData(template);
      this.extractEditableContent(this.selectedTemplate);
      this.templateTitle = `Mi ${template.name} personalizada`;
      this.currentView = 'editor';
      this.savedTemplateId = null;
      this.hasUnsavedChanges = false;

      this.$nextTick(() => {
        this.updatePreview();
      });
    },

    loadMyTemplate(template) {
      this.selectedTemplate = template.template;
      this.savedTemplateId = template.id;
      this.templateTitle = template.title;

      if (template.edited_fields) {
        try {
          this.editedContent = JSON.parse(template.edited_fields);
        } catch (error) {
          console.error('Error al parsear campos editados:', error);
          this.extractEditableContent(template.template);
        }
      } else {
        this.extractEditableContent(template.template);
      }

      this.currentView = 'editor';
      this.hasUnsavedChanges = false;

      this.$nextTick(() => {
        this.updatePreview();
      });
    },

    async deleteMyTemplate(templateId) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta plantilla?')) {
        return;
      }
    
      try {
        const response = await axios.delete(`/marketing/pages/edit-templates/${templateId}`);
        if (response.status === 200) {
          console.log('Plantilla eliminada exitosamente');
          this.fetchMyTemplates(); // Ya tienes esto, perfecto
        }
      } catch (error) {
        console.error('Error al eliminar plantilla:', error);
      }
    },

    updateEditedContent(content) {
      this.editedContent = { ...content };
      this.updatePreview();
    },

    updateTemplateTitle(title) {
      this.templateTitle = title;
    },

    extractEditableContent(template) {
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = template.content_html;

      const editableElements = tempDiv.querySelectorAll('[data-editable="true"]');

      const content = {};
      editableElements.forEach(el => {
        const field = el.getAttribute('data-field');
        if (field) {
          let textContent = el.textContent || el.innerText || '';
          textContent = textContent.replace(/\r\n/g, ' ').replace(/\s+/g, ' ').trim();
          content[field] = textContent;
        }
      });

      this.editedContent = content;
    },

    cleanTemplateData(template) {
      if (!template.content_html) return template;

      template.content_html = template.content_html
        .replace(/\r\n/g, '\n')
        .replace(/\n\s+/g, '\n')
        .replace(/\s+/g, ' ')
        .replace(/>\s+</g, '><');

      return template;
    },

    generatePreview() {
      if (!this.selectedTemplate) return '';

      try {
        let html = this.selectedTemplate.content_html;

        html = html.replace(/\\r\\n/g, ' ')
                  .replace(/\r\n/g, ' ')
                  .replace(/\s+/g, ' ')
                  .replace(/>\s+</g, '><');

        Object.keys(this.editedContent).forEach(field => {
          if (this.editedContent[field]) {
            const value = this.editedContent[field]
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;');

            const fieldRegex = new RegExp(
              `(<[^>]*data-field="${field}"[^>]*>)[^<]*(<\/[^>]*>|>)`,
              'gi'
            );

            html = html.replace(fieldRegex, (match, openTag, closeTag) => {
              if (closeTag === '>') {
                return openTag.replace(/placeholder="[^"]*"/, `placeholder="${value}"`);
              } else {
                return openTag + value + closeTag;
              }
            });
          }
        });

        const templateStyles = this.selectedTemplate.styles_css || this.getDefaultStyles();

        return `<!DOCTYPE html>
          <html lang="es">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>${this.selectedTemplate.name}</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
              ${templateStyles}
            </style>
          </head>
          <body>
            ${html}
          </body>
          </html>`;

      } catch (error) {
        console.error('Error al generar preview:', error);
        return this.getErrorTemplate();
      }
    },

    getDefaultStyles() {
      return `
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          line-height: 1.6;
          color: #333;
          background: #ffffff;
        }

        .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 20px;
        }

        h1, h2, h3, h4, h5, h6 {
          margin-bottom: 1rem;
          font-weight: 600;
        }

        p {
          margin-bottom: 1rem;
        }

        button {
          padding: 10px 20px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 1rem;
          transition: all 0.3s ease;
        }

        input, textarea, select {
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 5px;
          font-size: 1rem;
          width: 100%;
          margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
          .container {
            padding: 0 15px;
          }
        }
      `;
    },

    getErrorTemplate() {
      return `
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Error</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              display: flex;
              justify-content: center;
              align-items: center;
              min-height: 100vh;
              margin: 0;
              background: #f8f9fa;
            }
            .error-container {
              text-align: center;
              padding: 2rem;
              background: white;
              border-radius: 10px;
              box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
              max-width: 500px;
            }
            .error-icon {
              font-size: 3rem;
              color: #dc3545;
              margin-bottom: 1rem;
            }
            .error-title {
              color: #dc3545;
              margin-bottom: 1rem;
            }
            .error-message {
              color: #6c757d;
            }
          </style>
        </head>
        <body>
          <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h2 class="error-title">Error al cargar la plantilla</h2>
            <p class="error-message">Por favor, verifica la configuración de la plantilla</p>
          </div>
        </body>
        </html>
      `;
    },

    updatePreview() {
      clearTimeout(this.updateTimeout);
      this.updateTimeout = setTimeout(() => {
        try {
          this.livePreviewContent = this.generatePreview();
        } catch (error) {
          console.error('Error al generar preview:', error);
        }
      }, 150);
    },

    async savePage() {
      try {
        this.isSaving = true;
      
        const pageData = {
          user_id: this.user.id,
          template_id: this.selectedTemplate.id,
          title: this.templateTitle || 
                 this.editedContent.hero_title || 
                 this.editedContent.main_headline || 
                 this.editedContent.title || 
                 'Nueva Página Personalizada',
          content_html: this.generatePreview(),
          edited_fields: JSON.stringify(this.editedContent),
          status: 'draft'
        };
      
        const response = await axios.post('/marketing/pages/create/edit-template', pageData);
      
        if (response.status === 201) {
          console.log('Plantilla guardada exitosamente como borrador');
          this.savedTemplateId = response.data.data.id;
          this.hasUnsavedChanges = false;

          // Recargar myTemplates para actualizar el contador
          this.fetchMyTemplates();
        }
      
      } catch (error) {
        console.error('Error al guardar la plantilla:', error);
      } finally {
        this.isSaving = false;
      }
    },

    async updateExistingTemplate() {
      if (!this.savedTemplateId) {
        return this.savePage();
      }
    
      try {
        const pageData = {
          title: this.templateTitle || 
                 this.editedContent.hero_title || 
                 this.editedContent.main_headline || 
                 this.editedContent.title || 
                 'Nueva Página Personalizada',
          content_html: this.generatePreview(),
          edited_fields: JSON.stringify(this.editedContent),
          // ✅ CAMBIO CLAVE: Siempre poner como draft cuando se actualiza
          status: 'draft'
        };
      
        const response = await axios.put(`/marketing/pages/edit-templates/${this.savedTemplateId}`, pageData);
      
        if (response.status === 200) {
          console.log('Plantilla actualizada exitosamente - Estado cambiado a borrador');
          this.hasUnsavedChanges = false;

          // ✅ NUEVO: Refrescar la lista de plantillas para actualizar el estado
          await this.fetchMyTemplates();

          // Si tenía URL pública, la limpiamos porque ya no está publicada
          if (this.publishedUrl) {
            this.publishedUrl = null;
            console.log('URL pública eliminada - la plantilla ya no está publicada');
          }
        }
      
      } catch (error) {
        console.error('Error al actualizar la plantilla:', error);
      }
    },

    isTemplatePublished() {
      if (!this.savedTemplateId || !this.myTemplates.length) {
        return false;
      }
      
      const currentTemplate = this.myTemplates.find(t => t.id === this.savedTemplateId);
      const isPublished = currentTemplate && currentTemplate.status === 'published';
      
      // Si la plantilla ya no está publicada, limpiar la URL
      if (!isPublished && this.publishedUrl) {
        this.publishedUrl = null;
      }
      
      return isPublished;
    },

    async publishPage() {
      if (!this.savedTemplateId) {
        await this.savePage();
      }

      try {
        this.isSaving = true;

        const response = await axios.put(`/marketing/pages/edit-templates/${this.savedTemplateId}`, {
          title: this.templateTitle || 
                 this.editedContent.hero_title || 
                 this.editedContent.main_headline || 
                 this.editedContent.title || 
                 'Nueva Página Personalizada',
          content_html: this.generatePreview(),
          edited_fields: JSON.stringify(this.editedContent),
          status: 'published'
        });

        if (response.status === 200) {
          console.log('Plantilla publicada exitosamente');

          if (response.data.public_url) {
            this.publishedUrl = response.data.public_url;
            this.showUrlModal = true;
          }

          this.hasUnsavedChanges = false;
          
          // ✅ NUEVO: Refrescar plantillas y volver a la galería después de publicar
          await this.refreshAllTemplates();
          
          // Pequeño delay para que el usuario vea el modal si hay URL
          if (response.data.public_url) {
            setTimeout(() => {
              this.currentView = 'gallery';
            }, 2000); // 2 segundos para ver el modal
          } else {
            this.currentView = 'gallery';
          }
        }

      } catch (error) {
        console.error('Error al publicar la plantilla:', error);
      } finally {
        this.isSaving = false;
      }
    },

    async copyUrlToClipboard() {
      try {
        await navigator.clipboard.writeText(this.publishedUrl);
        console.log('URL copiada al portapapeles');
      } catch (error) {
        console.error('Error al copiar URL:', error);
      }
    },

    openPublishedPage() {
      if (this.publishedUrl) {
        window.open(this.publishedUrl, '_blank');
      }
    }
  }
};
</script>

<style scoped>
.template-gallery {
  min-height: 100vh;
}
</style>