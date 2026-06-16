<template>
    <div class="dinamica-specifications">
        <form @submit.prevent="submitForm">
            <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="feather icon-info"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="dinamica-nombre">Nombre de la dinámica *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="dinamica-nombre"
                                    v-model="form.nombre"
                                    placeholder="Ej: Ruleta de Navidad, Sorteo Semanal..."
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="dinamica-descripcion">Descripción (opcional)</label>
                                <textarea 
                                    class="form-control" 
                                    id="dinamica-descripcion"
                                    v-model="form.descripcion"
                                    rows="4"
                                    placeholder="Describe en detalle en qué consiste la dinámica, reglas, etc..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: CONFIGURACIÓN DE INSCRIPCIÓN -->
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="feather icon-clipboard"></i> Configuración de Inscripción
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="cierre-inscripcion">Modo de cierre de inscripción *</label>
                                <input type="hidden" v-model="form.modoInscripcion">
                                <select 
                                    class="form-control form-control--locked" 
                                    id="cierre-inscripcion"
                                    :value="form.modoInscripcion"
                                    disabled
                                >
                                    <option value="tiempo">Por tiempo</option>
                                </select>
                                <small class="form-text text-muted">
                                    Las inscripciones se cierran automáticamente al cumplirse el tiempo configurado.
                                </small>
                            </div>
                        </div>

                        <!-- Tiempo máximo de inscripción -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tiempo-inscripcion">
                                    Tiempo máximo de inscripción (minutos) *
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="tiempo-inscripcion"
                                    v-model.number="form.tiempoInscripcion"
                                    min="1"
                                    placeholder="Ej: 60, 120, 1440..."
                                    required
                                >
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: CONFIGURACIÓN DE PREMIOS -->
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="feather icon-gift"></i> Configuración de Premios
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" v-model="form.tipoPremio">
                    <input type="hidden" v-model.number="form.maxGanadores">
                    <div class="row premio-locked">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de premio en la ruleta *</label>
                                <input
                                    type="text"
                                    class="form-control form-control--locked"
                                    value="Premio único"
                                    disabled
                                >
                                <small class="form-text text-muted">
                                    Configuración fija: solo se entrega un premio por ruleta.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad máxima de ganadores *</label>
                                <input
                                    type="number"
                                    class="form-control form-control--locked"
                                    :value="form.maxGanadores"
                                    disabled
                                >
                                <small class="form-text text-muted">
                                    Valor predefinido; no requiere edición.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: GESTIÓN DE PREMIOS -->
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="feather icon-award"></i> Gestión de Premios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="premio-nombre">Nombre del premio *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="premio-nombre"
                                    v-model="nuevosPremios.nombre"
                                    placeholder="Ej: Cupón de descuento..."
                                >
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="premio-tipo">Tipo *</label>
                                <select 
                                    class="form-control" 
                                    id="premio-tipo"
                                    v-model="nuevosPremios.tipo"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="pdf">PDF</option>
                                    <option value="imagen">Imagen</option>
                                    <option value="enlace">Enlace</option>
                                    <option value="texto">Texto</option>
                                    <option value="codigo">Código</option>
                                    <option value="cupon">Cupón / código único</option>
                                    <option value="saldo">Saldo / monedas</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="premio-probabilidad">Probabilidad de ganar *</label>
                                <select 
                                    class="form-control" 
                                    id="premio-probabilidad"
                                    v-model="nuevosPremios.probabilidad"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="alta">🟢 Alta (Mayor chance de ganar)</option>
                                    <option value="media">🟡 Media (Chance moderada)</option>
                                    <option value="baja">🔴 Baja (Menor chance, premio especial)</option>
                                </select>
                                <small class="form-text text-muted">
                                    Alta: 50% | Media: 30% | Baja: 20% de probabilidad
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button 
                                type="button" 
                                :class="['btn', indiceEditando !== null ? 'btn-success' : 'btn-primary']" 
                                @click="agregarPremio"
                                :disabled="premioLimiteAlcanzado || !nuevosPremios.nombre || !nuevosPremios.tipo || !nuevosPremios.probabilidad"
                            >
                                <i :class="['feather', indiceEditando !== null ? 'icon-check' : 'icon-plus']"></i> 
                                {{ indiceEditando !== null ? 'Guardar cambios' : 'Agregar premio' }}
                            </button>
                            <button 
                                v-if="indiceEditando !== null"
                                type="button" 
                                class="btn btn-secondary ms-2" 
                                @click="resetFormularioPremio"
                            >
                                <i class="feather icon-x"></i> Cancelar edición
                            </button>
                            <p class="text-muted mt-2 mb-0" v-if="form.premios.length >= maxPremiosPermitidos && indiceEditando === null">
                                Solo puedes registrar un premio por ruleta. Edita o elimina el existente para reemplazarlo.
                            </p>
                        </div>
                    </div>

                    <!-- Tabla de Premios Agregados -->
                    <div class="row mt-4" v-if="form.premios.length > 0">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nombre del premio</th>
                                            <th>Tipo</th>
                                            <th class="text-center">Probabilidad</th>
                                            
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(premio, index) in form.premios" :key="index">
                                            <td>
                                                <strong>{{ premio.nombre }}</strong>
                                                <span v-if="premio.esVacio || premio.tipo === 'vacio'" class="badge badge-warning ml-2">Segmento vacío</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ premio.tipo }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span 
                                                    class="badge" 
                                                    :class="{
                                                        'badge-success': premio.probabilidad === 'alta',
                                                        'badge-warning': premio.probabilidad === 'media',
                                                        'badge-danger': premio.probabilidad === 'baja'
                                                    }"
                                                >
                                                    {{ probabilidadTexto(premio.probabilidad) }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button 
                                                        type="button"
                                                        class="btn btn-sm"
                                                        data-toggle="dropdown"
                                                        aria-haspopup="true"
                                                        aria-expanded="false"
                                                        style="font-weight: bold; font-size: 18px; line-height: 1; border: none; background: none; color: #6c757d;"
                                                    >
                                                        ⋮
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a 
                                                            class="dropdown-item" 
                                                            href="#" 
                                                            @click.prevent="editarPremio(index)"
                                                        >
                                                            Editar
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a 
                                                            class="dropdown-item text-danger" 
                                                            href="#" 
                                                            @click.prevent="eliminarPremio(index)"
                                                        >
                                                            Eliminar
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total de premios:</strong> {{ form.premios.length }}
                                </div>
                                <div>
                                    <strong>Premios agregados:</strong> {{ form.premios.length }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay premios -->
                    <div class="row mt-4" v-if="form.premios.length === 0">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <i class="feather icon-alert-circle"></i>
                                Aún no has agregado premios. Agrega al menos uno para continuar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 5: ACCIONES -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a :href="toolsUrl" class="btn btn-outline-secondary">
                                <i class="feather icon-x"></i> Cancelar
                            </a>
                            <button 
                                type="submit" 
                                class="btn btn-success" 
                                :disabled="loading || form.premios.length === 0"
                            >
                                <i class="feather icon-save"></i> 
                                {{ loading ? 'Guardando...' : 'Guardar dinámica' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    name: 'DinamicaSpecificationsForm',
    props: {
        toolsUrl: {
            type: String,
            default: '/marketing/tools'
        },
        storeUrl: {
            type: String,
            default: '/marketing/dinamica/specifications'
        },
        dinamicaData: {
            type: Object,
            default: null
        },
        premiosData: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            loading: false,
            maxPremiosPermitidos: 1,
            form: {
                id: null,
                nombre: '',
                descripcion: '',
                modoInscripcion: 'tiempo',
                tiempoInscripcion: null,
                maxParticipantes: null,
                tipoPremio: 'unico',
                maxGanadores: 1,
                premios: []
            },
            nuevosPremios: {
                nombre: '',
                tipo: '',
                probabilidad: '',
                limiteUsuario: 0,
                vigenciaInicio: null,
                vigenciaFin: null,
                claimUrl: ''
            },
            indiceEditando: null // null = agregar nuevo, número = editando premio en ese índice
        };
    },
    computed: {
        premioLimiteAlcanzado() {
            return this.form.premios.length >= this.maxPremiosPermitidos && this.indiceEditando === null;
        }
    },
    mounted() {
        if (this.dinamicaData) {
            this.form.id = this.dinamicaData.id || null;
            this.form.nombre = this.dinamicaData.nombre || '';
            this.form.descripcion = this.dinamicaData.descripcion || '';
            this.form.modoInscripcion = 'tiempo';
            this.form.tiempoInscripcion = this.dinamicaData.tiempo_inscripcion;
            this.form.maxParticipantes = this.dinamicaData.max_participantes;
            this.form.tipoPremio = 'unico';
            this.form.maxGanadores = 1;
        }

        if (!this.form.tipoPremio) {
            this.form.tipoPremio = 'unico';
        }
        if (!this.form.maxGanadores) {
            this.form.maxGanadores = 1;
        }
        
        if (this.premiosData && this.premiosData.length > 0) {
            const premiosNormalizados = this.premiosData.map((p) => {
                // Mapeo consistente: 50 = alta, 30 = media, 20 = baja
                let probabilidad = 'baja';
                if (p.peso >= 50) probabilidad = 'alta';
                else if (p.peso >= 30) probabilidad = 'media';
                
                return {
                    nombre: p.nombre,
                    tipo: p.tipo,
                    probabilidad: probabilidad,
                    peso: p.peso,
                    limiteUsuario: p.limite_usuario || 0,
                    vigenciaInicio: p.vigencia_inicio,
                    vigenciaFin: p.vigencia_fin,
                    claimUrl: p.claim_url,
                    esVacio: p.tipo === 'vacio' || p.es_vacio
                };
            });

            this.form.premios = premiosNormalizados.slice(0, this.maxPremiosPermitidos);
        }
    },
    methods: {
        agregarPremio() {
            if (this.premioLimiteAlcanzado) {
                this.notificarLimitePremios();
                return;
            }

            if (!this.nuevosPremios.nombre || !this.nuevosPremios.tipo || !this.nuevosPremios.probabilidad) {
                this.$swal.fire({
                        title: 'Campos incompletos',
                        text: 'Por favor completa nombre, tipo y probabilidad',
                        icon: 'warning',
                        confirmButtonColor: '#f7971e',
                        background: '#ffffff',
                        color: '#2c3e50'
                    });
                return;
            }

            if (this.nuevosPremios.vigenciaInicio && this.nuevosPremios.vigenciaFin) {
                const inicio = new Date(this.nuevosPremios.vigenciaInicio);
                const fin = new Date(this.nuevosPremios.vigenciaFin);
                if (fin < inicio) {
                    this.$swal.fire({
                        title: 'Vigencia inválida',
                        text: 'La fecha fin no puede ser menor a la fecha inicio',
                        icon: 'error',
                        confirmButtonColor: '#eb3349',
                        background: '#ffffff',
                        color: '#2c3e50'
                    });
                    return;
                }
            }

            // Convertir probabilidad a peso numérico
            const pesosPorProbabilidad = {
                'alta': 50,
                'media': 30,
                'baja': 20
            };

            const premioData = {
                nombre: this.nuevosPremios.nombre,
                tipo: this.nuevosPremios.tipo,
                probabilidad: this.nuevosPremios.probabilidad,
                peso: pesosPorProbabilidad[this.nuevosPremios.probabilidad],
                limiteUsuario: this.nuevosPremios.limiteUsuario || 0,
                vigenciaInicio: this.nuevosPremios.vigenciaInicio,
                vigenciaFin: this.nuevosPremios.vigenciaFin,
                claimUrl: this.nuevosPremios.claimUrl
            };

            // Si estamos editando, actualizar el premio existente
            if (this.indiceEditando !== null) {
                this.form.premios.splice(this.indiceEditando, 1, premioData);
                this.$swal.fire({
                    title: 'Premio actualizado',
                    text: 'El premio se actualizó correctamente',
                    icon: 'success',
                    confirmButtonColor: '#38ef7d',
                    background: '#ffffff',
                    color: '#2c3e50'
                });
            } else {
                // Si no estamos editando, agregar nuevo premio
                this.form.premios.push(premioData);
            }

            // Limpiar formulario y resetear modo edición
            this.resetFormularioPremio();
        },

        resetFormularioPremio() {
            this.nuevosPremios = {
                nombre: '',
                tipo: '',
                probabilidad: '',
                limiteUsuario: 0,
                vigenciaInicio: null,
                vigenciaFin: null,
                claimUrl: ''
            };
            this.indiceEditando = null;
        },

        notificarLimitePremios() {
            const mensaje = 'Solo puedes registrar un premio por ruleta. Edita o elimina el existente para reemplazarlo.';
            if (this.$swal) {
                this.$swal.fire({
                    title: 'Límite alcanzado',
                    text: mensaje,
                    icon: 'info',
                    confirmButtonColor: '#f7971e',
                    background: '#ffffff',
                    color: '#2c3e50'
                });
            } else {
                alert(mensaje);
            }
        },

        editarPremio(index) {
            const premio = this.form.premios[index];
            if (!premio) return;

            // Cargar datos del premio en el formulario
            this.nuevosPremios = {
                nombre: premio.nombre,
                tipo: premio.tipo,
                probabilidad: premio.probabilidad,
                limiteUsuario: premio.limiteUsuario || 0,
                vigenciaInicio: premio.vigenciaInicio,
                vigenciaFin: premio.vigenciaFin,
                claimUrl: premio.claimUrl || ''
            };
            this.indiceEditando = index;

            // Esperar a que Vue actualice el DOM antes de hacer scroll
            this.$nextTick(() => {
                const formulario = document.getElementById('premio-nombre');
                if (formulario) {
                    formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    formulario.focus();
                }
            });
        },

        eliminarPremio(index) {
            this.$swal.fire({
                title: '¿Eliminar premio?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb3349',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.form.premios.splice(index, 1);
                    this.$swal.fire({
                        title: 'Eliminado',
                        text: 'El premio ha sido eliminado',
                        icon: 'success',
                        confirmButtonColor: '#38ef7d',
                        background: '#ffffff',
                        color: '#2c3e50'
                    });
                }
            });
        },

        submitForm() {
            // Validar que al menos haya un premio
            if (this.form.premios.length === 0) {
                this.$swal.fire({
                    title: 'Premios requeridos',
                    text: 'Debes agregar un premio',
                    icon: 'error',
                    confirmButtonColor: '#eb3349',
                    background: '#ffffff',
                    color: '#2c3e50'
                });
                return;
            }

            this.loading = true;

            const payload = {
                id: this.form.id || null,
                nombre: this.form.nombre,
                descripcion: this.form.descripcion,
                modoInscripcion: this.form.modoInscripcion,
                tiempoInscripcion: this.form.tiempoInscripcion,
                maxParticipantes: this.form.maxParticipantes,
                mostrarInscritos: true,
                tipoPremio: this.form.tipoPremio,
                maxGanadores: this.form.maxGanadores,
                premios: this.form.premios.map((p) => ({
                    nombre: p.nombre,
                    tipo: p.tipo,
                    stock: 1, // Siempre 1 por defecto
                    peso: Number(p.peso),
                    limiteUsuario: Number(p.limiteUsuario || 0),
                    vigenciaInicio: p.vigenciaInicio || null,
                    vigenciaFin: p.vigenciaFin || null,
                    claimUrl: p.claimUrl || null,
                })),
            };

            axios.post(this.storeUrl, payload)
                .then((response) => {
                    const redirect = response.data?.redirect || this.toolsUrl;
                    this.$swal.fire({
                        title: 'Éxito',
                        text: response.data?.message || 'Especificaciones guardadas correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#38ef7d',
                        background: '#ffffff',
                        color: '#2c3e50'
                    }).then(() => {
                        window.location.href = redirect;
                    });
                })
                .catch((error) => {
                    console.error('Error:', error);
                    const message = error.response?.data?.message || 'Hubo un error al guardar las especificaciones';
                    const errors = error.response?.data?.errors;
                    let details = '';
                    if (errors) {
                        details = Object.values(errors).map((msgs) => msgs.join(' ')).join('\n');
                    }
                    this.$swal.fire({
                        title: 'Error',
                        text: message + (details ? `\n${details}` : ''),
                        icon: 'error',
                        confirmButtonColor: '#eb3349',
                        background: '#ffffff',
                        color: '#2c3e50'
                    });
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        probabilidadTexto(prob) {
            const textos = {
                'alta': '🟢 Alta',
                'media': '🟡 Media',
                'baja': '🔴 Baja'
            };
            return textos[prob] || prob;
        }
    }
};
</script>

<style scoped>
.dinamica-specifications {
    max-width: 1000px;
    margin: 0 auto;
}

.card {
    border: 1px solid #d0d0d0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.card-header {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #d0d0d0 !important;
    padding: 1rem;
}

.card-header h5 {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #2c3e50;
    font-weight: 600;
}

.card-header i {
    color: #007bff;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #2c3e50;
}

.form-control {
    border: 1px solid #d0d0d0;
    border-radius: 6px;
    padding: 0.625rem 0.875rem;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-text {
    display: block;
    margin-top: 0.4rem;
    font-size: 0.85rem;
}

.table {
    margin-bottom: 0;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
    border-color: #004085;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover:not(:disabled) {
    background-color: #218838;
    border-color: #1e7e34;
}

.alert {
    border-radius: 6px;
    border: 1px solid;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b3d9ff;
    color: #004085;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.form-control--locked {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: #475569;
    cursor: not-allowed;
    font-weight: 600;
}

.premio-locked .form-group {
    margin-bottom: 1.5rem;
}

.custom-switch .custom-control-label {
    margin-left: 0.5rem;
    cursor: pointer;
    font-weight: 500;
}

.gap-2 {
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .card-header h5 {
        font-size: 1rem;
    }

    .table {
        font-size: 0.85rem;
    }

    .btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.9rem;
    }

}
</style>
