<template>
  <div
    class="modal fade show"
    style="display: block; background-color: rgba(0, 0, 0, 0.5)"
    id="editar-datos"
    tabindex="-1"
    v-if="showDialog"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content modal-width justify-content-end">
        <div class="modal-header">
          <h5 class="modal-title w-100 border-bottom text-black" id="exampleModalLabel">
            {{ formData.id ? 'Actualizar Generational Bonus' : 'Registrar Generational Bonus' }}
          </h5>
          <button
            @click.prevent="closeModal"
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
          >
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body form-editar me-3">
          <div class="row">
            <!-- Input para el Nombre -->
            <div class="col-6">
              <div class="mb-2">
                <strong>Nombre</strong>
                <div class="form-control-wrap">
                  <input type="text" v-model="formData.range_name" class="form-control editar-input" disabled />
                </div>
              </div>
            </div>

            <!-- Ciclo para los campos de G1 a G8 -->
            <div v-for="n in 8" :key="n" class="col-6">
              <div class="mb-2">
                <strong>{{ `G${n}%` }}</strong>
                <div class="form-control-wrap">
                  <input
                    type="number"
                    v-model="formData[`g_${n}`]"
                    class="form-control editar-input"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Botón de guardar -->
          <div class="d-flex align-items-center justify-content-center">
            <button
              @click.prevent="saveGenerationalBonus"
              class="btn btn-primary mb-3 mt-2 b-aceptar d-flex align-items-center justify-content-center text-dark fs-6"
            >
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
  methods: {
    closeModal() {
      this.$emit('closeModal');
    },
    saveGenerationalBonus() {
      this.$emit('saveGenerationalBonus', this.formData);
    },
  },
};
</script>

