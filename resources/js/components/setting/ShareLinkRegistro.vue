<template>
  <div>

    <!-- FILA SUPERIOR: controles de enlace de registro -->
    <div class="row align-items-start mb-1">

      <!-- Tipo de enlace (fijo: Registro) -->
      <div class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body">
            <p class="ctrl-label">Tipo de enlace</p>
            <div class="btn-group" role="group">
              <button type="button" class="btn ctrl-btn btn-primary" disabled>Registro</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Ajuste de pierna -->
      <div class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body">
            <p class="ctrl-label">Ajuste de pierna</p>
            <div class="btn-group" role="group">
              <button type="button" class="btn ctrl-btn"
                :class="selectedLeg === 'izquierda' ? 'btn-primary' : 'btn-outline-secondary'"
                :disabled="hasActiveLink"
                @click="selectedLeg = 'izquierda'">Izquierda</button>
              <button type="button" class="btn ctrl-btn"
                :class="selectedLeg === 'derecha' ? 'btn-primary' : 'btn-outline-secondary'"
                :disabled="hasActiveLink"
                @click="selectedLeg = 'derecha'">Derecha</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Botón generar / suspender -->
      <div class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body d-flex align-items-center">
            <!-- v-show en lugar de v-if/v-else para evitar insertBefore -->
            <button v-show="!hasActiveLink"
              class="btn btn-primary ctrl-btn"
              @click.prevent="generateLink"
              :disabled="isSubmitting">
              <i v-show="!isSubmitting" data-feather="link" style="width:16px;height:16px;margin-right:6px;"></i>
              <span v-show="isSubmitting" class="spinner-border spinner-border-sm mr-50" role="status"></span>
              {{ isSubmitting ? 'Generando...' : 'Generar enlace' }}
            </button>
            <button v-show="hasActiveLink" class="btn btn-danger ctrl-btn" @click.prevent="suspendLink">
              <i data-feather="trash-2" style="width:16px;height:16px;margin-right:6px;"></i>
              Suspender enlace
            </button>
          </div>
        </div>
      </div>

      <!-- Enlace generado (v-show para evitar insertar/remover nodos) -->
      <div v-show="generatedLinkUrl" class="col-12 col-md mb-1">
        <div class="card ctrl-card mb-0" style="width:100%;">
          <div class="card-body">
            <p class="ctrl-label text-success">
              <i data-feather="link" style="width:13px;height:13px;margin-right:4px;"></i>
              Tu enlace de invitación
            </p>
            <div class="input-group">
              <input type="text" class="form-control" :value="generatedLinkUrl" readonly ref="linkInput" />
              <div class="input-group-append">
                <button class="btn btn-primary ctrl-btn" type="button" @click="copyLink">
                  <i data-feather="copy" style="width:15px;height:15px;margin-right:5px;"></i>
                  Copiar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- TABLA: personas que usaron el enlace de registro y pagaron -->
    <custom-spinner v-if="loading"></custom-spinner>
    <template v-else>
      <div v-once class="row">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header bg-light py-1">
              <h4 class="card-title mb-0 text-primary">Mis Directos</h4>
            </div>
            <div class="card-body">
              <p class="card-text text-muted">
                En la siguiente tabla encontrará los datos de sus directivos --
              </p>
            </div>
            <div class="table-responsive px-2">
              <table class="table table-hover-animation table-striped table-bordered" id="directosTableRegistro">
                <thead class="thead-light">
                  <tr>
                    <th class="align-middle">Nombre</th>
                    <th class="align-middle">Posición</th>
                    <th class="align-middle">Teléfono</th>
                    <th class="align-middle">Email</th>
                    <th class="align-middle">Inscripción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="row.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar mr-2">
                          <div class="avatar-initial rounded-circle bg-light-primary">
                            {{ getInitials(row.nombre) }}
                          </div>
                        </div>
                        <div class="user-info text-truncate">
                          <span class="d-block font-weight-bold text-truncate">{{ row.nombre || '—' }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-pill px-2 py-1"
                        :class="row.lado === 'izquierda' ? 'badge-light-success' : row.lado === 'derecha' ? 'badge-light-primary' : 'badge-light-secondary'">
                        {{ row.lado === 'izquierda' ? 'Izquierda' : row.lado === 'derecha' ? 'Derecha' : '—' }}
                      </span>
                    </td>
                    <td>
                      <a v-if="row.whatsapp" :href="'tel:' + row.whatsapp" class="text-body">{{ row.whatsapp }}</a>
                      <span v-else>—</span>
                    </td>
                    <td>
                      <a v-if="row.correo" :href="'mailto:' + row.correo" class="text-body text-truncate d-block">{{ row.correo }}</a>
                      <span v-else>—</span>
                    </td>
                    <td>
                      <span class="text-nowrap">{{ formatDate(row.fecha_registro) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>

  </div>
</template>

<script>
import api from '../../api/api';
import apiShareLink from '../../api/api.share-link';
import axios from 'axios';
import moment from 'moment';
import language from '../../api/traduction_es';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner';

export default {
  name: 'ShareLinkRegistro',
  components: { CustomSpinner },
  props: {
    username: { type: String, required: true },
  },
  data() {
    return {
      user: {},
      link: null,
      dbLink: null,
      isSubmitting: false,
      generatedLinkUrl: '',
      selectedLeg: 'izquierda',
      loading: true,
      rows: [],
      tableInitialized: false,
    };
  },
  computed: {
    hasActiveLink() {
      return !!(this.link?.url || this.generatedLinkUrl?.length);
    },
  },
  watch: {
    username: { immediate: true, handler(v) { if (v?.length > 0) this.loadAll(); } },
  },
  methods: {
    async loadAll() {
      await Promise.all([this.loadLink(), this.loadReferrals()]);
    },

    // ── Enlace ──────────────────────────────────────────────────────────────
    async loadLink() {
      try {
        const r = await api.get(`/config/share-link/${this.username}`);
        this.user = r[0] || {};
        this.dbLink = (r[1] && r[1] !== 0 && r[1].url) ? r[1] : null;
        this.link = this.dbLink;
        this.generatedLinkUrl = this.dbLink?.url || '';
      } catch {
        this.$message.error('No se pudo cargar la información del enlace');
      }
    },

    async generateLink() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      try {
        const r = await apiShareLink.add({ estado: true, user_id: this.user.id });
        if (r?.resource?.url) {
          this.generatedLinkUrl = r.resource.url;
          this.link = r.resource;
          this.$message.success('Enlace generado');
        } else throw new Error();
      } catch {
        this.$message.error('No se pudo crear el enlace');
      } finally {
        this.isSubmitting = false;
      }
    },

    async suspendLink() {
      try {
        await this.$confirm('¿Suspender este enlace?', 'Aviso', {
          confirmButtonText: 'Sí', cancelButtonText: 'No', type: 'warning',
        });
        const r = await axios.delete(`/config/share-link/delete/${this.link?.id}`);
        if (r.data.state == 200) {
          this.link = null;
          this.generatedLinkUrl = '';
          this.$message.success('Enlace suspendido');
        }
      } catch (e) {
        if (e !== 'cancel') this.$message.error('Error al suspender');
      }
    },

    copyLink() {
      const text = this.generatedLinkUrl;
      if (!text) return;
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => this.$message.success('Enlace copiado'));
      } else {
        const el = this.$refs.linkInput;
        if (el) { el.select(); document.execCommand('copy'); this.$message.success('Enlace copiado'); }
      }
    },

    // ── Tabla: solo registro + pagado ────────────────────────────────────────
    async loadReferrals() {
      this.loading = true;
      try {
        const r = await apiShareLink.referrals(this.username);
        this.rows = (r.rows || []).filter(
          row => row.origen === 'registro' && row.pago_estado === 'pagado'
        );
      } catch {
        this.$message.error('Error al cargar directivos');
      } finally {
        this.loading = false;
        // DataTable se inicializa en updated() la primera vez que se renderiza la tabla
      }
    },

    initDataTable() {
      if (typeof $ === 'undefined' || !$.fn || !$.fn.DataTable) return;
      if ($.fn.DataTable.isDataTable('#directosTableRegistro')) {
        $('#directosTableRegistro').DataTable().destroy();
      }
      $('#directosTableRegistro').DataTable({
        ...language,
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      });
    },

    // ── Helpers ──────────────────────────────────────────────────────────────
    getInitials(name) {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
    },

    formatDate(dateStr) {
      if (!dateStr) return '—';
      return moment(dateStr).format('DD/MM/YYYY hh:mm A');
    },
  },
  mounted() {
    if (window.feather) window.feather.replace();
  },
  updated() {
    // Inicializar DataTable solo UNA vez cuando la tabla se haya renderizado
    if (!this.tableInitialized && !this.loading && document.getElementById('directosTableRegistro')) {
      this.tableInitialized = true;
      this.$nextTick(() => this.initDataTable());
    }
  },
};
</script>

<style scoped>
/* ── Paneles de control ── */
.ctrl-card {
  display: inline-flex;
  flex-direction: column;
  width: fit-content;
  min-width: 0;
  border-radius: 0.5rem;
  box-shadow: 0 2px 10px rgba(34,41,47,.08);
}
.ctrl-card .card-body {
  padding: 1rem 1.25rem;
}
.ctrl-label {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6e6b7b;
  margin-bottom: 0.6rem;
}
.ctrl-btn {
  font-size: 0.95rem;
  padding: 0.45rem 1.1rem;
  font-weight: 500;
}

/* ── Tabla ── */
.card {
  padding-top: 6px !important;
  border-radius: 0.428rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
}
.avatar {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}
.avatar-initial {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.857rem;
  font-weight: 600;
}
.bg-light-primary {
  background-color: rgba(115, 103, 240, 0.12) !important;
  color: #7367f0 !important;
}
.user-info { max-width: 150px; }
.badge-light-success { background-color: rgba(40,199,111,.12); color: #28c76f; }
.badge-light-primary  { background-color: rgba(115,103,240,.12); color: #7367f0; }
.badge-light-secondary{ background-color: rgba(182,182,182,.12); color: #b6b6b6; }
</style>
