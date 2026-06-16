<template>
  <div>
    <!-- Formulario para agregar métodos de pago -->
    <div class="card p-4 mb-4">
      <h4>Agregar Método de Pago</h4>
      <div class="form-group">
        <label>Método de Pago</label>
        <select class="form-control" v-model="selectedMethod">
          <option value="" disabled>Selecciona un método</option>
          <option v-for="method in listMethods" :key="method.id" :value="method.id">
            {{ method.name }}
          </option>
        </select>
      </div>

      <!-- Formulario para Binance -->
      <div v-if="selectedMethod === 'binance'">
        <div class="form-group">
          <label>Correo Electrónico</label>
          <input type="email" class="form-control" v-model="email" @input="validateEmail" placeholder="Ingresa tu correo">
          <small v-if="errors.email" class="text-danger">Correo inválido</small>
        </div>
        <div class="form-group">
          <label>Nombre del Titular</label>
          <input type="text" class="form-control" v-model="accountName" @input="validateName" placeholder="Nombre del titular">
          <small v-if="errors.accountName" class="text-danger">Solo se permiten letras</small>
        </div>
        <div class="form-group">
          <label>ID de Binance</label>
          <input type="text" class="form-control" v-model="binanceId" placeholder="ID de Binance">
          <small v-if="errors.binanceId" class="text-danger">Este campo es requerido</small>
        </div>
        <div class="form-group">
          <label>Red</label>
          <select class="form-control" v-model="network">
            <option value="BSC">BSC</option>
            <option value="ETH">ETH</option>
            <option value="TRX">TRX</option>
            <option value="BTC">BTC</option>
          </select>
        </div>
        <div class="form-group">
          <label>Teléfono (Opcional)</label>
          <input type="text" class="form-control" v-model="phone" placeholder="Número de teléfono">
        </div>
      </div>

      <!-- Formulario para PayPal -->
      <div v-if="selectedMethod === 'paypal'">
        <div class="form-group">
          <label>Correo Electrónico de PayPal</label>
          <input type="email" class="form-control" v-model="email" @input="validateEmail" placeholder="correo@paypal.com">
          <small v-if="errors.email" class="text-danger">Correo inválido</small>
        </div>
        <div class="form-group">
          <label>Nombre del Titular</label>
          <input type="text" class="form-control" v-model="accountName" @input="validateName" placeholder="Nombre del titular">
          <small v-if="errors.accountName" class="text-danger">Solo se permiten letras</small>
        </div>
        <div class="form-group">
          <label>País</label>
          <select class="form-control" v-model="countryCode">
            <option value="PE">Perú</option>
            <option value="US">Estados Unidos</option>
            <option value="MX">México</option>
            <option value="CO">Colombia</option>
            <option value="AR">Argentina</option>
          </select>
        </div>
        <div class="form-group">
          <label>Moneda</label>
          <select class="form-control" v-model="currency">
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
            <option value="PEN">PEN</option>
          </select>
        </div>
        <div class="form-group">
          <label>Tipo de Cuenta</label>
          <select class="form-control" v-model="accountType">
            <option value="personal">Personal</option>
            <option value="business">Empresarial</option>
          </select>
        </div>
      </div>

      <div v-if="selectedMethod">
        <button class="btn btn-primary mt-3" @click="savePaymentMethod" :disabled="loading">
          {{ loading ? 'Guardando...' : 'Guardar' }}
        </button>
      </div>
    </div>

    <!-- Lista de Métodos de Pago Guardados -->
    <div class="card p-4">
      <h4>Lista de Métodos de Pago</h4>
      <div v-if="loadingAccounts" class="text-center">
        <p>Cargando cuentas...</p>
      </div>
      <table v-else class="table">
        <thead>
        <tr>
          <th>Tipo de Pago</th>
          <th>Correo</th>
          <th>Nombre</th>
          <th>Número/ID de Cuenta</th>
          <th>Info Extra</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(account, index) in accounts" :key="`${account.type}-${account.id}`">
          <td>{{ account.method }}</td>
          <td>{{ account.email }}</td>
          <td>{{ account.account_name }}</td>
          <td>{{ account.account_number }}</td>
          <td>
            <small v-if="account.type === 'binance'">
              Red: {{ account.extra_info.network }}<br>
              <span v-if="account.extra_info.phone">Tel: {{ account.extra_info.phone }}</span>
            </small>
            <small v-if="account.type === 'paypal'">
              {{ account.extra_info.country_code }} - {{ account.extra_info.currency }}<br>
              {{ account.extra_info.account_type }}
              <span v-if="account.extra_info.is_verified" class="badge badge-success">Verificado</span>
            </small>
          </td>
          <td>
            <button class="btn btn-warning btn-sm mr-2" @click="editAccount(account, index)">Editar</button>
            <button class="btn btn-danger btn-sm" @click="deleteAccount(account)" :disabled="loading">Eliminar</button>
          </td>
        </tr>
        </tbody>
      </table>
      <div v-if="accounts.length === 0 && !loadingAccounts" class="text-center text-muted">
        <p>No tienes métodos de pago registrados</p>
      </div>
    </div>

    <!-- Modal de edición -->
    <div v-if="isEditing" class="modal">
      <div class="modal-content">
        <h4>Editar {{ editedAccount.method }}</h4>
        
        <!-- Formulario de edición para Binance -->
        <div v-if="editedAccount.type === 'binance'">
          <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" class="form-control" v-model="editedAccount.email" @input="validateEditEmail">
            <small v-if="errors.editEmail" class="text-danger">Correo inválido</small>
          </div>
          <div class="form-group">
            <label>Nombre del Titular</label>
            <input type="text" class="form-control" v-model="editedAccount.account_name" @input="validateEditName">
            <small v-if="errors.editAccountName" class="text-danger">Solo se permiten letras</small>
          </div>
          <div class="form-group">
            <label>ID de Binance</label>
            <input type="text" class="form-control" v-model="editedAccount.binance_id">
          </div>
          <div class="form-group">
            <label>Red</label>
            <select class="form-control" v-model="editedAccount.network">
              <option value="BSC">BSC</option>
              <option value="ETH">ETH</option>
              <option value="TRX">TRX</option>
              <option value="BTC">BTC</option>
            </select>
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input type="text" class="form-control" v-model="editedAccount.phone">
          </div>
        </div>

        <!-- Formulario de edición para PayPal -->
        <div v-if="editedAccount.type === 'paypal'">
          <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" class="form-control" v-model="editedAccount.email" @input="validateEditEmail">
            <small v-if="errors.editEmail" class="text-danger">Correo inválido</small>
          </div>
          <div class="form-group">
            <label>Nombre del Titular</label>
            <input type="text" class="form-control" v-model="editedAccount.account_name" @input="validateEditName">
            <small v-if="errors.editAccountName" class="text-danger">Solo se permiten letras</small>
          </div>
          <div class="form-group">
            <label>País</label>
            <select class="form-control" v-model="editedAccount.country_code">
              <option value="PE">Perú</option>
              <option value="US">Estados Unidos</option>
              <option value="MX">México</option>
              <option value="CO">Colombia</option>
              <option value="AR">Argentina</option>
            </select>
          </div>
          <div class="form-group">
            <label>Moneda</label>
            <select class="form-control" v-model="editedAccount.currency">
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="PEN">PEN</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tipo de Cuenta</label>
            <select class="form-control" v-model="editedAccount.account_type">
              <option value="personal">Personal</option>
              <option value="business">Empresarial</option>
            </select>
          </div>
        </div>

        <button class="btn btn-success mt-3" @click="updateAccount" :disabled="loading">
          {{ loading ? 'Actualizando...' : 'Actualizar' }}
        </button>
        <button class="btn btn-secondary mt-3 ml-2" @click="cancelEdit">Cancelar</button>
      </div>
    </div>

    <!-- Mensajes de alerta -->
    <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      listMethods: [],
      selectedMethod: '',
      email: '',
      accountName: '',
      // Campos específicos de Binance
      binanceId: '',
      network: 'BSC',
      phone: '',
      // Campos específicos de PayPal
      countryCode: 'PE',
      currency: 'USD',
      accountType: 'personal',
      // Estado general
      accounts: [],
      isEditing: false,
      editedAccount: null,
      editedIndex: -1,
      errors: {},
      loading: false,
      loadingAccounts: false,
      message: '',
      messageType: 'success'
    };
  },
  methods: {
    async loadMethods() {
      try {
        const response = await axios.get('/payment/payment-methods/types');
        if (response.data.success) {
          this.listMethods = response.data.data;
        }
      } catch (error) {
        console.error("Error cargando métodos:", error);
        this.showMessage('Error cargando métodos de pago', 'error');
      }
    },

    async loadAccounts() {
      this.loadingAccounts = true;
      try {
        // OPCIÓN 1: Si implementas /payment/payment-methods
        const response = await axios.get('/payment/payment-methods');
        if (response.data.success) {
          this.accounts = response.data.data;
        }
        
        /* OPCIÓN 2: Si NO implementas /payment/payment-methods, usa esto:
        const [binanceResponse, paypalResponse] = await Promise.all([
          axios.get('/payment/binance-accounts'),
          axios.get('/payment/paypal-accounts')
        ]);
        
        const binanceAccounts = binanceResponse.data.data.map(account => ({
          id: account.id,
          type: 'binance',
          method: 'Binance',
          email: account.email,
          account_name: account.account_name,
          account_number: account.binance_id,
          extra_info: {
            network: account.network,
            phone: account.phone
          }
        }));

        const paypalAccounts = paypalResponse.data.data.map(account => ({
          id: account.id,
          type: 'paypal',
          method: 'PayPal',
          email: account.email,
          account_name: account.account_name,
          account_number: account.email,
          extra_info: {
            country_code: account.country_code,
            currency: account.currency,
            account_type: account.account_type
          }
        }));

        this.accounts = [...binanceAccounts, ...paypalAccounts];
        */
      } catch (error) {
        console.error("Error cargando cuentas:", error);
        this.showMessage('Error cargando las cuentas', 'error');
      } finally {
        this.loadingAccounts = false;
      }
    },

    validateEmail() {
      this.errors.email = !this.email.includes('@');
    },

    validateName() {
      this.errors.accountName = /[^a-zA-Z\s]/.test(this.accountName);
    },

    validateEditEmail() {
      this.errors.editEmail = !this.editedAccount.email.includes('@');
    },

    validateEditName() {
      this.errors.editAccountName = /[^a-zA-Z\s]/.test(this.editedAccount.account_name);
    },

    async savePaymentMethod() {
      if (Object.values(this.errors).some(error => error)) {
        this.showMessage('Por favor corrige los errores en el formulario', 'error');
        return;
      }

      if (!this.email || !this.accountName) {
        this.showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
      }

      this.loading = true;
      
      try {
        let response;
        let data = {
          email: this.email,
          account_name: this.accountName
        };

        if (this.selectedMethod === 'binance') {
          if (!this.binanceId) {
            this.showMessage('El ID de Binance es requerido', 'error');
            return;
          }
          data.binance_id = this.binanceId;
          data.network = this.network;
          if (this.phone) data.phone = this.phone;
          
          response = await axios.post('/payment/binance-accounts', data);
        } else if (this.selectedMethod === 'paypal') {
          data.country_code = this.countryCode;
          data.currency = this.currency;
          data.account_type = this.accountType;
          
          response = await axios.post('/payment/paypal-accounts', data);
        }

        if (response.data.success) {
          this.$message.success('Método de pago guardado exitosamente');
          this.resetForm();
          await this.loadAccounts();
        }
      } catch (error) {
        console.error('Error guardando método de pago:', error);
        const errorMessage = error.response?.data?.message || 'Error guardando el método de pago';
        this.showMessage(errorMessage, 'error');
      } finally {
        this.loading = false;
      }
    },

    async deleteAccount(account) {
      if (!confirm('¿Estás seguro de eliminar este método de pago?')) return;

      this.loading = true;
      try {
        let endpoint = account.type === 'binance' 
          ? `/payment/binance-accounts/${account.id}` 
          : `/payment/paypal-accounts/${account.id}`;
        
        const response = await axios.delete(endpoint);
        
        if (response.data.success) {
          this.$message.success('Método de pago eliminado exitosamente');
          await this.loadAccounts();
        }
      } catch (error) {
        console.error('Error eliminando método de pago:', error);
        this.showMessage('Error eliminando el método de pago', 'error');
      } finally {
        this.loading = false;
      }
    },

    editAccount(account, index) {
      this.editedIndex = index;
      
      if (account.type === 'binance') {
        this.editedAccount = {
          ...account,
          binance_id: account.account_number,
          network: account.extra_info.network,
          phone: account.extra_info.phone
        };
      } else if (account.type === 'paypal') {
        this.editedAccount = {
          ...account,
          country_code: account.extra_info.country_code,
          currency: account.extra_info.currency,
          account_type: account.extra_info.account_type
        };
      }
      
      this.isEditing = true;
    },

    async updateAccount() {
      if (Object.values(this.errors).some(error => error)) {
        this.showMessage('Por favor corrige los errores en el formulario', 'error');
        return;
      }

      this.loading = true;
      try {
        let endpoint, data;
        
        if (this.editedAccount.type === 'binance') {
          endpoint = `/payment/binance-accounts/${this.editedAccount.id}`;
          data = {
            email: this.editedAccount.email,
            account_name: this.editedAccount.account_name,
            binance_id: this.editedAccount.binance_id,
            network: this.editedAccount.network,
            phone: this.editedAccount.phone
          };
        } else if (this.editedAccount.type === 'paypal') {
          endpoint = `/payment/paypal-accounts/${this.editedAccount.id}`;
          data = {
            email: this.editedAccount.email,
            account_name: this.editedAccount.account_name,
            country_code: this.editedAccount.country_code,
            currency: this.editedAccount.currency,
            account_type: this.editedAccount.account_type
          };
        }

        const response = await axios.put(endpoint, data);
        
        if (response.data.success) {
          this.showMessage('Método de pago actualizado exitosamente', 'success');
          this.isEditing = false;
          await this.loadAccounts();
        }
      } catch (error) {
        console.error('Error actualizando método de pago:', error);
        this.showMessage('Error actualizando el método de pago', 'error');
      } finally {
        this.loading = false;
      }
    },

    cancelEdit() {
      this.isEditing = false;
      this.editedAccount = null;
      this.errors = {};
    },

    resetForm() {
      this.selectedMethod = '';
      this.email = '';
      this.accountName = '';
      this.binanceId = '';
      this.network = 'BSC';
      this.phone = '';
      this.countryCode = 'PE';
      this.currency = 'USD';
      this.accountType = 'personal';
      this.errors = {};
    },

    showMessage(text, type = 'success') {
      this.message = text;
      this.messageType = type;
      setTimeout(() => {
        this.message = '';
      }, 5000);
    }
  },

  async mounted() {
    await this.loadMethods();
    await this.loadAccounts();
  }
};
</script>

<style scoped>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  width: 500px;
  max-height: 80vh;
  overflow-y: auto;
}

.alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1001;
  min-width: 300px;
}

.badge {
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 0.75em;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>