<template>
    <section class="qc-form">
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body">
                <p class="qc-eyebrow mb-1">Trivia · Categorias de preguntas</p>
                <h2 class="h4 mb-1">{{ formTitle }}</h2>
                <p class="text-muted mb-0">{{ formSubtitle }}</p>
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
                                    <li v-for="(error, index) in formErrors" :key="`err-${index}`">{{ error }}</li>
                                </ul>
                            </div>
                            <div class="form-group">
                                <label for="category-name" class="font-weight-semibold">
                                    Nombre de la categoria <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="category-name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    maxlength="120"
                                    placeholder="ej. Cultura General"
                                    required
                                >
                                <small class="form-text text-muted">Nombre unico, maximo 120 caracteres.</small>
                            </div>

                            <div class="form-group">
                                <label for="category-description" class="font-weight-semibold">Descripcion</label>
                                <textarea
                                    id="category-description"
                                    v-model="form.description"
                                    class="form-control"
                                    rows="4"
                                    maxlength="255"
                                    placeholder="Descripcion corta (max 255 caracteres)"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold d-block">Estado</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        id="category-status"
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="custom-control-input"
                                    >
                                    <label class="custom-control-label" for="category-status">
                                        {{ form.is_active ? 'Activa' : 'Inactiva' }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">Usa este interruptor para controlar la visibilidad.</small>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Los campos marcados con * son obligatorios.
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary mr-1" @click="cancelForm">Cancelar</button>
                                    <button type="submit" class="btn btn-primary" :disabled="isSaving">
                                        <span v-if="isSaving" class="spinner-border spinner-border-sm mr-50" role="status"></span>
                                        {{ primaryActionLabel }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" v-if="isEdit && viewUrl">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-2">Necesitas revisar los detalles antes?</p>
                        <button type="button" class="btn btn-outline-primary btn-block" @click="goTo(viewUrl)">
                            Ver categoria
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'QuestionCategoryForm',
    props: {
        mode: {
            type: String,
            default: 'create',
        },
        initialCategory: {
            type: Object,
            default: () => null,
        },
        indexUrl: {
            type: String,
            required: true,
        },
        viewUrl: {
            type: String,
            default: null,
        },
        storeUrl: {
            type: String,
            default: null,
        },
        updateUrl: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            form: this.buildForm(),
            formErrors: [],
            isSaving: false,
        };
    },
    computed: {
        isEdit() {
            return this.mode === 'edit';
        },
        primaryActionLabel() {
            return this.isEdit ? 'Actualizar categoria' : 'Guardar categoria';
        },
        formTitle() {
            return this.isEdit ? 'Editar categoria' : 'Crear categoria';
        },
        formSubtitle() {
            return this.isEdit
                ? 'Actualiza la informacion de esta categoria.'
                : 'Agrega una nueva categoria para reutilizar en tu banco de preguntas.';
        },
    },
    watch: {
        initialCategory: {
            handler() {
                this.form = this.buildForm();
            },
            deep: true,
        },
    },
    methods: {
        buildForm() {
            return {
                name: this.initialCategory?.name || '',
                description: this.initialCategory?.description || '',
                is_active: this.resolveStatus(this.initialCategory),
            };
        },
        resolveStatus(category) {
            if (!category || category.is_active === undefined) {
                return true;
            }
            const value = category.is_active;
            if (typeof value === 'boolean') {
                return value;
            }
            if (typeof value === 'number') {
                return value === 1;
            }
            if (typeof value === 'string') {
                return ['1', 'true', 'active'].includes(value.toLowerCase());
            }
            return true;
        },
        async submitForm() {
            if (this.isSaving) {
                return;
            }

            const targetUrl = this.isEdit ? this.updateUrl : this.storeUrl;
            const method = this.isEdit ? 'PUT' : 'POST';

            if (!targetUrl) {
                console.warn('No hay URL configurada para guardar la categoría.');
                return;
            }

            this.isSaving = true;
            this.formErrors = [];

            try {
                const response = await fetch(targetUrl, {
                    method,
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
                    throw new Error('Error al guardar la categoría');
                }

                const payload = await response.json();
                const redirectUrl = payload?.redirect || this.indexUrl;
                window.location.href = redirectUrl;
            } catch (error) {
                console.error(error);
                this.formErrors = ['Ocurrió un error inesperado. Intenta nuevamente.'];
            } finally {
                this.isSaving = false;
            }
        },
        cancelForm() {
            this.goTo(this.indexUrl);
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
.qc-eyebrow {
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.75rem;
}
.font-weight-semibold {
    font-weight: 600;
}
</style>
