<template>
  <div class="container mt-5">
    <section v-if="!initialLoading">
      <div v-if="filteredEbooks.length === 0" class="alert alert-info">
        No se encontraron resultados para "{{ searchTerm }}"
      </div>
      <div class="row">
        <div v-for="ebook in filteredEbooks" :key="ebook.id" class="col-md-4 mb-4">
          <div class="card shadow" @click="redirectToLaravel(ebook.id)" style="cursor: pointer; position: relative;">
            
            <!-- Imagen con separación -->
            <div class="card-img-wrapper">
              <img
                v-if="ebook.images && ebook.images.length > 0"
                :src="getFirstImage(ebook.images)"
                class="card-img-top"
                alt="Portada del e-book"
              />
            </div>

            <div class="card-body">
              <h5 class="card-title">{{ ebook.title }}</h5>
              <p class="card-text">
                <strong>Autor:</strong> {{ ebook.author }} <br>
                <strong>Categoría:</strong> {{ ebook.category_name }} <br>
                <strong>Páginas:</strong> {{ ebook.pages }} <br>
              </p>
              <!-- Badge de E-book -->
              <span class="badge badge-warning position-absolute ebook-badge">E-book</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import axios from 'axios';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';

export default {
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      ebooks: [],
      searchTerm: '',
      initialLoading: true,
    };
  },
  components: {
    'custom-spinner': CustomSpinner,
  },
  computed: {
    filteredEbooks() {
      if (!this.searchTerm) {
        return this.ebooks;
      }

      const term = this.searchTerm.toLowerCase();
      return this.ebooks.filter(ebook => {
        return (
          ebook.title.toLowerCase().includes(term) ||
          ebook.author.toLowerCase().includes(term) ||
          ebook.category_name.toLowerCase().includes(term)
        );
      });
    }
  },
  mounted() {
    this.fetchEbooks();
    window.addEventListener('marketplace-search', this.handleSearch);
  },
  beforeDestroy() {
    window.removeEventListener('marketplace-search', this.handleSearch);
  },
  methods: {
    async fetchEbooks() {
      try {
        const response = await axios.get(`/marketing/marketplace/ebooks/list`);
        if (response.status === 200) {
          this.ebooks = response.data.data;
        }
      } catch (error) {
        console.error('Error al obtener los e-books:', error);
      } finally {
        this.initialLoading = false;
      }
    },

    handleSearch(event) {
      this.searchTerm = event.detail.searchTerm;
      if (this.$el.offsetParent === null) { 
        this.$nextTick(() => {
          this.$forceUpdate();
        });
      }
    },

    redirectToLaravel(id) {
      window.location.href = `/marketing/${id}/ebook`;
    },

    getFirstImage(images) {
      if (images.length > 0) {
        return images[0].image;
      }
      return 'https://via.placeholder.com/150';
    },
  },
};
</script>

<style scoped>
.card {
  transition: 0.3s;
  border-radius: 10px;
  min-height: 480px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}

.card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Imagen con separación */
.card-img-wrapper {
  padding: 16px;
  background-color: transparent;
}

/* 🔲 Imagen con borde blanco */
.card-img-top {
  height: 350px !important;
  max-height: none !important;
  min-height: 0 !important;
  object-fit: cover;
  width: 100%;
  border: 20px solid #fff; /* 👈 Borde blanco */
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.1); /* Suaviza el contraste */
  border-radius: 10px 10px 0 0; /* Mantiene esquinas redondeadas */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Cuerpo de la tarjeta */
.card-body {
  flex-grow: 1;
  background-color: #ffffff;
  padding: 1.25rem;
  position: relative;
}

/* Badge de E-book */
.ebook-badge {
  bottom: 10px;
  right: 15px;
}

.badge-success {
  background-color: #28a745;
}

.badge-danger {
  background-color: #dc3545;
}

.badge-warning {
  background-color: #00d0e4;
  color: #212529;
}
</style>