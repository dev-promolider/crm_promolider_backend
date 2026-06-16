<template>
  <div class="editor-view">
    <!-- Header del Editor -->
    <editor-header
      :selected-template="selectedTemplate"
      :has-unsaved-changes="hasUnsavedChanges"
      :saved-template-id="savedTemplateId"
      :show-preview="showPreview"
      :auto-save-enabled="autoSaveEnabled"
      :is-saving="isSaving"
      :published-url="publishedUrl"
      :show-url-modal="showUrlModal"
      :is-published="isPublished"
      @back-to-gallery="$emit('back-to-gallery')"
      @refresh-templates="$emit('refresh-templates')"
      @toggle-preview="$emit('toggle-preview', $event)"
      @toggle-auto-save="$emit('toggle-auto-save', $event)"
      @change-preview-device="$emit('change-preview-device', $event)"
      @open-full-preview="$emit('open-full-preview')"
      @save-template="$emit('save-template')"
      @update-template="$emit('update-template')"
      @publish-template="$emit('publish-template')"
      @copy-url="$emit('copy-url')"
      @open-published="$emit('open-published')"
      @close-url-modal="$emit('close-url-modal')"
    />

    <!-- Información de la Plantilla -->
    <div class="card mb-3 mx-4">
      <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
          <i class="fas fa-tag mr-2"></i>
          Información de la Plantilla
        </h6>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="font-weight-semibold text-dark mb-2">
            <i class="fas fa-signature mr-2"></i>
            Nombre de la Plantilla
          </label>
          <input
            :value="templateTitle"
            @input="$emit('update-title', $event.target.value)"
            type="text"
            class="form-control"
            placeholder="Ej: Mi Landing Page Personal"
            maxlength="255"
          />
          <small class="text-muted">
            Este será el nombre con el que se guardará tu plantilla personalizada
          </small>
        </div>
      </div>
    </div>

    <!-- Contenido del Editor con Vista Dividida -->
    <div class="editor-container">
      <!-- Panel de Edición -->
      <content-editor
        :edited-content="editedContent"
        :selected-template="selectedTemplate"
        :show-preview="showPreview"
        @update-content="$emit('update-content', $event)"
      />

      <!-- Panel de Preview en Tiempo Real -->
      <live-preview
        v-if="showPreview"
        :preview-device="previewDevice"
        :live-preview-content="generatePreview()"
        @change-device="$emit('change-preview-device', $event)"
      />
    </div>
  </div>
</template>

<script>
import EditorHeader from './EditorHeader.vue';
import ContentEditor from './ContentEditor.vue';
import LivePreview from './LivePreview.vue';

export default {
  name: 'EditorView',
  
  components: {
    EditorHeader,
    ContentEditor,
    LivePreview,
  },

  props: {
    selectedTemplate: {
      type: Object,
      required: true
    },
    editedContent: {
      type: Object,
      default: () => ({})
    },
    templateTitle: {
      type: String,
      default: ''
    },
    hasUnsavedChanges: {
      type: Boolean,
      default: false
    },
    savedTemplateId: {
      type: [String, Number],
      default: null
    },
    isSaving: {
      type: Boolean,
      default: false
    },
    showPreview: {
      type: Boolean,
      default: true
    },
    previewDevice: {
      type: String,
      default: 'desktop'
    },
    autoSaveEnabled: {
      type: Boolean,
      default: true
    },
    publishedUrl: {
      type: String,
      default: null
    },
    showUrlModal: {
      type: Boolean,
      default: false
    },
    isPublished: {
      type: Boolean,
      default: false
    }
  },

  emits: [
    'back-to-gallery',
    'refresh-templates',
    'update-content',
    'update-title',
    'toggle-preview',
    'toggle-auto-save',
    'change-preview-device',
    'open-full-preview',
    'save-template',
    'update-template',
    'publish-template',
    'copy-url',
    'open-published',
    'close-url-modal'
  ],

  methods: {
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
    }
  }
};
</script>

<style scoped>
.editor-view {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.editor-container {
  display: flex;
  height: calc(100vh - 200px);
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.font-weight-semibold {
  font-weight: 600;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 992px) {
  .editor-container {
    flex-direction: column;
  }
}
</style>