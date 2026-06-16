<template>
    <section class="qi-form">
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <p class="qi-eyebrow mb-1">Trivia · {{ categoryData.name }}</p>
                    <h2 class="h4 mb-1">{{ formHeading }}</h2>
                    <p class="text-muted mb-0">{{ formDescription }}</p>
                </div>
                <div class="mt-2 mt-lg-0">
                    <button type="button" class="btn btn-outline-secondary mr-1" @click="goTo(categoryUrl)">
                        Volver al detalle
                    </button>
                    <button type="button" class="btn btn-outline-primary" @click="goTo(indexUrl)">
                        Ir al listado
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-2">
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <div v-if="formErrors.length" class="alert alert-danger">
                                <p class="mb-1 font-weight-semibold">Revisa la información capturada:</p>
                                <ul class="mb-0 pl-3">
                                    <li v-for="(error, idx) in formErrors" :key="`error-${idx}`">{{ error }}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Enunciado <span class="text-danger">*</span></label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="form-control"
                                    maxlength="255"
                                    placeholder="Ej. ¿Cuál es la capital de Perú?"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Contexto o pista</label>
                                <textarea
                                    v-model="form.body"
                                    class="form-control"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Texto adicional opcional"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Tiempo (seg)</label>
                                <input
                                    v-model.number="form.time_limit"
                                    type="number"
                                    class="form-control"
                                    min="5"
                                    max="600"
                                    placeholder="Opcional"
                                >
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input
                                    id="question-active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="custom-control-input"
                                >
                                <label class="custom-control-label" for="question-active">
                                    {{ form.is_active ? 'Visible en dinámicas' : 'Oculta temporalmente' }}
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Opciones de respuesta</h5>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    :disabled="form.options.length >= maxOptions"
                                    @click="addOption"
                                >
                                    Añadir opción
                                </button>
                            </div>
                            <p class="text-muted small mb-2">Selecciona exactamente una respuesta correcta.</p>

                            <div v-for="(option, index) in form.options" :key="`option-${index}`" class="option-row">
                                <div class="option-label">{{ optionLabel(index) }}</div>
                                <div class="flex-grow-1 mr-2">
                                    <input
                                        v-model="option.text"
                                        type="text"
                                        class="form-control"
                                        maxlength="255"
                                        :placeholder="`Respuesta ${optionLabel(index)}`"
                                        required
                                    >
                                </div>
                                <div class="custom-control custom-radio mr-2">
                                    <input
                                        :id="`correct-${index}`"
                                        type="radio"
                                        class="custom-control-input"
                                        name="correct-answer"
                                        :checked="option.is_correct"
                                        @change="markAsCorrect(index)"
                                    >
                                    <label class="custom-control-label" :for="`correct-${index}`">Correcta</label>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-link text-danger p-0"
                                    :disabled="form.options.length <= minOptions"
                                    @click="removeOption(index)"
                                >
                                    Eliminar
                                </button>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <p class="text-muted small mb-2 mb-md-0">Debes capturar mínimo {{ minOptions }} respuestas y máximo {{ maxOptions }}.</p>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary mr-1" @click="goTo(categoryUrl)">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-success" :disabled="isSaving">
                                        <span v-if="isSaving" class="spinner-border spinner-border-sm mr-50" role="status"></span>
                                        {{ submitLabel }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Trabajando en: <strong>{{ categoryData.name }}</strong></p>
                        <p class="text-muted mb-0">Preguntas registradas: {{ categoryData.questionsCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'QuestionItemForm',
    props: {
        category: {
            type: Object,
            required: true,
        },
        defaults: {
            type: Object,
            default: () => ({}),
        },
        submitUrl: {
            type: String,
            required: true,
        },
        categoryUrl: {
            type: String,
            required: true,
        },
        indexUrl: {
            type: String,
            required: true,
        },
        mode: {
            type: String,
            default: 'create',
        },
        httpMethod: {
            type: String,
            default: 'POST',
        },
    },
    data() {
        return {
            form: this.buildForm(this.defaults),
            formErrors: [],
            isSaving: false,
            minOptions: 2,
            maxOptions: 6,
        };
    },
    watch: {
        defaults: {
            handler(next) {
                this.form = this.buildForm(next);
            },
            deep: true,
        },
    },
    computed: {
        categoryData() {
            const current = this.category || {};
            return {
                name: current.name || 'Categoria',
                questionsCount: typeof current.questions_count === 'number' ? current.questions_count : (current.questions_count || 0),
            };
        },
        isEditMode() {
            return this.mode === 'edit';
        },
        formHeading() {
            return this.isEditMode ? 'Editar pregunta' : 'Agregar nueva pregunta';
        },
        formDescription() {
            return this.isEditMode
                ? 'Actualiza el enunciado, las respuestas o el tiempo límite.'
                : 'Diseña la pregunta y define las respuestas correctas.';
        },
        submitLabel() {
            return this.isEditMode ? 'Actualizar pregunta' : 'Guardar pregunta';
        },
    },
    methods: {
        buildForm(defaults = {}) {
            const base = defaults || {};
            return {
                title: base.title || '',
                body: base.body || '',
                difficulty: base.difficulty || 'medium',
                status: base.status || 'draft',
                time_limit: base.time_limit ?? null,
                is_active: typeof base.is_active === 'boolean' ? base.is_active : true,
                options: this.buildOptions(base.options),
            };
        },
        buildOptions(options) {
            if (Array.isArray(options) && options.length) {
                const normalized = options.map(option => ({
                    text: option.text || '',
                    is_correct: Boolean(option.is_correct),
                }));

                if (!normalized.some(option => option.is_correct)) {
                    normalized[0].is_correct = true;
                }

                return normalized;
            }

            return this.buildDefaultOptions();
        },
        buildDefaultOptions(count = 4) {
            return Array.from({ length: count }, (_, index) => ({
                text: '',
                is_correct: index === 0,
            }));
        },
        optionLabel(index) {
            return String.fromCharCode(65 + index);
        },
        addOption() {
            if (this.form.options.length >= this.maxOptions) {
                return;
            }
            this.form.options.push({ text: '', is_correct: false });
        },
        removeOption(index) {
            if (this.form.options.length <= this.minOptions) {
                return;
            }
            const wasCorrect = this.form.options[index].is_correct;
            this.form.options.splice(index, 1);
            if (wasCorrect && this.form.options.length) {
                this.markAsCorrect(0);
            }
        },
        markAsCorrect(index) {
            this.form.options = this.form.options.map((option, idx) => ({
                ...option,
                is_correct: idx === index,
            }));
        },
        async submitForm() {
            if (this.isSaving) {
                return;
            }

            this.isSaving = true;
            this.formErrors = [];

            try {
                const response = await fetch(this.submitUrl, {
                    method: this.httpMethod,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                if (response.status === 422) {
                    const errorData = await response.json();
                    this.formErrors = Object.values(errorData.errors || {}).flat();
                    return;
                }

                if (!response.ok) {
                    throw new Error('Error al guardar la pregunta');
                }

                const payload = await response.json();
                const redirectUrl = payload && payload.redirect ? payload.redirect : this.categoryUrl;
                window.location.href = redirectUrl;
            } catch (error) {
                console.error(error);
                this.formErrors = ['Ocurrió un error inesperado. Intenta nuevamente.'];
            } finally {
                this.isSaving = false;
            }
        },
        goTo(url) {
            if (url) {
                window.location.href = url;
            }
        },
    },
};
</script>

<style scoped>
.qi-eyebrow {
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.7rem;
}
.qi-form .card + .card {
    margin-top: 1rem;
}
.option-row {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f2f7;
}
.option-row:last-child {
    border-bottom: none;
}
.option-label {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eef1f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 0.75rem;
}
.font-weight-semibold {
    font-weight: 600;
}
</style>
