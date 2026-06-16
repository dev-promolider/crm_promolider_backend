<template>
  <div>
    <div class="mb-1">
      <button class="btn btn-primary" @click="changeOptionModal('0')">Crear categoría</button>
    </div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="data-table-list-categories" class="table-hover-animation table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Categoría</th>
                  <th>Icono</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(category, index) in this.categories" v-bind:key="category.id" v-bind:index="index">
                  <td>
                    <p style="width: 30px">
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 120px">
                      {{ category.name }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 100px">
                      <span :class="category.icon"></span>
                    </p>
                  </td>
                  <td class="align-content-center">
                    <el-select id="customize_select" size="mini" clearable v-model="optionSelected"
                      @change="getOptionSelected(category)">
                      <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
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

    <custom-modal id="edit-category-modal" color="primary" size="large" v-if="categorySelected">
      <template #title>
        {{ categorySelected.id == '' ? 'Crear Categoría' : 'Editar Categoría' }}
        <u> {{ categorySelected.name }} </u></template>
      <template #body>
        <form-category-edit :category="categorySelected" @listenMethod="methodUpdateTable"></form-category-edit>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import moment from 'moment';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import language from '../../api/traduction_es';
import CategoryEdit from './CategoryEdit.vue';

export default {
  data() {
    return {
      initialLoading: false,
      moment: moment,
      categories: [],
      options: [
        {
          value: '1',
          label: 'Editar',
        },
      ],
      optionSelected: '',
      categorySelected: {
        id: '',
        name: '',
        icon: '',
      },
    };
  },
  components: {
    'custom-modal': ModalComponent,
    'custom-spinner': CustomSpinner,
    'form-category-edit': CategoryEdit,
  },

  mounted() {
    this.listCategories();
    this.initForm();
  },

  methods: {
    //update list from child
    methodUpdateTable() {
      this.listCategories();
    },
    /**
     * Return data about the option selected in the table
     * @param {object} category - The course object selected
     */
    initForm() {
      this.categorySelected = {
        id: '',
        name: '',
        icon: '',
      };
    },
    getOptionSelected(category) {
      // La opcion se recupera de la variable optionSelected
      let option = this.optionSelected;

      switch (option) {
        case '1':
          this.categorySelected = category;
          $('#edit-category-modal').modal('show');
          this.optionSelected = '';
          break;
        default:
          console.log('Error');
          break;
      }
    },
    loadDataTable() {
      $('#data-table-list-categories').DataTable().destroy();
      this.$nextTick(function () {
        $('#data-table-list-categories').DataTable(language);
      });
    },

    listCategories: function () {
      this.initialLoading = true;
      axios.get(`/course/categories`).then((response) => {
        this.categories = response.data;
        this.initialLoading = false;
        this.loadDataTable();
      });
    },
    changeOptionModal(option) {
      this.initForm();
      if (option == '0') {
        $('#edit-category-modal').modal('show');
      }
    },
  },
};
</script>
<style>
.el-select {
  width: 80%;
}

.el-select .el-input .el-select__caret::before {
  content: '\e794';
}

#customize_select {
  background-color: #ffffff !important;
  border: none !important;
  width: 0px !important;
  font-size: 0px !important;
  justify-content: center;
  text-align: center;
  padding: 0 !important;
}
</style>
