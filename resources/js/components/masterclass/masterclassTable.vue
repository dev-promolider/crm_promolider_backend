<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-masterclass" class="table-hover-animation table-striped table-bordered">
              <thead>
              <tr>
                <th>#</th>
                <th>Masterclass</th>
                <th>Categoría</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>N° Distribuidores</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(masterclass, index) in this.masterclasses" v-bind:key="masterclass.id" v-bind:index="index">
                <td>{{ index + 1 }}</td>
                <td>{{ masterclass.title }}</td>
                <td>{{ masterclass.category_name }}</td>
                <td>{{ masterclass.date }}</td>
                <td>{{ masterclass.hour }}</td>
                <td>
                    <span class="badge" :class="getStatusClass(masterclass.status)">
                        {{ getStatusText(masterclass.status) }}
                    </span>
                </td>
                <td>{{ masterclass.distributors_count }}</td>
                <td class="align-content-center">
                  <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                             @change="getOptionSelected(masterclass)">
                    <el-option v-for="item in getAvailableOptions()" :key="item.value"
                               :label="item.label" :value="item.value">
                    </el-option>
                  </el-select>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>
    <custom-modal id="edit-masterclass-modal" color="primary" size="large" v-if="masterclassSelected">
      <template #title>Editar Masterclass<u> {{ masterclassSelected.title }} </u></template>
      <template #body>
        <form-masterclass-edit :masterclass="masterclassSelected" :categoriesList="categories"
                          ></form-masterclass-edit>
      </template>
    </custom-modal>

    <custom-modal id="delete-masterclass-modal" color="danger">
      <template #title>Borrar Masterclass <u> {{ masterclassSelected.title }} </u></template>
      <template #body>¿Seguro que desea eliminar este curso?</template>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="deleteMasterclass(masterclassSelected.id)">
          Eliminar
        </button>
      </template>
    </custom-modal>

  </div>
</template>

<script>

import axios from 'axios';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import language from '../../api/traduction_es';
import ModalComponent from '@/components/common/ModalComponent.vue';
import MasterclassEdit from './MasterclassEdit.vue';

export default {
  props: {
    user: {
      type: Object,
      required: true, // Asegúrate de pasar el ID desde el componente padre
    },
  },
  data() {
    return {
      initialLoading: false,
      masterclasses: [],
      categories: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
        {
          value: '2',
          label: 'Eliminar',
        },
      ],
      optionSelected: '',
      masterclassSelected: {},
    };
  },
  components: {
    'custom-modal': ModalComponent,
    'custom-spinner': CustomSpinner,
    'form-masterclass-edit': MasterclassEdit,
  },
  mounted() {
    this.listMasterclasses();
    this.listCategories();
  },
  methods: {
    loadDataTable() {
        this.$nextTick(function () {
          $('#data-table-masterclass').DataTable(language);
        });
    },

    listMasterclasses: function() {
      this.initialLoading = true;
      axios.get(`/masterclass/${this.user.id}/masterclassList`).then((response) => { //consulta a la API
        this.masterclasses = response.data.data;
        this.initialLoading = false;
        this.loadDataTable();
        });
    },
    listCategories() {
      axios
        .get(`/course/categories`)
        .then((response) => {
          if (response.data.error) {
            this.message = 'Sin categorias registrados';
          } else {
            Object.keys(response.data).map((key) => {
              this.categories.push(response.data[key]);
            });
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    getStatusText(status) {
      return status ? 'Activo' : 'Inactivo';
    },

    getStatusClass(status) {
      return status ? 'badge-success' : 'badge-danger';
    },
    getAvailableOptions() {
      return this.options;
    },
    getOptionSelected(masterclass) {
      let option = this.optionSelected;

      switch (option){
        case '1':
          this.masterclassSelected = masterclass;
          $('#edit-masterclass-modal').modal('show');
          this.optionSelected = '';
          break;
        case '2':
          this.masterclassSelected = masterclass;
          $('#delete-masterclass-modal').modal('show');
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
      }
      this.optionSelected = '';
    },
    async deleteMasterclass(masterclass_id) {
      try {
        const { data } = await axios.delete(`/masterclass/delete/${masterclass_id}`);
        const { status } = data;

        if (status === 'ok') {
          this.$message.success('Se ha eliminado la masterclass correctamente');
          $('#delete-course-modal').modal('hide');
          // Filtrar la lista para eliminar la masterclass sin recargar la página
          this.masterclasses = this.masterclasses.filter(m => m.id !== masterclass_id);
        } else {
          this.$message.warning('Error al validar datos');
        }
      } catch (error) {
        console.error('Error al eliminar la masterclass:', error);
        this.$message.error('Ocurrió un error al eliminar la masterclass');
      }
    },
  }
};
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}

.badge-success {
  background-color: #28a745;
}

.badge-warning {
  background-color: #ffc107;
}

.badge-secondary {
  background-color: #6c757d;
}
</style>
