<template>
  <aside :class="['sidebar', { open: isOpen }]">
    <div class="sidebar-header">
      <h2 class="sidebar-title">Historial</h2>

      <button @click="$emit('close')" class="btn-close" title="Cerrar">
        <svg width="20" height="20" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-width="2" fill="none" 
            d="M18 6L6 18M6 6l12 12" />
        </svg>
      </button>
    </div>

    <button @click="emitNewChat" class="btn-new-chat">
      <svg width="18" height="18" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-width="2" fill="none" 
          d="M12 5v14M5 12h14"/>
      </svg>
      <span>Nueva conversación</span>
    </button>

    <div class="chat-list">
      <div v-if="chats.length === 0" class="empty-chats">
        <svg width="24" height="24" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-width="2" fill="none"
            d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <p>Sin conversaciones</p>
      </div>

      <div
        v-for="chat in chats"
        :key="chat.id"
        :class="['chat-item', { active: chat.id === currentChatId, 'menu-open': activeMenuId === chat.id }]"
        @click="emitSelectChat(chat.id)"
      >
        <div class="chat-info">
          <svg width="16" height="16" viewBox="0 0 24 24" class="icon-chat">
            <path stroke="currentColor" stroke-width="2" fill="none"
              d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>

          <span class="chat-title" :title="chat.title">
            {{ truncate(chat.title, 25) }}
          </span>
        </div>

        <div class="chat-actions">
          <button
            @click.stop="toggleMenu(chat.id)"
            class="btn-menu"
            title="Opciones"
          >
            <svg width="16" height="16" viewBox="0 0 24 24">
              <circle cx="12" cy="5" r="2" fill="currentColor"/>
              <circle cx="12" cy="12" r="2" fill="currentColor"/>
              <circle cx="12" cy="19" r="2" fill="currentColor"/>
            </svg>
          </button>

          <transition name="menu-fade">
            <div v-if="activeMenuId === chat.id" class="dropdown-menu" @click.stop>
              
              <button @click="openRenameModal(chat)" class="menu-item">
                <svg width="16" height="16" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-width="2" fill="none"
                    d="M12 20h9M16.5 3.5l4 4L7 21l-4 1 1-4z"/>
                </svg>
                <span>Cambiar nombre</span>
              </button>

              <button @click="openDeleteModal(chat)" class="menu-item menu-item-danger">
                <svg width="16" height="16" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-width="2" fill="none"
                    d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                </svg>
                <span>Eliminar</span>
              </button>

            </div>
          </transition>
        </div>
      </div>
    </div>

    <div class="sidebar-footer">
      <p class="footer-text">{{ chats.length }} conversación(es)</p>
    </div>

    <DeleteModal
      :isOpen="showDeleteModal"
      title="¿Eliminar chat?"
      :message="`Esto eliminará el chat: <strong>${chatToDelete?.title || ''}</strong>`"
      confirmText="Eliminar"
      cancelText="Cancelar"
      @confirm="confirmDelete"
      @cancel="closeDeleteModal"
    />

    <RenameModal
      :isOpen="showRenameModal"
      :currentName="chatToRename?.title || ''"
      @confirm="confirmRename"
      @cancel="closeRenameModal"
    />
  </aside>
</template>

<script>
import DeleteModal from "./DeleteModal.vue";
import RenameModal from "./RenameModal.vue";

export default {
  name: "ChatSidebar",

  components: {
    DeleteModal,
    RenameModal,
  },

  props: {
    isOpen: { type: Boolean, default: true },
    chats: { type: Array, required: true },
    currentChatId: { type: [String, Number], default: null }
  },

  data() {
    return {
      activeMenuId: null,
      showDeleteModal: false,
      showRenameModal: false,
      chatToDelete: null,
      chatToRename: null
    };
  },

  methods: {
    emitNewChat() {
      this.$emit("create-new-chat");
    },

    emitSelectChat(chatId) {
      this.$emit("select-chat", chatId);
      this.activeMenuId = null;
    },

    emitDeleteChat(chatId) {
      this.$emit("delete-chat", chatId);
    },

    emitRenameChat({ id, newName }) {
      this.$emit("rename-chat", { id, newName });
    },

    toggleMenu(chatId) {
      this.activeMenuId = this.activeMenuId === chatId ? null : chatId;
    },

    truncate(text, length) {
      if (!text) return "Nueva conversación";
      return text.length > length ? text.slice(0, length) + "..." : text;
    },

    openDeleteModal(chat) {
      this.chatToDelete = chat;
      this.showDeleteModal = true;
      this.activeMenuId = null;
    },

    closeDeleteModal() {
      this.showDeleteModal = false;
      this.chatToDelete = null;
    },

    confirmDelete() {
      this.emitDeleteChat(this.chatToDelete.id);
      this.closeDeleteModal();
    },

    openRenameModal(chat) {
      this.chatToRename = chat;
      this.showRenameModal = true;
      this.activeMenuId = null;
    },

    closeRenameModal() {
      this.showRenameModal = false;
      this.chatToRename = null;
    },

    confirmRename(newName) {
      if (!newName.trim()) return;

      this.emitRenameChat({
        id: this.chatToRename.id,
        newName: newName.trim()
      });

      this.closeRenameModal();
    },

    handleClickOutside(e) {
      if (!e.target.closest(".chat-actions")) {
        this.activeMenuId = null;
      }
    }
  },

  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },

  beforeDestroy() {
    document.removeEventListener("click", this.handleClickOutside);
  }
};
</script>

<style scoped>
.sidebar {
  position: absolute; 
  top: 0; 
  right: 0;
  bottom: 0;
  width: 320px;
  height: 100%; 
  
  /* CAMBIO: Fondo Blanco */
  background: #ffffff;
  border-left: 1px solid rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  z-index: 40;
  overflow: hidden;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.05);
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* CAMBIO: Texto negro */
.sidebar-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0;
  color: #000000;
}

.btn-close {
  background: transparent;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: none;
}

.btn-close:hover {
  background: #f0f0f0;
  color: #000;
}

/* CAMBIO: Botón verde específico */
.btn-new-chat {
  margin: 16px;
  padding: 12px 16px;
  /* El color solicitado */
  background: #20e404; 
  /* Texto blanco solicitado */
  color: white; 
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(32, 228, 4, 0.2);
}

.btn-new-chat:hover {
  /* Un poco más oscuro al hacer hover para efecto visual */
  background: #1cc603; 
  box-shadow: 0 6px 20px rgba(32, 228, 4, 0.3);
  transform: translateY(-2px);
}

.btn-new-chat:active {
  transform: translateY(0);
}

.chat-list {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.empty-chats {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 12px;
  color: #999;
  text-align: center;
}

.empty-chats p {
  margin: 0;
  font-size: 0.875rem;
}

/* CAMBIO: Estilos del item de chat */
.chat-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  
  /* Degradado solicitado: #dbf5ed -> blanco */
  background: linear-gradient(90deg, #dbf5ed 0%, #ffffff 100%);
  
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  position: relative;
  z-index: 1;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.chat-item:hover {
  transform: translateX(-2px);
  z-index: 5;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
  border-color: #20e404;
}

.chat-item.active {
  border: 1px solid #20e404;
  box-shadow: 0 0 12px rgba(32, 228, 4, 0.15);
  z-index: 5;
}

.chat-item.menu-open {
  z-index: 999 !important;
}

.chat-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.chat-info svg {
  flex-shrink: 0;
  color: #444; /* Icono oscuro */
}

/* CAMBIO: Texto negro en chats */
.chat-title {
  color: #000000;
  font-size: 1rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

.chat-actions {
  position: relative;
  flex-shrink: 0;
  z-index: inherit;
}

.btn-menu {
  background: transparent;
  border: none;
  color: #666; /* Gris oscuro para que se vea sobre el fondo claro */
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  opacity: 0;
}

.chat-item:hover .btn-menu {
  opacity: 1;
}

.btn-menu:hover {
  background: rgba(0,0,0,0.05);
  color: #000;
}

/* Ajuste del dropdown para que contraste con el estilo claro */
.dropdown-menu {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  background: #ffffff;
  border: 1px solid #eee;
  border-radius: 8px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  z-index: 1000;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 16px;
  background: transparent;
  border: none;
  color: #333;
  font-size: 0.875rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
}

.menu-item:hover {
  background: #f5f5f5;
}

.menu-item svg {
  flex-shrink: 0;
}

.menu-item-danger {
  color: #dc2626;
}

.menu-item-danger:hover {
  background: rgba(220, 38, 38, 0.05);
}

.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.menu-fade-enter,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.sidebar-footer {
  padding: 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  background: #f9fafb; /* Ligeramente gris para diferenciar del fondo blanco */
}

.footer-text {
  margin: 0;
  font-size: 0.75rem;
  color: #666;
  text-align: center;
}

.chat-list::-webkit-scrollbar {
  width: 6px;
}

.chat-list::-webkit-scrollbar-track {
  background: transparent;
}

.chat-list::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 9999px;
}

.chat-list::-webkit-scrollbar-thumb:hover {
  background: #ccc;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    max-width: 320px;
    border-left: none; /* En móvil suele estar a la izquierda o full width */
    border-right: 1px solid #eee;
  }

  .btn-close {
    display: flex;
  }

  .btn-new-chat {
    margin: 12px;
  }
}
</style>