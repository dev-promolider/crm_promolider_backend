<template>
  <div>
    <h3>Información General</h3>
    <div class="row">
      <div class="col-6 m-auto">
        <div class="card" v-loading="loading" element-loading-text="Cargando..."
          element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.6)">
          <div class="card-body">
            <form @submit.prevent="submit" method="post">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label>Nombre de la categoría</label>
                    <input type="text" v-model="form.name" class="form-control" placeholder="Nombre de la categoría" />
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label>Ícono</label>
                    <input type="text" class="form-control" v-model="form.icon" placeholder="Ícono de la categoría" />
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                  Guardar
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
  data() {
    return {
      loading: false,
      form: {},
      isSubmitting: false,
    };
  },
  created() {
    this.initForm();
  },
  methods: {
    initForm() {
      this.form = {
        name: null,
        icon: null,
      };
    },
    hasEspecialCharacters(text) {
      // Solo puede contener vocales con tilde A-Z  y 0-9
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },
    validateFields() {
      if (!this.form.name || this.form.name.trim() === '') {
        this.$message.warning('Nombre esta vacio!');
        return false;
      }

      if (this.form.name.length > 50) {
        this.$message.warning('Nombre demasiado largo!');
        return false;
      }

      if (!this.form.icon || this.form.icon.trim() === '') {
        this.$message.warning('Icono esta vacio!');
        return false;
      }
      return true;
    },

    async submit() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('name', this.form.name);
        formdata.append('icon', this.form.icon);

        try {
          this.loading = true;
          const request = await axios.post(`/config/category/store`, formdata);
          const response =
            request.status == 200
              ? this.$message.success('La categoría ha sido registrada correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          this.initForm();
          this.fileList = [];
          window.location.href = '/config/category/';
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },
  },
};
</script>
