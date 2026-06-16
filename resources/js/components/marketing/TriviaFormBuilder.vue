<template>
    <div class="trivia-form-builder">
        <div class="row">
            <div class="col-lg-8">
                <section class="card shadow-sm border-0 mb-2">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <small class="text-uppercase text-muted">Modulo 1</small>
                            <h4 class="mb-0">{{ isEditMode ? 'Editar trivia' : 'Crear nueva trivia' }}</h4>
                        </div>
                        <span class="badge badge-light mt-2 mt-md-0">{{ modeLabel }}</span>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label class="font-weight-semibold">Nombre de la trivia *</label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    placeholder="Ej: Trivia bienestar corporativo"
                                    v-model="trivia.title"
                                >
                                <small class="text-muted">Titulo publico mostrado en la pantalla inicial.</small>
                            </div>
                            <div class="form-group col-md-5">
                                <label class="font-weight-semibold">Slug (opcional)</label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text text-muted">/marketing/trivia/</span>
                                    </div>
                                    <input type="text" class="form-control" v-model="trivia.slug" placeholder="bienestar-2026">
                                </div>
                                <small class="text-muted">Puedes personalizar la URL amigable.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold">Descripcion</label>
                            <textarea class="form-control" rows="3" placeholder="Describe objetivo, premios o contexto" v-model="trivia.description"></textarea>
                            <small class="text-muted">Texto introductorio para motivar a los jugadores.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold d-block">Activacion</label>
                                <div class="custom-control custom-switch">
                                    <input id="trivia-active-toggle" type="checkbox" class="custom-control-input" v-model="trivia.isActive">
                                    <label class="custom-control-label" for="trivia-active-toggle">
                                        {{ trivia.isActive ? 'Activa para jugadores' : 'Inactiva (solo registro)' }}
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Las trivias nuevas se crean inactivas para permitirte preparar todo y activar cuando quieras que jueguen.</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold">Puntos minimos</label>
                                <input type="number" min="1" class="form-control" v-model.number="trivia.pointsMin">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold">Puntos maximos</label>
                                <input type="number" min="1" class="form-control" v-model.number="trivia.pointsMax">
                                <small class="text-muted">Cada jugador apuesta dentro de este rango.</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center">
                            <button type="button" class="btn btn-primary mr-2 mb-2" @click="simulateSave('save')">
                                <i class="feather icon-save mr-50"></i> Guardar configuracion
                            </button>
                            <button type="button" class="btn btn-success mb-2" @click="simulateSave('activate')">
                                <i class="feather icon-power mr-50"></i> Activar maqueta
                            </button>
                            <span class="text-muted small ml-md-2">Solo diseno/UI · sin logica.</span>
                        </div>
                        <div v-if="toastMessage" class="mock-toast mt-3">
                            <i class="feather icon-info mr-50"></i>{{ toastMessage }}
                        </div>
                    </div>
                </section>

                <section class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <small class="text-uppercase text-muted">Modulo 2</small>
                            <h4 class="mb-0">Dinámicas y categorias</h4>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 mt-md-0" @click="addDynamic">
                            <i class="feather icon-plus mr-50"></i> Agregar dinamica
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Ordena las dinamicas que vivirán los jugadores. Cada dinamica contiene categorias o niveles conectados al banco de preguntas.</p>
                        <div v-if="dynamics.length" class="timeline">
                            <article
                                v-for="(dynamic, index) in orderedDynamics"
                                :key="dynamic.id"
                                class="dynamic-card"
                            >
                                <header class="dynamic-card__head">
                                    <div>
                                        <p class="eyebrow mb-1">Paso {{ index + 1 }} · Posicion {{ dynamic.position }}</p>
                                        <h5 class="mb-0">{{ dynamic.title }}</h5>
                                    </div>
                                    <div class="dynamic-card__head-actions">
                                        <button type="button" class="btn btn-icon" :disabled="index === 0" @click="moveDynamic(index, -1)" aria-label="Subir dinamica">
                                            <i class="feather icon-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon" :disabled="index === orderedDynamics.length - 1" @click="moveDynamic(index, 1)" aria-label="Bajar dinamica">
                                            <i class="feather icon-arrow-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon" @click="duplicateDynamic(dynamic)" aria-label="Duplicar dinamica">
                                            <i class="feather icon-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon text-danger" @click="removeDynamic(dynamic.id)" aria-label="Eliminar dinamica">
                                            <i class="feather icon-trash-2"></i>
                                        </button>
                                    </div>
                                </header>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Titulo *</label>
                                        <input type="text" class="form-control" v-model="dynamic.title" placeholder="Ej: Ronda bienestar">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Posicion</label>
                                        <input type="number" min="1" class="form-control" v-model.number="dynamic.position" @change="syncDynamicPositions">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="d-block">Activo</label>
                                        <div class="custom-control custom-switch">
                                            <input :id="`dynamic-${dynamic.id}-active`" type="checkbox" class="custom-control-input" v-model="dynamic.isActive">
                                            <label class="custom-control-label" :for="`dynamic-${dynamic.id}-active`">{{ dynamic.isActive ? 'En juego' : 'Pausado' }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Descripcion</label>
                                    <textarea class="form-control" rows="2" placeholder="Describe el mood o instrucciones" v-model="dynamic.description"></textarea>
                                </div>
                                <div class="category-builder">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
                                        <div>
                                            <p class="font-weight-semibold mb-0">Categorias vinculadas</p>
                                            <small class="text-muted">Cada categoria trae preguntas desde el catalogo existente.</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0" @click="addCategory(dynamic.id)">
                                            <i class="feather icon-plus mr-50"></i> Agregar categoria
                                        </button>
                                    </div>
                                    <div v-if="dynamic.categories.length" class="category-stack">
                                        <div v-for="category in dynamic.categories" :key="category.id" class="category-card">
                                            <div class="form-row">
                                                <div class="form-group col-md-5">
                                                    <label>Categoria</label>
                                                    <select class="custom-select" v-model="category.questionCategoryId">
                                                        <option disabled value="">Selecciona una categoria</option>
                                                        <option v-for="option in questionCategoryOptions" :key="option.id" :value="option.id">
                                                            {{ option.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Posicion</label>
                                                    <input type="number" min="1" class="form-control" v-model.number="category.position">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Preguntas</label>
                                                    <input type="number" min="1" class="form-control" v-model.number="category.questionsCount">
                                                </div>
                                                <div class="form-group col-md-1 d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input :id="`category-${category.id}-active`" type="checkbox" class="custom-control-input" v-model="category.isActive">
                                                        <label class="custom-control-label" :for="`category-${category.id}-active`"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <span><i class="feather icon-hash mr-25"></i>Orden interno {{ category.position }}</span>
                                                <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="removeCategory(dynamic.id, category.id)">
                                                    <i class="feather icon-trash-2 mr-25"></i>Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="category-empty text-muted small">
                                        No hay categorias asignadas. Usa "Agregar categoria" para traer preguntas de otros bancos.
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div v-else class="empty-state text-center text-muted">
                            <p class="font-weight-semibold mb-1">Aun no agregas dinamicas.</p>
                            <p class="mb-0">Inicia con "Agregar dinamica" para definir juego uno, revancha, final, etc.</p>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="card summary-panel shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-uppercase small text-muted mb-1">Resumen rapido</p>
                        <h5 class="mb-3">{{ trivia.title || 'Trivia sin titulo' }}</h5>
                        <ul class="list-unstyled mb-3 summary-list">
                            <li>
                                <i class="feather icon-check-circle text-success mr-50"></i>
                                {{ dynamics.length }} dinamicas configuradas
                            </li>
                            <li>
                                <i class="feather icon-layers text-primary mr-50"></i>
                                {{ totalCategories }} categorias conectadas
                            </li>
                            <li>
                                <i class="feather icon-zap text-warning mr-50"></i>
                                Rango {{ trivia.pointsMin }} - {{ trivia.pointsMax }} puntos
                            </li>
                            <li>
                                <i class="feather icon-radio text-muted mr-50"></i>
                                Estado actual: {{ activationLabel }}
                            </li>
                        </ul>
                        <hr>
                        <p class="text-uppercase small text-muted mb-2">Checklist visual</p>
                        <ul class="list-unstyled checklist">
                            <li>
                                <span class="check-dot is-complete"></span>
                                Inputs de configuracion completados
                            </li>
                            <li>
                                <span :class="['check-dot', totalCategories ? 'is-complete' : '']"></span>
                                Categorias asignadas a dinamicas
                            </li>
                            <li>
                                <span class="check-dot" :class="{ 'is-complete': trivia.isActive }"></span>
                                Trivia activa para pruebas internas
                            </li>
                        </ul>
                        <div class="note-block mt-3">
                            <p class="font-weight-semibold mb-1">Notas rapidas</p>
                            <ul class="mb-0 small text-muted">
                                <li>Sin logica real. Usa como blueprint.</li>
                                <li>No hay validaciones ni guardado.</li>
                                <li>Todo en una sola pagina, sin modales.</li>
                            </ul>
                        </div>
                        <div class="helper-block mt-3">
                            <p class="font-weight-semibold mb-1">Siguiente paso</p>
                            <p class="text-muted small mb-2">Una vez definida la UI, conectar a API e implementar persistencia.</p>
                            <button type="button" class="btn btn-outline-secondary btn-block" @click="simulateSave('preview')">
                                Ver flujo completo (mock)
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TriviaFormBuilder',
    props: {
        mode: {
            type: String,
            default: 'create',
        },
        initialTrivia: {
            type: Object,
            default: null,
        },
        initialDynamics: {
            type: Array,
            default: () => [],
        },
        questionCategories: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            trivia: this.buildTriviaState(this.initialTrivia),
            dynamics: this.buildDynamicsState(this.initialDynamics),
            questionCategoryOptions: this.normalizeCategoryOptions(this.questionCategories),
            toastMessage: null,
            toastTimeout: null,
        };
    },
    computed: {
        isEditMode() {
            return this.mode === 'edit';
        },
        modeLabel() {
            return this.isEditMode ? 'Editar existente' : 'Crear desde cero';
        },
        activationLabel() {
            return this.trivia.isActive ? 'Activa' : 'Inactiva';
        },
        orderedDynamics() {
            return [...this.dynamics].sort((a, b) => a.position - b.position);
        },
        totalCategories() {
            return this.dynamics.reduce((total, dynamic) => total + dynamic.categories.length, 0);
        },
    },
    watch: {
        initialTrivia: {
            handler(value) {
                this.trivia = this.buildTriviaState(value);
            },
            deep: true,
        },
        initialDynamics: {
            handler(value) {
                this.dynamics = this.buildDynamicsState(value);
            },
            deep: true,
        },
        questionCategories: {
            handler(value) {
                this.questionCategoryOptions = this.normalizeCategoryOptions(value);
            },
            deep: true,
        },
    },
    methods: {
        buildTriviaState(payload) {
            if (payload && typeof payload === 'object') {
                return {
                    title: payload.title || '',
                    slug: payload.slug || '',
                    description: payload.description || '',
                    pointsMin: payload.points_min ?? payload.pointsMin ?? 1,
                    pointsMax: payload.points_max ?? payload.pointsMax ?? 10,
                    isActive: payload.is_active ?? payload.isActive ?? false,
                };
            }
            return {
                title: 'Trivia bienestar 2026',
                slug: 'trivia-bienestar-2026',
                description: 'Dinámica enfocada en bienestar y cultura organizacional.',
                pointsMin: 1,
                pointsMax: 10,
                isActive: false,
            };
        },
        buildDynamicsState(list) {
            if (Array.isArray(list) && list.length) {
                return list.map((entry, index) => this.normalizeDynamic(entry, index));
            }
            return [
                this.normalizeDynamic(
                    {
                        id: 1,
                        title: 'Juego 1 · Bienestar',
                        description: 'Preguntas mixtas para romper el hielo.',
                        position: 1,
                        categories: [
                            { id: 11, question_category_id: 1, position: 1, questions_count: 5, is_active: true },
                            { id: 12, question_category_id: 2, position: 2, questions_count: 5, is_active: true },
                        ],
                    },
                    0,
                ),
                this.normalizeDynamic(
                    {
                        id: 2,
                        title: 'Revancha · Cultura',
                        description: 'Aumenta dificultad con categorias especificas.',
                        position: 2,
                        is_active: true,
                        categories: [
                            { id: 21, question_category_id: 3, position: 1, questions_count: 7, is_active: true },
                        ],
                    },
                    1,
                ),
            ];
        },
        normalizeDynamic(entry, index) {
            return {
                id: entry.id || this.generateId(),
                title: entry.title || `Dinamica ${index + 1}`,
                description: entry.description || '',
                position: entry.position ?? index + 1,
                isActive: entry.is_active ?? entry.isActive ?? true,
                categories: this.normalizeDynamicCategories(entry.categories || entry.dynamic_categories || []),
            };
        },
        normalizeDynamicCategories(list) {
            if (!Array.isArray(list) || list.length === 0) {
                return [
                    {
                        id: this.generateId(),
                        questionCategoryId: '',
                        position: 1,
                        questionsCount: 5,
                        isActive: true,
                    },
                ];
            }
            return list.map((item, index) => ({
                id: item.id || this.generateId(),
                questionCategoryId: item.question_category_id ?? item.questionCategoryId ?? '',
                position: item.position ?? index + 1,
                questionsCount: item.questions_count ?? item.questionsCount ?? 5,
                isActive: item.is_active ?? item.isActive ?? true,
            }));
        },
        normalizeCategoryOptions(list) {
            if (Array.isArray(list) && list.length) {
                return list.map(option => ({
                    id: option.id,
                    name: option.name || option.title || `Categoria ${option.id}`,
                }));
            }
            return [
                { id: 1, name: 'Bienestar integral' },
                { id: 2, name: 'Cultura corporativa' },
                { id: 3, name: 'Innovacion y tecnologia' },
                { id: 4, name: 'Productos estrella' },
            ];
        },
        addDynamic() {
            const nextIndex = this.dynamics.length + 1;
            const draft = {
                id: this.generateId(),
                title: `Nueva dinamica ${nextIndex}`,
                description: 'Describe el objetivo de esta ronda.',
                position: nextIndex,
                isActive: true,
                categories: [
                    {
                        id: this.generateId(),
                        questionCategoryId: '',
                        position: 1,
                        questionsCount: 5,
                        isActive: true,
                    },
                ],
            };
            this.dynamics = [...this.dynamics, draft];
            this.setToast('Se agrego una dinamica mock.');
        },
        duplicateDynamic(dynamic) {
            if (!dynamic) {
                return;
            }
            const clone = JSON.parse(JSON.stringify(dynamic));
            clone.id = this.generateId();
            clone.title = `${dynamic.title} (copia)`;
            clone.categories = clone.categories.map(category => ({
                ...category,
                id: this.generateId(),
            }));
            this.dynamics = [...this.dynamics, clone];
            this.syncDynamicPositions();
            this.setToast('Duplicaste una dinamica para iterar rapidamente.');
        },
        moveDynamic(index, direction) {
            const updated = [...this.orderedDynamics];
            const targetIndex = index + direction;
            if (targetIndex < 0 || targetIndex >= updated.length) {
                return;
            }
            const [item] = updated.splice(index, 1);
            updated.splice(targetIndex, 0, item);
            this.dynamics = updated.map((dynamic, idx) => ({
                ...dynamic,
                position: idx + 1,
            }));
        },
        removeDynamic(id) {
            this.dynamics = this.dynamics.filter(dynamic => dynamic.id !== id);
            this.syncDynamicPositions();
        },
        syncDynamicPositions() {
            this.dynamics = this.orderedDynamics.map((dynamic, index) => ({
                ...dynamic,
                position: index + 1,
            }));
        },
        addCategory(dynamicId) {
            this.dynamics = this.dynamics.map(dynamic => {
                if (dynamic.id !== dynamicId) {
                    return dynamic;
                }
                const nextPosition = dynamic.categories.length + 1;
                const category = {
                    id: this.generateId(),
                    questionCategoryId: '',
                    position: nextPosition,
                    questionsCount: 5,
                    isActive: true,
                };
                return {
                    ...dynamic,
                    categories: [...dynamic.categories, category],
                };
            });
        },
        removeCategory(dynamicId, categoryId) {
            this.dynamics = this.dynamics.map(dynamic => {
                if (dynamic.id !== dynamicId) {
                    return dynamic;
                }
                const filtered = dynamic.categories.filter(category => category.id !== categoryId);
                return {
                    ...dynamic,
                    categories: filtered.map((category, index) => ({
                        ...category,
                        position: index + 1,
                    })),
                };
            });
        },
        setToast(message) {
            this.toastMessage = message;
            if (this.toastTimeout) {
                clearTimeout(this.toastTimeout);
            }
            this.toastTimeout = setTimeout(() => {
                this.toastMessage = null;
            }, 2600);
        },
        simulateSave(type) {
            if (type === 'activate') {
                this.setToast('Activacion simulada. Aun falta conectar a la API.');
                return;
            }
            if (type === 'preview') {
                this.setToast('Vista previa mock generada.');
                return;
            }
            if (type === 'save') {
                this.setToast('Configuracion visual guardada localmente (mock).');
                return;
            }
            this.setToast('Accion mock ejecutada.');
        },
        generateId() {
            return `${Date.now()}-${Math.floor(Math.random() * 10000)}`;
        },
    },
};
</script>

<style scoped>
.mock-toast {
    border: 1px solid #daf0ff;
    background: #f5fbff;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    color: #2f6b9c;
    display: inline-flex;
    align-items: center;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.dynamic-card {
    border: 1px solid #edf1f7;
    border-radius: 14px;
    padding: 1.25rem;
    position: relative;
    background: #fff;
    box-shadow: 0 15px 35px rgba(20, 34, 75, 0.08);
}

.dynamic-card__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.dynamic-card__head-actions {
    display: inline-flex;
}

.btn-icon {
    border: 1px solid #e1e5ef;
    border-radius: 50%;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    margin-left: 0.25rem;
    color: #6c7280;
}

.btn-icon:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 0.75rem;
    color: #9aa0b5;
}

.category-builder {
    border-top: 1px dashed #e1e5ef;
    margin-top: 1rem;
    padding-top: 1rem;
}

.category-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.category-card {
    border: 1px solid #e8ecf4;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    background: #fdfdfd;
}

.category-empty,
.empty-state {
    border: 1px dashed #dfe4ef;
    border-radius: 12px;
    padding: 1rem;
}

.summary-panel {
    position: sticky;
    top: 90px;
}

.summary-list li,
.checklist li {
    display: flex;
    align-items: center;
    margin-bottom: 0.4rem;
}

.check-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #d9dfe9;
    display: inline-block;
    margin-right: 0.5rem;
}

.check-dot.is-complete {
    background: #28c76f;
    border-color: #28c76f;
}

.note-block,
.helper-block {
    border: 1px solid #eef0f6;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    background: #fbfcff;
}

@media (max-width: 991.98px) {
    .summary-panel {
        position: static;
        margin-top: 1rem;
    }
}
</style>
