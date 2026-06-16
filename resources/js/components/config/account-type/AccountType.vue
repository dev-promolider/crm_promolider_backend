<template>
  <div>
    <custom-delete-modal @confirm-delete="confirmDeleteAccountType" :account-type="selectAccountType">
    </custom-delete-modal>
    <custom-success-modal :account-type="selectAccountType"></custom-success-modal>

    <custom-modal id="account-type-modal" color="primary" v-if="form" size="large">
      <template #title>
        {{ editMode ? 'Editar tipo de cuenta' : 'Crear Tipo de cuenta' }}
      </template>

      <template #body>
        <form class="form">
          <div class="row">
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="account"> Cuenta </label>
                <input v-model="form.account" type="text" id="account" class="form-control" name="account"
                  placeholder="Account" autocomplete="false" ref="account-account-type"
                  :class="errors.hasOwnProperty('account') ? 'is-invalid' : ''" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('account')">
                  {{ errors.account[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="price"> Precio</label>
                <input type="number" id="price" class="form-control" name="price" placeholder="Price"
                  autocomplete="false" ref="price-account-type" v-model="form.price"
                  :class="errors.hasOwnProperty('price') ? 'is-invalid' : ''" @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('price')">
                  {{ errors.price[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="comission"> Comisionable </label>
                <input type="number" id="comissiona" class="form-control" name="comission"
                  placeholder="comissionable (optional)" autocomplete="false" ref="comission-account-type"
                  v-model.trim="form.comission" :class="errors.hasOwnProperty('comission') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('comission')">
                  {{ errors.comission[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="iva"> Iva</label>
                <input type="number" id="iva" class="form-control" name="iva" placeholder="iva(optional)"
                  autocomplete="false" ref="iva-account-type" v-model="form.iva"
                  :class="errors.hasOwnProperty('iva') ? 'is-invalid' : ''" @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('iva')">
                  {{ errors.iva[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="pay_in_binary">% Ganancia por corte binario</label>
                <input type="number" id="pay_in_binary" class="form-control" name="pay_in_binary"
                  placeholder="Binary cut gain %" autocomplete="false" ref="pay_in_binary-account-type"
                  v-model="form.pay_in_binary" :class="errors.hasOwnProperty('pay_in_binary') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('pay_in_binary')">
                  {{ errors.pay_in_binary[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="disc_purchases_course">Descuento en compras</label>
                <input type="number" id="disc_purchases_course" class="form-control" name="disc_purchases_course"
                  placeholder="Purchase discount %" autocomplete="false" ref="disc_purchases-account-type"
                  v-model="form.disc_purchases_course"
                  :class="errors.hasOwnProperty('disc_purchases_course') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('disc_purchases_course')">
                  {{ errors.disc_purchases_course[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="productor_bonus">Bono de productor</label>
                <input type="number" id="productor_bonus" class="form-control" name="productor_bonus"
                  placeholder="Producer bonus" autocomplete="false" ref="productor_bonus-account-type"
                  v-model="form.productor_bonus" :class="errors.hasOwnProperty('productor_bonus') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('productor_bonus')">
                  {{ errors.productor_bonus[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="fast_cash_bonus">Bono por venta de curso</label>
                <input type="number" id="course_selling_bonus" class="form-control" name="course_selling_bonus"
                  placeholder="Course selling bonus" autocomplete="false" ref="course_selling_bonus-account-type"
                  v-model="form.course_selling_bonus"
                  :class="errors.hasOwnProperty('course_selling_bonus') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('course_selling_bonus')">
                  {{ errors.course_selling_bonus[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="fast_cash_bonus">Bono de efectivo rápido</label>
                <input type="number" id="fast_cash_bonus" class="form-control" name="fast_cash_bonus"
                  placeholder="Fast cash bonus" autocomplete="false" ref="fast_cash_bonus-account-type"
                  v-model="form.fast_cash_bonus" :class="errors.hasOwnProperty('fast_cash_bonus') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('fast_cash_bonus')">
                  {{ errors.fast_cash_bonus[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="points">Puntos</label>
                <input type="number" id="points" class="form-control" name="points" placeholder="Points"
                  autocomplete="false" ref="points-account-type" v-model="form.points"
                  :class="errors.hasOwnProperty('points') ? 'is-invalid' : ''" @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('points')">
                  {{ errors.points[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="enrollment_duration">Duración de la inscripción (Nro. de meses)</label>
                <input type="number" id="enrollment_duration" class="form-control" name="enrollment_duration"
                  placeholder="Nro. de meses" autocomplete="false" v-model="form.enrollment_duration"
                  :class="errors.hasOwnProperty('enrollment_duration') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('enrollment_duration')">
                  {{ errors.enrollment_duration[0] }}
                </div>
              </div>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label for="product_price">Precio del OPC</label>
                <input type="number" id="product_price" class="form-control" name="product_price"
                  placeholder="Precio del OPC" autocomplete="false" ref="product_price-account-type"
                  v-model="form.product_price" :class="errors.hasOwnProperty('product_price') ? 'is-invalid' : ''"
                  @keypress="validateNumber($event)" />
                <div class="invalid-feedback" v-if="errors.hasOwnProperty('product_price')">
                  {{ errors.product_price[0] }}
                </div>
              </div>
            </div>
          </div>
        </form>
      </template>

      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>
        <el-button class="btn btn-primary mr-1" @click="submit()" :disabled="loading">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="loading"></span>
          <span class="ml-25 align-middle">
            {{ editMode ? 'Editar' : 'Agregar' }}
          </span>
        </el-button>
      </template>
    </custom-modal>

    <div class="card">
      <div class="card-header">
        <button class="btn btn-primary" @click="openModal()">Crear tipo de cuenta</button>
      </div>
      <div class="card-body">
        <div class="card-text">Esta tabla lista todos los tipos de cuenta en Promolíder</div>
        <div class="table-responsive">
          <table id="datatable-account-type" class="table-hover-animation table-striped table-bordered">
            <thead>
              <tr>
                <th>N</th>
                <th>Cuenta</th>
                <th>Precio</th>
                <th>Estado</th>
                <th data-toggle="tooltip" data-placement="top" title="Iva">I</th>
                <th data-toggle="tooltip" data-placement="top" title="Descuento en compras">DC</th>
                <th data-toggle="tooltip" data-placement="top" title="Ganancia por corte binario">
                  GB
                </th>
                <th data-toggle="tooltip" data-placement="top" title="Bono de productor">BP</th>
                <th data-toggle="tooltip" data-placement="top" title="Bono por venta de curso">
                  BV
                </th>
                <th data-toggle="tooltip" data-placement="top" title="Bono de efectivo rápido">
                  BR
                </th>
                <th data-toggle="tooltip" data-placement="top" title="Comisionable">C</th>
                <th data-toggle="tooltip" data-placement="top" title="Puntos">P</th>
                <th data-toggle="tooltip" data-placement="top" title="Duración de la inscripción">
                  D
                </th>
                <th data-toggle="tooltip" data-placement="top" title="Precio del OPC">OPC</th>
                <th data-toggle="tooltip" data-placement="top" title="Opciones">O</th>
              </tr>
            </thead>
            <tbody v-if="!initialLoading">
              <tr v-for="accountType in accountTypes" :key="accountType.id">
                <td>{{ accountType.id }}</td>
                <td>{{ accountType.account }}</td>
                <td>{{ accountType.price }}</td>
                <td>
                  <div :class="accountType.status === '0' ? 'text-danger' : 'text-success'">
                    {{ accountType.status === '0' ? 'Eliminado' : 'Activo' }}
                  </div>
                </td>
                <td>{{ accountType.iva }}</td>
                <td>{{ accountType.disc_purchases_course }}</td>
                <td>{{ accountType.pay_in_binary }}</td>
                <td>{{ accountType.productor_bonus }}</td>
                <td>{{ accountType.course_selling_bonus }}</td>
                <td>{{ accountType.fast_cash_bonus }}</td>
                <td>{{ accountType.comission }}</td>
                <td>{{ accountType.points }}</td>
                <td>{{ accountType.enrollment_duration }}</td>
                <td>{{ accountType.product_price }}</td>
                <td>
                  <div class="demo-inline-spacing">
                    <button type="button" class="btn btn-outline-secondary btn-sm round mr-1"
                      @click.prevent="editAccountType(accountType.id)">
                      Editar
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm round"
                      @click.prevent="deleteAccountType(accountType.id)">
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="m-10" v-if="initialLoading">
            <custom-spinner></custom-spinner>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiAccountType from '../../../api/api.account-type';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner';
import CustomDeleteModal from './components/CustomDeleteModal';
import CustomSuccessModal from './components/CustomSuccessModal';
import ModalComponent from '@/components/common/ModalComponent.vue';
import language from '../../../api/traduction_es';

const formAccountType = {
  id: null,
  account: '',
  price: 0,
  iva: 0,
  disc_purchases_course: 0,
  pay_in_binary: 0,
  productor_bonus: 0,
  course_selling_bonus: 0,
  fast_cash_bonus: 0,
  comission: 0,
  state: '',
  points: 0,
  enrollment_duration: 0,
  product_price: 0,
};

export default {
  components: {
    CustomSuccessModal,
    CustomDeleteModal,
    CustomSpinner,
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      form: { ...formAccountType },
      selectAccountType: {},
      initialLoading: true,
      loading: false,
      accountTypes: [],
      editMode: false,
      errors: {},
    };
  },
  mounted() {
    this.listAccountTypes();
  },
  methods: {
    portKeydown(e) {
      if (/^\+$/.test(e.key)) {
        e.preventDefault();
      }
    },
    datatable() {
      $('#datatable-account-type').DataTable().destroy();
      this.$nextTick(function () {
        $('#datatable-account-type').DataTable(language);
      });
    },
    resetForm() {
      this.form = { ...formAccountType };
      this.editMode = false;
      this.errors = {};
    },
    validateFields() {
      if (!this.form.account || this.form.account.trim() == '') {
        this.$message.warning('Cuenta esta vacio!');
        return false;
      }

      if (this.form.account.length > 255) {
        this.$message.warning('Nombre demasiado largo!');
        return false;
      }

      var caracterEspecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/;

      if (caracterEspecial.test(this.form.account)) {
        this.$message.warning('El título no puede contener caracteres especiales');
        return false;
      }

      if (
        (!this.form.price && this.form.price !== 0) ||
        this.form.price.toString().trim() == '' ||
        this.form.price.length === 0
      ) {
        this.$message.warning('Precio esta vacio!');
        return false;
      }

      if (isNaN(this.form.price)) {
        this.$message.warning('Verifique el campo de precio');
        return false;
      }

      if (
        (!this.form.comission && this.form.comission !== 0) ||
        this.form.comission.toString().trim() == '' ||
        this.form.comission.length === 0
      ) {
        this.$message.warning('Comision esta vacio!');
        return false;
      }

      if (isNaN(this.form.comission)) {
        this.$message.warning('Verifique el campo de Comision');
        return false;
      }

      if (
        (!this.form.iva && this.form.iva !== 0) ||
        this.form.iva.toString().trim() == '' ||
        this.form.iva.length === 0
      ) {
        this.$message.warning('IVA esta vacio o su valor es 0!');
        return false;
      }

      if (isNaN(this.form.iva)) {
        this.$message.warning('Verifique el campo de IVA');
        return false;
      }

      if (
        (!this.form.pay_in_binary && this.form.pay_in_binary !== 0) ||
        this.form.pay_in_binary.toString().trim() == '' ||
        this.form.pay_in_binary.length === 0
      ) {
        this.$message.warning('Binario esta vacio!');
        return false;
      }

      if (isNaN(this.form.pay_in_binary)) {
        this.$message.warning('Verifique el campo de Binario');
        return false;
      }

      if (
        (!this.form.disc_purchases_course && this.form.disc_purchases_course !== 0) ||
        this.form.disc_purchases_course.toString().trim() == '' ||
        this.form.disc_purchases_course.length === 0
      ) {
        this.$message.warning('Descuento en compras esta vacio!');
        return false;
      }

      if (isNaN(this.form.disc_purchases_course)) {
        this.$message.warning('Verifique el campo de descuento en compras');
        return false;
      }

      if (
        !this.form.enrollment_duration ||
        this.form.enrollment_duration.toString().trim() == '' ||
        this.form.enrollment_duration.length === 0
      ) {
        this.$message.warning('Duración de la inscripción esta vacio o su valor es 0!');
        return false;
      }

      if (
        this.form.enrollment_duration.toString().charAt(0) == 0 ||
        isNaN(this.form.enrollment_duration)
      ) {
        this.$message.warning('Verifique el campo de duración de la inscripción');
        return false;
      }

      if (
        (!this.form.productor_bonus && this.form.productor_bonus !== 0) ||
        this.form.productor_bonus.toString().trim() == '' ||
        this.form.productor_bonus.length === 0
      ) {
        this.$message.warning('Bono de productor esta vacio!');
        return false;
      }

      if (isNaN(this.form.productor_bonus)) {
        this.$message.warning('Verifique el campo de Bono de productor');
        return false;
      }

      if (
        (!this.form.course_selling_bonus && this.form.course_selling_bonus !== 0) ||
        this.form.course_selling_bonus.toString().trim() == '' ||
        this.form.course_selling_bonus.length === 0
      ) {
        this.$message.warning('Bono por venta de curso esta vacio!');
        return false;
      }

      if (isNaN(this.form.course_selling_bonus)) {
        this.$message.warning('Verifique el campo de Bono por venta de curso');
        return false;
      }

      if (
        (!this.form.fast_cash_bonus && this.form.fast_cash_bonus !== 0) ||
        this.form.fast_cash_bonus.toString().trim() == '' ||
        this.form.fast_cash_bonus.length === 0
      ) {
        this.$message.warning('Bono de efectivo rápido esta vacio!');
        return false;
      }

      if (isNaN(this.form.fast_cash_bonus)) {
        this.$message.warning('Verifique el campo de Bono de efectivo rápido');
        return false;
      }

      if (
        (!this.form.points && this.form.points !== 0) ||
        this.form.points.toString().trim() == '' ||
        this.form.points.length === 0
      ) {
        this.$message.warning('Puntos esta vacio!');
        return false;
      }

      if (isNaN(this.form.points)) {
        this.$message.warning('Verifique el campo de Puntos');
        return false;
      }

      if (
        (!this.form.product_price && this.form.product_price !== 0) ||
        this.form.product_price.toString().trim() == '' ||
        this.form.product_price.length === 0
      ) {
        this.$message.warning('Precio del OPC esta vacio!');
        return false;
      }

      if (isNaN(this.form.product_price)) {
        this.$message.warning('Verifique el campo de Precio del OPC');
        return false;
      }

      return true;
    },

    openModal() {
      this.resetForm();
      $('#account-type-modal').modal('show');
    },

    errorsMessage(err) {
      if (err.response.data.hasOwnProperty('errors')) {
        const errors = err.response.data.errors;
        this.errors = errors;

        if (this.errors.account) {
          this.$refs['account-account-type'].focus();
          return;
        }
        if (this.errors.price) {
          this.$refs['price-account-type'].focus();
          return;
        }
        if (this.errors.comission) {
          this.$refs['comission-account-type'].focus();
          return;
        }
        if (this.errors.pay_in_binary) {
          this.$refs['pay_in_binary-account-type'].focus();
          return;
        }
        if (this.errors.disc_purchases_course) {
          this.$refs['disc_purchases-account-type'].focus();
          return;
        }
        if (this.errors.productor_bonus) {
          this.$refs['productor_bonus-account-type'].focus();
          return;
        }
        if (this.errors.course_selling_bonus) {
          this.$refs['course_selling_bonus-account-type'].focus();
          return;
        }
        if (this.errors.fast_cash_bonus) {
          this.$refs['fast_cash_bonus-account-type'].focus();
          return;
        }
        if (this.errors.product_price) {
          this.$refs['product_price-account-type'].focus();
          return;
        }
      }
    },

    submit() {
      if (this.validateFields()) {
        this.errors = {};
        this.loading = true;

        const accountType = {
          id: this.form.id,
          account: this.form.account,
          price: this.form.price,
          iva: this.form.iva,
          disc_purchases_course: this.form.disc_purchases_course,
          pay_in_binary: this.form.pay_in_binary,
          productor_bonus: this.form.productor_bonus,
          course_selling_bonus: this.form.course_selling_bonus,
          fast_cash_bonus: this.form.fast_cash_bonus,
          comission: this.form.comission,
          points: this.form.points,
          enrollment_duration: Number(this.form.enrollment_duration),
          product_price: this.form.product_price,
        };

        if (accountType.id && this.editMode) {
          apiAccountType
            .edit(accountType)
            .then((response) => {
              $('#datatable-account-type').DataTable().destroy();
              this.listAccountTypes();
              this.successfully(response, true);
              this.showToast(
                'success',
                `El tipo de cuenta ${response.data.account} fue actualizado correctamente`
              );
              $('#account-type-modal').modal('hide');
            })
            .catch((err) => {
              this.errorsMessage(err);
            })
            .finally(() => {
              this.loading = false;
            });
        } else {
          apiAccountType
            .add(accountType)
            .then((response) => {
              $('#datatable-account-type').DataTable().destroy();
              this.listAccountTypes();
              this.successfully(response, false);
              this.showToast(
                'success',
                `El tipo de cuenta ${response.data.account} fue agregado correctamente`
              );
              $('#account-type-modal').modal('hide');
            })
            .catch((err) => {
              this.errorsMessage(err);
            })
            .finally(() => {
              this.loading = false;
            });
        }
      }
    },

    editAccountType(id) {
      this.errors = {};
      this.editMode = true;
      this.form = this.accountTypes.find((accountType) => accountType.id === id);
      $('#account-type-modal').modal('show');
    },

    deleteAccountType(id) {
      this.selectAccountType = this.accountTypes.find((accountType) => accountType.id === id);
      $('#delete-modal').modal('show');
    },

    listAccountTypes() {
      this.initialLoading = true;
      apiAccountType.list().then((response) => {
        this.initialLoading = false;
        this.accountTypes = response.data;
        this.datatable();
      });
    },

    confirmDeleteAccountType(confirm, status) {
      if (confirm) {
        this.loading = true;
        apiAccountType
          .delete(this.selectAccountType.id)
          .then(() => {
            const message = status === '0' ? 'Eliminado' : 'Activo';
            $('#datatable-account-type').DataTable().destroy();
            this.listAccountTypes();
            this.resetForm();
            this.showToast('success', `El tipo de cuenta fue ${message} correctamente`);
          })
          .catch((error) => {
            console.error('Error deleting account type:', error);
            this.showToast('error', 'Error al eliminar el tipo de cuenta');
          })
          .finally(() => {
            this.loading = false;
            $('#delete-modal').modal('hide');
          });
      }
    },

    showToast(type, message) {
      toastr[type](`${message}`, `${type}!`, {
        positionClass: 'toast-top-center',
        closeButton: true,
        tapToDismiss: false,
      });
    },

    successfully(response, edit) {
      this.selectAccountType = response.data;
      this.selectAccountType.isEdit = edit;
      this.loading = false;
      this.listAccountTypes();
      this.resetForm();
    },

    changePage() {
      this.listAccountTypes();
    },

    validateNumber(e) {
      var invalidChars = ['-', 'e', '+', 'E'];
      if (invalidChars.includes(e.key)) {
        e.preventDefault();
      }
    },
  },
  name: 'AccountType',
};
</script>
