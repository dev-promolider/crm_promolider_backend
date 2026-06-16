<template>
  <div>
    <section v-if="!initialLoading">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table
              id="data-table-distributors"
              class="table-hover-animation table-striped table-bordered"
            >
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Teléfono</th>
                  <th>Fecha de Asociación</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(distributor, index) in distributors"
                  :key="distributor.distributor_id"
                >
                  <td>{{ index + 1 }}</td>
                  <td>{{ distributor.name }}</td>
                  <td>{{ distributor.email }}</td>
                  <td>{{ distributor.phone || 'N/A' }}</td>
                  <td>{{ formatDate(distributor.fecha_asociacion) }}</td>
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
import axios from "axios";
import CustomSpinner from "../../common/custom-spinner/CustomSpinner.vue";
import language from "../../api/traduction_es";

export default {
  props: {
    id: {
      type: Number,
      required: true,
    },
    contentType: {
      type: String,
      default: "masterclass",
      validator: (value) => ["masterclass", "minicourse", "ebook"].includes(value),
    },
  },
  data() {
    return {
      distributors: [],
      initialLoading: false,
    };
  },
  components: {
    "custom-spinner": CustomSpinner,
  },
  mounted() {
    this.showDistributors();
  },
  methods: {
    loadDataTable() {
      this.$nextTick(function () {
        $("#data-table-distributors").DataTable(language);
      });
    },
    async showDistributors() {
      this.initialLoading = true;
      try {
        let endpoint = "";
        
        switch (this.contentType) {
          case "masterclass":
            endpoint = `/marketing/masterclass/distributors-data/${this.id}`;
            break;
          case "minicourse":
            endpoint = `/marketing/minicourse/distributors-data/${this.id}`;
            break;
          case "ebook":
            endpoint = `/marketing/ebook/distributors-data/${this.id}`;
            break;
          default:
            throw new Error("Tipo de contenido no válido");
        }

        const { data } = await axios.get(endpoint);
        this.distributors = Array.isArray(data.data) ? data.data : [];
        this.initialLoading = false;
        this.loadDataTable();
      } catch (error) {
        console.error("Error al obtener distribuidores:", error);
        this.$message.error("No se pudo cargar la lista de distribuidores.");
        this.initialLoading = false;
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    },
  },
};
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}

.table-hover-animation tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.05);
  transition: background-color 0.2s ease-in-out;
}
</style>