<!-- ProfileUser.vue - Solo las partes modificadas -->

<template>
  <div>
    <section>
      <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
          <!-- Loading state para el perfil -->
          <div v-if="isLoading" class="card">
            <div class="card-body text-center">
              <div class="spinner-border" role="status">
                <span class="sr-only">Cargando...</span>
              </div>
              <p class="mt-2">Cargando perfil...</p>
            </div>
          </div>
          
          <!-- Contenido del perfil cuando ya cargó -->
          <div v-else class="card">
            <div class="card-body">
              <div class="user-avatar-section">
                <div class="d-flex align-items-center flex-column">
                  <img class="img-fluid rounded mt-1 mb-2" v-bind:src="userCurrent.photo" height="110" width="110"
                    alt="User avatar" />
                  <div class="user-info text-center">
                    <h4>{{ userCurrent.name + ' ' + userCurrent.last_name }}</h4>
                    <span class="badge bg-light-secondary">{{ role }}</span>
                    <span class="badge bg-light-secondary">{{ membreshipCurrent.account }}</span>
                  </div>
                </div>
              </div>

              <h4 class="fw-bolder border-bottom pb-50 mb-1">Detalles</h4>
              <div class="info-container">
                <ul class="list-unstyled">
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Usuario:</span>
                    <span>{{ userCurrent.username }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Correo:</span>
                    <span>{{ userCurrent.email }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Estado:</span>
                    <span class="badge" :class="userCurrent.request == '2' ? 'bg-light-success' : 'bg-light-danger'">{{
                      userCurrent.request == '2' ? 'Activo' : 'Desactivado' }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Rol:</span>
                    <span>{{ role }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Numero de documento:</span>
                    <span>{{ userCurrent.nro_document }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Contacto:</span>
                    <span>{{ userCurrent.phone }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">País:</span>
                    <span>{{ userCurrent.countryName }}</span>
                  </li>
                  <li class="mb-75">
                    <span class="fw-bolder font-weight-bold me-25">Creación:</span>
                    <span>{{ formatDate(userCurrent.created_at) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
          <!-- Solo mostrar pestañas cuando los datos están cargados -->
          <div v-if="!isLoading">
            <ul class="nav nav-pills mb-2">
              <li class="nav-item">
                <a class="nav-link" :class="option_men == 0 ? 'active' : ''">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-user font-medium-3 me-50">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  <span class="fw-bold" @click="viewMenu(0)">Cuenta</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="option_men == 1 ? 'active' : ''">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-lock font-medium-3 me-50">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                  <span class="fw-bold" @click="viewMenu(1)">Seguridad</span>
                </a>
              </li>
              <li v-if="userCurrent.id_account_type != 1" class="nav-item">
                <a class="nav-link" :class="option_men == 2 ? 'active' : ''">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-bookmark font-medium-3 me-50">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                  </svg>
                  <span class="fw-bold" @click="viewMenu(2)">Membresias</span>
                </a>
              </li>
            </ul>
            
            <div class="row">
              <div class="col-12">
                <div v-if="option_men == 0">
                  <AccountUserProfile :userCurrent="userCurrent" :countrys="countrys">
                  </AccountUserProfile>
                </div>

                <div v-if="option_men == 1">
                  <SecurityUser> </SecurityUser>
                </div>
                
                <div v-if="option_men == 2">
                  <BillingPlansUserProfile :userCurrent="userCurrent" :membreshipCurrent="membreshipCurrent">
                  </BillingPlansUserProfile>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Loading state para el contenido -->
          <div v-else class="text-center py-5">
            <div class="spinner-border" role="status">
              <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información...</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import api from '../../api/api';
import SecurityComponent from './Security.vue';
import BillingComponent from './Billing-Plans.vue';
import AccountComponent from './Account-Profile.vue';

export default {
  name: 'ProfileUser',
  components: {
    SecurityUser: SecurityComponent,
    BillingPlansUserProfile: BillingComponent,
    AccountUserProfile: AccountComponent,
  },
  data: () => ({
    userCurrent: {},
    membreshipCurrent: {},
    option_men: 0,
    role: '',
    countrys: [],
    isLoading: true, // NUEVO: Estado de carga
  }),

  methods: {
    viewMenu(opcion) {
      this.option_men = opcion;
    },

    async getDataUserCurrent() {
      try {
        this.isLoading = true;
        const response = await api.get(`/users/get-data-currentuser`);
        this.userCurrent = response;
        
        // Esperar a que se carguen los datos de membresía también
        await this.getDataMembershipCurrent();
        
        // Log para debugging
        console.log('Usuario cargado:', {
          username: this.userCurrent.username,
          id: this.userCurrent.id
        });
        
      } catch (error) {
        console.error('Error cargando datos del usuario:', error);
        this.$message.error('Error al cargar los datos del usuario');
      } finally {
        this.isLoading = false;
      }
    },

    async getDataMembershipCurrent() {
      try {
        if (this.userCurrent.id_account_type) {
          const response = await api.get(`/account-type/get-data-id/${this.userCurrent.id_account_type}`);
          this.membreshipCurrent = response;
        }
      } catch (error) {
        console.error('Error cargando membresía:', error);
      }
    },

    async getCountrys() {
      try {
        const response = await api.get(`/setting/countrys`);
        this.countrys = response;
      } catch (error) {
        console.error('Error cargando países:', error);
      }
    },

    async getCurrentRole() {
      try {
        const response = await api.get('/roles/currentRole');
        this.role = response[0];
      } catch (error) {
        console.error('Error cargando rol:', error);
      }
    },

    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const year = date.getFullYear();
      const month = ('0' + (date.getMonth() + 1)).slice(-2);
      const day = ('0' + date.getDate()).slice(-2);
      return `${year}-${month}-${day}`;
    },
  },

  async mounted() {
    // Cargar todos los datos de forma paralela cuando sea posible
    try {
      await Promise.all([
        this.getDataUserCurrent(), // Este incluye getDataMembershipCurrent
        this.getCurrentRole(),
        this.getCountrys()
      ]);
    } catch (error) {
      console.error('Error en el montaje del componente:', error);
    }
  },
};
</script>