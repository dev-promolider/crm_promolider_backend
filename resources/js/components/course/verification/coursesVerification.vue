<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table
              id="data-table-verification-courses"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre del Infoproducto</th>
                  <th>Productor</th>
                  <th>Tipo</th>
                  <th>Fecha solicitud</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(course, index) in this.courses" v-bind:key="course.id">
                  <td>
                    <p style="width: 15px">
                      {{ (index = index + 1) }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 220px">
                      {{ course.title }}
                    </p>
                  </td>
                  <td>
                    <p style="width: 220px">
                      {{ course.name }}
                    </p>
                  </td>
                  <td>
                    <span v-if="course.product_type_id === 1" class="badge badge-primary">Curso</span>
                    <span v-else-if="course.product_type_id === 2" class="badge badge-info">Libro</span>
                    <span v-else class="badge badge-secondary">Desconocido</span>
                  </td>
                  <td>{{ moment(course.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                 
                  <td>
                    <el-select
                      id="customize_select"
                      size="mini"
                      clearable
                      v-model="optionSelected"
                      @change="getOptionSelected(course)"
                    >
                      <el-option
                        v-for="item in options"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      >
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
  </div>
</template>
<script>
import moment from 'moment';
import CustomSpinner from '../../../common/custom-spinner/CustomSpinner.vue';
import language from '../../../api/traduction_es';

export default {
  data() {
    return {
      initialLoading: false,
      moment: moment,
      courses: [],
      optionSelected: '',
      categories: [],
      options: [
        {
          value: '1',
          label: 'Observar',
        },
      ],
      courseSelected: {},
    };
  },
  mounted() {
    this.listCourses();
    this.listCategories();
  },

  components: {
    'custom-spinner': CustomSpinner,
  },

  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $('#data-table-verification-courses').DataTable(language);
      });
    },

    listCourses: function () {
      this.initialLoading = true;
      axios.get(`/course/courseListVerification`).then((response) => {
        this.courses = response.data.data;
        this.initialLoading = false;
        this.loadDataTable();
      });
    },

    listCategories() {
      axios
        .get(`/course/categories`)
        .then((response) => {
          if (response.data.error) {
            this.message = 'Sin cursos registrados';
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

    /**
     * Return data about the option selected in the table
     * @param {Object} course    - The course selected
     */
    getOptionSelected(course) {
      let option = this.optionSelected;

      switch (option) {
        case '1':
          document.location.href = `/course/verification/${course.id}/review`;
          break;
        default:
          console.log('Error');
          break;
      }
      this.optionSelected = '';
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