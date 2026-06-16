<template>
  <!-- Modal para editar Mini Curso -->
  <div class="modal fade" id="editMiniCourseModal" tabindex="-1" role="dialog" aria-labelledby="editMiniCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editMiniCourseModalLabel">Editar Mini Curso</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="updateMiniCourse">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="titulo">Título <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="titulo" v-model="form.titulo" :class="{ 'is-invalid': errors.titulo }" required />
                  <div v-if="errors.titulo" class="invalid-feedback">{{ errors.titulo[0] }}</div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="nivel">Nivel <span class="text-danger">*</span></label>
                  <select class="form-control" id="nivel" v-model="form.nivel" :class="{ 'is-invalid': errors.nivel }" required>
                    <option value="">Seleccionar nivel</option>
                    <option value="principiante">Principiante</option>
                    <option value="intermedio">Intermedio</option>
                    <option value="avanzado">Avanzado</option>
                  </select>
                  <div v-if="errors.nivel" class="invalid-feedback">{{ errors.nivel[0] }}</div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="categoria">Categoría <span class="text-danger">*</span></label>
                  <select class="form-control" id="categoria" v-model="form.categoria" :class="{ 'is-invalid': errors.categoria }" required>
                    <option value="">Seleccionar categoría</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                  <div v-if="errors.categoria" class="invalid-feedback">{{ errors.categoria[0] }}</div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="duracion">Duración (min) <span class="text-danger">*</span></label>
                  <input type="number" min="1" class="form-control" id="duracion" v-model="form.duracion" :class="{ 'is-invalid': errors.duracion }" required />
                  <div v-if="errors.duracion" class="invalid-feedback">{{ errors.duracion[0] }}</div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="descripcion">Descripción <span class="text-danger">*</span></label>
              <textarea class="form-control" id="descripcion" rows="4" v-model="form.descripcion" :class="{ 'is-invalid': errors.descripcion }" required></textarea>
              <div v-if="errors.descripcion" class="invalid-feedback">{{ errors.descripcion[0] }}</div>
            </div>

            <div class="form-group">
              <label for="imagen">Imagen</label>
              <input type="file" class="form-control-file" id="imagen" 
                     @change="handleFileChange('imagen', $event)" 
                     accept="image/jpeg,image/png,image/jpg,image/webp" />
              <small class="form-text text-muted">Formatos: JPEG, PNG, JPG, WEBP. Máximo 2 MB.</small>
              <div v-if="errors.imagen" class="text-danger">{{ errors.imagen[0] }}</div>
            
              <!-- Previsualización si el usuario elige una nueva imagen -->
              <div v-if="previewImage" class="mt-2">
                <small class="text-muted">Nueva imagen seleccionada:</small>
                <img :src="previewImage" alt="Previsualización" class="img-thumbnail mt-1" style="max-height: 100px;" />
              </div>
            
              <!-- Si no hay nueva imagen, mostrar la actual -->
              <div v-else-if="currentMiniCourse && currentMiniCourse.images?.length" class="mt-2">
                <small class="text-muted">Imagen actual:</small>
                <img :src="currentMiniCourse.images[0].image" alt="Imagen actual" class="img-thumbnail mt-1" style="max-height: 100px;" />
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" @click="updateMiniCourse" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm mr-2" role="status"></span>
            {{ isLoading ? 'Actualizando...' : 'Actualizar Mini Curso' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MiniCourseEditModal",
  props: { 
    categories: { type: Array, default: () => [] }
  },
  data() {
    return {
      currentMiniCourse: null,
      form: { titulo: '', descripcion: '', duracion: '', nivel: '', categoria: '', imagen: null },
      previewImage: null, // <-- Nueva propiedad para previsualizar
      errors: {},
      isLoading: false
    };
  },
  methods: {
    openModal(mini) {
      if (!mini) return console.error('Mini curso no válido');
      this.currentMiniCourse = mini;
      this.resetForm();
      this.loadData(mini);
      $('#editMiniCourseModal').modal('show');
    },
    loadData(mini) {
      this.form = {
        titulo: mini.title || '',
        descripcion: mini.description || '',
        duracion: mini.duration || '',
        nivel: mini.level || '',
        categoria: mini.category_id || '',
        imagen: null
      };
      this.previewImage = null; // Reset previsualización al abrir modal
    },
    handleFileChange(field, e) {
      const file = e.target.files[0] || null;
      this.form[field] = file;

      if (file) {
        this.previewImage = URL.createObjectURL(file); // Crear URL temporal
      } else {
        this.previewImage = null;
      }
    },
    async updateMiniCourse() {
      if (!this.currentMiniCourse) return;
      this.isLoading = true; 
      this.errors = {};

      const data = new FormData();
      Object.entries(this.form).forEach(([k,v]) => { if (v) data.append(k, v); });
      data.append('_method', 'PUT');

      try {
        const response = await axios.post(`/marketing/mini-course/${this.currentMiniCourse.id}`, data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        $('#editMiniCourseModal').modal('hide');
        this.$emit('mini-updated', response.data.data);
        this.$swal.fire({ title: 'Éxito', text: 'Mini curso actualizado correctamente', icon: 'success', timer: 2000, showConfirmButton: false });
      } catch (e) {
        if (e.response?.status === 422) this.errors = e.response.data.errors;
        else this.$swal.fire({ title: 'Error', text: e.response?.data?.message || 'Error al actualizar el mini curso', icon: 'error' });
      } finally {
        this.isLoading = false;
      }
    },
    resetForm() {
      this.form = { titulo: '', descripcion: '', duracion: '', nivel: '', categoria: '', imagen: null };
      this.previewImage = null;
      this.errors = {};
    }
  }
};
</script>