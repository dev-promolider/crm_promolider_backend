<template>
  <div>
    <custom-modal id="delete-payment-method-modal" color="danger">
      <template #title>Borrar método de pago <u> {{ form.name }} </u></template>
      <template #body>¿Está seguro de que desea eliminar el método de pago?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deletePaymentMethod(form.id)">
          Eliminar
        </button>
      </template>
    </custom-modal>

    <section>
      <div class="row">
        <div class="col-md-12">
          <div class="mb-1">
            <button class="btn btn-primary" @click="changeOptionModal('0')">Añadir método</button>
          </div>

          <div class="col-12">
            <div class="table-responsive">
              <table id="data-table-list-payments" class="table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Nro</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(tempPaymentMethod, index) in paymentMethods" :key="tempPaymentMethod.id">
                    <td>{{ index + 1 }}</td>
                    <td style="width: 220px">{{ tempPaymentMethod.name }}</td>
                    <td>
                      <div :class="tempPaymentMethod.status === '1'
                          ? 'badge badge-secondary'
                          : 'badge badge-success'
                        ">
                        {{ tempPaymentMethod.status === '1' ? 'inactivo' : 'Activo' }}
                      </div>
                    </td>
                    <td class="align-content-center">
                      <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                        @change="getOptionSelected(tempPaymentMethod)">
                        <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                        </el-option>
                      </el-select>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <custom-modal id="payment-method-modal" color="primary" v-if="form">
      <template #title>{{ optionModal == '0' ? 'Añadir Método' : 'Editar Método ' }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="form-group row">
              <div class="col-12">
                <label for="bank-name"> Nombre del método de pago </label>
              </div>
              <div class="col-12">
                <input v-model="form.name" type="text" id="bank-name" class="form-control" name="name" placeholder=""
                  ref="name-bank" :class="rules ? '' : 'is-invalid'" />
                <div class="invalid-feedback" v-if="!rules">Este campo es requerido</div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="submit()">
          {{ optionModal == '0' ? 'Añadir método' : 'Editar método' }}</el-button>
      </template>
    </custom-modal>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner';
import CustomDeleteModal from './components/CustomDeleteModal';
import CustomSuccessModal from './components/CustomSuccessModal';
import language from '../../../api/traduction_es';
import ModalComponent from '@/components/common/ModalComponent.vue';

const formPaymentMethod = {
  id: null,
  name: '',
  status: '',
};
export default {
  components: {
    CustomSuccessModal,
    CustomDeleteModal,
    CustomSpinner,
    'custom-modal': ModalComponent,
  },
  mounted() {
    this.listPaymentMethods();
  },
  data() {
    return {
      rules: true,
      selectPaymentMethod: {},
      initialLoading: true,
      loading: false,
      paymentMethods: [],
      form: { ...formPaymentMethod },
      editMode: false,
      requestLoading: false,
      optionSelected: '',
      optionModal: '0',
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '3',
          label: 'Cambiar de estado',
        },
      ],
    };
  },
  methods: {
    loadDataTable() {
      $('#data-table-list-payments').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-payments').DataTable(language);
      });
    },
    resetForm() {
      this.form = { ...formPaymentMethod };
      this.editMode = false;
      this.rules = true;
    },
    listPaymentMethods() {
      this.initialLoading = true;
      axios.get(`/config/payment-method/list`).then((response) => {
        this.initialLoading = false;
        this.paymentMethods = response.data.data;
        this.loadDataTable();
      });
    },
    editPaymentMethod(id) {
      this.editMode = true;
      this.form = this.paymentMethods.find((tempPaymentMethod) => tempPaymentMethod.id === id);
    },
    deletePaymentMethod(id) {
      this.selectPaymentMethod = this.paymentMethods.find(
        (tempPaymentMethod) => tempPaymentMethod.id === id
      );
    },
    confirmDeletePaymentMethod(confirm, status) {
      if (confirm) {
        const message = status === '1' ? 'Deleted' : 'Activated';
        this.resetForm();
        this.showToast('success', `Payment method was successfully ${message}`);
        this.listPaymentMethods();
      }
    },
    submit() {
      if (this.form.name === '') {
        this.rules = false;
        return;
      }
      this.rules = true;

      this.loading = true;
      let paymentMethod = {
        id: this.form.id,
        name: this.form.name,
        status: this.form.status,
      };
      if (this.optionModal === '1') {
        axios
          .put(`/config/payment-method/edit/${paymentMethod.id}`, paymentMethod)
          .then((response) => {
            this.notification(response);
          });
      } else {
        axios.post('/config/payment-method/add', paymentMethod).then((response) => {
          this.notification(response);
        });
      }
    },
    notification(response) {
      if (response.data == 'ok') {
        if (this.optionModal === '1') {
          this.showToast('success', `El método de pago ha sido actualizado correctamente`);
        } else {
          this.showToast('success', `El método de pago ha sido registrado correctamente`);
        }
        this.listPaymentMethods();
        $('#payment-method-modal').modal('hide');
      } else if (response.data == 'error_name') {
        this.showToast(
          'warning',
          `El nombre del método de pago ya esta registrado, ingrese otro nombre`
        );
      } else {
        this.showToast('warning', `Ocurrio un error al realizar la acción`);
      }
    },
    showToast(type, message) {
      toastr[type](`${message}`, `${type}!`, {
        positionClass: 'toast-top-center',
        closeButton: true,
        tapToDismiss: false,
      });
    },
    getOptionSelected(tempPaymentMethod) {
      let option = this.optionSelected;
      switch (option) {
        case '1':
          this.rules = true;
          this.optionModal = option;
          this.form = tempPaymentMethod;
          $('#payment-method-modal').modal('show');
          this.optionSelected = '';
          break;
        case '2':
          this.rules = true;
          this.form = tempPaymentMethod;
          $('#delete-payment-method-modal').modal('show');
          this.optionSelected = '';
          break;
        case '3':
          this.optionModal = '1';
          this.form = tempPaymentMethod;
          this.form.status = tempPaymentMethod.status == '0' ? '1' : '0';
          this.submit();

          this.optionSelected = '';
          break;

        default:
          console.log('Error');
          break;
      }
    },
    changeOptionModal(option) {
      this.optionModal = option;
      if (this.optionModal == '0') {
        this.resetForm();
        $('#payment-method-modal').modal('show');
      }
    },
    deletePaymentMethod(id) {
      axios.delete(`/config/payment-method/delete/${id}`).then((response) => {
        if (response.data == 'ok') {
          this.showToast('success', `El método de pago ha sido eliminado correctamente`);
        } else {
          this.showToast('warning', `Ocurrió un error al realizar la acción`);
        }
        this.listPaymentMethods();
        $('#delete-payment-method-modal').modal('hide');
      });
    },
  },
  name: 'PaymentMethod',
};
</script>
