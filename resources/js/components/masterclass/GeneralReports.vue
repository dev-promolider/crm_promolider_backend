<template>
  <div>
    <section v-if="!initialLoading">
      <div v-if="role === 'Admin' || role === 'Producer'" class="row mb-3">
        <div class="col-md-3">
          <label for="contentType">Tipo de Contenido:</label>
          <select v-model="selectedContentType" class="form-control" @change="listMasterclasses">
            <option value="masterclass">Masterclass</option>
            <option value="minicourse">Mini Cursos</option>
            <option value="ebook">Ebooks</option>
          </select>
        </div>

        <div v-if="role === 'Admin'" class="col-md-3">
          <label for="reportType">Selecciona un Reporte:</label>
          <select v-model="selectedReport" class="form-control" @change="listMasterclasses">
            <option value="masterclass">
              Agrupar por {{
                selectedContentType === 'masterclass' ? 'Masterclass' :
                selectedContentType === 'minicourse' ? 'Mini Curso' : 'Ebook'
              }}
            </option>
            <option value="distributor">Agrupar por Distribuidores</option>
          </select>
        </div>
      </div>

      <!-- VISTA PRODUCER CON BOTONES -->
      <div v-if="role === 'Producer'" class="mt-3">
        <div class="btn-group mb-2" role="group">
          <button 
            type="button" 
            class="btn btn-primary mr-1 mb-1"
            :class="{ active: activeTab === 'distributor' }"
            @click="activeTab = 'distributor'"
          >
            Vista por Distribuidor
          </button>
          <button 
            type="button" 
            class="btn btn-primary mr-1 mb-1"
            :class="{ active: activeTab === 'content' }"
            @click="activeTab = 'content'"
          >
            Vista por Contenido
          </button>
        </div>
      
        <div class="tab-content mt-1">

          <!-- PESTAÑA: Vista por Distribuidor -->
          <div v-show="activeTab === 'distributor'" class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Distribuidor</th>
                  <th>Email Distribuidor</th>
                  <th>Teléfono Distribuidor</th>
                  <th>
                    {{
                      selectedContentType === 'masterclass'
                        ? 'Masterclass'
                        : selectedContentType === 'minicourse'
                        ? 'Mini Curso'
                        : 'Ebook'
                    }}
                  </th>
                  <th>Fecha</th>
                  <th>Usuarios Registrados</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in filteredMasterclasses" :key="index">
                  <td>{{ index + 1 }}</td>
                  <td>{{ item.distribuidor_nombre ?? 'N/A' }}</td>
                  <td>{{ item.distribuidor_email ?? 'N/A' }}</td>
                  <td>{{ item.distribuidor_phone ?? 'N/A' }}</td>
                  <td>
                    {{
                      selectedContentType === 'masterclass'
                        ? item.masterclass_nombre
                        : selectedContentType === 'minicourse'
                        ? item.minicourse_nombre
                        : item.ebook_nombre
                    }}
                  </td>
                  <td>{{ item.fecha ?? 'Sin Fecha' }}</td>
                  <td>{{ item.usuarios_registrados ?? 0 }}</td>
                  <td>
                    <span
                      :class="{
                        'badge bg-success': item.status_code == 1,
                        'badge bg-warning': item.status_code == 2,
                        'badge bg-danger': item.status_code == 0 || item.estado === 'No Publicado',
                      }"
                    >
                      {{ item.estado }}
                    </span>
                  </td>
                  <td>
                    <button
                      class="btn btn-sm btn-primary"
                      @click="showStudents(
                        selectedContentType === 'masterclass' ? item.masterclass_id :
                        selectedContentType === 'minicourse' ? item.minicourse_id :
                        item.ebook_id,
                        item.distribuidor_nombre
                      )"
                    >
                      Ver Alumnos
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        
          <!-- PESTAÑA: Vista por Contenido -->
          <div v-show="activeTab === 'content'" class="accordion" id="contentAccordion">
            <div v-for="(group, contentId) in groupedByContent" :key="contentId" class="card mb-2">
              <div class="card-header" :id="'heading-' + contentId">
                <h2 class="mb-0">
                  <button
                    class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center"
                    type="button"
                    @click="toggleAccordion(contentId)"
                    style="justify-content: space-between; width: 100%; padding-left: 0; padding-right: 0;"
                  >
                    <div style="flex: 1;">
                      <strong>
                        {{
                          selectedContentType === 'masterclass' ? group[0].masterclass_nombre :
                          selectedContentType === 'minicourse' ? group[0].minicourse_nombre :
                          group[0].ebook_nombre
                        }}
                      </strong>
                      <br>
                      <small class="text-muted d-block">
                        Fecha: {{ group[0].fecha ?? 'Sin Fecha' }}
                      </small>
                      <small class="text-muted d-block mt-1">
                        {{ group.length }} Distribuidor(es) | 
                        {{ getTotalUsers(group) }} Usuario(s) Total
                      </small>
                    </div>
                    <span class="toggle-icon" style="font-size: 1.5rem; transition: transform 0.3s ease; margin-left: 2rem; padding-right: 1rem;" :style="{ transform: openAccordions.includes(contentId) ? 'rotate(180deg)' : 'rotate(0deg)' }">
                      ▼
                    </span>
                  </button>
                </h2>
              </div>
            
              <div 
                :id="'collapse-' + contentId" 
                class="collapse"
                :class="{ show: openAccordions.includes(contentId) }"
                :aria-labelledby="'heading-' + contentId"
                data-parent="#contentAccordion"
              >
                <div class="card-body">
                  <table class="table table-sm table-bordered table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Distribuidor</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Usuarios Registrados</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in group" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.distribuidor_nombre ?? 'N/A' }}</td>
                        <td>{{ item.distribuidor_email ?? 'N/A' }}</td>
                        <td>{{ item.distribuidor_phone ?? 'N/A' }}</td>
                        <td>{{ item.usuarios_registrados ?? 0 }}</td>
                        <td>
                          <span
                            :class="{
                              'badge bg-success': item.status_code == 1,
                              'badge bg-warning': item.status_code == 2,
                              'badge bg-danger': item.status_code == 0 || item.estado === 'No Publicado',
                            }"
                          >
                            {{ item.estado }}
                          </span>
                        </td>
                        <td>
                          <button
                            class="btn btn-sm btn-primary"
                            @click="showStudents(
                              selectedContentType === 'masterclass' ? item.masterclass_id :
                              selectedContentType === 'minicourse' ? item.minicourse_id :
                              item.ebook_id
                            )"
                          >
                            Ver Alumnos
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        
        </div>
      </div>

      <!-- VISTA ADMIN -->
      <div v-else-if="role === 'Admin'" class="accordion" id="accordionExample">
        <div v-if="selectedReport === 'masterclass'" class="row mt-2">
          <div
            v-for="(group, id) in groupedData"
            :key="id"
            class="card card-custom bg-white border-white border-0 mx-2"
          >
            <div class="card-custom-img" :style="{ backgroundImage: `url(${group[0].imagen})` }"></div>
            <div class="card-custom-avatar">
              <img
                class="img-fluid"
                src="http://res.cloudinary.com/d3/image/upload/c_pad,g_center,h_200,q_auto:eco,w_200/bootstrap-logo_u3c8dx.jpg"
                alt="Avatar"
              />
            </div>
            <div class="card-body" style="overflow-y: auto">
              <h4 class="card-title">
                {{
                  selectedContentType === 'masterclass' ? group[0].masterclass_nombre :
                  selectedContentType === 'minicourse' ? group[0].minicourse_nombre :
                  group[0].ebook_nombre
                }}
              </h4>
              <p class="card-text">
                FECHA: {{ group[0].fecha ?? 'Sin Fecha' }}
                PRODUCTOR: {{ group[0].productor_nombre ?? 'Sin Productor' }}
              </p>
              <table class="table table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Distribuidor</th>
                    <th>Usuarios Registrados</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in group" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.distribuidor_nombre ?? 'N/A' }}</td>
                    <td>{{ item.usuarios_registrados ?? 0 }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- VISTA DISTRIBUTOR - TABLA -->
      <div v-else-if="role === 'Distributor'" class="mt-3">
        <div class="row mb-3">
          <div class="col-md-3">
            <label for="contentType">Tipo de Contenido:</label>
            <select v-model="selectedContentType" class="form-control" @change="listMasterclasses">
              <option value="masterclass">Masterclass</option>
              <option value="minicourse">Mini Cursos</option>
              <option value="ebook">Ebooks</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>
                  {{
                    selectedContentType === 'masterclass' ? 'Masterclass' :
                    selectedContentType === 'minicourse' ? 'Mini Curso' : 'Ebook'
                  }}
                </th>
                <th>Productor</th>
                <th>Usuarios Registrados</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in filteredMasterclasses" :key="index">
                <td>{{ index + 1 }}</td>
                <td>
                  {{
                    selectedContentType === 'masterclass' ? (item.masterclass_nombre ?? 'N/A') :
                    selectedContentType === 'minicourse' ? (item.minicourse_nombre ?? 'N/A') :
                    (item.ebook_nombre ?? 'N/A')
                  }}
                </td>
                <td>{{ item.productor_nombre ?? 'N/A' }}</td>
                <td>{{ item.usuarios_registrados ?? 0 }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-primary"
                    @click="showStudents(
                      selectedContentType === 'masterclass' ? item.masterclass_id :
                      selectedContentType === 'minicourse' ? item.minicourse_id :
                      item.ebook_id
                    )"
                  >
                    Ver Alumnos
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import axios from 'axios';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  props: {
    user: Object,
    role: String,
  },
  data() {
    return {
      initialLoading: false,
      masterclasses: [],
      selectedReport: 'masterclass',
      selectedMasterclass: null,
      selectedContentType: 'masterclass',
      activeTab: 'distributor',
      openAccordions: [],
    };
  },
  components: {
    'custom-spinner': CustomSpinner,
    'custom-modal': ModalComponent,
  },
  mounted() {
    this.listMasterclasses();
  },
  computed: {
    filteredMasterclasses() {
      return this.masterclasses.filter(item => item.status_code == 1);
    },
    groupedByContent() {
      const grouped = {};
      let key = '';

      if (this.selectedContentType === 'masterclass') {
        key = 'masterclass_id';
      } else if (this.selectedContentType === 'minicourse') {
        key = 'minicourse_id';
      } else if (this.selectedContentType === 'ebook') {
        key = 'ebook_id';
      }

      this.filteredMasterclasses.forEach(item => {
        if (!grouped[item[key]]) grouped[item[key]] = [];
        grouped[item[key]].push(item);
      });

      return grouped;
    },
    groupedData() {
      const grouped = {};
      let key = '';

      if (this.selectedContentType === 'masterclass') {
        key = this.selectedReport === 'masterclass' ? 'masterclass_id' : 'distribuidor_id';
      } else if (this.selectedContentType === 'minicourse') {
        key = this.selectedReport === 'masterclass' ? 'minicourse_id' : 'distribuidor_id';
      } else if (this.selectedContentType === 'ebook') {
        key = this.selectedReport === 'masterclass' ? 'ebook_id' : 'distribuidor_id';
      }

      this.filteredMasterclasses.forEach(item => {
        if (!grouped[item[key]]) grouped[item[key]] = [];
        grouped[item[key]].push(item);
      });

      return grouped;
    },
  },
  methods: {
    listMasterclasses() {
      this.initialLoading = true;
      let endpoint = '';

      if (this.role === 'Admin') {
        if (this.selectedContentType === 'masterclass') {
          endpoint = this.selectedReport === 'masterclass'
            ? `/marketing/report-admin-m`
            : `/marketing/report-admin-d`;
        } else if (this.selectedContentType === 'minicourse') {
          endpoint = this.selectedReport === 'masterclass'
            ? `/marketing/report-minicourse-admin-m`
            : `/marketing/report-minicourse-admin-d`;
        } else if (this.selectedContentType === 'ebook') {
          endpoint = this.selectedReport === 'masterclass'
            ? `/marketing/report-ebook-admin-m`
            : `/marketing/report-ebook-admin-d`;
        }
      } else if (this.role === 'Producer') {
        if (this.selectedContentType === 'masterclass') {
          endpoint = `/marketing/report-producer-m/${this.user.id}`;
        } else if (this.selectedContentType === 'minicourse') {
          endpoint = `/marketing/report-minicourse-producer-m/${this.user.id}`;
        } else if (this.selectedContentType === 'ebook') {
          endpoint = `/marketing/report-ebook-producer-m/${this.user.id}`;
        }
      } else if (this.role === 'Distributor') {
        if (this.selectedContentType === 'masterclass') {
          endpoint = `/marketing/report-distributor/${this.user.id}`;
        } else if (this.selectedContentType === 'minicourse') {
          endpoint = `/marketing/report-minicourse-distributor/${this.user.id}`;
        } else if (this.selectedContentType === 'ebook') {
          endpoint = `/marketing/report-ebook-distributor/${this.user.id}`;
        }
      }

      axios
        .get(endpoint)
        .then((response) => {
          this.masterclasses = Array.isArray(response.data.data)
            ? response.data.data
            : [];
        })
        .catch((error) => {
          console.error('Error al obtener datos:', error);
          this.masterclasses = [];
        })
        .finally(() => {
          this.initialLoading = false;

          if (this.activeTab === 'distributor' && (this.role === 'Producer' || this.role === 'Distributor')) {
            this.initDataTable();
          }
        });
    },

    initDataTable() {
      this.$nextTick(() => {
        const table = $('.table.table-striped.table-bordered.table-hover');

        if ($.fn.DataTable.isDataTable(table)) {
          table.DataTable().destroy();
        }

        table.DataTable({
          pageLength: 10,
          lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Todos']
          ],
          responsive: true,
          destroy: true,
          language: {
            sLengthMenu: 'Mostrar _MENU_ registros por página',
            sZeroRecords: 'No se encontraron resultados',
            sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            sInfoEmpty: 'Mostrando 0 a 0 de 0 registros',
            sInfoFiltered: '(filtrado de _MAX_ registros totales)',
            sSearch: 'Buscar:',
            oPaginate: {
              sFirst: 'Primero',
              sLast: 'Último',
              sNext: 'Siguiente',
              sPrevious: 'Anterior'
            }
          }
        });
      });
    },

    async validateDistributorAccess(distributorName) {
      try {
        const response = await axios.post('/marketing/validate-distributor', {
          distributor_name: distributorName,
        });
        return response.data.success;
      } catch (error) {
        console.error('Error validando distribuidor:', error);
        return false;
      }
    },

    async showStudents(id, distributorName) {
      const isValid = await this.validateDistributorAccess(distributorName);

      if (!isValid) {
        this.$message.warning('No puedes ver estos alumnos.');
        return;
      }

      let url = '';
      switch (this.selectedContentType) {
        case 'masterclass':
          url = `/marketing/masterclass/lista-estudiantes/${id}/`;
          break;
        case 'minicourse':
          url = `/marketing/minicourse/lista-estudiantes/${id}/`;
          break;
        case 'ebook':
          url = `/marketing/ebook/lista-estudiantes/${id}/`;
          break;
      }

      window.location.href = url;
    },

    toggleAccordion(contentId) {
      const index = this.openAccordions.indexOf(contentId);
      if (index > -1) {
        this.openAccordions.splice(index, 1);
      } else {
        this.openAccordions.push(contentId);
      }
    },

    getTotalUsers(group) {
      return group.reduce((sum, item) => sum + (item.usuarios_registrados ?? 0), 0);
    },
  },
};
</script>

<style scoped>
.card-custom {
  overflow: hidden;
  min-height: 450px;
  box-shadow: 0 0 15px rgba(10, 10, 10, 0.3);
}

.card-custom-img {
  height: 200px;
  min-height: 200px;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  border-color: inherit;
}

.card-custom-img::after {
  position: absolute;
  content: '';
  top: 161px;
  left: 0;
  width: 0;
  height: 0;
  border-style: solid;
  border-top-width: 40px;
  border-left-width: calc(575px - 5vw);
  border-top-color: transparent;
  border-left-color: inherit;
}

.card-custom-avatar img {
  border-radius: 50%;
  box-shadow: 0 0 15px rgba(10, 10, 10, 0.3);
  position: absolute;
  top: 100px;
  left: 1.25rem;
  width: 100px;
  height: 100px;
}

.nav-tabs .nav-link {
  cursor: pointer;
}

.nav-tabs .nav-link.active {
  font-weight: bold;
}

.card-header button {
  text-decoration: none;
}

.card-header button:hover {
  text-decoration: none;
}

.badge {
  font-size: 0.85rem;
  padding: 0.4rem 0.6rem;
}
</style>