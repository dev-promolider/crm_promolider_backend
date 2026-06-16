<template>
  <div>
    <section>
      <div class="row">
        <div class="col-md-12 col-12">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-sm-12">
                  <button class="btn btn-primary mt-1" @click="viewCloseModal(1, 'modalSolicitudFondos')">
                    Solicitud de Fondos
                  </button>
                  <button class="btn btn-primary mt-1" @click="viewCloseModal(1, 'modaltransferFounds')">
                    Trasladar Fondos
                  </button>
                  <button class="btn btn-primary mt-1" @click="viewCloseModal(1, 'modalRecargarFondos')">
                    Recargar Fondos
                  </button>

                  <a href="./config" class="btn btn-primary mt-1"> Configuración de billetera </a>
                  <!-- NUEVO BOTÓN DE CANJE -->
                  <button class="btn btn-success mt-1" @click="goToRedemption">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                      <path d="M20 7h-9"></path>
                      <path d="M14 17H5"></path>
                      <circle cx="17" cy="17" r="3"></circle>
                      <circle cx="7" cy="7" r="3"></circle>
                    </svg>
                    Canje de Premios
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="card">
      <div class="card-body">
        <div class="table-responsive" v-if="!initialLoading">
          <table class="table table-bordered" id="example">
            <thead>
            <tr>
              <td>Fecha</td>
              <td>Monto</td>
              <td>Motivo</td>
              <td>Estado</td>
              <td>Soporte</td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="movement in movements" :key="movement.id">
              <td :data-sort="movement.created_at">{{ moment(movement.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
              <td>
                  <span v-if="movement.status == 2" class="font-weight-bolder text-danger" v-text="evalularEgreso(movement)
                    ? '-' + formatMoney(movement.amount)
                    : '+' + formatMoney(movement.amount)">
                  </span>
                <span v-else-if="movement.status == 0" class="font-weight-bolder text-warning" v-text="evalularEgreso(movement)
                    ? '-' + formatMoney(movement.amount)
                    : '+' + formatMoney(movement.amount)">
                  </span>
                <span v-else-if="movement.status == 1" class="font-weight-bolder"
                      :class="evalularEgreso(movement) ? 'text-danger' : 'text-success'" v-text="evalularEgreso(movement)
                      ? '-' + formatMoney(movement.amount)
                      : '+' + formatMoney(movement.amount)">
                  </span>
              </td>
              <td>{{ movement.reason }}</td>
              <td>
                <span v-if="movement.status == 0" class="badge badge-warning">Pendiente</span>
                <span v-else-if="movement.status == 1" class="badge badge-success">Aceptado</span>
                <span v-else class="badge badge-danger">Ejecutado</span>
              </td>
              <td>
                <div :class="movement.status === 0
                    ? 'text-warning'
                    : movement.status === 1
                      ? 'text-success'
                      : movement.status === 2
                        ? 'text-danger'
                        : ''" @click="openModal(movement)">
                  Ver
                  <span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                           class="icon icon-tabler icons-tabler-outline icon-tabler-eye">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path
                            d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                      </svg>
                    </span>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <h4 class="d-inline">Total:</h4>
        <h3 class="mb-75 mt-2 pt-50 d-inline">
          <a> {{ formatMoney(walletBalance) }} </a>
        </h3>
      </div>
    </div>

    <!-- Modal de Solicitud de Fondos -->
    <custom-modal v-bind:id="'modalSolicitudFondos'">
      <template #title>$ Solicitud de Fondos</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
            <tr>
              <td>
                <div class="form-group">
                  <label>Cantidad de fondos a solicitar</label>
                  <input class="form-control" v-model.number="form_request.amount"
                         placeholder="Cantidad de fondos a solicitar" required />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div class="form-group">
                  <label>Tipo de cuenta</label>
                  <select class="form-control" 
                          v-model="form_request.account_type" 
                          @change="onAccountTypeChange()"
                          required>
                    <option value="">Seleccione tipo de cuenta</option>
                    <option v-for="accountType in availableAccountTypes" 
                            :key="accountType.id" 
                            :value="accountType.id">
                      {{ accountType.name }}
                    </option>
                  </select>
                </div>
              </td>
            </tr>
            <!-- NUEVO: Select para cuentas específicas -->
            <tr v-if="form_request.account_type">
              <td>
                <div class="form-group">
                  <label>
                    <span v-if="form_request.account_type === 'paypal'">Seleccione cuenta de PayPal</span>
                    <span v-else-if="form_request.account_type === 'binance'">Seleccione cuenta de Binance</span>
                    <span v-else>Seleccione cuenta</span>
                  </label>

                  <!-- Mostrar selector solo si hay cuentas disponibles -->
                  <select v-if="availableAccounts.length > 0"
                          class="form-control" 
                          v-model="form_request.selected_account"
                          required>
                    <option value="">
                      <span v-if="form_request.account_type === 'paypal'">Seleccione cuenta PayPal</span>
                      <span v-else-if="form_request.account_type === 'binance'">Seleccione cuenta Binance</span>
                      <span v-else>Seleccione cuenta</span>
                    </option>
                    <option v-for="account in availableAccounts" 
                            :key="account.id" 
                            :value="getAccountValue(account)">
                      {{ getAccountDisplayText(account) }}
                    </option>
                  </select>

                  <!-- Mostrar mensaje si no hay cuentas -->
                  <div v-else class="alert alert-warning" role="alert">
                    <small>
                      <span v-if="form_request.account_type === 'paypal'">
                        No tienes cuentas de PayPal configuradas. 
                        <a href="./config" class="alert-link">Agregar cuenta PayPal</a>
                      </span>
                      <span v-else-if="form_request.account_type === 'binance'">
                        No tienes cuentas de Binance configuradas. 
                        <a href="./config" class="alert-link">Agregar cuenta Binance</a>
                      </span>
                    </small>
                  </div>

                  <!-- Mensaje de ayuda dinámico -->
                  <small class="form-text text-muted" v-if="form_request.account_type && availableAccounts.length > 0">
                    <span v-if="form_request.account_type === 'paypal'">
                      Selecciona la cuenta de PayPal donde deseas recibir los fondos
                    </span>
                    <span v-else-if="form_request.account_type === 'binance'">
                      Selecciona la cuenta de Binance donde deseas recibir los fondos
                    </span>
                  </small>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" 
                @click="requestFounds()" 
                :disabled="!isFormValid || availableAccounts.length === 0">
          <span class="ml-25 align-middle">Enviar Petición</span>
          <span class="spinner-border spinner-border-sm text-danger" 
                role="status" 
                aria-hidden="true"
                v-if="loading"></span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal de Trasladar Fondos -->
    <custom-modal v-bind:id="'modaltransferFounds'">
      <template #title>-> Transladar Fondos a..</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
            <input type="number" class="form-control" v-model.number="form_transfer.amount"
                   placeholder="Cantidad a transladar" required />
            <div class="form-group mt-1">
              <label for="directo_select">Mis directos: </label>
              <el-select v-model="form_transfer.direct" placeholder="Seleccione Directo" class="d-inline">
                <el-option v-for="directo in myDirects" :key="directo.id"
                           :label="directo.username + '-' + directo.name + ' ' + directo.last_name" :value="directo.id">
                </el-option>
              </el-select>
            </div>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" @click="transferFounds()">
          <span class="ml-25 align-middle">Transladar Fondos a ...</span>
          <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"
                v-if="loading"></span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal de Recargar Fondos -->
    <custom-modal v-bind:id="'modalRecargarFondos'">
      <template #title>-> Recargar Fondos</template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-striped">
            <tbody>
            <input type="number" name="amount" v-model="form_recharge.amount" class="form-control"
                   placeholder="Monto a recargar" required />
            <div class="form-group mt-1">
              <label for="directo_select">Bancos disponibles: </label>
              <el-select v-model="form_recharge.type_payment" placeholder="Seleccione Banco" class="d-inline">
                <el-option v-for="paymentMe in listPaymentMethod" :key="paymentMe.id" :label="paymentMe.name"
                           :value="paymentMe.id">
                </el-option>
              </el-select>
            </div>
            </tbody>
          </table>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" @click="rechargeFounds()">
          <span class="ml-25 align-middle">Recargar Fondos</span>
          <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"
                v-if="loading"></span>
        </button>
      </template>
    </custom-modal>

    <!-- Modal de ver Soporte -->
    <custom-modal v-bind:id="'modalVerSoporte'" message="selectedMovement.message"
                  supportImage="selectedMovement.support_image">
      <template #title>Soporte</template>
      <template #body>
        <div class="text-center">
          <img v-if="selectedMovement.support_image" :src="selectedMovement.support_image" alt="Soporte"
               class="img-fluid mb-3" />
          <p v-if="selectedMovement.status == 0">Todavia no se agrego mensajes</p>
          <p>{{ selectedMovement.message }}</p>
        </div>
      </template>
      <template #footer>
        <div class="d-flex justify-content-center">
          <a :href="selectedMovement.support_image" download="Image_Support" class="btn btn-primary">
            Descargar Imagen
          </a>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import apiUsers from '../../api/api.users';
import apiPaymentMethod from '../../api/api.payment-method';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  name: 'WalletHistoryUser',
  components: {
    'custom-modal': ModalComponent,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
    username: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      movements: [],
      initialLoading: false,
      loading: false,
      myDirects: [],
      listPaymentMethod: [],
      availableAccountTypes: [],
      paypalAccounts: [],
      binanceAccounts: [],
      form_request: {},
      form_recharge: {},
      form_transfer: {},
      moment: moment,
      selectedMovement: {},
      walletBalance: 0,
    };
  },

  computed: {
    isFormValid() {
      return (
        this.form_request.amount > 0 &&
        this.form_request.account_type &&
        this.form_request.selected_account &&
        this.form_request.selected_account.trim() !== ''
      );
    },

    availableAccounts() {
      if (this.form_request.account_type === 'paypal') {
        return this.paypalAccounts;
      } else if (this.form_request.account_type === 'binance') {
        return this.binanceAccounts;
      }
      return [];
    }
  },

  mounted() {
    this.listMovements();
    this.listDirects();
    this.listBanks();
    this.loadAvailableAccountTypes();
    this.initForms();
    this.getWalletBalance();
    this.loadPaypalAccounts();
    this.loadBinanceAccounts();
  },

  methods: {
    async getWalletBalance() {
      try {
        const response = await api.get('/reports/wallet/balance');
        
        if (response.data && typeof response.data.total_balance !== 'undefined') {
          this.walletBalance = response.data.total_balance;
        } else {
          this.walletBalance = this.calculateLocalTotal();
        }
      } catch (error) {
        console.error('❌ Error al obtener el balance de la wallet:', error);
        this.$message.error('Error al cargar el balance de la billetera');
        this.walletBalance = this.calculateLocalTotal();
      }
    },

    goToRedemption() {
      // Opción 1: Navegar a una ruta
      window.location.href = '/wallet/redemption';
      
      // Opción 2: Si usas Vue Router
      // this.$router.push('/wallet/redemption');
      
      // Opción 3: Mostrar en un modal
      // this.showRedemptionModal = true;
    },

    async onAccountTypeChange() {
      this.form_request.selected_account = '';

      if (this.form_request.account_type === 'paypal') {
        await this.loadPaypalAccounts();
      } else if (this.form_request.account_type === 'binance') {
        await this.loadBinanceAccounts();
      }
    },

    getAccountDisplayText(account) {
      if (this.form_request.account_type === 'paypal') {
        return `${account.account_name} (${account.email})`;
      } else if (this.form_request.account_type === 'binance') {
        return `${account.account_name} (${account.binance_id})`;
      }
      return '';
    },

    getAccountValue(account) {
      if (this.form_request.account_type === 'paypal') {
        return account.email;
      } else if (this.form_request.account_type === 'binance') {
        return account.binance_id;
      }
      return '';
    },

    async loadPaypalAccounts() {
      try {
        const response = await api.get('/payment/paypal-accounts');
        
        if (response.data && response.data.success && Array.isArray(response.data.data)) {
          this.paypalAccounts = response.data.data;
          return;
        }
        
        if (Array.isArray(response.data)) {
          this.paypalAccounts = response.data;
          return;
        }
        
        this.paypalAccounts = [];
      } catch (error) {
        console.error('❌ Error al cargar cuentas de PayPal:', error);
        this.$message.error('Error al cargar cuentas de PayPal');
        this.paypalAccounts = [];
      }
    },

    async loadBinanceAccounts() {
      try {
        const response = await api.get('/payment/binance-accounts');
        
        if (response.data && response.data.success && Array.isArray(response.data.data)) {
          this.binanceAccounts = response.data.data;
          return;
        }
        
        if (Array.isArray(response.data)) {
          this.binanceAccounts = response.data;
          return;
        }
        
        this.binanceAccounts = [];
      } catch (error) {
        console.error('❌ Error al cargar cuentas de Binance:', error);
        this.$message.error('Error al cargar cuentas de Binance');
        this.binanceAccounts = [];
      }
    },

    async loadAvailableAccountTypes() {
      try {
        const response = await api.get('/payment/payment-methods/types');
        
        if (response.data && response.data.success && response.data.data) {
          this.availableAccountTypes = response.data.data;
        } else if (Array.isArray(response.data)) {
          this.availableAccountTypes = response.data;
        } else {
          this.availableAccountTypes = [
            { id: 'binance', name: 'Binance' },
            { id: 'paypal', name: 'PayPal' }
          ];
        }
      } catch (error) {
        console.error('❌ Error al cargar tipos de cuenta:', error);
        this.$message.error('Error al cargar tipos de cuenta disponibles');
        
        this.availableAccountTypes = [
          { id: 'binance', name: 'Binance' },
          { id: 'paypal', name: 'PayPal' }
        ];
      }
    },

    calculateLocalTotal() {
      let total = 0;
      this.movements.forEach((movement) => {
        if (movement.status == 1 || movement.status == 0) {
          if (this.evalularEgreso(movement)) {
            total = total - movement.amount;
          } else {
            total += movement.amount;
          }
        }
      });
      return total;
    },

    async isFreeMembership() {
      try {
        if (
            this.membreshipCurrent &&
            (this.membreshipCurrent.account === 'Basic' ||
                this.membreshipCurrent.account === 'Guest' ||
                this.membreshipCurrent.price === 0)
        ) {
          return true;
        }

        const response = await fetch(`/account-type/get-data-id/${this.id}`);
        const accountData = await response.json();
        return (
            accountData.price === 0 ||
            accountData.account === 'Basic' ||
            accountData.account === 'Guest'
        );
      } catch (error) {
        console.error('Error al verificar tipo de membresía:', error);
        return false;
      }
    },

    async validateOPCStatus() {
      if (await this.isFreeMembership()) {
        return true;
      }

      try {
        const response = await fetch('/opc_config/get-expiration-date');
        const data = await response.json();
        const expirationDate = new Date(data.expiration_date);
        const currentDate = new Date();

        if (currentDate > expirationDate) {
          this.$message.warning('Su OPC está vencido. No puede realizar operaciones.');
          return false;
        }

        return true;
      } catch (error) {
        console.error('Error al verificar el estado del OPC:', error);
        this.$message.error('No se pudo verificar el estado del OPC');
        return false;
      }
    },

    async validateOPCPrice() {
      if (await this.isFreeMembership()) {
        return true;
      }
      try {
        const response = await fetch('/opc_config/get-current-price');
        const data = await response.json();
        const opcPrice = parseFloat(data.price);

        if (this.walletBalance < opcPrice) {
          this.$message.warning(
              `Su saldo actual es insuficiente. Necesita al menos $${opcPrice} para realizar operaciones.`
          );
          return false;
        }

        return true;
      } catch (error) {
        console.error('Error al validar el precio del OPC:', error);
        this.$message.error('No se pudo validar el saldo');
        return false;
      }
    },

    async validateMinimumBalanceForOPC(amount) {
      if (await this.isFreeMembership()) {
        return true;
      }
      try {
        const priceResponse = await fetch('/opc_config/get-current-price');
        const priceData = await priceResponse.json();
        const opcPrice = parseFloat(priceData.price);

        const remainingBalance = this.walletBalance - amount;
        if (remainingBalance < opcPrice) {
          this.$message.warning(
              `No puede realizar esta operación. Debe mantener al menos $${opcPrice} en su saldo para el OPC.`
          );
          return false;
        }

        return true;
      } catch (error) {
        console.error('Error al validar saldo mínimo para OPC:', error);
        this.$message.error('No se pudo validar el saldo');
        return false;
      }
    },

    validateAccountNumber() {
      this.form_request.account_number = this.form_request.account_number.replace(/\D/g, '');
    },

    initForms() {
      this.form_request = {
        amount: '',
        account_type: '',
        selected_account: ''
      };
      this.form_transfer = {
        direct: '',
        amount: '',
      };
      this.form_recharge = {
        amount: '',
        type_payment: 1,
      };
    },

    openModal(movement) {
      this.selectedMovement = movement;
      $('#modalVerSoporte').modal('show');
    },

    listMovements() {
      this.initialLoading = true;
      api.get(`/reports/mymovements/${this.id}`).then((response) => {
        this.initialLoading = false;
        this.movements = response.data;
        $('#example').DataTable().destroy();
        this.loadDataTable();
        this.getWalletBalance();
      });
    },

    loadDataTable() {
      this.$nextTick(function () {
        $('#example').DataTable(Object.assign({}, language, { order: [[0, 'desc']] }));
      });
    },

    listBanks() {
      apiPaymentMethod.list().then((response) => {
        this.listPaymentMethod = response.data.filter((data) =>
            ['Tarjeta crédito / débito'].includes(data.name)
        );
      });
    },

    listDirects() {
      apiUsers.listMyDirects().then((response) => {
        this.myDirects = response;
      });
    },

    viewCloseModal(opcion, id_modal) {
      this.initForms();
      if (opcion == 1) {
        $('#' + id_modal + '').modal('show');
      } else {
        $('#' + id_modal + '').modal('hide');
      }
    },

    evalularEgreso(movement) {
      if (movement.type == 0) {
        if (movement.id_receiver == this.id) {
          return false;
        } else {
          return true;
        }
      }
      return false;
    },

    formatMoney(money) {
      const formateado = money.toLocaleString('en', {
        style: 'currency',
        currency: 'USD',
      });
      return formateado;
    },

    validateFounds(founds) {
      if (founds <= 0) {
        this.$message.warning('La cantidad no puede ser 0');
        return false;
      }
      if (founds > this.walletBalance) {
        this.$message.warning('No cuenta con fondos suficientes');
        return false;
      }
      return true;
    },

    validateLabels() {
      if (this.form_transfer.direct == '') {
        this.$message.warning('Seleccione un directo');
        return false;
      }
      return true;
    },

    requestFounds() {
      if (this.form_request.amount < 20) {
        this.$message.warning('El monto mínimo para solicitar fondos es de $20');
        return;
      }

      if (!this.form_request.account_type) {
        this.$message.warning('Debe seleccionar un tipo de cuenta');
        return;
      }

      if (!this.form_request.selected_account) {
        this.$message.warning('Debe seleccionar una cuenta');
        return;
      }

      Promise.all([
        this.validateOPCStatus(),
        this.validateOPCPrice(),
        this.validateMinimumBalanceForOPC(this.form_request.amount),
        this.validateFounds(this.form_request.amount),
      ]).then(([opcValid, priceValid, minBalanceValid, foundValid]) => {
        if (opcValid && priceValid && minBalanceValid && foundValid) {
          this.loading = true;
          const dataform = new FormData();
          dataform.append('amount', this.form_request.amount);
          dataform.append('account_type', this.form_request.account_type);
          dataform.append('account_number', this.form_request.selected_account);

          api.post('/reports/movements/request-founds', dataform).then((response) => {
            if (response.data.status === 'ok') {
              this.listMovements();
              this.viewCloseModal(2, 'modalSolicitudFondos');
              this.$message.success('Solicitud Enviada');
            } else {
              this.$message.warning('Error al realizar la transferencia');
            }
            this.loading = false;
          });
        }
      });
    },

    transferFounds() {
      if (this.form_transfer.amount < 20) {
        this.$message.warning('El monto mínimo para transferir fondos es de $20');
        return;
      }

      Promise.all([
        this.validateOPCStatus(),
        this.validateOPCPrice(),
        this.validateMinimumBalanceForOPC(this.form_transfer.amount),
        this.validateFounds(this.form_transfer.amount),
        this.validateLabels(),
      ]).then(([opcValid, priceValid, minBalanceValid, foundValid, labelsValid]) => {
        if (opcValid && priceValid && minBalanceValid && foundValid && labelsValid) {
          this.$confirm('¿Está seguro de realizar esta operación?', 'Confirmación', {
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
            type: 'warning',
          })
              .then(() => {
                const formdata = new FormData();
                formdata.append('amount', this.form_transfer.amount);
                formdata.append('direct', this.form_transfer.direct);

                api.post(`/reports/movements/transfer-founds`, formdata).then((response) => {
                  if (response.data.status === 'ok') {
                    this.listMovements();
                    this.viewCloseModal(2, 'modaltransferFounds');
                    this.$message.success('Transferencia realizada');
                  } else {
                    this.$message.warning('Error al realizar la transferencia');
                  }
                });
              })
              .catch(() => {
                this.$message({
                  type: 'info',
                  message: 'Operación cancelada',
                });
              });
        }
      });
    },

    getPlaceholderText() {
      switch (this.form_request.account_type) {
        case 'paypal':
          return 'ejemplo@correo.com';
        case 'binance':
          return 'ID de Binance';
        default:
          return 'Ingrese número de cuenta';
      }
    },

    rechargeFounds() {
      if (this.form_recharge.amount <= 0) {
        this.$message.warning('La cantidad no puede ser 0');
        return false;
      }
      if (!this.form_recharge.type_payment) {
        this.$message.warning('Seleccione al menos un metodo de pago');
        return false;
      }

      this.$confirm('Esta seguro de realizar esta Operación?', 'Confirmación!', {
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        type: 'warning',
        center: true,
      })
          .then(() => {
            window.location.href = `/pay/recharge-openpay/${this.form_recharge.amount}/${this.form_recharge.type_payment}`;
          })
          .catch(() => {
            this.$message({
              type: 'info',
              message: 'Operación cancelada',
            });
          });
    },
  },
};
</script>