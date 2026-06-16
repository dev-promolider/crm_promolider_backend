<template>
  <div>
    <div class="d-inline-block">
      <div
        class="modal fade text-left modal-danger"
        id="delete-modal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="myModalLabel140"
        aria-hidden="true"
      >
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="myModalLabel140">{{ title }}</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body">
              ¿Quiere Eliminar este bono? <span v-if="selectBonus">{{ selectBonus.id }}</span>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-danger" @click="deleteBonus()">
                <span
                  class="spinner-border spinner-border-sm text-danger"
                  role="status"
                  aria-hidden="true"
                  v-if="loading"
                ></span>
                <span class="ml-25 align-middle"> Aceptar </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="basic-horizontal-layouts">
      <div class="row">
        <div class="col-md-12 col-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Agregar {{ title }}</h4>
            </div>
            <div class="card-body">
              <form class="form form-horizontal">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group row">
                      <div class="col-sm-3 col-form-label">
                        <label for="bank-name"> Precio del bono </label>
                      </div>
                      <div class="col-sm-9">
                        <input
                          v-model="form.price"
                          type="text"
                          id="price"
                          class="form-control"
                          name="price"
                          placeholder="Price"
                          autocomplete="false"
                          ref="price"
                          :class="errors.hasOwnProperty('price') ? 'is-invalid' : ''"
                        />
                        <div class="invalid-feedback" v-if="errors.hasOwnProperty('price')">
                          {{ errors.price[0] }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-9 offset-sm-3">
                    <button
                      @click.prevent="add()"
                      type="reset"
                      class="btn btn-primary mr-1"
                      :disabled="loading"
                    >
                      <span
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-hidden="true"
                        v-if="loading"
                      ></span>
                      <span class="ml-25 align-middle">Agregar </span>
                    </button>
                    <button
                      @click.prevent="resetForm"
                      type="reset"
                      class="btn btn-outline-secondary"
                    >
                      Reiniciar
                    </button>
                  </div>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table-hover-animation table-striped table-bordered" id="datatable">
                  <thead>
                    <tr>
                      <th>Nro</th>
                      <th>Precio</th>
                      <th>Opciones</th>
                    </tr>
                  </thead>
                  <tbody v-if="!initialLoading">
                    <tr v-for="bonus in bonuses" :key="bonus.id">
                      <td>{{ bonus.id }}</td>
                      <td>{{ bonus.price }}</td>
                      <td>
                        <button
                          @click="selectBonus = bonus"
                          type="button"
                          class="btn round btn-sm btn-outline-danger"
                          data-toggle="modal"
                          data-target="#delete-modal"
                        >
                          Eliminar
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import api from '../../api/api';


const formBonus = {
  id: null,
  price: 0,
};

export default {
  name: 'Bonus',

  props: {
    title: {
      type: String,
      required: true,
    },
    url: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      form: { ...formBonus },
      selectBonus: {},
      initialLoading: true,
      loading: false,
      bonuses: [],
      errors: {},
      editMode: false,
    };
  },
  mounted: function () {
    this.listBonus();
  },
  methods: {
    datatable() {
      this.$nextTick(function () {
        $('#datatable').DataTable();
      });
    },
    listBonus() {
      this.initialLoading = true;
      api.get(`${this.url}`).then((response) => {
        this.initialLoading = false;
        this.bonuses = response.data;
        this.datatable();
      });
    },
    resetForm() {
      this.form = { ...formBonus };
      this.editMode = false;
      this.errors = {};
    },
    showToast(type, message) {
      toastr[type](`${message}`, `${type}!`, {
        positionClass: 'toast-top-center',
        closeButton: true,
        tapToDismiss: false,
      });
    },
    successfully(response, edit) {
      this.selectBonus = response.data;
      this.selectBonus.isEdit = edit;
      this.loading = false;
      this.listBonus();
      this.resetForm();
    },
    errorsMessage(err) {
      if (err.response.data.hasOwnProperty('errors')) {
        const errors = err.response.data.errors;
        this.errors = errors;
        if (this.errors.price) {
          this.$refs['price'].focus();
          return;
        }
      }
    },
    deleteBonus() {
      api.delete(`${this.url}/${this.selectBonus.id}`).then(() => {
        $('#delete-modal').modal('hide');
        this.loading = false;
        this.showToast('success', `The bonus was successfully deleted`);
        $('#datatable').DataTable().destroy();
        this.listBonus();
      });
    },
    add() {
      this.errors = {};
      this.loading = true;

      const bonus = {
        id: this.form.id,
        price: this.form.price,
      };

      api
        .post(`${this.url}`, bonus)
        .then((response) => {
          $('#datatable').DataTable().destroy();
          this.listBonus();
          this.successfully(response, false);
          this.showToast('success', `The bonus was successfully Added`);
        })
        .catch((err) => {
          console.log(err.response.data.errors);
          this.errorsMessage(err);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>

<style scoped></style>
