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
        </div>

      <div v-if="role === 'Producer' || role === 'Admin'" class="mt-3">
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
                <th>Fecha</th>
                <th># Distribuidores</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(group, index) in groupedContentList" :key="index">
                <td>{{ index + 1 }}</td>
                <td>
                  {{
                    selectedContentType === 'masterclass' ? group.masterclass_nombre :
                    selectedContentType === 'minicourse' ? group.minicourse_nombre :
                    group.ebook_nombre
                  }}
                </td>
                <td>{{ group.fecha ?? 'Sin Fecha' }}</td>
                <td>{{ group.num_distribuidores }}</td>
                <td>
                  <span
                    :class="{
                      'badge bg-success': group.status_code == 1,
                      'badge bg-warning': group.status_code == 2,
                      'badge bg-danger': group.status_code == 0 || group.estado === 'No Publicado',
                    }"
                  >
                    {{ group.estado }}
                  </span>
                </td>
                <td class="align-content-center">
                  <el-select 
                    id="customize_select" 
                    size="mini" 
                    clearable 
                    v-model="optionSelected"
                    @change="getOptionSelected(group)"
                    placeholder="Seleccionar"
                  >
                    <el-option 
                      v-for="item in actionOptions" 
                      :key="item.value"
                      :label="item.label" 
                      :value="item.value">
                    </el-option>
                  </el-select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

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
              <tr v-for="(item, index) in masterclasses" :key="index">
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

    <custom-modal id="invitation-modal" color="primary" size="large" v-if="selectedMasterclass && showInvitationModal">
      <template #title>
        <i class="feather icon-share-2"></i> Invitar: 
        <u>{{
          selectedContentType === 'masterclass' ? selectedMasterclass.masterclass_nombre :
          selectedContentType === 'minicourse' ? selectedMasterclass.minicourse_nombre :
          selectedMasterclass.ebook_nombre
        }}</u>
      </template>
      <template #body>
        <div v-if="invitationData.existInvitation && !newInvitationLink" class="alert alert-info">
          <h6><i class="feather icon-info"></i> Link de Invitación Activo</h6>
          <p>Ya tienes un link de invitación activo para esta herramienta.</p>
          <div class="input-group mb-3">
            <input type="text" class="form-control" :value="invitationData.invitationLink" readonly>
            <div class="input-group-append">
              <button class="btn btn-outline-primary" type="button" @click="copyToClipboard(invitationData.invitationLink)">
                <i class="feather icon-copy"></i> Copiar
              </button>
            </div>
          </div>
        </div>
        
        <div v-if="!invitationData.existInvitation && !newInvitationLink" class="alert alert-warning">
          <h6><i class="feather icon-alert-triangle"></i> Crear Link de Invitación</h6>
          <p>No tienes un link de invitación activo. Haz clic en el botón para crear uno.</p>
          <button @click="createInvitationLink" :disabled="loadingInvitation" class="btn btn-primary">
            <i class="feather icon-plus"></i> 
            {{ loadingInvitation ? 'Creando...' : 'Crear Link de Invitación' }}
          </button>
        </div>

        <div v-if="newInvitationLink" class="alert alert-success mt-3">
          <h6><i class="feather icon-check-circle"></i> Link Creado Exitosamente</h6>
          <p>Tu link de invitación ha sido creado y es válido por 7 días.</p>
          <div class="input-group mb-3">
            <input type="text" class="form-control" :value="newInvitationLink" readonly>
            <div class="input-group-append">
              <button class="btn btn-outline-success" type="button" @click="copyToClipboard(newInvitationLink)">
                <i class="feather icon-copy"></i> Copiar
              </button>
            </div>
          </div>
        </div>

        <div class="mt-4" v-if="invitationData.existInvitation || newInvitationLink">
          <h6 class="font-weight-bold">Compartir via:</h6>
          <div class="share-buttons d-flex flex-wrap gap-2 mt-3">
            <a :href="getWhatsappShareUrl()" target="_blank" class="btn btn-success btn-sm">
              <i class="feather icon-message-circle"></i> WhatsApp
            </a>
            <a :href="getFacebookShareUrl()" target="_blank" class="btn btn-primary btn-sm">
              <i class="feather icon-facebook"></i> Facebook
            </a>
            <button @click="copyToClipboard(getCurrentInvitationLink())" class="btn btn-info btn-sm">
              <i class="feather icon-copy"></i> Copiar Link
            </button>
          </div>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-secondary" @click="closeInvitationModal">Cerrar</button>
      </template>
    </custom-modal>

    <div v-if="showToast" class="toast-notification">
      <div class="toast-content">
        <i class="feather icon-check-circle text-success"></i>
        <span>{{ toastMessage }}</span>
        <button @click="showToast = false" class="toast-close">
          <i class="feather icon-x"></i>
        </button>
      </div>
    </div>
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
      selectedMasterclass: null,
      selectedContentType: 'masterclass',
      optionSelected: '',
      actionOptions: [
        {
          value: '1',
          label: 'Invitar',
        },
        {
          value: '2',
          label: 'Lista de Distribuidores',
        },
        {
          value: '3',
          label: 'Lista de Participantes',
        },
      ],
      showInvitationModal: false,
      loadingInvitation: false,
      invitationData: {
        existInvitation: false,
        invitationLink: ''
      },
      newInvitationLink: '',
      showToast: false,
      toastMessage: '',
      verifyingOwnership: false, // Nuevo flag para mostrar loading
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
    groupedContentList() {
      const grouped = {};
      let key = '';
      let nameKey = '';

      if (this.selectedContentType === 'masterclass') {
        key = 'masterclass_id';
        nameKey = 'masterclass_nombre';
      } else if (this.selectedContentType === 'minicourse') {
        key = 'minicourse_id';
        nameKey = 'minicourse_nombre';
      } else if (this.selectedContentType === 'ebook') {
        key = 'ebook_id';
        nameKey = 'ebook_nombre';
      }

      this.masterclasses.forEach(item => {
        const contentId = item[key];
        
        if (contentId) {
            if (!grouped[contentId]) {
            grouped[contentId] = {
                content_id: contentId,
                [nameKey]: item[nameKey],
                masterclass_nombre: item.masterclass_nombre,
                minicourse_nombre: item.minicourse_nombre,
                ebook_nombre: item.ebook_nombre,
                fecha: item.fecha,
                estado: item.estado,
                status_code: item.status_code,
                distribuidores: [],
                num_distribuidores: 0
            };
            }
            grouped[contentId].distribuidores.push(item);
        }
      });

      return Object.values(grouped).map(group => {
        group.num_distribuidores = group.distribuidores.length;
        return group;
      });
    },
  },
  methods: {
    listMasterclasses() {
      this.initialLoading = true;
      let endpoint = '';

      if (this.role === 'Admin') {
        if (this.selectedContentType === 'masterclass') {
           endpoint = `/marketing/report-admin-m`;
        } else if (this.selectedContentType === 'minicourse') {
           endpoint = `/marketing/report-minicourse-admin-m`;
        } else if (this.selectedContentType === 'ebook') {
           endpoint = `/marketing/report-ebook-admin-m`;
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

          if (['Distributor', 'Producer', 'Admin'].includes(this.role)) {
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
      if(this.role !== 'Admin') {
        const isValid = await this.validateDistributorAccess(distributorName);
        if (!isValid) {
            this.$message.warning('No puedes ver estos alumnos.');
            return;
        }
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

    getOptionSelected(group) {
      let option = this.optionSelected;

      switch (option) {
        case '1':
          this.openInvitationModal(group);
          break;
        case '2':
          this.showDistributorsList(group.content_id);
          break;
        case '3':
          this.showParticipants(group.content_id);
          break;
        default:
          break;
      }

      this.optionSelected = '';
    },

    async openInvitationModal(group) {
      this.selectedMasterclass = group;
      this.newInvitationLink = '';
      this.showInvitationModal = true;

      const hasPurchased = await this.checkPurchaseOrRegistration(group);
      
      if (hasPurchased) {
        await this.checkExistingInvitation(group);
        $('#invitation-modal').modal('show');
      } else {
        this.showInvitationModal = false;
        this.$swal.fire({
          title: 'No disponible',
          text: 'Debes registrar/comprar esta herramienta antes de poder invitar a otros.',
          icon: 'warning'
        });
      }
    },

    async checkPurchaseOrRegistration(group) {
      try {
        let endpoint = '';
        const contentId = group.content_id;

        switch (this.selectedContentType) {
          case 'masterclass':
            endpoint = `/masterclass/check-registration/${contentId}`;
            break;
          case 'minicourse':
            endpoint = `/marketing/mini-course/invitation/check-purchase/${contentId}`;
            break;
          case 'ebook':
            endpoint = `/marketing/ebook/check-purchase/${contentId}`;
            break;
        }

        const response = await axios.get(endpoint);
        return response.data.isPurchased || response.data.isRegistered || false;

      } catch (error) {
        console.error('Error al verificar compra/registro:', error);
        return false;
      }
    },

    async checkExistingInvitation(group) {
      try {
        let endpoint = '';
        const contentId = group.content_id;

        switch (this.selectedContentType) {
          case 'masterclass':
            endpoint = `/masterclass/check-invitation/${contentId}`;
            break;
          case 'minicourse':
            endpoint = `/marketing/mini-course/invitation/check-invitation/${contentId}`;
            break;
          case 'ebook':
            endpoint = `/marketing/ebook/check-invitation/${contentId}`;
            break;
        }

        const response = await axios.get(endpoint);

        this.invitationData = {
          existInvitation: response.data.existInvitation,
          invitationLink: response.data.invitationLink || ''
        };

      } catch (error) {
        console.error('Error al verificar invitación:', error);
        this.invitationData = {
          existInvitation: false,
          invitationLink: ''
        };
      }
    },

    async createInvitationLink() {
      if (!this.selectedMasterclass || !this.selectedMasterclass.content_id) return;

      this.loadingInvitation = true;
      try {
        let endpoint = '';
        const contentId = this.selectedMasterclass.content_id;

        switch (this.selectedContentType) {
          case 'masterclass':
            endpoint = `/masterclass/create-invitation/${contentId}`;
            break;
          case 'minicourse':
            endpoint = `/marketing/mini-course/invitation/invitation-link/${contentId}`;
            break;
          case 'ebook':
            endpoint = `/marketing/ebook/invitation-link/${contentId}`;
            break;
        }

        const response = await axios.post(endpoint);

        this.newInvitationLink = response.data.link;
        this.invitationData.existInvitation = true;
        this.invitationData.invitationLink = response.data.link;

        this.showToastMessage('Link de invitación creado exitosamente');

      } catch (error) {
        console.error('Error al crear link de invitación:', error);

        this.$swal.fire({
          title: 'Error',
          text: 'Hubo un problema al crear el link de invitación.',
          icon: 'error'
        });
      } finally {
        this.loadingInvitation = false;
      }
    },

    closeInvitationModal() {
      this.showInvitationModal = false;
      this.newInvitationLink = '';
      $('#invitation-modal').modal('hide');
    },

    async copyToClipboard(text) {
      try {
        await navigator.clipboard.writeText(text);
        this.showToastMessage('Link copiado al portapapeles');
      } catch (error) {
        console.error('Error al copiar:', error);
        this.$message({
          message: 'No se pudo copiar el link',
          type: 'error',
          duration: 3000,
        });
      }
    },

    showToastMessage(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },

    getCurrentInvitationLink() {
      return this.newInvitationLink || this.invitationData.invitationLink || '';
    },

    getWhatsappShareUrl() {
      const link = this.getCurrentInvitationLink();
      const contentName = this.selectedMasterclass ? 
        (this.selectedMasterclass.masterclass_nombre || 
         this.selectedMasterclass.minicourse_nombre || 
         this.selectedMasterclass.ebook_nombre) : '';
      
      const contentTypeLabel = this.selectedContentType === 'masterclass' ? 'Masterclass' :
                               this.selectedContentType === 'minicourse' ? 'Mini Curso' : 'Ebook';

      const text = `¡Hola! Te invito a conocer este increíble ${contentTypeLabel}: "${contentName}". Regístrate usando mi link de invitación: ${link}`;

      return `https://wa.me/?text=${encodeURIComponent(text)}`;
    },

    getFacebookShareUrl() {
      const link = this.getCurrentInvitationLink();
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
    },

    // ========== MÉTODO MODIFICADO CON VERIFICACIÓN DE PROPIEDAD ==========
    async showDistributorsList(contentId) {
      // Si es Admin, permitir acceso directo sin verificación
      if (this.role === 'Admin') {
        this.redirectToDistributorsList(contentId);
        return;
      }
    
      // Para Producer y Distributor, verificar propiedad
      this.verifyingOwnership = true;
    
      try {
        // Cambio a GET con parámetros en la URL
        const response = await axios.get(`/marketing/verify-ownership/${this.selectedContentType}/${contentId}`);
      
        const data = response.data;
      
        if (data.success && data.has_access) {
          // Usuario tiene acceso (como productor o distribuidor)
          this.redirectToDistributorsList(contentId);
        } else {
          // Usuario no tiene acceso
          this.$swal.fire({
            title: 'Acceso Denegado',
            text: 'No tienes permisos para ver la lista de distribuidores de esta herramienta.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      
      } catch (error) {
        console.error('Error al verificar propiedad:', error);
        
        this.$swal.fire({
          title: 'Error',
          text: 'Hubo un problema al verificar los permisos. Intenta nuevamente.',
          icon: 'error',
          confirmButtonText: 'Cerrar'
        });
      } finally {
        this.verifyingOwnership = false;
      }
    },

    // Método auxiliar para redireccionar
    redirectToDistributorsList(contentId) {
      let url = '';
      switch (this.selectedContentType) {
        case 'masterclass':
          url = `/marketing/masterclass/list-distributors/${contentId}`;
          break;
        case 'minicourse':
          url = `/marketing/minicourse/list-distributors/${contentId}`;
          break;
        case 'ebook':
          url = `/marketing/ebook/list-distributors/${contentId}`;
          break;
      }
      window.location.href = url;
    },

    async showParticipants(contentId) {
      let url = '';
      switch (this.selectedContentType) {
        case 'masterclass':
          url = `/marketing/masterclass/lista-estudiantes/${contentId}/`;
          break;
        case 'minicourse':
          url = `/marketing/minicourse/lista-estudiantes/${contentId}/`;
          break;
        case 'ebook':
          url = `/marketing/ebook/lista-estudiantes/${contentId}/`;
          break;
      }
      window.location.href = url;
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

.badge {
  font-size: 0.85rem;
  padding: 0.4rem 0.6rem;
}

#customize_select {
  min-width: 180px;
}

.share-buttons {
  gap: 0.5rem;
}

.share-buttons .btn {
  min-width: 120px;
}

.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  animation: slideInRight 0.3s ease-out;
}

.toast-content {
  background: white;
  border-radius: 8px;
  padding: 15px 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 300px;
}

.toast-content i.feather {
  font-size: 24px;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  margin-left: auto;
  padding: 0;
  color: #999;
}

.toast-close:hover {
  color: #333;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>