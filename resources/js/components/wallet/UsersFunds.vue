<template>
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover-animation table-striped table-bordered" id="myTable">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Cantidad</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="wallet in wallets" :key="wallet.id">
              <td>{{ wallet.user.fullName }}</td>
              <td>
                <span class="text-primary font-weight-bold">$ {{ wallet.available }}</span>
              </td>
              <td>
                <button
                  class="btn btn-sm btn-primary"
                  @click="listWalletsUser(wallet.user.username)"
                >
                  Details
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <h4 class="d-inline">Total:</h4>
      <h3 class="mb-75 mt-2 pt-50 d-inline">
        <a>$ {{ total }} </a>
      </h3>
    </div>

    <custom-modal v-bind:id="'walletDetails'" size="large">
      <template #title>Wallet Details</template>
      <template #body>
        <h3>Wallet for {{ selectedUser }}</h3>
        <div class="table-responsive" v-if="!initialLoading">
          <table class="table table-bordered" id="example">
            <thead>
              <tr>
                <td>Amount</td>
                <td>reason</td>
                <td>Date</td>
              </tr>
            </thead>

            <tbody>
              <tr v-for="wallet in walletsUser" :key="wallet.id">
                <td>
                  <span
                    class="font-weight-bolder"
                    :class="wallet.amount < 0 ? 'text-danger' : 'text-success'"
                    v-text="wallet.amount > 0 ? '+' + wallet.amount : wallet.amount"
                  >
                  </span>
                </td>
                <td>{{ wallet.reason }}</td>
                <td>{{ wallet.created_at | formatDate }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '@/api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import WalletHistoryUser from './WalletHistoryUser.vue';

export default {
  name: 'UsersFunds',
  components: {
    'custom-modal': ModalComponent,
    'wallet-histroy-user': WalletHistoryUser,
  },
  data() {
    return {
      wallets: [],
      selectedUser: '',
      walletsUser: [],
    };
  },

  created() {
    this.listWallets();
  },
  computed: {
    total() {
      let total = 0;
      this.wallets.forEach((wallet) => {
        total += wallet.available;
      });
      return total;
    },
  },
  methods: {
    listWallets() {
      this.initialLoading = true;
      this.wallet = [];
      api.get(`/reports/walletslist`).then((response) => {
        this.initialLoading = false;
        this.wallets = response.data;
        $('#myTable').DataTable().destroy();
        this.loadDataTable();
      });
    },
    loadDataTable() {
      this.$nextTick(function () {
        $('#myTable').DataTable({
          responsive: true,
          processing: true,
        });
      });
    },
    listWalletsUser(user) {
      this.selectedUser = user.toString();

      // console.log([user]);
      // alert(user);
      $('#walletDetails').modal('show');

      api.get(`/reports/mywalletinfo/${user}`).then((response) => {
        this.walletsUser = response.data;
        $('#example').DataTable().destroy();
        this.loadDataTable1();
      });
    },
    loadDataTable1() {
      this.$nextTick(function () {
        $('#example').DataTable({
          responsive: true,
          processing: true,
        });
      });
    },
  },
};
</script>

<style>
</style>