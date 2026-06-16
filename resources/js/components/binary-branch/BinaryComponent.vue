<template>
  <div class="row text-center">
    <div class="tree-wrapper">
      <vue2-org-tree :data="treeData" :render-content="renderContent" :collapsable="collapsable"
        :horizontal="horizontal" label-class-name="orglabel" @on-node-click="onNodeClick" />
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
  name: 'BinaryComponent',
  data() {
    return {
      moment: moment,
      treeData: {},
      userSelected: {
        account_type: {
          account: {},
        },
      },
      horizontal: false,
      collapsable: true,
    };
  },
  created() {
    this.fetchAndBuildTree();
  },
  methods: {
    fetchAndBuildTree() {
      console.log('=== INICIO fetchAndBuildTree ===');
      console.log('Entorno:', {
        NODE_ENV: process.env.NODE_ENV,
        MIX_APP_URL: process.env.MIX_APP_URL,
        current_url: window.location.href
      });
    
      // Verificar qué endpoint se está
      //  llamando
      const endpoint = '/binaryTreeLocal';
      console.log('Endpoint que se va a llamar:', endpoint);
      console.log('Objeto API:', api);
    
      api.get(endpoint).then((response) => {
        console.log('=== RESPUESTA RECIBIDA ===');
        console.log('Response completo:', response);
        console.log('Response.data:', response.data);
        console.log('Response.config:', response.config);
        console.log('URL llamada:', response.config?.url);
        console.log('Base URL:', response.config?.baseURL);
      
        // Determinar si response.data existe o usar response directamente
        const apiData = response.data || response;
        console.log('Datos procesados:', apiData);
        console.log('Tipo de datos:', typeof apiData);
        console.log('¿Es array?', Array.isArray(apiData));
        console.log('¿Tiene id?', apiData && apiData.id);

        if (apiData && typeof apiData === 'object') {
          console.log('Claves del objeto:', Object.keys(apiData));
        }
      
        if (apiData && apiData.id) {
          console.log('=== TRANSFORMANDO ÁRBOL ===');
          console.log('Datos antes de transformar:', apiData);

          const rootNode = this.transformApiDataToTree(apiData);
          console.log('Nodo raíz transformado:', rootNode);

          this.$set(rootNode, 'expand', true);
          this.treeData = rootNode;

          console.log('TreeData final asignado:', this.treeData);
        } else {
          console.error('=== DATOS INVÁLIDOS ===');
          console.error('apiData:', apiData);
          console.error('apiData.id:', apiData?.id);

          this.treeData = {};
        }
      }).catch(error => {
        console.error('=== ERROR EN API CALL ===');
        console.error('Error completo:', error);
        console.error('Error message:', error.message);
        console.error('Error response:', error.response);
        console.error('Error config:', error.config);

        if (error.response) {
          console.error('Status:', error.response.status);
          console.error('Data:', error.response.data);
          console.error('Headers:', error.response.headers);
        }

        if (error.config) {
          console.error('URL llamada:', error.config.url);
          console.error('Base URL:', error.config.baseURL);
          console.error('Método:', error.config.method);
        }
      });
    },

    transformApiDataToTree(node, position = null) {
      if (!node || !node.id) {
        return null;
      }

      const transformedNode = {
        id: node.id,
        label: node.username || node.name,
        photo: node.photo || 'https://promolider-storage-user.s3-accelerate.amazonaws.com/images/avatar1.png',
        position: position,
        children: [],
        expand: false
      };

      const leftChild = this.transformApiDataToTree(node.left, '0');
      if (leftChild) {
        transformedNode.children.push(leftChild);
      }

      const rightChild = this.transformApiDataToTree(node.right, '1');
      if (rightChild) {
        transformedNode.children.push(rightChild);
      }

      return transformedNode;
    },

    listUser(id) {
      api.get(`/users/get-data-user-id/${id}`).then((response) => {
        this.userSelected = response;
      });
    },

    renderContent(h, data) {
      return h('div', { class: 'binary-tree-node' }, [
        h('img', {
          class: 'node-image',
          attrs: { src: data.photo, alt: data.label },
        }),
        h('span', { class: 'node-name' }, data.label),
        h('div', { class: 'position-indicator' }, [
          h('span', { class: 'position-arrow' },
            data.position === '0' ? '←' : data.position === '1' ? '→' : null
          ),
        ]),
        h('button', {
          class: 'btn btn-sm btn-outline-primary ml-2',
          on: {
            click: (event) => {
              event.stopPropagation();
              data.expand = !data.expand;
            }
          }
        }, data.expand ? '-' : '+')
      ]);
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
  cursor: pointer;
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

.org-tree-node-label-inner {
  padding: 0 !important;
  border: none !important;
  border-radius: 8px;
}

.org-tree-node-label-inner:hover {
  background: none !important;
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