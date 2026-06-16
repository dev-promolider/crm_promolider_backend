<template>
  <div>
    <div class="table-responsive" v-if="!initialLoading">
      <table id="datatable" class="table-hover table-striped table-bordered">
        <thead>
        <tr>
          <th>Fecha de pago</th>
          <th>Cliente</th>
          <th>Tipo de cuenta</th>
          <th>Número de cuenta</th>
          <th>Descripción</th>
          <th>Monto</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="payment in payments" :key="payment.id">
          <td>{{ moment(payment.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          <td>
            <a href="#" @click="openModal(payment, 'viewUser')">
              {{ payment.wallet.user.fullName }}
            </a>
          </td>
          <td>{{ payment.account_type || '-' }}</td>
          <td>{{ payment.account_number || '-' }}</td>
          <td>{{ payment.reason }}</td>
          <td>$ {{ payment.amount }}</td>
          <td>
            <button class="btn btn-info" @click="modalUser(payment)">observar</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
    <custom-spinner v-else></custom-spinner>

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
              <p><strong>Usuario: </strong> {{optionSelected.wallet.user.fullName}}</p>
            </div>
            <div class="form-group">
              <p><strong>Tipo de cuenta: </strong> {{optionSelected.account_type || '-'}}</p>
            </div>
            <div class="form-group">
              <p><strong>Número de cuenta: </strong> {{optionSelected.account_number || '-'}}</p>
            </div>
            <div class="form-group">
              <p><strong>Cantidad de pago: </strong> $ {{ optionSelected.amount }}</p>
            </div>
            <div class="form-group">
              <p><strong>Nro. de documento: </strong> {{ optionSelected.wallet.user.nro_document }}</p>
            </div>
            <form @submit.prevent="submit" id="upload" enctype="multipart/form-data" method="post">
              <div class="form-group">
                <p><strong>Message:</strong> </p>
                <textarea class="textArea" rows="5" cols="30" v-model="message"></textarea>
              </div>
              <div class="form-group">
                <p><strong>Agregar archivo:</strong></p>
                <div
                    class="file-upload-area"
                    @drop.prevent="handleDrop"
                    @dragover.prevent
                    @click="triggerFileInput"
                >
                  <input
                      type="file"
                      ref="fileInput"
                      style="display: none;"
                      @change="handleFileChange"
                  />
                  <p>Arrastra y suelta un archivo aquí o haz clic para seleccionar uno</p>
                </div>
              </div>
            </form>
            <div v-if="selectedFile" class="form-group">
              <p><strong>Vista previa del archivo:</strong></p>
              <img :src="imagePreview" alt="Vista previa" style="max-width: 100%; height: auto;" />
            </div>
          </div>
          <div class="modal-footer">
            <button
                type="button"
                class="btn btn-danger"
                @click="rejectRequest(optionSelected.id)"
            >
              Denegar
            </button>
            <button
                type="button"
                form="upload"
                class="btn btn-primary"
                @click="submit(optionSelected.id)"
            >
              Aprobar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner';
import PaymentRequestModalProduct from '@/components/payment/PaymentRequestModalProduct.vue';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  name: 'PaymentRequest',
  components: {
    CustomSpinner,
    'payment-request-modal-product': PaymentRequestModalProduct,
  },
  data: () => ({
    payments: {
      wallet:{
        user:{

        }
      }
    },
    message: '',
    id: null,
    imagePreview: null,
    selectedFile: null,
    paymentSelect: {},
    optionSelected:{
      wallet:{
        user:{

        }
      }
    },
    initialLoading: true,
    moment: moment,
  }),
  mounted() {
    this.listPayments();
  },
  methods: {
    modalUser(payment) {
      this.optionSelected = payment;
      this.id = payment.id;
      $('#modalViewRequest').modal('show');
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.selectedFile = file;
        this.imagePreview = URL.createObjectURL(file);
      };
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedFile = file;
        this.imagePreview = URL.createObjectURL(file);
      };
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#datatable').DataTable(language);
      });
    },
    listPayments() {
      this.initialLoading = true;
      axios.get(`/reports/movements/request-founds/list`).then((response) => {
        this.initialLoading = false;
        this.payments = response.data;
        $('#datatable').DataTable().destroy();
        this.loadDataTable();
      });
    },
    openModal(payment, idSelect) {
      this.paymentSelect = payment;
      $(`#${idSelect}`).modal('show');
    },
    async submit() {

      const formdata = new FormData();
      formdata.append('id', this.id); // Agregar el ID al FormData
      formdata.append('message', this.message);
      if (this.selectedFile) {
        formdata.append('support_image', this.selectedFile);
      }

      try {
        const response = await axios({
          url: '/reports/movements/request-founds/approve',
          method: 'post',
          data: formdata,
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.status === 200) {
          this.$message.success('Petición aceptada');
          $('#modalViewRequest').modal('hide');
          this.listPayments();
        } else {
          this.$message.warning('Error al validar datos');
        }

        this.message = ''; // Limpiar el campo de mensaje
        this.selectedFile = null; // Limpiar el archivo seleccionado
        this.id = null; // Limpiar el ID después del envío
      } catch (error) {
        console.log(error);
        this.$message.error('Error al enviar la solicitud');
      }
    },
    rejectRequest(id){
      const form = {
        id: id
      }
      axios.patch('/reports/movements/request-founds/reject', form).then((response) => {
        $('#modalViewRequest').modal('hide');
        this.listPayments();
        this.$message.warning('Petición Rechazada!');
      });
    }
  },
};
</script>

<style lang="css" scoped>
.textArea{
  resize:none;
  padding:4px;
}
.file-upload-area {
  border: 2px dashed #ccc;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  margin-bottom: 15px;
  background-color: #f9f9f9;
}
.file-upload-area:hover {
  background-color: #e9e9e9;
}
</style>
