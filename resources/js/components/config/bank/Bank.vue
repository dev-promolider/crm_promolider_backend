<template>
  <div>
    <section>
      <div class="row">
        <div class="col-md-12">
          <div class="mb-1">
            <button class="btn btn-primary" @click="changeOptionModal('0')">Añadir Banco</button>
          </div>

          <div class="table-responsive">
            <table id="data-table-list-bank" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th>Número</th>
                  <th>Nombre</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(tempBank, index) in banks" :key="tempBank.id">
                  <td>{{ index + 1 }}</td>
                  <td style="width: 220px">{{ tempBank.name }}</td>
                  <td>
                    <div class="row">
                      <div class="demo-inline-spacing">
                        <button type="button" class="btn btn-outline-secondary round"
                          @click.prevent="editBank(tempBank.id)">
                          Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger round" @click="verifyBankDelete(tempBank)">
                          Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
    <custom-modal id="modal-bank" color="primary" v-if="form">
      <template #title>{{ optionModal == '0' ? 'Añadir Banco' : 'Editar Banco ' }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="form-group row">
              <div class="col-sm-3 col-form-label">
                <label for="bank-name"> Nombre del banco </label>
              </div>
              <div class="col-sm-9">
                <input v-model="form.name" type="text" id="bank-name" class="form-control" name="name"
                  placeholder="nombre del banco" ref="name-bank" :class="rules ? '' : 'is-invalid'" />
                <div class="invalid-feedback" v-if="!rules">Este campo es requerido</div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="submit()">
          {{ optionModal == '0' ? 'Añadir Banco' : 'Editar Banco' }}</el-button>
      </template>
    </custom-modal>

    <custom-spinner v-else></custom-spinner>
    <custom-modal id="delete-bank-modal" color="danger">
      <template #title>Eliminar Banco <u> {{ form.name }} </u></template>
      <template #body>¿Está seguro de que desea eliminar el banco?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deleteBank(form.id)">Eliminar</button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import apiBank from '../../../api/api.bank';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner';
import language from '../../../api/traduction_es';
import ModalComponent from '@/components/common/ModalComponent.vue';

const formBank = {
  id: null,
  name: '',
};
export default {
  components: { CustomSpinner, 'custom-modal': ModalComponent },
  mounted() {
    this.listBanks();
  },
  data() {
    return {
      rules: true,
      initialLoading: true,
      loading: false,
      loadingDelete: false,
      requestLoading: false,
      optionSelected: '',
      optionModal: '0',
      banks: [],
      form: { ...formBank },
      editMode: false,
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0,
      },
    };
  },
  methods: {
    loadDataTable() {
      $('#data-table-list-bank').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-bank').DataTable(language);
      });
    },
    resetForm() {
      this.form = { ...formBank };
      this.editMode = false;
    },
    listBanks() {
      this.initialLoading = true;
      apiBank.list().then((response) => {
        this.initialLoading = false;
        this.banks = response.data;
        this.loadDataTable();
      });
    },
    editBank(id) {
      this.optionModal = '1';
      this.editMode = true;
      this.rules = true;
      this.form = this.banks.find((tempBank) => tempBank.id === id);
      $('#modal-bank').modal('show');
      this.$refs['name-bank'].focus();
    },
    verifyBankDelete(bank) {
      this.form = bank;
      $('#delete-bank-modal').modal('show');
    },
    deleteBank(id) {
      this.loadingDelete = true;
      apiBank.delete(id).then(() => {
        this.loadingDelete = false;
        this.listBanks();
        $('#delete-bank-modal').modal('hide');
        this.$message.success('El registro se ha eliminado correctamente');
      });
    },
    submit() {
      if (this.form.name === '') {
        this.rules = false;
        this.$refs['name-bank'].focus();
        return;
      }
      this.rules = true;
      this.loading = true;
      const bank = {
        id: this.form.id,
        name: this.form.name,
      };
      if (bank.id && this.editMode) {
        apiBank.edit(bank).then((response) => {
          this.loading = false;
          if (response == 'ok') {
            $('#modal-bank').modal('hide');
            this.listBanks();
            this.resetForm();
            this.$message.success('El registro se ha guardado correctamente');
          } else if (response == 'error_name') {
            this.$message.warning('El nombre del banco ya existe');
          } else {
            this.$message.warning('Error al realizar la acción');
          }
        });
      } else {
        apiBank.add(bank).then((response) => {
          this.loading = false;
          if (response == 'ok') {
            $('#modal-bank').modal('hide');
            this.listBanks();
            this.resetForm();
            this.$message.success('Registro se ha actualizado correctamente');
          } else if (response == 'error_name') {
            this.$message.warning('El nombre del banco ya existe');
          } else {
            this.$message.warning('Error al realizar la acción');
          }
        });
      }
    },
    getOptionSelected() {
      this.changeOptionModal('1');
      let option = this.optionSelected;
      switch (option) {
        case '1':
          $('#modal-bank').modal('show');
          this.optionSelected = '';
          break;

        default:
          console.log('Error');
          break;
      }
    },
    changeOptionModal(option) {
      this.rules = true;
      this.resetForm();
      this.optionModal = option;
      if (this.optionModal == '0') {
        $('#modal-bank').modal('show');
      }
    },
  },
  name: 'Bank',
};
</script>
