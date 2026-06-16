<template>
  <div class="modal fade show" style="display: block; background-color: rgba(0, 0, 0, 0.5)" id="agregar-datos"
    tabindex="-1" v-if="showDialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content modal-width justify-content-end">
        <div class="modal-header">
          <h5 class="modal-title w-100 border-bottom text-black" id="exampleModalLabel">
            {{ formData.id ? 'Actualizar Rank Bonus' : 'Registrar Rank Bonus' }}
          </h5>
          <button @click.prevent="closeModal" type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body form-editar me-3">
          <div class="row">
            <div class="col-6">
              <div class="mb-2">
                <strong>Nombre</strong>
                <div class="form-control-wrap">
                  <input type="text" v-model="formData.name" class="form-control editar-input" />
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-2">
                <strong>Volumen de puntos</strong>
                <div class="form-control-wrap">
                  <input type="number" v-model="formData.vol_min" class="form-control editar-input" />
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-2">
                <strong>Pack University</strong>
                <div class="form-control-wrap">
                  <input type="number" v-model="formData.pack_max" class="form-control editar-input" />
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-2">
                <strong>Activos Directos</strong>
                <div class="form-control-wrap">
                  <input type="number" v-model="formData.active_direct" class="form-control editar-input" />
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-2">
                <strong>Maximo pagos</strong>
                <div class="form-control-wrap">
                  <input type="number" v-model="formData.max_pay" class="form-control editar-input" />
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-2">
                <strong>Bono Mensual</strong>
                <div class="form-control-wrap">
                  <input type="number" v-model="formData.monthly_bonus" class="form-control editar-input" />
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-center">
            <button @click.prevent="saveRankBonus"
              class="btn btn btn-primary mb-3 mt-2 b-aceptar d-flex align-items-center justify-content-center text-dark fs-6">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    showDialog: {
      type: Boolean,
      required: true,
    },
    formData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {};
  },
  created() { },
  methods: {
    closeModal() {
      this.$emit('closeModal');
    },
    saveRankBonus() {
      this.$emit('saveRankBonus', this.formData);
    },
  },
};
</script>
