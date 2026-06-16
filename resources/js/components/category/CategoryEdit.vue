<template>
  <div>
    <h3>Información General</h3>
    <div class="row">
      <div class="col-12 m-auto">
        <div v-loading="loading" element-loading-text="Cargando..." element-loading-spinner="el-icon-loading"
          element-loading-background="rgba(0, 0, 0, 0.6)">
          <div>
            <form @submit.prevent="submit">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label>Nombre de la categoría</label>
                    <input type="text" v-model="mutableCategory.name" class="form-control"
                      placeholder="Nombre de la categoría" />
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label>Ícono</label>
                    <input type="text" class="form-control" v-model="mutableCategory.icon"
                      placeholder="Ícono de la categoría" />
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                  {{ mutableCategory.id == '' ? 'Crear Categoría' : 'Editar Categoría' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      form: {},
      isSubmitting: false,
      mutableCategory: {
        name: null,
      },
    };
  },
  watch: {
    category: function (value) {
      this.mutableCategory = value;
    },
  },
  methods: {
    hasEspecialCharacters(text) {
      // Solo puede contener vocales con tilde A-Z  y 0-9
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },
    validateFields() {
      if (!this.mutableCategory.name || this.mutableCategory.name.trim() === '') {
        this.$message.warning('Nombre esta vacio!');
        return false;
      }

      if (this.mutableCategory.name.length > 50) {
        this.$message.warning('Nombre demasiado largo!');
        return false;
      }

      if (!this.mutableCategory.icon || this.mutableCategory.icon.trim() === '') {
        this.$message.warning('Icono esta vacio!');
        return false;
      }
      return true;
    },
    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('id', this.mutableCategory.id);
        formdata.append('name', this.mutableCategory.name);
        formdata.append('icon', this.mutableCategory.icon);

        var ruta =
          this.mutableCategory.id == '' ? '/config/category/store' : '/config/category/update';

        try {
          this.loading = true;
          const request = await axios.post(ruta, formdata);
          if (request.data == 'ok') {
            this.$message.success('La categoría ha sido Actualizada correctamente');
            $('#edit-category-modal').modal('hide');
            this.$emit('listenMethod');
          } else {
            this.$message.warning('Error al validar datos');
          }
          this.isSubmitting = false;
          this.loading = false;
          this.fileList = [];
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
          this.$message.warning('Error al validar datos');
        }
      }
    },
  },
};
</script>
