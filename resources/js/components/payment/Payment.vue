<template>
  <div>
    <section v-if="!loading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table
              id="data-table-list-payments"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Usuario</th>
                  <th>Monto</th>
                  <th>Método de pago</th>
                  <th>Número de operación</th>
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
                  <td height="30px">{{ moment(tempPayment.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import apiPayment from '../../api/api.payment';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner';
import language from '../../api/traduction_es';
import moment from 'moment';

export default {
  name: 'Payment',
  components: { CustomSpinner },
  data: () => ({
    payments: [],
    loading: false,
    moment: moment,
  }),
  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $('#data-table-list-payments').DataTable(language);
      });
    },
    listPayments() {
      this.loading = true;
      apiPayment.list().then((response) => {
        this.loading = false;
        this.payments = response.data;
        this.loadDataTable();
      });
    },
  },
  mounted() {
    this.listPayments();
  },
};
</script>

<style lang="css" scoped></style>
