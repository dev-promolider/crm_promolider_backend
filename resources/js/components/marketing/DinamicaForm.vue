<template>
    <div>
        <!-- Tarjetas de Opciones de Dinámicas -->
        <div class="row mb-4">
            <!-- Tarjeta Ruleta Interactiva -->
            <div class="col-md-6">
                <div class="card border-left-primary h-100 shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span style="font-size: 56px;">🎡</span>
                        </div>
                        <h5 class="card-title text-center font-weight-bold">Ruleta Interactiva</h5>
                        <p class="card-text text-muted">
                            Crea una ruleta con premios y segmentos personalizables para enganchar a tu audiencia.
                        </p>
                        <ul class="list-unstyled small mb-3">
                            <li class="mb-1"><i class="feather icon-check text-success"></i> Hasta 6 segmentos</li>
                            <li class="mb-1"><i class="feather icon-check text-success"></i> Personalización de colores</li>
                            <li class="mb-1"><i class="feather icon-check text-success"></i> Sistema de premios</li>
                            <li class="mb-1"><i class="feather icon-check text-success"></i> Control de participantes</li>
                        </ul>
                        <button type="button" class="btn btn-success btn-block" @click="irAEspecificaciones">
                            <i class="feather icon-play"></i> Crear Ruleta
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Trivia Interactiva -->
            <div class="col-md-6">
                <div class="card border-left-info h-100 shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span style="font-size: 56px;">🧠</span>
                        </div>
                        <h5 class="card-title text-center font-weight-bold">Trivia interactiva</h5>
                        <p class="card-text text-muted text-center">
                            Construye trivias apoyadas en tus categorías base sin duplicar preguntas.
                        </p>
                        <ul class="list-unstyled small mb-3">
                            <li class="mb-1"><i class="feather icon-book text-info"></i> Usa el catálogo de categorías existente</li>
                            <li class="mb-1"><i class="feather icon-layers text-info"></i> Flujo visual completo: inicio, preguntas, resultados</li>
                            <li class="mb-1"><i class="feather icon-users text-info"></i> Pensado para admins y usuarios finales</li>
                        </ul>
                        <button type="button" class="btn btn-outline-primary btn-block" @click="irATrivia">
                            <i class="feather icon-zap"></i> Diseñar Trivia
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Formulario de Dinámica -->
        <form @submit.prevent="submitForm" v-if="selectedType">
            <div class="alert alert-info" role="alert">
                <i class="feather icon-info"></i> 
                <strong>Tipo seleccionado:</strong> {{ getTypeLabel(selectedType) }}
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="dinamica-title">Título de la Dinámica *</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="dinamica-title"
                            v-model="form.title"
                            placeholder="Ej: Ruleta de premios, Concurso semanal..."
                            required
                        >
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dinamica-category">Categoría *</label>
                        <select 
                            class="form-control" 
                            id="dinamica-category"
                            v-model="form.category_id"
                            required
                        >
                            <option value="">Seleccione una categoría</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dinamica-segments">Cantidad de Segmentos *</label>
                        <input 
                            type="number" 
                            class="form-control" 
                            id="dinamica-segments"
                            v-model="form.segments"
                            min="1"
                            max="6"
                            placeholder="Entre 1 y 6 segmentos"
                            required
                        >
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="dinamica-description">Descripción *</label>
                        <textarea 
                            class="form-control" 
                            id="dinamica-description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Describe en qué consiste la dinámica..."
                            required
                        ></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dinamica-start">Fecha de Inicio *</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            id="dinamica-start"
                            v-model="form.start_date"
                            required
                        >
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dinamica-end">Fecha de Finalización *</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            id="dinamica-end"
                            v-model="form.end_date"
                            required
                        >
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="dinamica-prize">Premio o Recompensa</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="dinamica-prize"
                            v-model="form.prize"
                            placeholder="Describe el premio para los ganadores..."
                        >
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Visibilidad</label>
                        <div class="custom-control custom-radio">
                            <input 
                                type="radio" 
                                class="custom-control-input" 
                                id="public" 
                                value="1"
                                v-model="form.is_public"
                            >
                            <label class="custom-control-label" for="public">
                                Pública (visible para todos)
                            </label>
                        </div>
                        <div class="custom-control custom-radio mt-1">
                            <input 
                                type="radio" 
                                class="custom-control-input" 
                                id="private" 
                                value="0"
                                v-model="form.is_public"
                            >
                            <label class="custom-control-label" for="private">
                                Privada (solo participantes invitados)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-2">

            <div class="row">
                <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary mr-2" @click="cancelForm">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" :disabled="loading">
                        <i class="feather icon-save"></i> 
                        {{ loading ? 'Creando...' : 'Crear Dinámica' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    name: 'DinamicaForm',
    props: {
        categories: {
            type: Array,
            default: () => []
        },
        specificationsUrl: {
            type: String,
            default: '/marketing/dinamica/specifications'
        },
        triviaUrl: {
            type: String,
            default: '/marketing/dinamica/trivia'
        }
    },
    data() {
        return {
            loading: false,
            selectedType: null,
            form: {
                title: '',
                type: 'ruleta',
                category_id: '',
                segments: 3,
                description: '',
                start_date: '',
                end_date: '',
                prize: '',
                is_public: '1'
            }
        };
    },
    methods: {
        selectDinamicaType(type) {
            this.selectedType = type;
            this.form.type = type;
        },
        getTypeLabel(type) {
            const labels = {
                'ruleta': 'Ruleta Interactiva',
                'concurso': 'Concurso',
                'quiz': 'Quiz',
                'reto': 'Reto'
            };
            return labels[type] || type;
        },
        cancelForm() {
            this.selectedType = null;
            this.form = {
                title: '',
                type: 'ruleta',
                category_id: '',
                segments: 3,
                description: '',
                start_date: '',
                end_date: '',
                prize: '',
                is_public: '1'
            };
        },
        irAEspecificaciones() {
            window.location.href = this.specificationsUrl;
        },
        irATrivia() {
            window.location.href = this.triviaUrl;
        },
        async submitForm() {
            this.loading = true;
            try {
                // Aquí irá la llamada a la API para guardar la dinámica
                console.log('Formulario enviado:', this.form);
                
                this.$swal.fire({
                    title: 'Éxito',
                    text: 'Dinámica creada correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '{{ route("marketing.tools") }}';
                });
            } catch (error) {
                console.error('Error:', error);
                this.$swal.fire({
                    title: 'Error',
                    text: 'Hubo un error al crear la dinámica',
                    icon: 'error'
                });
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

<style scoped>
.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
}

.card {
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 1px solid #d0d0d0 !important;
}

.card:hover:not(.opacity-75) {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    transform: translateY(-2px);
}

.border-left-primary {
    border-left: 4px solid #d0d0d0 !important;
}

.border-left-secondary {
    border-left: 4px solid #d0d0d0 !important;
}

.card-title {
    color: #2c3e50;
    margin-bottom: 12px;
}

.opacity-75 {
    opacity: 0.6;
}

.list-unstyled li {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-block {
    display: block;
    width: 100%;
}

.alert-info {
    border-radius: 6px;
}

.card i[style*="font-size"] {
    color: #9ca3af !important;
}
</style>
