<template>
  <div class="row text-center">
    <div class="tree-wrapper">
      <vue2-org-tree :render-content="renderContent" class="org-tree" selectedKey="id" :data="users" :label-width="200"
        label-class-name="orglabel" :horizontal="horizontal" :collapsable="collapsable" @on-node-click="onNodeClick" />
    </div>

    <div class="modal fade" id="modal-view-user" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Información del Usuario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <tbody>
                  <tr>
                    <th class="text-muted">Usuario</th>
                    <td class="text-right font-weight-bold">{{ userSelected.username }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Nombre</th>
                    <td class="text-right">{{ userSelected.name }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Apellido</th>
                    <td class="text-right">{{ userSelected.last_name }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Correo electrónico</th>
                    <td class="text-right">{{ userSelected.email }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Celular</th>
                    <td class="text-right">{{ userSelected.phone }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Fecha de nacimiento</th>
                    <td class="text-right">
                      {{ moment(userSelected.date_birth).format('DD/MM/YYYY') }}
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Fecha de registro</th>
                    <td class="text-right">
                      {{ moment(userSelected.created_at).format('DD/MM/YYYY HH:mm') }}
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Membresía</th>
                    <td class="text-right">{{ userSelected.account_type.account }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Estado OPC</th>
                    <td class="text-right">
                      <span v-if="userSelected.active" class="badge badge-success">Activo</span>
                      <span v-else class="badge badge-danger">Inactivo</span>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Estado de membresía</th>
                    <td class="text-right">
                      <span v-if="userSelected.membershipActive" class="badge badge-success">Activo</span>
                      <span v-else class="badge badge-danger">Inactivo</span>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Calificado</th>
                    <td class="text-right">
                      <span v-if="userSelected.qualified" class="badge badge-success">Calificado</span>
                      <span v-else class="badge badge-danger">No calificado</span>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Volumen izquierdo</th>
                    <td class="text-right font-weight-bold text-primary">
                      {{ userSelected.LeftPoints }}
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted">Volumen derecho</th>
                    <td class="text-right font-weight-bold text-primary">
                      {{ userSelected.RightPoints }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import 'vue2-org-tree/dist/style.css';
import moment from 'moment';
import api from '@/api/api';

export default {
  name: 'TreeComponent',
  data() {
    return {
      moment: moment,
      users: {},
      userSelected: {
        account_type: {
          account: {},
        },
      },
      expandAll: false,
      horizontal: false,
      collapsable: false,
    };
  },
  created() {
    this.listUsers();
  },
  methods: {
    listUsers() {
      api.get('/viewTree').then((response) => {
        console.log(response);
        this.users = response;
      });
    },

    listUser(id) {
      api.get(`/users/get-data-user-id/${id}`).then((response) => {
        this.userSelected = response;
      });
    },

    renderContent(h, data) {
      return h(
        'div',
        {
          class: 'binary-tree-node',
        },
        [
          h('img', {
            class: 'node-image',
            attrs: {
              src: data.photo,
              alt: data.name,
            },
          }),
          h('span', { class: 'node-name' }, data.name),
          h(
            'div',
            {
              class: 'position-indicator',
            },
            [
              h(
                'span',
                {
                  class: 'position-arrow',
                },
                data.position === '0' ? '←' : data.position === '1' ? '→' : null
              ),
            ]
          ),
        ]
      );
    },

    onNodeClick(e, data) {
      if (data.id != 0) {
        this.listUser(data.id);
        $('#modal-view-user').modal('show');
      }
    },
  },
};
</script>

<style>
.tree-wrapper {
  width: 100%;
  overflow-x: auto;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.org-tree {
  display: inline-block;
  min-width: min-content;
}

.binary-tree-node {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.binary-tree-node:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.node-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 0.75rem;
}

.node-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #2c3e50;
  margin-right: 0.75rem;
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.position-indicator {
  margin-left: auto;
}

.position-arrow {
  font-size: 1.2rem;
  color: #6c757d;
}

.org-tree-node-label {
  cursor: pointer;
  margin: 0.5rem 0;
}

.org-tree-node-label-inner {
  min-height: 75px;
  max-height: 75px;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.3s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.org-tree-node-label-inner:hover {
  background-color: #f8f9fa;
}

.modal-content {
  border: none;
  border-radius: 12px;
}

.modal-header {
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.badge {
  padding: 0.5em 1em;
  border-radius: 20px;
}

.table td,
.table th {
  vertical-align: middle;
  border-top: 1px solid #dee2e6;
}

.text-muted {
  color: #6c757d !important;
}

@media (max-width: 768px) {
  .tree-wrapper {
    padding: 0.5rem;
  }

  .binary-tree-node {
    padding: 0.25rem;
  }

  .node-image {
    width: 40px;
    height: 40px;
  }

  .node-name {
    font-size: 0.8rem;
  }

  .modal-dialog {
    margin: 0.5rem;
  }

  .table td,
  .table th {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
}
</style>
