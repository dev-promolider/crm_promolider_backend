<template>
  <div>
    <table id="datatable" class="table-hover table-striped table-bordered">
      <thead>
      <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Fecha de suscripción</th>
        <th>Tipo de membresía</th>
        <th>Id order</th>
        <th>Monto</th>
        <th>Comprobante de pago</th>
        <th>Estado</th>
        <th>Opción</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(user, index) in dataUsers" :key="user.id">
        <td>{{ index + 1 }}</td>
        <td>{{ user.name }}</td>
        <td>{{ user.email || 'N/A' }}</td>
        <td>{{ user.phone || 'N/A' }}</td>
        <td>{{ moment(user.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
        <td>{{ user.account_type?.account || 'N/A' }}</td>
        <td>{{ user.payments_client?.operation_number || 'N/A' }}</td>
        <td>{{ user.payments_client?.amount || 'N/A' }}</td>
        <td>
          <div v-if="user.payments_client?.receipt_image">
            <img :src="getImageUrl(user.payments_client.receipt_image)"
                 alt="Comprobante de pago"
                 class="img-fluid receipt-image"
                 @click="showImageModal(user.payments_client.receipt_image)"
                 @error="handleImageError"/>
          </div>
          <span v-else>N/A</span>
        </td>
        <td>
          <span class="badge" :class="user.request == 1
            ? 'badge-warning'
            : user.request == 2
              ? 'badge-success'
              : 'badge-danger'
            " v-text="user.request == 1 ? 'Pendiente ' : user.request == 2 ? 'Aceptado' : 'Rechazado'
              "></span>
        </td>
        <td><button class="btn btn-info" @click="modalUser(user.id)">observar</button></td>
      </tr>
      </tbody>
    </table>

    <!-- Modal View Request -->
    <div class="modal fade" id="modalViewRequest" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Informacion del Usuario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <p><strong>Usuario: </strong> {{ dataUser.name + ' ' + dataUser.last_name }}</p>
            </div>
            <div class="form-group">
              <p><strong>Correo: </strong> {{ dataUser.email }}</p>
            </div>
            <div class="form-group">
              <p><strong>Teléfono: </strong> {{ dataUser.phone }}</p>
            </div>
            <div class="form-group">
              <p><strong>Patrocinador: </strong> {{ sponsor.username }}</p>
            </div>
            <div class="form-group">
              <p><strong>Cantidad de pago: </strong> $ {{ payments.amount }}</p>
            </div>
            <div class="form-group">
              <p><strong>Tipo de documento: </strong> {{ typeDocuments.document }}</p>
            </div>
            <div class="form-group">
              <p><strong>Nro. de documento: </strong> {{ dataUser.nro_document }}</p>
            </div>
            <div class="form-group">
              <p><strong>Método de pago: </strong> {{ paymentMethod.name }}</p>
            </div>
            <div class="form-group">
              <p><strong>N° Binance: </strong> {{ payments.operation_number }}</p>
            </div>
            <div class="form-group" v-if="payments.receipt_image">
              <p><strong>Comprobante de pago: </strong></p>
              <img :src="getImageUrl(payments.receipt_image)"
                   alt="Comprobante de pago"
                   class="img-fluid receipt-image-modal"
                   @click="showImageModal(payments.receipt_image)"
                   @error="handleImageError"/>
            </div>
          </div>
          <div class="modal-footer">
            <div v-if="dataUser.request == 1">
              <button v-if="dataUser.request != 3" type="button" class="btn btn-danger" @click="denyAfiliation()">
                Denegar
              </button>
              <button v-if="dataUser.request != 2" type="button" class="btn btn-primary"
                      @click="approveDeny(dataUser.id, 2, dataUser.id_referrer_sponsor)">
                Aprobar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Reject Afiliation -->
    <custom-modal v-bind:id="'viewRejectAfiliation'">
      <template #title>¿Seguro que desea rechazar la afiliación?</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
            <p>Denegar el pago de afiliación de {{ dataUser.name + ' ' + dataUser.last_name }}</p>
            <div class="form-group">
                <textarea name="mensaje" required="" class="form-control"
                          placeholder="Introduzca el motivo de rechazo del pago de esta afiliación"
                          v-model="justification"></textarea>
            </div>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="approveDeny(dataUser.id, 3, dataUser.id_referrer_sponsor)">
          <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"
                v-if="loading"></span>
          <span class="ml-25 align-middle"> Desautorizar </span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal Image Viewer -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Comprobante de Pago</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <img :src="selectedImage"
                 alt="Comprobante de pago"
                 class="img-fluid"
                 v-if="selectedImage"/>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  components: {
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      initialLoading: true,
      dataUsers: [],
      dataUser: [],
      sponsor: Object,
      payments: [],
      typeDocuments: [],
      paymentMethod: [],
      moment: moment,
      justification: '',
      loading: false,
      selectedImage: null,
      fallbackImage: '/path/to/fallback-image.png'
    };
  },
  mounted() {
    this.listUsers();
  },
  methods: {
    getImageUrl(imagePath) {
      if (!imagePath) return this.fallbackImage;
      return imagePath.startsWith('/storage/') ? imagePath : `/storage/${imagePath}`;
    },

    handleImageError(e) {
      e.target.src = this.fallbackImage;
      this.$message.error('Error al cargar la imagen del comprobante');
    },

    showImageModal(imagePath) {
      if (!imagePath) {
        this.$message.warning('No hay imagen disponible');
        return;
      }
      this.selectedImage = this.getImageUrl(imagePath);
      $('#imageModal').modal('show');
    },

    modalUser(id) {
      $('#modalViewRequest').modal('show');
      axios.get(`user-request/get-user-by-id/${id}`).then((r) => {
        this.dataUser = r.data || {};
        this.sponsor = this.dataUser.sponsor || {};
        this.payments = this.dataUser.payments_client || {};
        this.typeDocuments = this.dataUser.document_type || {};
        this.paymentMethod = this.payments.payment_method || {};
      }).catch(error => {
        console.error('Error al cargar los datos del usuario:', error);
        this.$message.error('No se pudieron cargar los datos del usuario');
        $('#modalViewRequest').modal('hide');
      });
    },
    denyAfiliation() {
      $('#modalViewRequest').modal('hide');
      $(`#viewRejectAfiliation`).modal('show');
    },
    approveDeny(id, status, id_referrer_sponsor) {
      this.loading = true;
      const form = {
        id: id,
        status: status,
        justification: this.justification,
        id_referrer_sponsor: id_referrer_sponsor,
      };

      axios.post(`user-request/update-unverified-request`, form).then((r) => {
        if (r.status == 200 && status == 2) {
          $('#modalViewRequest').modal('hide');
          this.initialLoading = false;
          this.listUsers();
          this.$message.success('Petición aceptada');
        } else {
          this.$message.warning('Petición Rechazada!');
          $(`#viewRejectAfiliation`).modal('hide');
          this.loading = false;
          this.listUsers();
        }
      }).catch(error => {
        console.error('Error al procesar la solicitud:', error);
      });
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#datatable').DataTable(language);
      });
    },
    listUsers() {
      this.initialLoading = true;
      api.get(`user-request/list`).then((response) => {
        this.initialLoading = false;
        this.dataUsers = response.data;
        $('#datatable').DataTable().destroy();
        this.loadDataTable();
      }).catch(error => {
        console.error('Error al obtener la lista de usuarios:', error);
      });
    },
  },
};
</script>

<style scoped>
.receipt-image {
  max-width: 100px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.receipt-image-modal {
  max-width: 100%;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.receipt-image:hover,
.receipt-image-modal:hover {
  transform: scale(1.05);
}
</style><template>
  <div>
    <table id="datatable" class="table-hover table-striped table-bordered">
      <thead>
      <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Fecha de suscripción</th>
        <th>Tipo de membresía</th>
        <th>Id order</th>
        <th>Monto</th>
        <th>Comprobante de pago</th>
        <th>Estado</th>
        <th>Opción</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(user, index) in dataUsers" :key="user.id">
        <td>{{ index + 1 }}</td>
        <td>{{ user.name }}</td>
        <td>{{ user.email || 'N/A' }}</td>
        <td>{{ user.phone || 'N/A' }}</td>
        <td>{{ moment(user.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
        <td>{{ user.account_type?.account || 'N/A' }}</td>
        <td>{{ user.payments_client?.operation_number || 'N/A' }}</td>
        <td>{{ user.payments_client?.amount || 'N/A' }}</td>
        <td>
          <div v-if="user.payments_client?.receipt_image">
            <img :src="getImageUrl(user.payments_client.receipt_image)"
                 alt="Comprobante de pago"
                 class="img-fluid receipt-image"
                 @click="showImageModal(user.payments_client.receipt_image)"
                 @error="handleImageError"/>
          </div>
          <span v-else>N/A</span>
        </td>
        <td>
          <span class="badge" :class="user.request == 1
            ? 'badge-warning'
            : user.request == 2
              ? 'badge-success'
              : 'badge-danger'
            " v-text="user.request == 1 ? 'Pendiente ' : user.request == 2 ? 'Aceptado' : 'Rechazado'
              "></span>
        </td>
        <td><button class="btn btn-info" @click="modalUser(user.id)">observar</button></td>
      </tr>
      </tbody>
    </table>

    <!-- Modal View Request -->
    <div class="modal fade" id="modalViewRequest" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Informacion del Usuario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <p><strong>Usuario: </strong> {{ dataUser.name + ' ' + dataUser.last_name }}</p>
            </div>
            <div class="form-group">
              <p><strong>Correo: </strong> {{ dataUser.email }}</p>
            </div>
            <div class="form-group">
              <p><strong>Teléfono: </strong> {{ dataUser.phone }}</p>
            </div>
            <div class="form-group">
              <p><strong>Patrocinador: </strong> {{ sponsor.username }}</p>
            </div>
            <div class="form-group">
              <p><strong>Cantidad de pago: </strong> $ {{ payments.amount }}</p>
            </div>
            <div class="form-group">
              <p><strong>Tipo de documento: </strong> {{ typeDocuments.document }}</p>
            </div>
            <div class="form-group">
              <p><strong>Nro. de documento: </strong> {{ dataUser.nro_document }}</p>
            </div>
            <div class="form-group">
              <p><strong>Método de pago: </strong> {{ paymentMethod.name }}</p>
            </div>
            <div class="form-group">
              <p><strong>N° Binance: </strong> {{ payments.operation_number }}</p>
            </div>
            <div class="form-group" v-if="payments.receipt_image">
              <p><strong>Comprobante de pago: </strong></p>
              <img :src="getImageUrl(payments.receipt_image)"
                   alt="Comprobante de pago"
                   class="img-fluid receipt-image-modal"
                   @click="showImageModal(payments.receipt_image)"
                   @error="handleImageError"/>
            </div>
          </div>
          <div class="modal-footer">
            <div v-if="dataUser.request == 1">
              <button v-if="dataUser.request != 3" type="button" class="btn btn-danger" @click="denyAfiliation()">
                Denegar
              </button>
              <button v-if="dataUser.request != 2" type="button" class="btn btn-primary"
                      @click="approveDeny(dataUser.id, 2, dataUser.id_referrer_sponsor)">
                Aprobar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Reject Afiliation -->
    <custom-modal v-bind:id="'viewRejectAfiliation'">
      <template #title>¿Seguro que desea rechazar la afiliación?</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
            <p>Denegar el pago de afiliación de {{ dataUser.name + ' ' + dataUser.last_name }}</p>
            <div class="form-group">
                <textarea name="mensaje" required="" class="form-control"
                          placeholder="Introduzca el motivo de rechazo del pago de esta afiliación"
                          v-model="justification"></textarea>
            </div>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="approveDeny(dataUser.id, 3, dataUser.id_referrer_sponsor)">
          <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"
                v-if="loading"></span>
          <span class="ml-25 align-middle"> Desautorizar </span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal Image Viewer -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Comprobante de Pago</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <img :src="selectedImage"
                 alt="Comprobante de pago"
                 class="img-fluid"
                 v-if="selectedImage"/>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  components: {
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      initialLoading: true,
      dataUsers: [],
      dataUser: [],
      sponsor: Object,
      payments: [],
      typeDocuments: [],
      paymentMethod: [],
      moment: moment,
      justification: '',
      loading: false,
      selectedImage: null,
      fallbackImage: '/path/to/fallback-image.png'
    };
  },
  mounted() {
    this.listUsers();
  },
  methods: {
    getImageUrl(imagePath) {
      if (!imagePath) return this.fallbackImage;
      return imagePath.startsWith('/storage/') ? imagePath : `/storage/${imagePath}`;
    },

    handleImageError(e) {
      e.target.src = this.fallbackImage;
      this.$message.error('Error al cargar la imagen del comprobante');
    },

    showImageModal(imagePath) {
      if (!imagePath) {
        this.$message.warning('No hay imagen disponible');
        return;
      }
      this.selectedImage = this.getImageUrl(imagePath);
      $('#imageModal').modal('show');
    },

    modalUser(id) {
      $('#modalViewRequest').modal('show');
      axios.get(`user-request/get-user-by-id/${id}`).then((r) => {
        this.dataUser = r.data || {};
        this.sponsor = this.dataUser.sponsor || {};
        this.payments = this.dataUser.payments_client || {};
        this.typeDocuments = this.dataUser.document_type || {};
        this.paymentMethod = this.payments.payment_method || {};
      }).catch(error => {
        console.error('Error al cargar los datos del usuario:', error);
        this.$message.error('No se pudieron cargar los datos del usuario');
        $('#modalViewRequest').modal('hide');
      });
    },
    denyAfiliation() {
      $('#modalViewRequest').modal('hide');
      $(`#viewRejectAfiliation`).modal('show');
    },
    approveDeny(id, status, id_referrer_sponsor) {
      this.loading = true;
      const form = {
        id: id,
        status: status,
        justification: this.justification,
        id_referrer_sponsor: id_referrer_sponsor,
      };

      axios.post(`user-request/update-unverified-request`, form).then((r) => {
        if (r.status == 200 && status == 2) {
          $('#modalViewRequest').modal('hide');
          this.initialLoading = false;
          this.listUsers();
          this.$message.success('Petición aceptada');
        } else {
          this.$message.warning('Petición Rechazada!');
          $(`#viewRejectAfiliation`).modal('hide');
          this.loading = false;
          this.listUsers();
        }
      }).catch(error => {
        console.error('Error al procesar la solicitud:', error);
      });
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#datatable').DataTable(language);
      });
    },
    listUsers() {
      this.initialLoading = true;
      api.get(`user-request/list`).then((response) => {
        this.initialLoading = false;
        this.dataUsers = response.data;
        $('#datatable').DataTable().destroy();
        this.loadDataTable();
      }).catch(error => {
        console.error('Error al obtener la lista de usuarios:', error);
      });
    },
  },
};
</script>

<style scoped>
.receipt-image {
  max-width: 100px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.receipt-image-modal {
  max-width: 100%;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.receipt-image:hover,
.receipt-image-modal:hover {
  transform: scale(1.05);
}
</style>