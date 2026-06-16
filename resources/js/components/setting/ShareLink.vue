<template>
  <div>

    <!-- FILA SUPERIOR -->
    <div class="row align-items-start mb-1">

      <!-- Tipo de enlace -->
      <div class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body">
            <p class="ctrl-label">Tipo de enlace</p>
            <div class="btn-group" role="group">
              <button type="button" class="btn ctrl-btn btn-primary" disabled>Preregistro</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Ajuste de pierna (preregistro) -->
      <div v-if="tipoEnlace === 'preregistro'" class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body">
            <p class="ctrl-label">Ajuste de pierna</p>
            <div class="btn-group" role="group">
              <button type="button" class="btn ctrl-btn"
                :class="selectedLeg === 'izquierda' ? 'btn-primary' : 'btn-outline-secondary'"
                :disabled="linkActive"
                @click="selectedLeg = 'izquierda'">Izquierda</button>
              <button type="button" class="btn ctrl-btn"
                :class="selectedLeg === 'derecha' ? 'btn-primary' : 'btn-outline-secondary'"
                :disabled="linkActive"
                @click="selectedLeg = 'derecha'">Derecha</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Botón generar / suspender (preregistro) -->
      <div v-if="tipoEnlace === 'preregistro'" class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body d-flex align-items-center">
            <button v-if="!linkActive" class="btn btn-primary ctrl-btn"
              @click.prevent="generatePreregistroLink"
              :disabled="isSavingConfig">
              <span v-if="isSavingConfig" class="spinner-border spinner-border-sm mr-50" role="status"></span>
              <i v-if="!isSavingConfig" data-feather="link" style="width:16px;height:16px;margin-right:6px;"></i>
              {{ isSavingConfig ? 'Generando...' : 'Generar link' }}
            </button>
            <button v-else class="btn btn-danger ctrl-btn" @click.prevent="suspendPreregistroLink">
              <i data-feather="trash-2" style="width:16px;height:16px;margin-right:6px;"></i>
              Suspender enlace
            </button>
          </div>
        </div>
      </div>

      <!-- Botón generar / suspender (registro) -->
      <div v-if="tipoEnlace === 'registro'" class="col-auto mb-1">
        <div class="card ctrl-card h-100 mb-0">
          <div class="card-body d-flex align-items-center">
            <button v-if="!hasActiveLink"
              class="btn btn-primary ctrl-btn"
              @click.prevent="handleGenerate"
              :disabled="isSubmitting">
              <i v-if="!isSubmitting" data-feather="link" style="width:16px;height:16px;margin-right:6px;"></i>
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm mr-50" role="status"></span>
              {{ isSubmitting ? 'Generando...' : 'Generar enlace' }}
            </button>
            <button v-else class="btn btn-danger ctrl-btn" @click.prevent="deleteLink(link && link.id)">
              <i data-feather="trash-2" style="width:16px;height:16px;margin-right:6px;"></i>
              Suspender enlace
            </button>
          </div>
        </div>
      </div>

      <!-- Enlace generado (registro) -->
      <div v-if="tipoEnlace === 'registro' && generatedLinkUrl" class="col-12 col-md mb-1">
        <div class="card ctrl-card mb-0">
          <div class="card-body">
            <p class="ctrl-label text-success">
              <i data-feather="link" style="width:13px;height:13px;margin-right:4px;"></i>
              Tu enlace de invitación
            </p>
            <div class="input-group">
              <input type="text" class="form-control" :value="generatedLinkUrl" readonly ref="linkInput" />
              <div class="input-group-append">
                <button class="btn btn-primary ctrl-btn" type="button" @click="copyLinkDirect">
                  <i data-feather="copy" style="width:15px;height:15px;margin-right:5px;"></i>
                  Copiar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enlace generado (preregistro) -->
      <div v-if="tipoEnlace === 'preregistro' && linkActive" class="col-12 col-md mb-1">
        <div class="card ctrl-card mb-0" style="width:100%;">
          <div class="card-body">
            <p class="ctrl-label text-success">
              <i data-feather="link" style="width:13px;height:13px;margin-right:4px;"></i>
              Tu enlace de invitación
            </p>
            <div class="input-group">
              <input type="text" class="form-control" :value="preregistroLinkUrl" readonly ref="linkInputPreregistro" />
              <div class="input-group-append">
                <button class="btn btn-primary ctrl-btn" type="button" @click="copyLinkPreregistro">
                  <i data-feather="copy" style="width:15px;height:15px;margin-right:5px;"></i>
                  Copiar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- GALERÍA DE LANDINGS -->
    <div v-if="tipoEnlace === 'preregistro'" class="card mt-1">
      <div class="card-header py-1">
        <h4 class="card-title mb-0">Selecciona el tipo de landing</h4>
        <p class="card-text text-muted font-small-2 mb-0">Elige el diseño que verán tus referidos al abrir tu enlace</p>
      </div>
      <div class="card-body">
        <div class="row">            <div v-for="landing in landings" :key="landing.tema" class="col-12 col-sm-6 col-lg-4 mb-2">
            <div class="card mb-0 h-100 cursor-pointer landing-card"
              :class="[
                tipoPreregistro === landing.tema ? 'border-primary shadow' : 'border',
                linkActive ? 'disabled-card' : ''
              ]"
              style="border-width: 2px !important; transition: all .2s; overflow:hidden;"
              @click="linkActive ? null : (tipoPreregistro = landing.tema)">

              <!-- Thumbnail iframe -->
              <div class="landing-thumb-wrap">
                <iframe
                  :src="`${getBaseUrl()}/preregistro/${username}?lado=izquierda&tema=${landing.tema}`"
                  class="landing-thumb-iframe"
                  scrolling="no"
                  tabindex="-1"
                ></iframe>
                <!-- Check badge -->
                <div v-if="tipoPreregistro === landing.tema"
                  class="badge badge-success landing-check">
                  <i data-feather="check" style="width:13px;height:13px;"></i>
                </div>
              </div>

              <!-- Info -->
              <div class="card-body py-1 px-2">
                <h6 class="card-title mb-25">{{ landing.nombre }}</h6>
                <p class="card-text text-muted font-small-2 mb-1">{{ landing.descripcion }}</p>
                <div class="d-flex" style="gap:8px;">
                  <button class="btn btn-outline-secondary btn-sm flex-shrink-0"
                    @click.stop="abrirPreviewLanding(landing.tema)">
                    <i data-feather="eye" style="width:12px;height:12px;margin-right:3px;"></i>
                    Vista previa
                  </button>
                  <button class="btn btn-sm flex-grow-1"
                    :class="tipoPreregistro === landing.tema ? 'btn-outline-primary' : 'btn-primary'"
                    :disabled="linkActive"
                    @click.stop="linkActive ? null : (tipoPreregistro = landing.tema)">
                    {{ tipoPreregistro === landing.tema ? 'Seleccionado' : 'Seleccionar' }}
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLA DE PREREGISTROS PENDIENTES -->
    <div class="card shadow-sm mt-2">
      <div class="card-header bg-light py-1">
        <h4 class="card-title mb-0 text-primary">Mis Preregistros</h4>
      </div>
      <div class="card-body pb-0">
        <p class="card-text text-muted">
          En la siguiente tabla encontrará los datos de sus preregistros pendientes de pago --
        </p>
      </div>
      <div class="table-responsive px-2">
        <table class="table table-hover-animation table-striped table-bordered" id="preregistrosTable">
          <thead class="thead-light">
            <tr>
              <th class="align-middle">Nombre</th>
              <th class="align-middle">Pierna</th>
              <th class="align-middle">WhatsApp</th>
              <th class="align-middle">Email</th>
              <th class="align-middle">Inscripción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in referralsRows" :key="row.id">
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

    <!-- Estado vacío registro -->
    <div v-if="!hasActiveLink && !isSubmitting && tipoEnlace === 'registro'" class="card">
      <div class="card-body text-center py-3">
        <i data-feather="link" style="width:32px;height:32px;color:#b9b9c3;"></i>
        <p class="text-muted mt-1 mb-0">Aún no tienes un enlace activo.<br>Haz clic en <strong>Generar enlace</strong> para crear uno.</p>
      </div>
    </div>

    <!-- MODAL PREVIEW LANDING -->
    <div v-if="mostrarPreviewModal" class="preview-overlay" @click.self="cerrarPreviewLanding">
      <div class="preview-container">
        <div class="preview-header d-flex align-items-center justify-content-between px-2 py-1 bg-light border-bottom">
          <span class="font-weight-bold font-small-3">Vista previa — Landing <strong>{{ tipoPreviewActual }}</strong></span>
          <button class="btn btn-outline-secondary btn-sm" @click="cerrarPreviewLanding">
            <i data-feather="x" style="width:14px;height:14px;margin-right:4px;"></i>
            Cerrar
          </button>
        </div>
        <iframe :src="previewLandingUrl" style="flex:1; width:100%; border:none;" frameborder="0"></iframe>
      </div>
    </div>

    <!-- MODAL COPIAR -->
    <div class="modal fade" id="modalViewRequest" tabindex="-1" role="dialog" ref="modal">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Copiar enlace</h5>
            <button type="button" class="close" @click="closeModal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <p class="mb-50">Pierna: <strong>{{ updatePosition }}</strong></p>
            <input type="text" class="form-control form-control-sm" :value="generatedLinkUrl" readonly ref="linkInputModal" />
          </div>
          <div class="modal-footer">
            <button class="btn btn-light btn-sm" @click="closeModal">Cancelar</button>
            <button class="btn btn-primary btn-sm" @click="copy(generatedLinkUrl)" ref="copyButton">Copiar y cerrar</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import api from '../../api/api';
import apiShareLink from '../../api/api.share-link';
import AdjustLeg from '@/components/binary-branch/AdjustLeg.vue';
import axios from 'axios';
import moment from 'moment';
import language from '../../api/traduction_es';

export default {
  name: 'ShareLink',
  props: {
    username: { type: String, required: true, validator: v => v && v.length > 0 },
  },
  data() {
    return {
      user: {}, initialLoading: false, link: null, dbLink: null,
      isSubmitting: false, generatedLinkUrl: '',
      tipoEnlace: 'preregistro', selectedLeg: 'izquierda', tipoPreregistro: 'oscuro',
      linkActive: false,
      mostrarPreviewModal: false, previewLandingUrl: '', tipoPreviewActual: '',
      // Configuración de preregistro (preregistro_links)
      preregistroConfig: null,
      isSavingConfig: false,

      // Tabla de preregistros
      referralsLoading: false,
      referralsSummary: null,
      referralsRows: [],
      landings: [
        {
          tema: 'oscuro',
          nombre: 'Landing Oscuro',
          descripcion: 'Diseño moderno con fondo negro y acentos verdes. Ideal para un look premium y tecnológico.',
        },
        {
          tema: 'claro',
          nombre: 'Landing Claro',
          descripcion: 'Diseño limpio con fondo blanco y tonos verdes. Ideal para una apariencia profesional y accesible.',
        },
      ],
    };
  },
  watch: {
    username: { immediate: true, handler(v) { if (v?.length > 0) { this.listUser(); this.loadReferrals(); this.loadPreregistroConfig(); } } },
    tipoEnlace() {
      this.selectedLeg = 'izquierda';
      if (this.tipoEnlace === 'registro') {
        this.link = this.dbLink; this.generatedLinkUrl = this.dbLink?.url || '';
      } else {
        this.link = null; this.generatedLinkUrl = '';
      }
    },
  },
  computed: {
    updatePosition() { return this.user?.position === 0 ? 'izquierda' : 'derecha'; },
    hasActiveLink() { return !!(this.link?.url || this.generatedLinkUrl?.length); },
    // Enlace limpio sin query params — la config se lee de la DB
    preregistroLinkUrl() {
      return `${window.location.origin}/preregistro/${this.username}`;
    },
  },
  methods: {
    getBaseUrl() { return window.location.origin; },

    // ── Configuración de preregistro (preregistro_links) ────────────────────
    async loadPreregistroConfig() {
      try {
        const r = await api.get(`/preregistro/config/${this.username}`);
        this.preregistroConfig = r;
        this.selectedLeg = r.lado || 'izquierda';
        this.tipoPreregistro = r.landing || 'oscuro';
        this.linkActive = true;
      } catch {
        // Primera vez, aún no hay config
        this.preregistroConfig = null;
        this.linkActive = false;
      }
    },

    async generatePreregistroLink() {
      if (this.isSavingConfig) return;
      this.isSavingConfig = true;
      try {
        const r = await api.post('/preregistro/save-config', {
          username: this.username,
          lado: this.selectedLeg,
          landing: this.tipoPreregistro,
        });
        if (r.ok) {
          this.preregistroConfig = r.config;
          this.linkActive = true;
          this.$message.success('Enlace generado correctamente');
          this.$nextTick(() => { if (window.feather) window.feather.replace(); });
        } else {
          throw new Error();
        }
      } catch {
        this.$message.error('Error al generar el enlace');
      } finally {
        this.isSavingConfig = false;
      }
    },

    async suspendPreregistroLink() {
      try {
        await this.$confirm('¿Suspender este enlace?', 'Aviso', {
          confirmButtonText: 'Sí', cancelButtonText: 'No', type: 'warning'
        });
        this.linkActive = false;
        this.preregistroConfig = null;
        this.generatedLinkUrl = '';
        this.link = null;
        this.$message.success('Enlace suspendido');
      } catch (e) {
        if (e !== 'cancel') this.$message.error('Error al suspender el enlace');
      }
    },

    copyLinkPreregistro() {
      this.copyToClipboard(this.preregistroLinkUrl);
    },
    async listUser() {
      this.initialLoading = true;
      try {
        const r = await api.get(`/config/share-link/${this.username}`);
        this.user = r[0] || {};
        this.dbLink = (r[1] && r[1] !== 0 && r[1].url) ? r[1] : null;
        if (this.tipoEnlace === 'registro') {
          this.link = this.dbLink; this.generatedLinkUrl = this.dbLink?.url || '';
        } else {
          this.link = null; this.generatedLinkUrl = '';
        }
      } catch { this.$message.error('No se pudo cargar la información'); }
      finally { this.initialLoading = false; }
    },
    async submit() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      try {
        const r = await apiShareLink.add({ estado: true, user_id: this.user.id });
        if (r?.resource?.url) { this.generatedLinkUrl = r.resource.url; this.link = r.resource; this.$message.success('Enlace generado'); }
        else throw new Error();
      } catch { this.$message.error('No se pudo crear el enlace'); this.generatedLinkUrl = ''; }
      finally { this.isSubmitting = false; }
    },
    async deleteLink(id) {
      try {
        await this.$confirm('¿Suspender este enlace?', 'Aviso', { confirmButtonText: 'Sí', cancelButtonText: 'No', type: 'warning' });
        const r = await axios.delete(`/config/share-link/delete/${id}`);
        if (r.data.state == 200) { this.link = null; this.generatedLinkUrl = ''; this.$message.success('Operación exitosa'); }
      } catch (e) { if (e !== 'cancel') this.$message.error('Error al suspender'); }
    },
    async handleGenerate() {
      if (this.tipoEnlace === 'registro') await this.submit();
    },

    async showModal() {
      if (!this.user.id) await this.listUser();
      const el = this.$refs.modal;
      if (el) { $(el).modal('show'); $(el).one('shown.bs.modal', () => this.$refs.copyButton?.focus()); }
    },
    closeModal() { const el = this.$refs.modal; if (el) $(el).modal('hide'); },
    copyLinkDirect() { this.copyToClipboard(this.generatedLinkUrl); },
    copy(t) { this.closeModal(); this.copyToClipboard(t); },
    async copyToClipboard(text) {
      if (!text) return;
      try {
        if (navigator.clipboard && window.isSecureContext) { await navigator.clipboard.writeText(text); this.$message.success('Enlace copiado'); return; }
        throw new Error();
      } catch { this.fallbackCopy(text); }
    },
    fallbackCopy(text) {
      const el = this.$refs.linkInput || this.$refs.linkInputPreregistro || this.$refs.linkInputModal;
      if (el) { el.select(); document.execCommand('copy'); window.getSelection().removeAllRanges(); this.$message.success('Enlace copiado'); return; }
      const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); this.$message.success('Enlace copiado');
    },
    abrirPreviewLanding(tema) {
      this.tipoPreviewActual = tema || this.tipoPreregistro;
      this.previewLandingUrl = `${window.location.origin}/preregistro/${this.username}?lado=${this.selectedLeg}&tema=${this.tipoPreviewActual}`;
      this.mostrarPreviewModal = true; document.body.style.overflow = 'hidden';
    },
    cerrarPreviewLanding() { this.mostrarPreviewModal = false; this.previewLandingUrl = ''; document.body.style.overflow = ''; },

    async loadReferrals() {
      if (!this.username || this.referralsLoading) return;
      this.referralsLoading = true;
      try {
        const r = await apiShareLink.referrals(this.username);
        // Solo preregistros pendientes de pago (origen preregistro, sin pago)
        this.referralsSummary = r.summary || null;
        this.referralsRows = (r.rows || []).filter(
          row => row.origen === 'preregistro' && (row.pago_estado === 'pendiente' || row.pago_estado === 'sin_pago')
        );
      } catch {
        this.$message.error('No se pudieron cargar los preregistros');
      } finally {
        this.referralsLoading = false;
        this.$nextTick(() => {
          if (window.feather) window.feather.replace();
          this.initDataTable();
        });
      }
    },

    initDataTable() {
      if ($.fn.DataTable.isDataTable('#preregistrosTable')) {
        $('#preregistrosTable').DataTable().destroy();
      }
      $('#preregistrosTable').DataTable({
        ...language,
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, 'Todos'],
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      });
    },

    getInitials(name) {
      if (!name) return '?';
      return name
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },

    pagoLabel(estado) {
      const map = { pagado: 'Pagado', en_revision: 'En revisión', sin_pago: 'Sin pago', pendiente: 'Pendiente' };
      return map[estado] || estado || '—';
    },

    formatDate(dateStr) {
      if (!dateStr) return '—';
      return moment(dateStr).format('DD/MM/YYYY hh:mm A');
    },

    scaleIframes() {
      this.$nextTick(() => {
        const wraps = document.querySelectorAll('.landing-thumb-wrap');
        wraps.forEach(wrap => {
          const iframe = wrap.querySelector('.landing-thumb-iframe');
          if (!iframe) return;
          const wrapW = wrap.offsetWidth;
          const scale = wrapW / 1280;
          iframe.style.transform = `scale(${scale})`;
          iframe.style.height = Math.ceil(160 / scale) + 'px';
        });
      });
    },
  },
  updated() {
    this.scaleIframes();
    if (window.feather) window.feather.replace();
  },
  mounted() {
    this.scaleIframes();
    if (window.feather) window.feather.replace();
    window.addEventListener('resize', this.scaleIframes);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.scaleIframes);
    const el = this.$refs.modal;
    if (el) { $(el).off(); $(el).modal('dispose'); }
    document.body.style.overflow = '';
  },
};
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }

/* ── Paneles de control superiores ── */
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

/* Cards que se ajustan al contenido pero se apilan verticalmente — legacy, mantenido por compatibilidad */
.card.d-inline-block {
  display: block !important;
  width: fit-content !important;
  min-width: 0;
}

/* Avatar en tabla */
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
.user-info {
  max-width: 160px;
}
.badge-light-success {
  background-color: rgba(40, 199, 111, 0.12);
  color: #28c76f;
}
.badge-light-primary {
  background-color: rgba(115, 103, 240, 0.12);
  color: #7367f0;
}
.badge-light-secondary {
  background-color: rgba(182, 182, 182, 0.12);
  color: #b6b6b6;
}

.landing-card:hover {
  border-color: #28a745 !important;
  box-shadow: 0 4px 18px rgba(40,167,69,.15) !important;
  transform: translateY(-2px);
}

.landing-card.disabled-card {
  opacity: 0.7;
  cursor: not-allowed;
  pointer-events: none;
}
.landing-card.disabled-card:hover {
  border-color: inherit !important;
  box-shadow: none !important;
  transform: none;
}

/* Thumbnail contenedor — clip exacto */
.landing-thumb-wrap {
  position: relative;
  width: 100%;
  height: 160px;
  overflow: hidden;
  background: #f8f9fa;
}

/* El iframe ocupa 1280px de ancho real y se escala al ancho del contenedor */
.landing-thumb-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 1280px;
  height: 800px;
  border: none;
  pointer-events: none;
  /* scale se aplica via JS en mounted/updated para que sea dinámico */
  transform: scale(0.23);
  transform-origin: top left;
}

/* Check badge */
.landing-check {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

/* Overlay modal preview */
.preview-overlay {
  position: fixed;
  inset: 0;
  background: rgba(34,41,47,.75);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.preview-container {
  width: 100%;
  max-width: 1140px;
  height: 90vh;
  background: #fff;
  border-radius: 0.428rem;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(34,41,47,.4);
}

@media (max-width: 576px) {
  .preview-container { height: 95vh; }
}
</style>
