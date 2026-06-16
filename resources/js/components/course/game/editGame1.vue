<template>
  <div>
    <div class="card" v-loading="loading" element-loading-text="Cargando..." element-loading-spinner="el-icon-loading"
      element-loading-background="rgba(0, 0, 0, 0.6)">
      <div class="card-body">
        <form @submit.prevent="storeDetail" id="upload" method="post">
          <div class="row">
            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Descripción</label>
                <input type="text" v-model="mutableDetail.description" class="form-control" placeholder="Descripción" />
              </div>
            </div>
            <div class="col-md-3 col-lg-3">
              <div class="form-group">
                <label>Palabra</label>
                <input type="text" v-model="mutableDetail.word" class="form-control"
                  placeholder="Ingrese la palabra a adivinar" />
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success" :disabled="isSubmitting">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    game: Object,
    detail: Object,
  },
  data() {
    return {
      loading: false,
      isSubmitting: false,
      mutableDetail: {},
    };
  },
  created() {
    if (this.detail == null) {
      this.mutableDetail = {};
    } else {
      this.mutableDetail.description = this.detail.data.description;
      this.mutableDetail.word = this.detail.data.word;
    }
  },
  methods: {
    validateFields() {
      if (
        !this.mutableDetail.description ||
        this.mutableDetail.description.trim() === '' ||
        this.mutableDetail.description.length === 0
      ) {
        this.$message.warning('Descipción esta vacio!');
        return false;
      }
      if (this.mutableDetail.description.length > 255) {
        this.$message.warning('Descripción demasiado largo!');
        return false;
      }
      if (
        !this.mutableDetail.word ||
        this.mutableDetail.word.trim() === '' ||
        this.mutableDetail.word.length === 0
      ) {
        this.$message.warning('La palabra vacio!');
        return false;
      }

      if (this.mutableDetail.word.length > 255) {
        this.$message.warning('Palabra demasiado largo!');
        return false;
      }
      return true;
    },

    async storeDetail() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('game_id', this.game.id);
        formdata.append('description', this.mutableDetail.description);
        formdata.append('word', this.mutableDetail.word);
        try {
          this.loading = true;
          const request = await axios({
            url: `/course/game/storeDetail`,
            method: 'post',
            data: formdata,
          });
          const response =
            request.status == 200
              ? this.$message.success('El juego se ha guardado correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
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
