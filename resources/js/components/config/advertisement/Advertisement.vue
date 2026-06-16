<template>
  <div>
    <custom-delete-modal @confirm-delete="confirmDeleteAdvertisement"
      :advertisement="selectAdvertisement"></custom-delete-modal>

    <section v-if="totalMessages" id="alerts-with-icons">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <div class="demo-spacing-0">
                <div class="alert" :class="totalMessages <= 5
                    ? 'alert-primary'
                    : totalMessages > 5 && totalMessages <= 8
                      ? 'alert-info'
                      : 'alert-danger'
                  " role="alert">
                  <div class="alert-body">
                    <i data-feather="star"></i>
                    <span> Mensajes totales {{ totalMessages }}/10 </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="basic-textarea">
      <div class="row">
        <div class="col-12">
          <div class="mb-1">
            <button class="btn btn-primary" @click="changeOptionModal('0')">Añadir Banco</button>
          </div>

          <div class="table-responsive">
            <table id="data-table-list-payments" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th>Nro</th>
                  <th>Mensaje</th>
                  <th>Fecha de registro</th>
                  <th>Estado</th>
                  <th v-if="can('action-delete-advertisements')">Acción</th>
                  <th v-if="can('action-edit-advertisements')">Editar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(tempAdvertisement, index) in advertisements" :key="tempAdvertisement.id">
                  <td>{{ index + 1 }}</td>
                  <td style="width: 360px">{{ tempAdvertisement.message }}</td>
                  <td>{{ moment(tempAdvertisement.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                  <td>
                    <div :class="tempAdvertisement.status === '1' ? 'text-danger' : 'text-success'">
                      {{ tempAdvertisement.status === '1' ? 'Deshabilitado' : 'Habilitado' }}
                    </div>
                  </td>
                  <td>
                    <button type="button" class="btn round" @click="deleteAdvertisement(tempAdvertisement.id)"
                      data-toggle="modal" data-target="#delete-modal" :class="tempAdvertisement.status === '0'
                          ? 'btn-outline-danger'
                          : 'btn-outline-success'
                        " v-if="can('action-delete-advertisements')">
                      {{ tempAdvertisement.status === '0' ? 'Deshabilitar' : 'Activar' }}
                    </button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-outline-secondary round"
                      @click.prevent="editAdvertisement(tempAdvertisement.id)" v-if="can('action-edit-advertisements')">
                      Editar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <custom-modal id="level-modal" color="primary" v-if="form">
      <template #title>{{ optionModal == '0' ? 'Añadir Aviso' : 'Editar Aviso ' }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="text-area-message">Mensaje :</label>
              <textarea ref="message-advertisement" v-model="form.message" class="form-control" id="text-area-message"
                rows="3" placeholder="Agregar mensaje" :class="rules ? '' : 'is-invalid'" />
              <div class="invalid-feedback" v-if="!rules">Este campo es obligatorio</div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <el-button data-dismiss="modal" type="text" class="text-danger">Cancelar</el-button>

        <el-button class="btn btn-primary" :loading="requestLoading" @click="submit()">
          {{ optionModal == '0' ? 'Añadir Aviso' : 'Editar Aviso' }}</el-button>
      </template>
    </custom-modal>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import apiAdvertisement from '../../../api/api.advertisement';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner.vue';
import CustomDeleteModal from './components/CustomDeleteModal';
import moment from 'moment';
import language from '../../../api/traduction_es';
import ModalComponent from '@/components/common/ModalComponent.vue';

const formAdvertisement = {
  id: null,
  message: '',
  state: '',
  created_at: null,
};

export default {
  components: { CustomSpinner, CustomDeleteModal, 'custom-modal': ModalComponent },
  mounted() {
    this.listAdvertisements();
  },
  data() {
    return {
      rules: true,
      totalMessages: null,
      form: { ...formAdvertisement },
      selectAdvertisement: {},
      editMode: false,
      initialLoading: false,
      loading: false,
      advertisements: [],
      moment: moment,
      requestLoading: false,
      optionSelected: '',
      optionModal: '0',
    };
  },
  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $('#data-table-list-payments').DataTable(language);
      });
    },
    submit() {
      if (this.form.message === '') {
        this.rules = false;
        this.$refs['message-advertisement'].focus();
        return;
      }

      if (this.totalMessages === 10 && !this.editMode) {
        this.showToast('error', 'Lo sentimos, ha alcanzado el límite para agregar anuncios.');
        return;
      }

      this.rules = true;
      this.loading = true;
      const advertisement = {
        id: this.form.id,
        message: this.form.message,
      };
      if (advertisement.id && this.editMode) {
        apiAdvertisement.edit(advertisement).then((response) => {
          this.successfully(response, true);
          this.showToast('success', `El anuncio se actualizó con éxito`);
        });
      } else {
        apiAdvertisement.add(advertisement).then((response) => {
          this.successfully(response, false);
          this.showToast('success', `El anuncio se agregó con éxito`);
        });
      }
    },
    deleteAdvertisement(id) {
      if (id) {
        this.selectAdvertisement = this.advertisements.find(
          (tempAdvertisement) => tempAdvertisement.id === id
        );
      }
    },
    confirmDeleteAdvertisement(confirm, status) {
      if (confirm) {
        const message = status === '1' ? 'Disable' : 'Activate';
        this.listAdvertisements();
        this.resetForm();
        this.showToast('success', `El anuncio ${message} fue exitoso`);
      }
    },
    editAdvertisement(id) {
      if (id) {
        this.editMode = true;
        this.form = this.advertisements.find((tempAdvertisement) => tempAdvertisement.id === id);
        this.$refs['message-advertisement'].focus();
      }
    },
    listAdvertisements() {
      this.initialLoading = true;
      apiAdvertisement.list().then((response) => {
        this.initialLoading = false;
        this.advertisements = response.data;
        this.totalMessages = this.advertisements.length;
        this.loadDataTable();
      });
    },
    successfully(response, edit) {
      this.selectAdvertisement = response.data;
      this.selectAdvertisement.isEdit = edit;
      this.loading = false;
      // $('#success-modal').modal('show');
      this.listAdvertisements();
      this.resetForm();
    },
    showToast(type, message) {
      toastr[type](`${message}`, `${type.toUpperCase()}!`, {
        positionClass: 'toast-top-center',
        closeButton: true,
        tapToDismiss: false,
      });
    },
    resetForm() {
      this.form = { ...formAdvertisement };
      this.editMode = false;
      this.rules = true;
    },
    getOptionSelected() {
      this.changeOptionModal('1');
      let option = this.optionSelected;
      switch (option) {
        case '1':
          $('#level-modal').modal('show');
          this.optionSelected = '';
          break;

        default:
          console.log('Error');
          break;
      }
    },
    changeOptionModal(option) {
      this.optionModal = option;
      if (this.optionModal == '0') {
        $('#level-modal').modal('show');
      }
    },
  },
  name: 'Advertisement',
};
</script>

<style lang="scss" scoped></style>
