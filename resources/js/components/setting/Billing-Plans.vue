<template>
  <div>
    <section>
      <div class="row">
        <div class="col-12">
          <div class="card border-primary">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <span class="mt-1">
                  <h3>
                    Membresía Actuales:
                    <span class="badge bg-light-primary">
                      {{ membreshipCurrent.account }}
                    </span>
                  </h3>
                </span>
                <div class="d-flex justify-content-center">
                  <sup class="h5 pricing-currency text-primary mt-1 mb-0">$</sup>
                  <span class="fw-bolder display-4 mb-0 text-primary">
                    {{ membreshipCurrent.price }}
                  </span>
                  <sub class="pricing-duration font-small-4 ms-25 mt-auto mb-2">/year</sub>
                </div>
              </div>
              <ul class="ps-1 mb-2 ml-3">
                <li>
                  IVA : <b>{{ membreshipCurrent.iva }}</b>
                </li>
                <li>
                  Descuento en compras : <b>{{ membreshipCurrent.disc_purchases_course }}</b>
                </li>
                <li>
                  Ganancia por corte binario : <b>{{ membreshipCurrent.pay_in_binary }}</b>
                </li>
                <li>
                  Ganancia en compras : <b>{{ membreshipCurrent.productor_bonus }}</b>
                </li>
                <li>
                  Ganancia en segunda generación de compras :
                  <b>{{ membreshipCurrent.course_selling_bonus }}</b>
                </li>
                <li>
                  Bono de efectivo rápido : <b>{{ membreshipCurrent.fast_cash_bonus }}</b>
                </li>
                <li>
                  Comisionable : <b>{{ membreshipCurrent.comission }}</b>
                </li>
              </ul>
              <div class="d-flex justify-content-between align-items-center fw-bolder mb-50">
                <span>Días</span>
                <span v-if="membreshipCurrent.account == 'Basic'"> Indefinido </span>
                <span v-else-if="diferencia_dias > 0">
                  {{ diferencia_dias }} de {{ qtyDays }} días
                </span>
                <span v-else-if="diferencia_dias == 0"> 0 de {{ qtyDays }} días </span>
                <span v-else> Expirado </span>
              </div>
              <div class="progress mb-50" style="height: 8px">
                <div class="progress-bar" role="progressbar"
                  :style="'width:' + (qtyDays - diferencia_dias) * 3.33 + '%'" aria-valuemax="30" aria-valuemin="0">
                </div>
              </div>
              <template v-if="membreshipCurrent.account == 'Basic'">
                <span>Indefinido</span>
              </template>
              <template v-else-if="diferencia_dias == 1">
                <span>1 día restante</span>
              </template>
              <template v-else-if="diferencia_dias == 0">
                <span>Último día</span>
              </template>
              <template v-else-if="diferencia_dias > 1">
                <span>{{ diferencia_dias }} días restantes</span>
              </template>
              <template v-else>
                <span>Su membresía ha expirado</span>
              </template>
              <div class="d-grid w-100 mt-2">
                <button class="btn btn-primary waves-effect waves-float waves-light" data-bs-target="#"
                  data-bs-toggle="" @click="changeOptionModal()">
                  Plan de actualización
                </button>
                <button class="btn btn-primary waves-effect waves-float waves-light" data-bs-target="#"
                  data-bs-toggle="" @click="openMembershipHistory()">
                  Historial de membresía
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <custom-modal id="level-modal-badges" color="primary">
      <template #title>Adquiere un nueva membresía!</template>
      <template #body>
        <div class="row">
          <el-table :data="accountTypes" style="width: 100%">
            <el-table-column prop="account" label="Membresia" width="180"> </el-table-column>
            <el-table-column prop="price" label="Precio" width="180">
              <template slot-scope="scope">
                {{ scope.row.price }} + IVA ({{ scope.row.iva }}%)
              </template>
            </el-table-column>
            <el-table-column label="Acción">
              <template slot-scope="scope">
                <el-button class="btn btn-primary waves-effect waves-float waves-light"
                  @click="changeOptionModalTwo(scope.row)">Seleccionar</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </template>
      <template #footer> </template>
    </custom-modal>

    <custom-modal id="confirmationYesNo" color="primary">
      <template #title>Membresía</template>
      <template #body>
        <div class="row">
          <div class="col-12 mb-2">
            <el-select clearable v-model="payment_method_id" placeholder="Seleccione metodo de pago"
              @change="changePaymentType">
              <el-option v-for="item in paymentMethod" :label="item.name" :value="item.id" :key="item.id"></el-option>
            </el-select>
          </div>
          <div class="form-group col-12" v-if="payment_method_id == 5">
            <p style="font-weight: bold">Saldo Billetera: $/ {{ saldoTotal }}</p>
            <p style="font-weight: bold">Precio base: $/ {{ id_membership.price }}</p>
            <p style="font-weight: bold">
              IVA ({{ id_membership.iva }}%): $/
              {{ (id_membership.price * (id_membership.iva / 100)).toFixed(2) }}
            </p>
            <p style="font-weight: bold">Total membresía: $/ {{ importeMenbreship.toFixed(2) }}</p>
          </div>
          <div class="col-12">
            {{ messageComputed }}
            <p id="message_conditions" class="pt-3 m-0">
              Al hacer click en "Comprar" usted está aceptando nuestros
              <button type="button" class="btn btn-link" @click="showConditions()">
                términos y condiciones.
              </button>
            </p>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button
          :disabled="payment_method_id === 5 && saldoTotal < importeMenbreship"
          v-if="shouldDisplayBuyButton"
          class="btn btn-primary"
          @click="buyMembership()"
        >
          Comprar
        </el-button>
      </template>
    </custom-modal>

    <custom-modal id="user-membership-history" color="primary">
      <template #title>Historial de membresía</template>
      <template #body>
        <div class="row">
          <el-table :data="listUserMembership" style="width: 100%">
            <el-table-column prop="account_type_name" label="Membresía" min-width="100">
            </el-table-column>

            <el-table-column label="Fecha de vencimiento" width="180">
              <template slot-scope="scope">
                {{ moment(scope.row.expiration_membership_date).format('DD/MM/YYYY hh:mm A') }}
              </template>
            </el-table-column>

            <el-table-column label="Estado">
              <template slot-scope="scope">
                <p v-if="scope.row.status == 1" class="label text-success">Activo</p>
                <p v-else class="label text-danger">Expirado</p>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </template>
      <template #footer> </template>
    </custom-modal>

    <div class="modal fade" id="modal_conditions2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Términos y condiciones</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div v-html="conditions"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click="hideConditions()">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import apiAccountType from '../../api/api.account-type';
import moment from 'moment';
import axios from 'axios';

export default {
  name: 'BillingPlansUserProfile',
  components: {
    'custom-modal': ModalComponent,
  },
  props: {
    membreshipCurrent: {
      type: Object,
      required: true,
    },
    userCurrent: {
      type: Object,
      required: true,
    },
  },
  computed: {
    shouldDisplayBuyButton() {
      if (this.membreshipCurrent.price === 0) {
        return this.payment_method_id !== null;
      }
    
      // Si es pago con tarjeta u otro método id=1
      if (this.sameMembership === 0 && this.payment_method_id === 1) {
        return true;
      }
    
      // Si es pago con billetera id=5, validar saldo
      if (this.payment_method_id === 5) {
        return this.saldoTotal >= this.importeMenbreship;
      }
    
      return false;
    },
    messageComputed() {
      return this.message;
    },
  },
  data: () => ({
    diferencia_dias: 0,
    accountTypes: [],
    paymentMethod: [],
    initialLoading: true,
    message: '',
    process: 0,
    id_membership: [],
    sameMembership: 0,
    listUserMembership: [],
    moment: moment,
    yearDifference: 0,
    membershipData: [],
    qtyDays: '',
    conditions: '',
    payment_method_id: null,
    saldoTotal: 0,
    importeMenbreship: 0,
  }),
  methods: {
    async showConditions() {
      await axios.get('/pay/get-openpay-conditions').then((r) => {
        this.conditions = r.data;
      });
      $('#confirmationYesNo').modal('hide');
      $('#modal_conditions2').modal({ show: true });
      $('#modal_conditions2').addClass('overflow-auto');
    },
    hideConditions() {
      $('#modal_conditions2').modal('hide');
      $('#confirmationYesNo').modal({ show: true });
    },
    getPaymentMethod() {
      axios.get(`/config/payment-method/list-array`).then((response) => {
        this.paymentMethod = response.data.filter(
          (data) => !['Efectivo', 'Paypal', 'Transferencia', 'Binance'].includes(data.name)
        );
      });
    },
    difference(date1, date2) {
      const date1utc = Date.UTC(date1.getFullYear(), date1.getMonth(), date1.getDate());
      const date2utc = Date.UTC(date2.getFullYear(), date2.getMonth(), date2.getDate());
      let day = 1000 * 60 * 60 * 24;
      return (date2utc - date1utc) / day;
    },

    async calcularDiasRestantes() {
      var fecha_expiracion_srt = String(this.userCurrent.expiration_membership_date).split('T');
      var fecha_expiracion = new Date(fecha_expiracion_srt[0]);
      var fecha_actual = new Date();
      this.diferencia_dias = this.difference(fecha_actual, fecha_expiracion);
      await axios
        .get(`/account-type/get-data-id/${this.userCurrent.id_account_type}`)
        .then((response) => {
          this.membershipData = response.data;
        });
      this.qtyDays = this.membershipData.account == 'Guest' ? 30 : 365;
    },

    changeOptionModal() {
      $('#level-modal-badges').modal('show');
    },
    changeOptionModalTwo(account) {
      if (
        this.membreshipCurrent.account === 'Basic' ||
        this.membreshipCurrent.account === 'Guest'
      ) {
        this.sameMembership = 0;
        this.message = 'Ha seleccionado la membresía ' + account.account;
        this.process = 1;
      } else {
        if (account.price < this.membreshipCurrent.price) {
          this.message = 'No se puede cambiar a una membresía inferior';
          this.sameMembership = 1;
        } else {
          this.message = 'Ha seleccionado la membresía ' + account.account;
          this.process = 1;
          this.sameMembership = 0;
        }
      }

      this.importeMenbreship = account.price + account.price * (account.iva / 100);
      this.id_membership = {
        ...account,
        iva: account.iva,
      };

      $('#level-modal-badges').modal('hide');
      $('#confirmationYesNo').modal('show');
    },
    changeOptionModalReturn() {
      this.payment_method_id = null;
      $('#confirmationYesNo').modal('hide');
      $('#level-modal-badges').modal('show');
    },

    listAccountTypes() {
      this.initialLoading = true;
      apiAccountType.list().then((response) => {
        this.initialLoading = false;
      
        let filteredAccounts = response.data.filter((data) => !['Admin'].includes(data.account));
      
        const fechaActual = new Date();
        const fechaExpiracion = new Date(this.userCurrent.expiration_membership_date);
        const membershipExpired = fechaActual > fechaExpiracion;
      
        // 🔧 Nuevo filtro: solo mostrar membresías con precio mayor a la actual
        filteredAccounts = filteredAccounts.filter((account) => account.price > this.membreshipCurrent.price);
      
        this.accountTypes = filteredAccounts;
        this.filterUserMembership();
      });
    },
    changePaymentType() {
      if (this.payment_method_id == 5) {
        this.importeMenbreship =
          this.id_membership.price + this.id_membership.price * (this.id_membership.iva / 100);
      }
    },
    getWalletUser() {
      axios.get(`/reports/mymovements/${this.userCurrent.id}`).then((response) => {
        this.saldoTotal = response.data.data.reduce((saldo, transaction) => {
          if (transaction.status == 1 || transaction.status == 0) {
            if (transaction.type == 1) {
              return saldo + transaction.amount;
            } else if (transaction.type == 0) {
              if (transaction.id_receiver == this.userCurrent.id) {
                return saldo + transaction.amount;
              } else {
                return saldo - transaction.amount;
              }
            }
          }
          return saldo;
        }, 0);
      });
    },
    buyMembership() {
      if (this.process == 1) {
        if (this.payment_method_id == 1) {
          window.location.href = '/pay/membership-openpay/' + this.id_membership.id;
        } else if (this.payment_method_id == 5) {
          axios
            .post(`/pay/membership-wallet`, { membership_id: this.id_membership.id })
            .then((response) => {
              if (response.status == 200) {
                this.$message({
                  type: 'success',
                  message: 'Su actualización se realizo con exito',
                });
                this.changeOptionModalReturn();
                setInterval(() => {
                  window.location.reload();
                }, 2000);
              } else {
                console.log('Ocurrio un error', response);
              }
            })
            .catch((error) => {
              console.error('Error:', error);
            });
        }
      } else {
        api.post(`/pay/membership-update-process-basic`).then((response) => {
          window.location.href = '/setting/profile';
        });
      }
    },
    getHistoryOfUserMembership() {
      api.get(`/account-type/detail/membership-history`).then((response) => {
        this.listUserMembership = response;
        this.filterUserMembership();
      });
    },
    filterUserMembership() {
      if (this.listUserMembership && this.accountTypes) {
        const fechaActual = new Date();

        this.accountTypes = this.accountTypes.filter((userMembership) => {
          const matchingMemberships = this.listUserMembership.filter(
            (accountType) => accountType.account_type_name === userMembership.account
          );

          const hasMembershipWithin15Days = matchingMemberships.some((membership) => {
            const fechaVencimiento = new Date(membership.expiration_date);
            const diasHastaVencimiento = Math.ceil(
              (fechaVencimiento - fechaActual) / (1000 * 60 * 60 * 24)
            );
            return diasHastaVencimiento <= 15;
          });

          return hasMembershipWithin15Days || matchingMemberships.length === 0;
        });
      }
    },
    openMembershipHistory() {
      $('#user-membership-history').modal('show');
    },
  },

  mounted() {
    this.calcularDiasRestantes();
    this.getWalletUser();
    this.listAccountTypes();
    this.getHistoryOfUserMembership();
    this.getPaymentMethod();
  },
};
</script>
