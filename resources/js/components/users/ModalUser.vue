<template>
  <div>
    <custom-modal v-bind:id="'viewUser'" class="user-modal">
      <template #title>
        <h4 class="modal-title">Datos de usuario</h4>
      </template>
      <template #body>
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <tbody class="user-details">
              <tr>
                <th class="field-label">Usuario</th>
                <td class="field-value">{{ user.username }}</td>
              </tr>
              <tr>
                <th class="field-label">Nombre</th>
                <td class="field-value">{{ user.name }}</td>
              </tr>
              <tr>
                <th class="field-label">Apellido</th>
                <td class="field-value">{{ user.last_name }}</td>
              </tr>
              <tr>
                <th class="field-label">Correo electrónico</th>
                <td class="field-value">{{ user.email }}</td>
              </tr>
              <tr>
                <th class="field-label">Celular</th>
                <td class="field-value">{{ user.phone }}</td>
              </tr>
              <tr>
                <th class="field-label">Fecha nacimiento</th>
                <td class="field-value">
                  {{ moment(user.date_birth).format('DD/MM/YYYY') }}
                </td>
              </tr>
              <tr>
                <th class="field-label">Fecha de registro</th>
                <td class="field-value">
                  {{ moment(user.created_at).format('DD/MM/YYYY') }}
                </td>
              </tr>
              <tr>
                <th class="field-label">Membresía</th>
                <td class="field-value">{{ user.account_type.account }}</td>
              </tr>
              <tr>
                <th class="field-label">Estado de OPC</th>
                <td class="field-value">
                  <span v-if="user.active" class="badge bg-success">Activo</span>
                  <span v-else class="badge bg-danger">Inactivo</span>
                </td>
              </tr>
              <tr>
                <th class="field-label">Estado de membresía</th>
                <td class="field-value">
                  <span v-if="user.membershipActive" class="badge bg-success">Activo</span>
                  <span v-else class="badge bg-danger">Inactivo</span>
                </td>
              </tr>
              <tr>
                <th class="field-label">Calificado</th>
                <td class="field-value">
                  <span v-if="user.qualified" class="badge bg-success">Calificado</span>
                  <span v-else class="badge bg-danger">No calificado</span>
                </td>
              </tr>
              <tr class="volume-row">
                <td class="volume-cell volume-left">
                  <span class="volume-label">Volumen izq.</span>
                  <span class="volume-value">{{ user.LeftPoints }}</span>
                </td>
                <td class="volume-cell volume-right">
                  <span class="volume-label">Volumen der.</span>
                  <span class="volume-value">{{ user.RightPoints }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import ModalComponent from '@/components/common/ModalComponent.vue';
import moment from 'moment';

export default {
  components: {
    'custom-modal': ModalComponent,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      moment: moment,
    };
  },
  mounted() {
    this.$nextTick(() => {
      document.querySelectorAll('.user-details tr').forEach((row) => {
        const label = row.querySelector('.field-label');
        const value = row.querySelector('.field-value');
        if (label && value && label.offsetWidth + value.offsetWidth > row.offsetWidth) {
          row.setAttribute('data-long', 'true');
        }
      });
    });
  },
};
</script>

<style scoped>
.user-modal {
  max-width: 100%;
}

.modal-title {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 1rem;
}

.table-responsive {
  margin: -0.5rem;
  padding: 0.5rem;
}

.table {
  margin-bottom: 0;
  width: 100%;
}

.user-details tr {
  transition: background-color 0.2s ease;
}

.user-details tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.field-label {
  width: 40%;
  color: #6c757d;
  font-weight: 600;
  padding: 1rem !important;
  vertical-align: middle !important;
}

.field-value {
  width: 60%;
  text-align: right;
  padding: 1rem !important;
  vertical-align: middle !important;
}

.badge {
  font-size: 0.875rem;
  padding: 0.5em 1em;
  border-radius: 0.25rem;
}

.volume-row {
  background-color: #f8f9fa;
  border-top: 2px solid #dee2e6;
  justify-content: space-between;
  width: 100%;
}

.volume-cell {
  padding: 1rem !important;
  flex: 0 0 auto;
}

.volume-left {
  text-align: left;
  padding-left: 1rem !important;
}

.volume-right {
  text-align: right;
  padding-right: 1rem !important;
}

.volume-label {
  color: #6c757d;
  font-weight: 500;
  margin-right: 0.5rem;
}

.volume-value {
  font-weight: 600;
  color: #2c3e50;
}

@media (max-width: 768px) {
  .user-details tr {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    text-align: left;
  }

  .field-label,
  .field-value {
    display: inline-block;
    width: auto;
    text-align: left;
    padding: 0.5rem 1rem !important;
    flex: 1 1 auto;
    word-wrap: break-word;
  }

  .field-value {
    text-align: right;
  }

  .user-details tr[data-long='true'] {
    flex-direction: column;
    align-items: flex-start;
  }

  .user-details tr[data-long='true'] .field-label,
  .user-details tr[data-long='true'] .field-value {
    width: 100%;
    text-align: left;
    padding: 0.5rem 1rem !important;
  }

  .volume-row {
    flex-direction: column;
    gap: 0.5rem;
  }

  .volume-cell {
    width: 100%;
    text-align: left !important;
    padding: 0.5rem 1rem !important;
  }
}
</style>
