<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table
              id="data-table-students"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombres</th>
                  <th>Email</th>
                  <th>Fecha de Registro</th>
                  <th>Participación</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(student, index) in students"
                  :key="student.id"
                  :class="{ 'table-row-red': student.participant == 0 }"
                >
                  <td>{{ index + 1 }}</td>
                  <td>{{ student.name }}</td>
                  <td>{{ student.email }}</td>
                  <td>{{ student.date }}</td>
                  <td class="text-center">
                    <span
                      class="participation-circle"
                      :class="{
                        'circle-red': student.participant == 0,
                        'circle-green': student.participant == 1
                      }"
                      :title="student.participant == 1 ? 'Participante' : 'No participante'"
                      @click="markAttendance(student)"
                    ></span>
                  </td>
                  <td class="align-content-center">
                    <el-select
                      id="customize_select"
                      size="mini"
                      clearable
                      v-model="optionSelected"
                      @change="getOptionSelected(student)"
                      placeholder="Seleccionar acción"
                    >
                      <el-option
                        v-for="item in getAvailableOptions(student)"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                        :disabled="item.disabled"
                      >
                      </el-option>
                    </el-select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>

    <!-- Modal para crear reunión -->
    <div
      v-if="showCreateMeeting"
      class="modal"
      style="display:block; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1000;"
    >
      <div
        class="modal-content"
        style="background: white; padding: 20px; width: 50%; margin: 10% auto; border-radius: 10px;"
      >
        <h3>Crear Reunión</h3>

        <label for="meetingTitle">Titulo de la reunión (sea breve)</label>
        <input type="text" id="meetingTitle" v-model="meetingTitle" />

        <label for="meetingDate">Fecha:</label>
        <input type="date" id="meetingDate" v-model="meetingDate" />

        <label for="meetingTime">Hora:</label>
        <input type="time" id="meetingTime" v-model="meetingTime" />

        <button @click="createMeeting">Crear Reunión</button>
        <button @click="closeModal">Cancelar</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import CustomSpinner from "../../common/custom-spinner/CustomSpinner.vue";
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from "../../api/traduction_es";

export default {
  props: {
    id: {
      type: Number,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
    contentType: {
      type: String,
      default: "masterclass",
      validator: (value) => ["masterclass", "minicourse", "ebook"].includes(value),
    },
  },
  data() {
    return {
      students: [],
      options: [
        {
          value: "1",
          label: "Programar Reunión",
        },
        {
          value: "2",
          label: "Marcar Asistencia",
        },
      ],
      initialLoading: false,
      showCreateMeeting: false,
      optionSelected: "",
      meetingTime: "",
      meetingDate: "",
      meetingTitle: "",
      selectedParticipantId: "",
      selectedContentId: "",
    };
  },
  components: {
    "custom-spinner": CustomSpinner,
    "custom-modal": ModalComponent,
  },
  mounted() {
    this.showStudents(this.id);
    this.meetingTime = this.getCurrentTime();
    this.meetingDate = this.getCurrentDate();
  },
  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $("#data-table-students").DataTable(language);
      });
    },
    getCurrentTime() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, "0");
      const minutes = String(now.getMinutes()).padStart(2, "0");
      return `${hours}:${minutes}`;
    },
    getCurrentDate() {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      const day = String(now.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    async showStudents() {
      this.initialLoading = true;
      try {
        let endpoint = `/marketing/${this.id}/list-students`;
        if (this.contentType !== "masterclass") {
          endpoint = `/marketing/${this.id}/list-students/${this.contentType}`;
        }

        const { data } = await axios.get(endpoint);
        this.students = Array.isArray(data.data) ? data.data : [];
        this.initialLoading = false;
        this.loadDataTable();
      } catch (error) {
        console.error("Error al obtener estudiantes:", error);
        this.$message.error("No se pudo cargar la lista de estudiantes.");
      }
    },
    getAvailableOptions(student) {
      let availableOptions = [...this.options];

      availableOptions = availableOptions.map((option) => {
        if (option.value === "2" && student && student.participant == 1) {
          return {
            ...option,
            label: "✓ Ya es participante",
            disabled: true,
          };
        }
        return option;
      });

      return availableOptions;
    },
    getOptionSelected(student) {
      let option = this.optionSelected;
      switch (option) {
        case "1":
          this.selectedParticipantId = student.id;
          this.selectedContentId = student.content_id;
          $("#students-modal").modal("hide");
          this.showCreateMeeting = true;
          this.optionSelected = "";
          break;
        case "2":
          this.markAttendance(student);
          this.optionSelected = "";
          break;
        default:
          console.log("Error");
          break;
      }
    },
    closeModal() {
      this.showCreateMeeting = false;
      this.meetingTime = "";
      this.meetingTitle = "";
      this.selectedContentId = null;
      this.selectedParticipantId = null;
    },
    createMeeting() {
      if (!this.meetingDate || !this.meetingTime || !this.meetingTitle) {
        alert("Por favor completa todos los campos.");
        return;
      }

      const newMeeting = {
        date: this.meetingDate,
        time: this.meetingTime,
        contentId: this.selectedContentId,
        contentType: this.contentType,
        participantId: this.selectedParticipantId,
        title: this.meetingTitle,
        owner_id: this.user.id,
      };

      console.log(newMeeting);

      let createEndpoint = "/masterclass/create-meeting";
      if (this.contentType === "minicourse") {
        createEndpoint = "/minicourse/create-meeting";
      } else if (this.contentType === "ebook") {
        createEndpoint = "/ebook/create-meeting";
      }

      axios
        .post(createEndpoint, newMeeting)
        .then((response) => {
          alert("Reunión creada exitosamente");
          this.closeModal();
        })
        .catch((error) => {
          console.error("Error al crear la reunión:", error);
          alert("Error al crear la reunión.");
        });
    },
    
    async markAttendance(student) {
      // Si ya es participante, permitir cambiar a no participante
      const currentStatus = student.participant == 1;
      const newStatus = !currentStatus;

      const action = newStatus ? 'marcar como participante' : 'desmarcar como participante';
      const confirmResult = confirm(`¿Está seguro de ${action} a ${student.name}?`);
      if (!confirmResult) return;
    
      try {
        let updateEndpoint;
        switch (this.contentType) {
          case "masterclass":
            // Asumiendo que también actualizaste el endpoint de masterclass
            updateEndpoint = `/masterclass/participants/${student.id}`;
            break;
          case "minicourse":
            updateEndpoint = `/marketing/mini-course/participants/${student.id}`;
            break;
          case "ebook":
            // Asumiendo que también actualizaste el endpoint de ebook
            updateEndpoint = `/marketing/ebook/participants/${student.id}`;
            break;
          default:
            throw new Error("Tipo de contenido no válido");
        }
      
        console.log(`🎯 Llamando a endpoint: ${updateEndpoint} con estado: ${newStatus}`);

        const response = await axios.patch(updateEndpoint, {
          participant: newStatus
        });
      
        if (response.data.success) {
          // Actualizar el estado local
          const studentIndex = this.students.findIndex((s) => s.id === student.id);
          if (studentIndex !== -1) {
            this.$set(this.students[studentIndex], "participant", newStatus ? 1 : 0);
          }

          const successMessage = newStatus ? 
            "Asistencia marcada correctamente" : 
            "Participación desmarcada correctamente";
          this.$message.success(successMessage);
        } else {
          throw new Error(response.data.message || "Error al actualizar participación");
        }
      } catch (error) {
        console.error("Error al actualizar participación:", error);
        let errorMessage = "Error al actualizar la participación.";
        if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        } else if (error.message) {
          errorMessage = error.message;
        }
        this.$message.error(errorMessage);
      }
    },
  },
};
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}

.badge-success {
  background-color: #28a745;
}

.badge-warning {
  background-color: #ffc107;
}

.badge-secondary {
  background-color: #6c757d;
}

/* Estilos para la columna de participación */
.participation-circle {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  cursor: pointer;
}

.circle-red {
  background-color: #dc3545;
  box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
}

.circle-green {
  background-color: #28a745;
  box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
}

.table-row-red {
  background-color: rgba(220, 53, 69, 0.1) !important;
  border-left: 3px solid #dc3545;
}

.participation-circle:hover {
  transform: scale(1.2);
  transition: transform 0.2s ease-in-out;
}
</style>