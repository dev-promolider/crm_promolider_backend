<template>
  <div>
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
              </tr>
            </thead>

            <tbody>
              <tr v-for="data in dataset" :key="data.id">
                <td :data-sort="data.created_at">{{ moment(data.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                <td>
                  <span
                    class="font-weight-bolder"
                    :class="evalularEgreso(data) ? 'text-danger' : 'text-success'"
                    v-text="
                      evalularEgreso(data)
                        ? '-' + formatMoney(data.amount)
                        : '+' + formatMoney(data.amount)
                    "
                  >
                  </span>
                </td>
                <td>{{ data.reason }}</td>
                <td>
                  <span v-if="data.type == 'incoming'" class="badge badge-success">Aceptado</span>
                  <span v-else-if="data.type == 'expenses'" class="badge badge-danger">Ejecutado</span>
                  <span v-else class="badge badge-warning">Pendiente</span>
                </td>
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
import ModalComponent from '@/components/common/ModalComponent.vue';
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
      initialLoading: false,
      loading: false,
      moment: moment,
      dataset: [],

    };
  },
  computed: {
    total() {
      let total = 0;
      this.dataset.forEach((data) => {
        if (data.type == 'incoming') {
          total += data.amount;
        } else {
          total -= data.amount;
        }
      });
      return total;
    },
  },
  mounted() {
    this.processData();
  },
  methods: {
    async processData() {
        const {data:expenses} = await axios.get(`/reports/movements/all`);
        const {data:incoming} = await axios.get('/payment/get-all');

        incoming.forEach(element => {
            element.type = "incoming"
        });
        expenses.forEach(element => {
            element.type = "expenses"
        });
        this.dataset = incoming.concat(expenses);
        $('#example').DataTable().destroy();
        this.loadDataTable();
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#example').DataTable(Object.assign({}, language, { order: [[0, 'desc']] }));
      });
    },
    evalularEgreso(movement) {
      //comprueba si el movimiento genera ingreso o generar perdida
        if (movement.type == 'incoming') {
            return false;
        } else {
            return true;
        }
    },
    formatMoney(money) {
      const formateado = money.toLocaleString('en', {
        style: 'currency',
        currency: 'USD',
      });
      return formateado;
    },
  },
};
</script>

<style></style>
