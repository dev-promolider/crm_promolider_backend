// resources/js/components/CalendarComponent.vue 
<template>
  <div class="calendar-container">
    <!-- Header del calendario -->
    <div class="calendar-header">
      <button @click="previousMonth" class="nav-button">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15,18 9,12 15,6"></polyline>
        </svg>
      </button>
      
      <h2 class="month-year">{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
      
      <button @click="nextMonth" class="nav-button">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </button>
    </div>

    <!-- Mensajes de notificación -->
    <div v-if="notification.show" :class="['notification', notification.type]">
      {{ notification.message }}
    </div>

    <!-- Días de la semana -->
    <div class="calendar-weekdays">
      <div v-for="day in weekDays" :key="day" class="weekday">
        {{ day }}
      </div>
    </div>

    <!-- Días del mes -->
    <div class="calendar-days">
      <div
        v-for="day in calendarDays"
        :key="day.key"
        :class="[
          'calendar-day',
          { 'other-month': !day.isCurrentMonth },
          { 'today': day.isToday },
          { 'has-notes': day.hasNotes }
        ]"
        @click="selectDay(day)"
      >
        <span class="day-number">{{ day.day }}</span>
        <div v-if="day.hasNotes" class="notes-indicator">
          <span class="notes-count">{{ day.notes.length }}</span>
        </div>
      </div>
    </div>

    <!-- Loading spinner -->
    <div v-if="loading" class="loading-spinner">
      <div class="spinner"></div>
      <p>Cargando...</p>
    </div>

    <!-- Modal para agregar/editar notas -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Notas para {{ selectedDate.day }}/{{ selectedDate.month + 1 }}/{{ selectedDate.year }}</h3>
          <button @click="closeModal" class="close-button">×</button>
        </div>
        
        <div class="modal-body">
          <!-- Lista de notas existentes -->
          <div v-if="selectedDateNotes && selectedDateNotes.length > 0" class="existing-notes">
            <h4>Notas existentes:</h4>
            <div v-for="(note, index) in selectedDateNotes" :key="note.id || index" class="note-item">
              <div class="note-content">
                <span class="note-time">{{ note.time }}</span>
                <span class="note-text">{{ note.text }}</span>
              </div>
              <button @click="deleteNote(index)" class="delete-note-btn" :disabled="loading">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Formulario para nueva nota -->
          <div class="new-note-form">
            <h4>Agregar nueva nota:</h4>
            <div class="form-group">
              <label>Hora:</label>
              <div class="time-picker">
                <div class="time-input-group">
                  <select v-model="newNote.hour" class="time-select">
                    <option value="">HH</option>
                    <option v-for="hour in hours" :key="hour" :value="hour">
                      {{ hour.toString().padStart(2, '0') }}
                    </option>
                  </select>
                  <span class="time-separator">:</span>
                  <select v-model="newNote.minute" class="time-select">
                    <option value="">MM</option>
                    <option v-for="minute in minutes" :key="minute" :value="minute">
                      {{ minute.toString().padStart(2, '0') }}
                    </option>
                  </select>
                </div>
                <div class="quick-time-buttons">
                  <button 
                    type="button" 
                    @click="setQuickTime('09:00')" 
                    class="quick-time-btn"
                    :class="{ active: formatTime() === '09:00' }"
                  >
                    9:00 AM
                  </button>
                  <button 
                    type="button" 
                    @click="setQuickTime('12:00')" 
                    class="quick-time-btn"
                    :class="{ active: formatTime() === '12:00' }"
                  >
                    12:00 PM
                  </button>
                  <button 
                    type="button" 
                    @click="setQuickTime('18:00')" 
                    class="quick-time-btn"
                    :class="{ active: formatTime() === '18:00' }"
                  >
                    6:00 PM
                  </button>
                  <button 
                    type="button" 
                    @click="setCurrentTime()" 
                    class="quick-time-btn current-time"
                  >
                    Ahora
                  </button>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="note-text">Nota:</label>
              <textarea
                id="note-text"
                v-model="newNote.text"
                class="form-control"
                rows="3"
                placeholder="Escribe tu nota aquí..."
              ></textarea>
            </div>
            <button 
              @click="addNote" 
              class="add-note-btn" 
              :disabled="!newNote.text.trim() || loading"
            >
              {{ loading ? 'Guardando...' : 'Agregar Nota' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CalendarComponent',
  data() {
    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      showModal: false,
      selectedDate: null,
      loading: false,
      newNote: {
        hour: '',
        minute: '',
        text: ''
      },
      notes: {},
      notification: {
        show: false,
        message: '',
        type: 'success' // 'success' o 'error'
      },
      monthNames: [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
      ],
      weekDays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
    }
  },
  computed: {
    hours() {
      return Array.from({ length: 24 }, (_, i) => i);
    },
    minutes() {
      return Array.from({ length: 60 }, (_, i) => i);
    },
    calendarDays() {
      const days = [];
      const today = new Date();
      const firstDay = new Date(this.currentYear, this.currentMonth, 1);
      const startDate = new Date(firstDay);
      
      // Ajustar al primer día de la semana
      startDate.setDate(startDate.getDate() - startDate.getDay());
      
      // Generar 42 días (6 semanas)
      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dateKey = this.getDateKey(date);
        const dayNotes = this.notes[dateKey] || [];
        
        days.push({
          key: dateKey,
          day: date.getDate(),
          month: date.getMonth(),
          year: date.getFullYear(),
          isCurrentMonth: date.getMonth() === this.currentMonth,
          isToday: date.toDateString() === today.toDateString(),
          hasNotes: dayNotes.length > 0,
          notes: dayNotes,
          fullDate: date
        });
      }
      
      return days;
    },
    selectedDateNotes() {
      if (!this.selectedDate) return [];
      const dateKey = this.getDateKey(this.selectedDate.fullDate);
      return this.notes[dateKey] || [];
    }
  },
  methods: {
    previousMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear--;
      } else {
        this.currentMonth--;
      }
    },
    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear++;
      } else {
        this.currentMonth++;
      }
    },
    selectDay(day) {
      this.selectedDate = day;
      this.showModal = true;
      this.newNote = { hour: '', minute: '', text: '' };
    },
    closeModal() {
      this.showModal = false;
      this.selectedDate = null;
      this.newNote = { hour: '', minute: '', text: '' };
    },

    async addNote() {
      if (!this.newNote.text.trim()) return;

      this.loading = true;

      try {
        const time = this.formatTime() || '00:00';
        const dateKey = this.getDateKey(this.selectedDate.fullDate);

        const response = await axios.post('/marketing/calendar/notes', {
          date: dateKey,
          time: time,
          content: this.newNote.text.trim()
        });

        if (response.data.success) {
          // Crear una copia del objeto notes para forzar la reactividad
          const updatedNotes = { ...this.notes };

          if (!updatedNotes[dateKey]) {
            updatedNotes[dateKey] = [];
          }

          // Agregar la nueva nota con el ID del servidor
          const newNoteObj = {
            id: response.data.data.id,
            time: response.data.data.time,
            text: response.data.data.text
          };

          updatedNotes[dateKey].push(newNoteObj);

          // Ordenar notas por hora
          updatedNotes[dateKey].sort((a, b) => a.time.localeCompare(b.time));

          // Asignar el objeto actualizado para activar la reactividad
          this.notes = updatedNotes;

          // Limpiar el formulario
          this.newNote = { hour: '', minute: '', text: '' };

          // Mostrar mensaje de éxito
          this.showSuccessMessage('Nota agregada correctamente');

          // Emitir evento para notificar cambios
          this.$emit('notes-updated', this.notes);
        }
      } catch (error) {
        console.error('Error al agregar nota:', error);
        
        // Manejar diferentes tipos de errores
        let errorMessage = 'Error al agregar la nota. Por favor, inténtalo de nuevo.';
        
        if (error.response) {
          // Error del servidor
          if (error.response.status === 422) {
            errorMessage = 'Datos inválidos. Verifica la información ingresada.';
          } else if (error.response.status === 401) {
            errorMessage = 'No tienes permisos para realizar esta acción.';
          } else if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        } else if (error.request) {
          // Error de red
          errorMessage = 'Error de conexión. Verifica tu conexión a internet.';
        }
        
        this.showErrorMessage(errorMessage);
      } finally {
        this.loading = false;
      }
    },

    async deleteNote(index) {
      if (this.loading) return;
      
      this.loading = true;

      try {
        const dateKey = this.getDateKey(this.selectedDate.fullDate);
        const noteToDelete = this.notes[dateKey][index];

        if (!noteToDelete.id) {
          this.showErrorMessage('No se puede eliminar una nota sin ID');
          return;
        }

        const response = await axios.delete(`/marketing/calendar/notes/${noteToDelete.id}`);

        if (response.data.success) {
          // Crear una copia del objeto notes para forzar la reactividad
          const updatedNotes = { ...this.notes };

          if (updatedNotes[dateKey]) {
            updatedNotes[dateKey].splice(index, 1);

            if (updatedNotes[dateKey].length === 0) {
              delete updatedNotes[dateKey];
            }
          }

          // Asignar el objeto actualizado para activar la reactividad
          this.notes = updatedNotes;

          // Mostrar mensaje de éxito
          this.showSuccessMessage('Nota eliminada correctamente');

          this.$emit('notes-updated', this.notes);
        }
      } catch (error) {
        console.error('Error al eliminar nota:', error);
        
        let errorMessage = 'Error al eliminar la nota. Por favor, inténtalo de nuevo.';
        
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        }
        
        this.showErrorMessage(errorMessage);
      } finally {
        this.loading = false;
      }
    },

    getDateKey(date) {
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    },

    async loadNotes() {
      this.loading = true;
      
      try {
        const response = await axios.get('/marketing/calendar/notes');

        if (response.data.success) {
          this.notes = response.data.data || {};
          return response.data.data;
        } else {
          console.error('Error en la respuesta del servidor:', response.data.message);
          return this.loadNotesFromLocalStorage();
        }
      } catch (error) {
        console.error('Error al cargar notas:', error);
        this.showErrorMessage('Error al cargar las notas del servidor. Usando datos locales.');

        // Fallback a localStorage si hay error del servidor
        return this.loadNotesFromLocalStorage();
      } finally {
        this.loading = false;
      }
    },

    // Método fallback para localStorage
    loadNotesFromLocalStorage() {
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          const savedNotes = window.localStorage.getItem('calendarNotes');
          return savedNotes ? JSON.parse(savedNotes) : {};
        }
        return {};
      } catch (error) {
        console.error('Error loading notes from localStorage:', error);
        return {};
      }
    },

    // Guardar en localStorage como backup
    saveNotesToLocalStorage() {
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          const notesData = JSON.stringify(this.notes);
          window.localStorage.setItem('calendarNotes', notesData);
        }
      } catch (error) {
        console.error('Error saving to localStorage:', error);
      }
    },

    formatTime() {
      if (this.newNote.hour === '' || this.newNote.minute === '') {
        return '';
      }
      const hour = this.newNote.hour.toString().padStart(2, '0');
      const minute = this.newNote.minute.toString().padStart(2, '0');
      return `${hour}:${minute}`;
    },

    setQuickTime(time) {
      const [hour, minute] = time.split(':');
      this.newNote.hour = parseInt(hour);
      this.newNote.minute = parseInt(minute);
    },

    setCurrentTime() {
      const now = new Date();
      this.newNote.hour = now.getHours();
      this.newNote.minute = now.getMinutes();
    },

    async syncNotesToServer() {
      try {
        const response = await axios.post('/marketing/calendar/sync-notes', {
          notes: this.notes
        });

        if (response.data.success) {
          console.log('Notas sincronizadas correctamente');
          this.showSuccessMessage('Notas sincronizadas');
        }
      } catch (error) {
        console.error('Error al sincronizar notas:', error);
        this.showErrorMessage('Error al sincronizar notas con el servidor');
      }
    },

    // Métodos para mostrar mensajes
    showSuccessMessage(message) {
      this.notification = {
        show: true,
        message: message,
        type: 'success'
      };
      
      // Auto-ocultar después de 3 segundos
      setTimeout(() => {
        this.notification.show = false;
      }, 3000);
    },

    showErrorMessage(message) {
      this.notification = {
        show: true,
        message: message,
        type: 'error'
      };
      
      // Auto-ocultar después de 5 segundos
      setTimeout(() => {
        this.notification.show = false;
      }, 5000);
    }
  },

  async created() {
    // Cargar las notas del servidor al crear el componente
    try {
      this.notes = await this.loadNotes();
    } catch (error) {
      console.error('Error al inicializar notas:', error);
      this.notes = this.loadNotesFromLocalStorage();
    }
  },

  watch: {
    // Guardar en localStorage cuando las notas cambien
    notes: {
      handler() {
        this.saveNotesToLocalStorage();
      },
      deep: true
    }
  },

  beforeDestroy() {
    // Limpiar intervalos si existen
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
    }
  }
}
</script>

<style scoped>
.calendar-container {
  max-width: 800px;
  margin: 0 auto;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Estilos para notificaciones */
.notification {
  padding: 12px 16px;
  margin-bottom: 20px;
  border-radius: 6px;
  font-weight: 500;
  animation: slideDown 0.3s ease-out;
}

.notification.success {
  background-color: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.notification.error {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

@keyframes slideDown {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Loading spinner */
.loading-spinner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.9);
  padding: 20px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 1001;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Resto de estilos existentes */
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 0 10px;
}

.month-year {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.nav-button {
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.nav-button:hover {
  background: #4338ca;
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e5e7eb;
  border-radius: 8px 8px 0 0;
}

.weekday {
  background: #f9fafb;
  padding: 12px;
  text-align: center;
  font-weight: 600;
  color: #6b7280;
  font-size: 0.875rem;
}

.calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e5e7eb;
  border-radius: 0 0 8px 8px;
}

.calendar-day {
  background: white;
  min-height: 80px;
  padding: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
  display: flex;
  flex-direction: column;
}

.calendar-day:hover {
  background: #f3f4f6;
}

.calendar-day.other-month {
  background: #f9fafb;
  color: #9ca3af;
}

.calendar-day.today {
  background: #dbeafe;
  border: 2px solid #3b82f6;
}

.calendar-day.has-notes {
  background: #fef3c7;
}

.calendar-day.has-notes.today {
  background: #bfdbfe;
}

.day-number {
  font-weight: 600;
  font-size: 0.875rem;
}

.notes-indicator {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  color: #1f2937;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-button:hover {
  color: #374151;
}

.modal-body {
  padding: 20px;
}

.existing-notes {
  margin-bottom: 20px;
}

.existing-notes h4 {
  margin: 0 0 10px 0;
  color: #374151;
}

.note-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 6px;
  margin-bottom: 8px;
}

.note-content {
  flex: 1;
}

.note-time {
  font-weight: 600;
  color: #4f46e5;
  margin-right: 10px;
}

.note-text {
  color: #374151;
}

.delete-note-btn {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.delete-note-btn:hover:not(:disabled) {
  background: #dc2626;
}

.delete-note-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.new-note-form h4 {
  margin: 0 0 15px 0;
  color: #374151;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.add-note-btn {
  background: #10b981;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 10px 20px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-note-btn:hover:not(:disabled) {
  background: #059669;
}

.add-note-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

/* Estilos para el selector de tiempo personalizado */
.time-picker {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 16px;
  background: #f9fafb;
}

.time-input-group {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  gap: 8px;
}

.time-select {
  padding: 8px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  background: white;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 70px;
  text-align: center;
}

.time-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.time-select:hover {
  border-color: #9ca3af;
}

.time-separator {
  font-size: 24px;
  font-weight: bold;
  color: #4f46e5;
  margin: 0 4px;
}

.quick-time-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

.quick-time-btn {
  padding: 8px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 20px;
  background: white;
  color: #6b7280;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.quick-time-btn:hover {
  border-color: #4f46e5;
  background: #f8fafc;
  color: #4f46e5;
}

.quick-time-btn.active {
  border-color: #4f46e5;
  background: #4f46e5;
  color: white;
}

.quick-time-btn.current-time {
  border-color: #10b981;
  color: #10b981;
}

.quick-time-btn.current-time:hover {
  background: #10b981;
  color: white;
}

@media (max-width: 768px) {
  .calendar-day {
    min-height: 60px;
    padding: 4px;
  }
  
  .modal-content {
    width: 95%;
    margin: 10px;
  }
  
  .modal-header, .modal-body {
    padding: 15px;
  }
  
  .quick-time-buttons {
    justify-content: center;
  }
  
  .quick-time-btn {
    flex: 1;
    min-width: 60px;
  }
  
  .time-input-group {
    flex-direction: row;
    gap: 12px;
  }
  
  .time-select {
    flex: 1;
    min-width: 60px;
  }
}
</style>