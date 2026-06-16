<template>
  <div>
    <div class="card">
      <div class="card-body">
        <div class="table-responsive" v-if="!initialLoading">
          <table class="table table-bordered" id="mypurchase">
            <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Usuario</th>
                  <th>Monto</th>
                  <th>Método de pago</th>
                  <th>Número de operación</th>
                  <th>Detalle</th>
                  <th>Fecha de compra</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tempPayment in payments" :key="tempPayment.id">
                  <td height="30px">
                    {{
                      tempPayment.user.name + ' ' + tempPayment.user.last_name
                    }}
                  </td>
                  <td height="30px">{{ tempPayment.user.username }}</td>
                  <td height="30px">${{ tempPayment.amount }}</td>
                  <td height="30px">{{ tempPayment.payment_method.name }}</td>
                  <td height="30px">{{ tempPayment.operation_number }}</td>
                  <td height="30px">{{ tempPayment.details }}</td>
                  <td height="30px">{{ moment(tempPayment.created_at).format('DD/MM/YYYY hh:mm') }}</td>
                </tr>
              </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <h4 class="d-inline">Total:</h4>
        <h3 class="mb-75 mt-2 pt-50 d-inline">
          <a> {{ formatMoney(total) }} </a>
        </h3>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
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
      payments: [],
      initialLoading: false,
      moment: moment,
    };
  },
  computed: {
    total() {
      let total = 0;
      this.payments.forEach((pay) => {
        total += pay.amount;
      });
      return total;
    },
  },
  mounted() {
    this.listMovements();
  },
  methods: {
    listMovements() {
      this.initialLoading = true;
      api.get(`/payment/get-all-user/${this.id}`)
      .then((response) => {
        this.initialLoading = false;
        this.payments = response.data;
        $('#mypurchase').DataTable().destroy();
        this.loadDataTable();
      });
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#mypurchase').DataTable(language);
      });
    },
    formatMoney(money) {
      const formateado = money.toLocaleString('en', {
        style: 'currency',
        currency: 'USD',
      });
      return formateado;
    }
  },
};
</script>