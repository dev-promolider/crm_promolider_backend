<template>
  <div>
    <div class="card">
      <div class="card-body">
        <div class="table-responsive" v-if="!initialLoading">
          <table class="table table-bordered" id="mySales">
            <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Monto</th>
                  <th>Detalle</th>
                  <th>Fecha de venta</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in sales" :key="sale.id">
                  <td height="30px">
                    {{
                      sale.name + ' ' + sale.last_name
                    }}
                  </td>
                  <td height="30px">${{ sale.amount }}</td>
                  <td height="30px">
                    <span v-if="sale.bonus_type_id==2">{{ sale.reason }} - <strong>Bono por venta</strong></span>
                    <span v-else>{{ sale.reason }} - <strong>Bono Productor</strong></span>
                  </td>
                  <td height="30px">{{ moment(sale.created_at).format('DD/MM/YYYY hh:mm') }}</td>
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
    }
  },

  data() {
    return {
      sales: [],
      initialLoading: false,
      moment: moment
    };
  },
  computed: {
    total() {
      let total = 0;
      this.sales.forEach((pay) => {
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
      api.get(`/reports/getsales/${this.id}`)
      .then((response) => {
        this.initialLoading = false;
        this.sales = response.data;
        console.log(response)
        $('#mySales').DataTable().destroy();
        this.loadDataTable();
      });
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#mySales').DataTable(language);
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