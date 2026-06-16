<template>
  <div :class="['message', `message-${message.role}`]">
    
    <div v-if="message.role === 'user'" class="user-message-wrapper">
      <div class="user-bubble">
        {{ message.content }}
      </div>
      </div>

    <div v-else class="ai-message-wrapper">
      <div class="ai-avatar">
        <img 
          src="/images/LiderBot-icon.png"
          class="avatar-image" 
          alt="Bot"
        />
      </div>

      <div class="ai-content">
        <div class="ai-bubble">
          <div v-if="message.content === 'Pensando...'" class="thinking-wrapper">
            <span class="thinking-text">Analizando tu consulta</span>
            <span class="thinking-dots">
              <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </span>
          </div>

          <div v-else v-html="renderedContent" class="markdown-content"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MarkdownIt from 'markdown-it';

// Configuración de Markdown
const md = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
  breaks: true
});

export default {
  name: 'ChatMessage',
  props: {
    message: {
      type: Object,
      required: true
    }
  },
  computed: {
    renderedContent() {
      return md.render(this.message.content || '');
    }
  }
};
</script>

<style scoped>
/* --- CONFIGURACIÓN GENERAL --- */
.message {
  display: flex;
  width: 100%;
  margin-bottom: 20px;
  animation: slideInUp 0.3s ease;
}

.message-user {
  justify-content: flex-end;
}

.message-pickle {
  justify-content: flex-start;
}

@keyframes slideInUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* --- ESTILOS DEL USUARIO (TÚ) --- */
.user-message-wrapper {
  position: relative;
  max-width: 75%;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.user-bubble {
  /* CAMBIO: Color verde solicitado */
  background: #20e404;
  color: #ffffff; /* Texto blanco */
  
  padding: 14px 18px;
  border-radius: 18px 18px 2px 18px; 
  font-size: 0.95rem;
  line-height: 1.5;
  
  /* Sombra ajustada al color verde */
  box-shadow: 0 4px 15px rgba(32, 228, 4, 0.25);
  
  word-wrap: break-word;
  text-align: left;
}

/* --- ESTILOS DE LA IA (PICKLE/BOT) --- */
.ai-message-wrapper {
  display: flex;
  gap: 16px;
  max-width: 85%;
}

.ai-avatar {
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  /* Fondo blanco para el avatar en modo claro */
  background: #20e404;
  padding: 2px;
  border: 1px solid #20e404;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: contain; /* Cambiado a contain para que no se recorte si tiene fondo */
  border-radius: 8px;
}

.ai-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.ai-bubble {
  /* CAMBIO: Color gris muy claro solicitado */
  background: #f9fafb;
  /* CAMBIO: Texto negro */
  color: #000000;
  
  /* Borde sutil gris para separar del fondo blanco general */
  border: 1px solid #e5e7eb;
  padding: 16px;
  
  border-radius: 2px 18px 18px 18px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  font-size: 0.95rem;
  line-height: 1.6;
}

/* --- ANIMACIÓN PENSANDO --- */
.thinking-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  /* Color gris medio para que se vea sobre el fondo claro */
  color: #666666; 
  font-size: 0.9rem;
  font-style: italic;
}

.thinking-dots .dot {
  display: inline-block;
  width: 5px;
  height: 5px;
  background: #666666;
  border-radius: 50%;
  margin-right: 3px;
  animation: bounce 1.4s infinite ease-in-out both;
}

.thinking-dots .dot:nth-child(1) { animation-delay: -0.32s; }
.thinking-dots .dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

/* --- ESTILOS MARKDOWN (ADAPTADO A FONDO CLARO) --- */
.markdown-content >>> p {
  margin-bottom: 12px;
  color: #1f2937; /* Gris muy oscuro casi negro */
}
.markdown-content >>> p:last-child {
  margin-bottom: 0;
}
.markdown-content >>> strong {
  color: #000000; /* Negritas en negro puro */
  font-weight: 700;
}
.markdown-content >>> ul, .markdown-content >>> ol {
  padding-left: 20px;
  margin-bottom: 12px;
  color: #1f2937;
}
.markdown-content >>> li {
  margin-bottom: 4px;
}

/* Bloques de código */
.markdown-content >>> pre {
  background: #1a1d2e; /* Mantenemos fondo oscuro para código para contraste */
  padding: 12px;
  border-radius: 8px;
  overflow-x: auto;
  margin: 10px 0;
}
.markdown-content >>> code {
  font-family: 'Courier New', monospace;
  /* Código inline con fondo gris suave */
  background: rgba(0, 0, 0, 0.05);
  padding: 2px 4px;
  border-radius: 4px;
  color: #d63384; /* Color rosado/rojo típico de código */
}
.markdown-content >>> pre code {
  /* El código dentro del bloque PRE usa colores claros */
  background: transparent;
  padding: 0;
  color: #e0e0e0; 
}

/* Títulos en el mensaje del bot */
.markdown-content >>> h1, 
.markdown-content >>> h2, 
.markdown-content >>> h3 {
  color: #000000;
  margin-top: 16px;
  margin-bottom: 8px;
  font-weight: 700;
}

/* Links */
.markdown-content >>> a {
  color: #2563eb;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .user-bubble { max-width: 85%; }
  .ai-message-wrapper { max-width: 95%; }
}
</style>