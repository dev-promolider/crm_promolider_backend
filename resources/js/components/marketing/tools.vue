<template>
    <div>
        <section v-if="!initialLoading">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="data-table-list-tools" class="table-hover-animation table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Fecha de registro</th>
                                    <th>N° Distribuidores</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in this.herramientas" v-bind:key="item.id" v-bind:index="index">
                                    <td>
                                        <p style="width: 15px">
                                            {{ (index = index + 1) }}
                                        </p>
                                    </td>
                                    <td :style="getTipoStyle(item.tipo)">
                                        {{ item.tipo }}
                                    </td>
                                    <td>
                                        <p style="width: 220px">
                                            {{ item.nombre }}
                                        </p>
                                    </td>
                                    <td>{{ item.category_name }}</td>
                                    <td>{{ moment(item.fecha).format('DD/MM/YYYY hh:mm A') }}</td>
                                    <td>{{ item.distribuidores }}</td>
                                    <td>
                                        <span :class="getEstadoClass(item.estado)">
                                            {{ getEstadoLabel(item.estado) }}
                                        </span>
                                    </td>
                                    <td class="align-content-center">
                                        <el-select 
                                            id="customize_select" 
                                            size="mini" 
                                            clearable 
                                            v-model="optionSelected"
                                            @change="getOptionSelected(item)"
                                        >
                                            <el-option 
                                                v-for="option in getAvailableOptions(item)" 
                                                :key="option.value"
                                                :label="option.label" 
                                                :value="option.value">
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

        <custom-modal id="edit-ebook-modal" color="primary" size="large" v-if="toolSelected && toolSelected.tipo === 'E-book'">
            <template #title>Editar E-book <u>{{ toolSelected.nombre }}</u></template>
            <template #body>
                <EbookEditModal 
                    ref="ebookEditModal"
                    :categories="categories"
                    @ebook-updated="handleEbookUpdated"
                />
            </template>
        </custom-modal>
        
        <custom-modal id="edit-mini-modal" color="primary" size="large" v-if="toolSelected && toolSelected.tipo === 'Mini Curso'">
            <template #title>Editar Mini Curso <u>{{ toolSelected.nombre }}</u></template>
            <template #body>
                <MiniCourseEditModal 
                    ref="miniEditModal"
                    :categories="categories"
                    @mini-updated="handleMiniUpdated"
                />
            </template>
        </custom-modal>
        
        <custom-modal id="edit-masterclass-modal" color="primary" size="large" v-if="toolSelected && toolSelected.tipo === 'Masterclass'">
            <template #title>Editar Masterclass <u>{{ toolSelected.nombre }}</u></template>
            <template #body>
                <MasterclassEditModal 
                    ref="masterclassEditModal"
                    :categories="categories"
                    @masterclass-updated="handleMasterclassUpdated"
                />
            </template>
        </custom-modal>

        <custom-modal id="change-status-modal" color="primary" v-if="toolSelected">
            <template #title>Cambiar estado de {{ toolSelected.tipo }}: <u>{{ toolSelected.nombre }}</u></template>
            <template #body>
                <div class="form-group">
                    <label for="status-select" class="font-weight-bold">Seleccione el nuevo estado:</label>
                    <select 
                        id="status-select" 
                        v-model="newStatus" 
                        class="form-control form-control-lg"
                    >
                        <option value="" disabled>-- Seleccione un estado --</option>
                        <option value="0">No publicado</option>
                        <option value="1">Publicado</option>
                        <option value="2">Privado</option>
                    </select>
                    
                    <div class="mt-3" v-if="newStatus !== ''">
                        <div class="alert" :class="getAlertClass(parseInt(newStatus))">
                            <i :class="getStatusIcon(parseInt(newStatus))"></i>
                            <strong>{{ getEstadoLabel(parseInt(newStatus)) }}</strong>
                            <p class="mb-0 mt-2">{{ getStatusDescription(parseInt(newStatus)) }}</p>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button 
                    type="button" 
                    class="btn btn-primary" 
                    @click="confirmChangeStatus"
                    :disabled="newStatus === '' || parseInt(newStatus) === toolSelected.estado"
                >
                    Cambiar Estado
                </button>
            </template>
        </custom-modal>

        <custom-modal id="delete-tool-modal" color="danger" v-if="toolSelected">
            <template #title>Eliminar {{ toolSelected.tipo }} <u>{{ toolSelected.nombre }}</u></template>
            <template #body>¿Seguro que desea eliminar esta herramienta?</template>
            <template #footer>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" @click="confirmDeleteTool(toolSelected.id)">
                    Eliminar
                </button>
            </template>
        </custom-modal>

        <custom-modal id="invitation-modal" color="primary" size="large" v-if="toolSelected && showInvitationModal">
            <template #title>
                <i class="feather icon-share-2"></i> Invitar a {{ toolSelected.tipo }}: <u>{{ toolSelected.nombre }}</u>
            </template>
            <template #body>
                <div v-if="invitationData.existInvitation && !newInvitationLink" class="alert alert-info">
                    <h6><i class="feather icon-info"></i> Link de Invitación Activo</h6>
                    <p>Ya tienes un link de invitación activo para esta herramienta.</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" :value="invitationData.invitationLink" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" @click="copyToClipboard(invitationData.invitationLink)">
                                <i class="feather icon-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div v-if="!invitationData.existInvitation && !newInvitationLink" class="alert alert-warning">
                    <h6><i class="feather icon-alert-triangle"></i> Crear Link de Invitación</h6>
                    <p>No tienes un link de invitación activo. Haz clic en el botón para crear uno.</p>
                    <button @click="createInvitationLink" :disabled="loadingInvitation" class="btn btn-primary">
                        <i class="feather icon-plus"></i> 
                        {{ loadingInvitation ? 'Creando...' : 'Crear Link de Invitación' }}
                    </button>
                </div>

                <div v-if="newInvitationLink" class="alert alert-success mt-3">
                    <h6><i class="feather icon-check-circle"></i> Link Creado Exitosamente</h6>
                    <p>Tu link de invitación ha sido creado y es válido por 7 días.</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" :value="newInvitationLink" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-success" type="button" @click="copyToClipboard(newInvitationLink)">
                                <i class="feather icon-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4" v-if="invitationData.existInvitation || newInvitationLink">
                    <h6 class="font-weight-bold">Compartir via:</h6>
                    <div class="share-buttons d-flex flex-wrap gap-2 mt-3">
                        <a :href="getWhatsappShareUrl()" target="_blank" class="btn btn-success btn-sm">
                            <i class="feather icon-message-circle"></i> WhatsApp
                        </a>
                        <a :href="getFacebookShareUrl()" target="_blank" class="btn btn-primary btn-sm">
                            <i class="feather icon-facebook"></i> Facebook
                        </a>
                        <button @click="copyToClipboard(getCurrentInvitationLink())" class="btn btn-info btn-sm">
                            <i class="feather icon-copy"></i> Copiar Link
                        </button>
                    </div>
                </div>
            </template>
            <template #footer>
                <button type="button" class="btn btn-secondary" @click="closeInvitationModal">Cerrar</button>
            </template>
        </custom-modal>

        <custom-modal id="create-dinamica-modal" color="success" size="large">
            <template #title>
                Crear Nueva Dinámica
            </template>
            <template #body>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100" style="cursor: pointer; border: 2px solid #28a745;">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="feather icon-disc" style="font-size: 48px; color: #e83e8c;"></i>
                                </div>
                                <h5 class="card-title" style="color: #6c757d;">🎯 Ruleta Interactiva</h5>
                                <p class="card-text text-muted">
                                    Crea una ruleta con premios y segmentos personalizables. Los estudiantes 
                                    girarán para ganar premios.
                                </p>
                                <ul class="list-unstyled text-left mt-3">
                                    <li><i class="feather icon-check text-success"></i> Hasta 6 segmentos</li>
                                    <li><i class="feather icon-check text-success"></i> Personalización de colores</li>
                                    <li><i class="feather icon-check text-success"></i> Sistema de premios</li>
                                    <li><i class="feather icon-check text-success"></i> Control de participantes</li>
                                </ul>
                                <button class="btn btn-success btn-block mt-3">
                                    Crear Ruleta
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card h-100" style="border: 2px solid #e0e0e0;">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="feather icon-clock" style="font-size: 48px; color: #ccc;"></i>
                                </div>
                                <h5 class="card-title text-muted">Próximamente</h5>
                                <p class="card-text text-muted">
                                    Más dinámicas en desarrollo...
                                </p>
                                <div class="mt-5 pt-5">
                                    <button class="btn btn-secondary btn-block" disabled>
                                        Próximamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </template>
        </custom-modal>

        <div v-if="showToast" class="toast-notification">
            <div class="toast-content">
                <i class="feather icon-check-circle text-success"></i>
                <span>{{ toastMessage }}</span>
                <button @click="showToast = false" class="toast-close">
                    <i class="feather icon-x"></i>
                </button>
            </div>
        </div>

    </div>
</template>

<script>
import moment from 'moment';
import EbookEditModal from './EbookEditModal.vue';
import MiniCourseEditModal from './MiniCourseEditModal.vue';
import MasterclassEditModal from './MasterclassEditModal.vue';
import ModalComponent from '@/components/common/ModalComponent.vue';
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
import language from '../../api/traduction_es';

export default {
    name: "Tools",
    components: {
        EbookEditModal,
        MiniCourseEditModal,
        MasterclassEditModal,
        'custom-modal': ModalComponent,
        'custom-spinner': CustomSpinner,
    },
    data() {
        return {
            showInvitationModal: false,
            loadingInvitation: false,
            invitationData: {
                existInvitation: false,
                invitationLink: ''
            },
            newInvitationLink: '',
            showToast: false,
            toastMessage: '',
            initialLoading: false,
            moment: moment,
            herramientas: [],
            categories: [],
            options: [
                {
                    value: '1', 
                    label: 'Editar',
                },
                {
                    value: '2',
                    label: 'Eliminar',
                },
                {
                    value: '3',
                    label: 'Módulos y Clases',
                },
                {
                    value: '4',
                    label: 'Cambiar Estado',
                },
                {
                    value: '5',
                    label: 'Invitar',
                }
            ],
            optionSelected: '',
            toolSelected: {},
            newStatus: '' // Para el modal de cambio de estado
        };
    },
    mounted() {
        this.loadTools();
        this.loadCategories();
    },
    methods: {
        loadDataTable() {
            this.$nextTick(function () {
                $('#data-table-list-tools').DataTable(language);
            });
        },

        async loadTools() {
            this.initialLoading = true;
            try {
                console.log('Iniciando carga de herramientas...');

                const response = await axios.get('/marketing/tools/list');

                console.log('Respuesta completa:', response);
                console.log('Datos recibidos:', response.data);
                console.log('Array de herramientas:', response.data.data);
                console.log('Cantidad de herramientas:', response.data.data.length);

                this.herramientas = response.data.data;
                this.initialLoading = false;
                this.loadDataTable();

                console.log('Herramientas asignadas al componente:', this.herramientas);

            } catch (error) {
                console.error('Error al cargar herramientas:', error);
                console.error('Respuesta del error:', error.response);

                this.$swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar las herramientas',
                    icon: 'error'
                });
                this.initialLoading = false;
            }
        },

        async loadCategories() {
            try {
                const response = await axios.get('/marketing/categories');
                this.categories = response.data.data || response.data;
                console.log('Categorías cargadas:', this.categories);
            } catch (error) {
                console.error('Error al cargar categorías:', error);
                this.categories = [];

                this.$swal.fire({
                    title: 'Advertencia',
                    text: 'No se pudieron cargar las categorías. Algunas funciones podrían estar limitadas.',
                    icon: 'warning',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },
        
        async openInvitationModal(tool) {
            this.toolSelected = tool;
            this.newInvitationLink = '';
            this.showInvitationModal = true;
    
            // Primero verificar si ya compró/registró la herramienta
            const hasPurchased = await this.checkPurchaseOrRegistration(tool);
    
            if (hasPurchased) {
                // Si ya la tiene, verificar si existe un link de invitación
                await this.checkExistingInvitation(tool);
                $('#invitation-modal').modal('show');
            } else {
                this.showInvitationModal = false;
                this.$swal.fire({
                    title: 'No disponible',
                    text: 'Debes registrar/comprar esta herramienta antes de poder invitar a otros.',
                    icon: 'warning'
                });
            }
        },

        async checkPurchaseOrRegistration(tool) {
            try {
                let endpoint = '';
    
                switch (tool.tipo) {
                    case 'E-book':
                        endpoint = `/marketing/ebook/check-purchase/${tool.id}`;
                        break;
                    case 'Mini Curso':
                        endpoint = `/marketing/mini-course/invitation/check-purchase/${tool.id}`;
                        break;
                    case 'Masterclass':
                        endpoint = `/masterclass/check-registration/${tool.id}`;
                        break;
                }
    
                const response = await axios.get(endpoint);
    
                // Para E-book y Mini Curso viene como isPurchased
                // Para Masterclass viene como isRegistered
                return response.data.isPurchased || response.data.isRegistered || false;
    
            } catch (error) {
                console.error('Error al verificar compra/registro:', error);
                return false;
            }
        },

        async checkExistingInvitation(tool) {
            try {
                let endpoint = '';
    
                switch (tool.tipo) {
                    case 'E-book':
                        endpoint = `/marketing/ebook/check-invitation/${tool.id}`;
                        break;
                    case 'Mini Curso':
                        endpoint = `/marketing/mini-course/invitation/check-invitation/${tool.id}`;
                        break;
                    case 'Masterclass':
                        endpoint = `/masterclass/check-invitation/${tool.id}`;
                        break;
                }
    
                const response = await axios.get(endpoint);
    
                this.invitationData = {
                    existInvitation: response.data.existInvitation,
                    invitationLink: response.data.invitationLink || ''
                };
    
            } catch (error) {
                console.error('Error al verificar invitación:', error);
                this.invitationData = {
                    existInvitation: false,
                    invitationLink: ''
                };
            }
        },

        async createInvitationLink() {
            if (!this.toolSelected || !this.toolSelected.id) return;
    
            this.loadingInvitation = true;
            try {
                let endpoint = '';
    
                switch (this.toolSelected.tipo) {
                    case 'E-book':
                        endpoint = `/marketing/ebook/invitation-link/${this.toolSelected.id}`;
                        break;
                    case 'Mini Curso':
                        endpoint = `/marketing/mini-course/invitation/invitation-link/${this.toolSelected.id}`;
                        break;
                    case 'Masterclass':
                        endpoint = `/masterclass/create-invitation/${this.toolSelected.id}`;
                        break;
                }
    
                const response = await axios.post(endpoint);
    
                this.newInvitationLink = response.data.link;
                this.invitationData.existInvitation = true;
                this.invitationData.invitationLink = response.data.link;
    
                this.showToastMessage('Link de invitación creado exitosamente');
    
            } catch (error) {
                console.error('Error al crear link de invitación:', error);
    
                this.$swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al crear el link de invitación.',
                    icon: 'error'
                });
            } finally {
                this.loadingInvitation = false;
            }
        },

        closeInvitationModal() {
            this.showInvitationModal = false;
            this.newInvitationLink = '';
            $('#invitation-modal').modal('hide');
        },

        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.showToastMessage('Link copiado al portapapeles');
            } catch (error) {
                console.error('Error al copiar:', error);
                this.$message({
                    message: 'No se pudo copiar el link',
                    type: 'error',
                    duration: 3000,
                });
            }
        },

        showToastMessage(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        },

        getCurrentInvitationLink() {
            return this.newInvitationLink || this.invitationData.invitationLink || '';
        },

        getWhatsappShareUrl() {
            const link = this.getCurrentInvitationLink();
            const toolName = this.toolSelected ? this.toolSelected.nombre : '';
            const toolType = this.toolSelected ? this.toolSelected.tipo : '';
    
            const text = `¡Hola! Te invito a conocer este increíble ${toolType}: "${toolName}". Regístrate usando mi link de invitación: ${link}`;
    
            return `https://wa.me/?text=${encodeURIComponent(text)}`;
        },

        getFacebookShareUrl() {
            const link = this.getCurrentInvitationLink();
            return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
        },

        getEstadoLabel(estado) {
            const estados = {
                0: 'No publicado',
                1: 'Publicado',
                2: 'Privado'
            };
            return estados[estado] || 'Desconocido';
        },
        
        getEstadoClass(estado) {
            const classes = {
                0: 'badge badge-danger',
                1: 'badge badge-success',
                2: 'badge badge-warning'
            };
            return classes[estado] || 'badge badge-secondary';
        },

        getStatusIcon(estado) {
            const icons = {
                0: 'feather icon-x-circle',
                1: 'feather icon-check-circle',
                2: 'feather icon-lock'
            };
            return icons[estado] || 'feather icon-help-circle';
        },

        getStatusDescription(estado) {
            const descriptions = {
                0: 'La herramienta no estará visible para nadie. Estado de borrador.',
                1: 'La herramienta será visible públicamente en el marketplace.',
                2: 'Solo los distribuidores autorizados podrán acceder a esta herramienta.'
            };
            return descriptions[estado] || '';
        },

        getAlertClass(estado) {
            const classes = {
                0: 'alert-secondary',
                1: 'alert-success',
                2: 'alert-warning'
            };
            return classes[estado] || 'alert-info';
        },

        getAvailableOptions(tool) {
            return this.options.filter((option) => {
                // Módulos y Clases solo para Mini Curso
                if (option.value === '3') {
                    return tool.tipo === 'Mini Curso';
                }
                
                // Las demás opciones para todos
                return true;
            });
        },

        getOptionSelected(tool) {
            let option = this.optionSelected;
        
            switch (option) {
                case '1': // Editar
                    this.editTool(tool);
                    break;
                case '2': // Eliminar
                    this.toolSelected = tool;
                    $('#delete-tool-modal').modal('show');
                    break;
                case '3': // Ver módulos (solo Mini Curso)
                    this.viewMiniModule(tool.id);
                    break;
                case '4': // Cambiar Estado
                    this.openChangeStatusModal(tool);
                    break;
                case '5': // Invitar
                    this.openInvitationModal(tool);
                    break;
                default:
                    console.log('Opción no válida');
                    break;
            }
        
            // Limpiar selección
            this.optionSelected = '';
        },

        openChangeStatusModal(tool) {
            this.toolSelected = tool;
            this.newStatus = tool.estado.toString(); // Establecer el estado actual
            $('#change-status-modal').modal('show');
        },

        async confirmChangeStatus() {
            if (this.newStatus === '' || parseInt(this.newStatus) === this.toolSelected.estado) {
                this.$message({
                    message: 'Debe seleccionar un estado diferente al actual',
                    type: 'warning',
                    duration: 3000,
                });
                return;
            }

            try {
                const statusValue = parseInt(this.newStatus);
                const statusText = this.getEstadoLabel(statusValue);
                
                let endpoint = '';
                
                // Determinar el endpoint según el tipo de herramienta
                switch (this.toolSelected.tipo) {
                    case 'Masterclass':
                        endpoint = `/masterclass/${this.toolSelected.id}/status`;
                        break;
                    case 'Mini Curso':
                        endpoint = `/marketing/mini-course/${this.toolSelected.id}/status`;
                        break;
                    case 'E-book':
                        endpoint = `/marketing/ebook/${this.toolSelected.id}/status`;
                        break;
                }

                const response = await axios.patch(endpoint, {
                    status: statusValue
                });

                this.$message({
                    message: `Estado cambiado a "${statusText}" correctamente`,
                    type: 'success',
                    duration: 3000,
                });

                // Cerrar modal
                $('#change-status-modal').modal('hide');

                // Recargar la lista
                await this.loadTools();
                $('#data-table-list-tools').DataTable().destroy();
                this.$nextTick(() => {
                    this.loadDataTable();
                });

                // Limpiar estado
                this.newStatus = '';
                
            } catch (error) {
                console.error('Error al cambiar el estado:', error);
                
                let errorMessage = `Error al cambiar el estado de ${this.toolSelected.tipo}`;
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }

                this.$swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error'
                });
            }
        },

        addMiniModule(miniCourseId) {
            const url = `/marketing/mini-course/${miniCourseId}/add-module`;
            window.location.href = url;
        },    

        viewMiniModule(miniCourseId) {
            const url = `/marketing/mini-course/${miniCourseId}/modules`;
            window.location.href = url;
        },
    
        getTipoStyle(tipo) {
          switch (tipo) {
            case 'Masterclass':
              return { color: '#20e205', fontWeight: '600' };
            case 'Mini Curso':
              return { color: '#ffc107', fontWeight: '600' };
            case 'E-book':
              return { color: '#00d0e4', fontWeight: '600' };
            default:
              return { color: '#6c757d', fontWeight: '600' };
          }
        },

        viewMiniModules(miniCourseId) {
            const url = `/marketing/mini-course/${miniCourseId}/module`;
            window.open(url, '_blank');
        },

        async editTool(tool) {
            this.toolSelected = tool;
            
            if (tool.tipo === 'E-book') {
                await this.openEbookEditModal(tool);
                $('#edit-ebook-modal').modal('show');
            } else if (tool.tipo === 'Mini Curso') {
                await this.openMiniCourseEditModal(tool);
                $('#edit-mini-modal').modal('show');
            } else if (tool.tipo === 'Masterclass') {
                await this.openMasterclassEditModal(tool);
                $('#edit-masterclass-modal').modal('show');
            }
        },

        async openEbookEditModal(tool) {
            try {
                console.log('Intentando abrir modal para e-book:', tool);

                const response = await axios.get(`/marketing/ebook/${tool.id}`);
                console.log('Respuesta del endpoint:', response.data);

                const ebook = response.data.data;

                if (ebook) {
                    this.$refs.ebookEditModal.openModal(ebook);
                } else {
                    throw new Error('E-book no encontrado en la respuesta');
                }

            } catch (error) {
                console.error('Error al cargar datos del e-book:', error);
                console.error('Tool object:', tool);
                console.error('Error response:', error.response);

                this.$swal.fire({
                    title: 'Error',
                    text: 'No se pudo cargar la información del e-book. Por favor, intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        },

        async openMiniCourseEditModal(tool) {
            try {
                const response = await axios.get(`/marketing/mini-course/${tool.id}`);
                const mini = response.data.data;
            
                if (mini) {
                    this.$refs.miniEditModal.openModal(mini);
                } else {
                    throw new Error('Mini curso no encontrado en la respuesta');
                }
            } catch (error) {
                console.error('Error al cargar datos del mini curso:', error);
                this.$swal.fire({
                    title: 'Error',
                    text: 'No se pudo cargar la información del mini curso. Por favor, intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        },

        async openMasterclassEditModal(tool) {
            try {
                console.log('Intentando abrir modal para masterclass:', tool);

                const response = await axios.get(`/masterclass/${tool.id}`);
                console.log('Respuesta del endpoint masterclass:', response.data);

                const masterclass = response.data.data;

                if (masterclass) {
                    this.$refs.masterclassEditModal.openModal(masterclass);
                } else {
                    throw new Error('Masterclass no encontrada en la respuesta');
                }

            } catch (error) {
                console.error('Error al cargar datos de la masterclass:', error);
                console.error('Tool object:', tool);
                console.error('Error response:', error.response);

                this.$swal.fire({
                    title: 'Error',
                    text: 'No se pudo cargar la información de la masterclass. Por favor, intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        },

        handleEbookUpdated(updatedEbook) {
            this.loadTools();
            $('#edit-ebook-modal').modal('hide');
            this.$message({
                message: 'E-book actualizado correctamente',
                type: 'success',
                duration: 3000,
            });
        },

        handleMiniUpdated() {
            this.loadTools();
            $('#edit-mini-modal').modal('hide');
            this.$message({
                message: 'Mini curso actualizado correctamente',
                type: 'success',
                duration: 3000,
            });
        },

        handleMasterclassUpdated(updatedMasterclass) {
            this.loadTools();
            $('#edit-masterclass-modal').modal('hide');
            this.$message({
                message: 'Masterclass actualizada correctamente',
                type: 'success',
                duration: 3000,
            });
        },

        async confirmDeleteTool(toolId) {
            try {
                let deleteUrl = '';
                switch (this.toolSelected.tipo) {
                    case 'Masterclass':
                        deleteUrl = `/masterclass/delete/${toolId}`;
                        break;
                    case 'Mini Curso':
                        deleteUrl = `/marketing/mini-course/${toolId}`;
                        break;
                    case 'E-book':
                        deleteUrl = `/marketing/ebook/${toolId}`;
                        break;
                    case 'Dinámica':
                        deleteUrl = `/marketing/dinamica/${toolId}`;
                        break;
                }

                if (deleteUrl) {
                    const { data } = await axios.delete(deleteUrl);

                    this.$message({
                        message: `${this.toolSelected.tipo} eliminado correctamente`,
                        type: 'success',
                        duration: 3000,
                    });

                    await this.loadTools();
                    $('#data-table-list-tools').DataTable().destroy();
                    this.$nextTick(() => {
                        this.loadDataTable();
                    });
                }

                $('#delete-tool-modal').modal('hide');
            } catch (error) {
                console.error('Error al eliminar:', error);
                this.$message({
                    message: 'Error al procesar la solicitud. Por favor, inténtelo de nuevo.',
                    type: 'error',
                    duration: 5000,
                });
            }
        }
    }
}
</script>

<style scoped>
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

.badge-success {
  background-color: #28a745 !important;
  color: white;
}

.badge-danger {
  background-color: #dc3545 !important;
  color: white;
}

.badge-secondary {
  background-color: #6c757d !important;
  color: white;
}

.badge-warning {
  background-color: #ffc107 !important;
  color: #333;
}

/* Estilos para el modal de cambio de estado */
#status-select {
    font-size: 1rem;
    padding: 0.75rem;
    border: 2px solid #ddd;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

#status-select:focus {
    border-color: #7367f0;
    box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.25);
}

.alert i {
    font-size: 1.5rem;
    vertical-align: middle;
    margin-right: 0.5rem;
}

.alert-secondary {
    background-color: #f8f9fa;
    border-color: #6c757d;
    color: #383d41;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}
</style>