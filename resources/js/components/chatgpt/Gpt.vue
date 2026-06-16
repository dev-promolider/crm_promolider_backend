<template>
  <div class="chat-container">
    <header class="chat-header">
      <div class="header-content">
        <div class="logo-section">
          <div class="logo-badge">
            <img 
              src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/avatar-s-11.png" 
              alt="Pickle" 
              class="logo-image"
            />
          </div>
          <!-- Usamos el computed authUserId para mostrar el ID sin importar cómo se pasó -->
          <h1 class="logo-text">LiderBot </h1>
        </div>
        
        <div class="header-actions">
          <button
            @click="toggleSidebar"
            class="btn-toggle-sidebar"
            :title="sidebarOpen ? 'Cerrar historial' : 'Abrir historial'"
          >
            <menu-icon size="24" />
          </button>
        </div>
      </div>
    </header>

    <div :class="['chat-layout', { 'sidebar-open': sidebarOpen }]">
      <div class="chat-main">
        <main class="messages-container" ref="messagesContainer">
          <div class="messages-wrapper">
            <div v-if="currentMessages.length === 0" class="empty-state">
              <div class="empty-icon">🤖</div>
              <h2>Hola, ¿en qué puedo ayudarte?</h2>
              <p>Hazme cualquier pregunta sobre nuestros cursos digitales</p>
            </div>

            <div v-else class="messages-list">
              <chat-message
                v-for="msg in currentMessages"
                :key="msg.id"
                :message="msg"
              />
            </div>

            <PDFGenerator 
              v-if="showPDFButton && courseDataForPDF"
              :courseData="courseDataForPDF"
              :showButton="true"
            />

            <div ref="messagesEnd"></div>
          </div>
        </main>

        <chat-input @send="handleSendMessage" :isLoading="isLoading" />
      </div>

      <transition name="slide-right">
        <chat-sidebar 
          v-if="sidebarOpen"
          :isOpen="sidebarOpen"
          :chats="chats"
          :currentChatId="currentChatId"
          @create-new-chat="createNewChat"
          @select-chat="loadChat"
          @delete-chat="deleteChat"
          @rename-chat="renameChat"
          @close="toggleSidebar"
        />
      </transition>
    </div>

    <transition name="fade">
      <div
        v-if="sidebarOpen && isMobile"
        class="sidebar-overlay"
        @click="toggleSidebar"
      ></div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import ChatMessage from './ChatMessage.vue';
import ChatInput from './ChatInput.vue';
import ChatSidebar from './ChatSidebar.vue';
import PDFGenerator from './PDFGenerator.vue';
import { MenuIcon } from 'vue-feather-icons';

const AGENTE_API = 'https://agente.picklechatbot.promolider.org';

export default {
  name: 'Gpt',
  components: {
    ChatMessage,
    ChatInput,
    ChatSidebar,
    PDFGenerator,
    MenuIcon
  },

  props: {
    // Aceptamos ambas convenciones para evitar errores de "Missing required prop"
    userId: {
      type: [String, Number],
      default: null
    },
    // Soporte legacy para cuando se pasa como snake_case desde Blade (user_id="...")
    user_id: {
      type: [String, Number],
      default: null
    }
  },

  data() {
    return {
      sidebarOpen: false,
      isLoading: false,
      isMobile: false,
      showPDFButton: false,
      courseDataForPDF: null,
      chats: [],
      currentMessages: [],
      currentChatId: null,
    };
  },

  computed: {
    // Propiedad computada para normalizar el ID
    authUserId() {
      return this.userId || this.user_id;
    }
  },

  watch: {
    currentMessages: {
      handler(messages) {
        this.checkForPDFReady(messages);
        this.$nextTick(() => this.scrollToBottom());
      },
      deep: true
    },

    isLoading(newVal, oldVal) {
      if (oldVal === true && newVal === false) {
        setTimeout(() => {
          this.checkForPDFReady(this.currentMessages);
        }, 100);
      }
    },

    currentChatId(newChatId, oldChatId) {
      if (newChatId !== oldChatId) {
        this.showPDFButton = false;
        this.courseDataForPDF = null;
      }
    }
  },

  async mounted() {
    console.log("Usuario autenticado ID:", this.authUserId); 
    
    // Verificación de seguridad básica
    if (!this.authUserId) {
      console.warn("ADVERTENCIA: No se recibió User ID. El chat podría no funcionar correctamente.");
    }

    await this.loadChatsFromAPI();
    window.addEventListener('resize', this.handleResize);
    this.handleResize();
  },

  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  },

  methods: {
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    async scrollToBottom() {
      await this.$nextTick();
      if (this.$refs.messagesContainer) {
        this.$refs.messagesContainer.scrollTop =
          this.$refs.messagesContainer.scrollHeight;
      }
    },

    handleResize() {
      this.isMobile = window.innerWidth < 768;
    },

    async loadChatsFromAPI() {
      if (!this.authUserId) return;

      try {
        const response = await axios.get(`${AGENTE_API}/chats`, {
          params: { user_id: this.authUserId } 
        });

        this.chats = response.data || [];

        if (this.chats.length === 0) {
          await this.createNewChat();
        } else {
          await this.loadChat(this.chats[0].id);
        }
      } catch (error) {
        console.error('Error al cargar chats:', error);
        await this.createNewChat();
      }
    },

    async createNewChat() {
      if (!this.authUserId) return;

      try {
        const response = await axios.post(`${AGENTE_API}/chats`, {
          user_id: this.authUserId, 
          title: 'Nueva conversación'
        });

        const newChat = response.data;
        this.chats.unshift(newChat);
        this.currentChatId = newChat.id;
        this.currentMessages = [];

        const welcomeMsg = {
          id: `welcome-${newChat.id}`,
          role: 'pickle',
          content: `¡Hola! Soy **LiderBot** 🥒, tu asesor especialista en cursos digitales.\n\nAntes de empezar, cuéntame:\n\n**¿En qué eres bueno o cuál es tu especialidad?**\n(Ejemplo: contabilidad, desarrollo web, diseño gráfico, marketing digital, fitness, yoga, relaciones personales, etc.)`,
          sources: []
        };

        this.currentMessages.push(welcomeMsg);

        await axios.post(`${AGENTE_API}/chats/${newChat.id}/messages`, {
          chat_id: newChat.id,
          role: 'pickle',
          content: welcomeMsg.content,
          sources: []
        });

      } catch (error) {
        console.error('Error al crear chat:', error);
      }
    },

    async loadChat(chatId) {
      try {
        const response = await axios.get(`${AGENTE_API}/chats/${chatId}/messages`);
        
        this.currentChatId = chatId;
        this.currentMessages = response.data.map((msg, index) => ({
          id: msg.id || `msg-${chatId}-${index}`,
          role: msg.role,
          content: msg.content,
          sources: msg.sources || []
        }));

        await this.scrollToBottom();
      } catch (error) {
        console.error('Error al cargar mensajes:', error);
      }
    },

    async handleSendMessage(message) {
      if (!message.trim() || !this.currentChatId) return;

      this.isLoading = true;

      try {
        const userMsg = {
          id: `user-${Date.now()}`,
          role: 'user',
          content: message.trim(),
          sources: []
        };

        this.currentMessages.push(userMsg);

        await axios.post(`${AGENTE_API}/chats/${this.currentChatId}/messages`, {
          chat_id: this.currentChatId,
          role: 'user',
          content: message.trim(),
          sources: []
        });

        const thinkingMsg = {
          id: `thinking-${Date.now()}`,
          role: 'pickle',
          content: 'Pensando...',
          sources: []
        };
        this.currentMessages.push(thinkingMsg);

        await this.scrollToBottom();

        const history = this.currentMessages
          .filter(m => m.content !== 'Pensando...')
          .map(m => ({ role: m.role, content: m.content }));

        const response = await axios.post(`${AGENTE_API}/query`, {
          query: message.trim(),
          namespace: 'global',
          top_k: 4,
          history: history.slice(0, -1)
        });

        this.currentMessages[this.currentMessages.length - 1] = {
          id: `pickle-${Date.now()}`,
          role: 'pickle',
          content: response.data.answer,
          sources: response.data.sources || []
        };

        await axios.post(`${AGENTE_API}/chats/${this.currentChatId}/messages`, {
          chat_id: this.currentChatId,
          role: 'pickle',
          content: response.data.answer,
          sources: response.data.sources || []
        });

        if (this.currentMessages.length === 3) {
          await axios.put(
            `${AGENTE_API}/chats/${this.currentChatId}/title`,
            null,
            { params: { title: message.slice(0, 50) } }
          );
          await this.loadChatsFromAPI();
        }

        await this.scrollToBottom();

      } catch (error) {
        console.error('Error al enviar mensaje:', error);
        
        this.currentMessages[this.currentMessages.length - 1] = {
          id: `error-${Date.now()}`,
          role: 'pickle',
          content: `❌ Error: ${error.response?.data?.message || error.message}`,
          sources: []
        };
      } finally {
        this.isLoading = false;
      }
    },

    async deleteChat(chatId) {
      try {
        await axios.delete(`${AGENTE_API}/chats/${chatId}`);
        
        this.chats = this.chats.filter(c => c.id !== chatId);

        if (this.currentChatId === chatId) {
          this.currentChatId = null;
          this.currentMessages = [];

          if (this.chats.length > 0) {
            await this.loadChat(this.chats[0].id);
          } else {
            await this.createNewChat();
          }
        }
      } catch (error) {
        console.error('Error al eliminar chat:', error);
      }
    },

    async renameChat({ id, newName }) {
      try {
        await axios.put(`${AGENTE_API}/chats/${id}/title`, null, {
          params: { title: newName }
        });

        const chat = this.chats.find(c => c.id === id);
        if (chat) chat.title = newName;
      } catch (error) {
        console.error('Error al renombrar chat:', error);
      }
    },

    checkForPDFReady(messages) {
      if (this.isLoading || !messages || messages.length === 0) {
        this.showPDFButton = false;
        return;
      }

      const assistantMessages = messages.filter(msg => msg.role === 'pickle');
      
      if (assistantMessages.length === 0) {
        this.showPDFButton = false;
        return;
      }

      const lastAssistantMsg = assistantMessages[assistantMessages.length - 1];
      const content = lastAssistantMsg.content || '';
      const hasSummary = content.includes('RESUMEN FINAL DEL CURSO');

      if (hasSummary) {
        this.extractCourseData(content);
        this.showPDFButton = true;
      } else {
        this.showPDFButton = false;
      }
    },

    extractCourseData(content) {
      const courseData = {
        especialidad: 'No especificado',
        nicho: 'No especificado',
        audiencia: 'No especificado',
        problema: 'No especificado',
        solucion: 'No especificado',
        titulo: 'No especificado',
        metodo: 'No especificado',
        nivel: 'Básico',
        descripcion: 'No especificado',
        acerca_del: 'No especificado',
        destinado_a: 'No especificado',
        lo_que_aprenderas: 'No especificado',
        conocimientos_previos: 'No requiere conocimientos previos',
        modulos: []
      };

      const extract = label => {
        const regex = new RegExp(`${label}:\\s*(.+?)(?=\\nPASO|\\nMódulo|\\n##|$)`, 's');
        const match = content.match(regex);
        return match && match[1] ? match[1].trim() : 'No especificado';
      };

      courseData.especialidad = extract('Especialidad');
      courseData.nicho = extract('Nicho');
      courseData.audiencia = extract('Audiencia ideal');
      courseData.problema = extract('Problema específico');
      courseData.solucion = extract('Solución');
      courseData.titulo = extract('Título');
      courseData.metodo = extract('Método');
      courseData.nivel = extract('Nivel') || 'Básico';
      courseData.descripcion = extract('Descripción');
      courseData.acerca_del = extract('Acerca del curso');
      courseData.destinado_a = extract('Curso destinado a');
      courseData.lo_que_aprenderas = extract('Lo que aprenderás');

      const conocimientos = extract('Conocimientos previos');
      courseData.conocimientos_previos = conocimientos !== 'No especificado' 
        ? conocimientos 
        : 'No requiere conocimientos previos';

      const modulosMatch = content.match(/Módulo\s+1:[\s\S]+?(?=RESUMEN FINAL|$)/i);

      if (modulosMatch) {
        const modulosText = modulosMatch[0];
        const modulosSplit = modulosText.split(/Módulo\s+\d+:/i).filter(s => s.trim().length > 0);

        modulosSplit.forEach((moduloText, index) => {
          const numero = index + 1;
          const lineas = moduloText.split(/[\r\n]+/).map(l => l.trim()).filter(l => l.length > 0);
          const nombre = lineas[0] || `Módulo ${numero}`;

          const temasMatch = moduloText.match(/Temas:\s*([\s\S]+?)(?=A quién va dirigido:|$)/i);
          let temas = [];

          if (temasMatch) {
            temas = temasMatch[1]
              .split(/[\r\n]+/)
              .map(t => t.trim())
              .filter(t => t.length > 0)
              .filter(t => !t.toLowerCase().startsWith('a quién') && !t.toLowerCase().startsWith('módulo'))
              .map(t => t.replace(/^[-•·*]\s*/, ''))
              .filter(t => t.length > 3);
          }

          const dirigidoMatch = moduloText.match(/A quién va dirigido:\s*([^\n]+)/i);
          const dirigido = dirigidoMatch ? dirigidoMatch[1].trim() : 'No especificado';

          if (nombre && temas.length > 0) {
            courseData.modulos.push({ nombre, temas, dirigido });
          }
        });
      }

      this.courseDataForPDF = courseData;
    }
  }
};
</script>

<style scoped>
/* --- CONTENEDOR PRINCIPAL --- */
.chat-container {
  display: flex;
  flex-direction: column;
  background: var(--color-bg-primary);
  position: relative;
  
  /* POR DEFECTO (Pantallas grandes): Restamos 14rem */
  height: calc(100vh - 14rem); 
  
  overflow: hidden; 
  border-radius: 0.5rem; 
  box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
}

/* --- MEDIA QUERY: LAPTOPS Y PANTALLAS MEDIANAS (ej. 1366px) --- */
@media (max-width: 1440px) {
  .chat-container {
    /* Aquí restamos MENOS espacio al header del sitio, 
       dando MÁS altura al chat */
    height: calc(100vh - 10rem); 
  }
}

/* --- MEDIA QUERY: TABLETS Y MÓVILES --- */
@media (max-width: 768px) {
  .chat-container {
    /* En móvil queremos aprovechar casi toda la altura.
       Restamos muy poco (solo el header del móvil) */
    height: calc(100vh - 110px); 
    border-radius: 0; /* En móvil se ve mejor sin bordes redondeados */
  }
}

/* --- HEADER DEL CHAT --- */
.chat-header {
  flex-shrink: 0;
  height: 54px;
  background: #f9f9f9;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  z-index: 10; 
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  position: relative;
}

/* --- LAYOUT --- */
.chat-layout {
  display: flex;
  flex: 1; 
  position: relative;
  width: 100%;
  overflow: hidden;
}

/* --- AREA PRINCIPAL --- */
.chat-main {
  display: flex;
  flex-direction: column;
  flex: 1;
  width: 100%;
  height: 100%;
  position: relative;
  transition: margin-right 0.3s ease;
  z-index: 10;
}

.chat-layout.sidebar-open .chat-main {
  margin-right: 320px;
}

/* --- CONTENIDO HEADER --- */
.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 20px; 
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-badge {
  width: 38px; 
  height: 38px;
  background: linear-gradient(135deg, #1f77b4 0%, #1564a0 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(31, 119, 180, 0.3);
}

.logo-image {
  width: 120%;
  height: 120%;
  object-fit: cover;
  border-radius: 8px;
}

.logo-text {
  font-size: 1.25rem; 
  font-weight: 800;
  color: #000000;
  margin: 0;
}

.btn-toggle-sidebar {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.1);
  color: #2d3142;
  padding: 8px; 
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-toggle-sidebar:hover {
  background: #f0f0f0;
  color: #1f77b4;
  border-color: #1f77b4;
}

/* --- MENSAJES CONTAINER --- */
.messages-container {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 24px; /* Padding generoso en PC */
  display: flex;
  flex-direction: column;
  scroll-behavior: smooth;
}

.messages-wrapper {
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: min-content;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-width: 900px;
  margin: 0 auto;
  width: 100%;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 16px;
  text-align: center;
  color: #b5bac1;
}

.empty-icon { font-size: 64px; }
.empty-state h2 { color: #fafbfc; margin: 0; font-size: 1.5rem; }

.sidebar-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 35;
}

/* Transiciones */
.slide-right-enter-active, .slide-right-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-right-enter, .slide-right-leave-to {
  transform: translateX(100%);
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter, .fade-leave-to { opacity: 0; }

.messages-container::-webkit-scrollbar { width: 8px; }
.messages-container::-webkit-scrollbar-track { background: #1a1d2e; }
.messages-container::-webkit-scrollbar-thumb { background: #2d3142; border-radius: 9999px; }

/* --- RESPONSIVE FINAL --- */
@media (max-width: 1024px) {
  .chat-layout.sidebar-open .chat-main { margin-right: 0; }
  .logo-text { font-size: 1.125rem; }
}

@media (max-width: 768px) {
  .logo-text { display: none; }
  
  /* En móvil reducimos el padding interno para ganar espacio horizontal */
  .messages-container { padding: 12px; } 
  
  /* Ajustamos el gap entre mensajes en móvil */
  .messages-list { gap: 16px; } 
}
</style>