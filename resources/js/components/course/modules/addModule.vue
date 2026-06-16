<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Modulos</h3>
        <button type="button" class="btn btn-primary" @click="showInputModule">(+) Agregar</button>
      </div>
      <div class="card-body" v-if="showDialogAddModule == true">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroup-sizing-default"
                    >Modulo {{ modulesList.length + 1 }} :</span
                  >
                </div>
                <input
                  placeholder="Ingrese el título del módulo"
                  @keyup.enter="storeModule"
                  type="text"
                  class="form-control"
                  aria-label="Default"
                  aria-describedby="inputGroup-sizing-default"
                  v-model="title"
                />
              </div>
            </div>
            <!-- <button type="button" class="btn btn-link" @click="dialogAddClass">
              (+) Agregar Clase
            </button> -->
          </div>
        </div>
      </div>
    </div>
    <!-- <add-class
        :showDialog.sync="showDialogAddClass"
    ></add-class> -->
  </div>
</template>
<script>
import addClass from './classes/addClass.vue';
export default {
  components: {
    addClass,
  },
  props: {
    modulesList: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      modules: [],
      showDialogAddModule: false,
      title: null,
    };
  },
  methods: {
    showInputModule() {
      this.showDialogAddModule = true;
    },
    async storeModule() {
      try {
        let formData = new FormData();
        formData.append('name', this.title);
        formData.append('course_id', this.$attrs.courses.id);

        const request = await axios({
          url: '/course/module/store',
          method: 'post',
          data: formData,
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        const response =
          request.status == 200
            ? this.$message.success('El curso ha sido registrado correctamente')
            : await this.$message.warning('Error al validar datos');
      } catch (error) {
        console.log(error);
      }
      this.title = null;
    },

    // dialogAddClass() {
    //   this.showDialogAddClass = true;
    // },
  },
};
</script>