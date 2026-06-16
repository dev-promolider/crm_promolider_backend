<template>
    <section class="qc-list">
        <header class="qc-list__header card shadow-sm border-0">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div class="mb-2 mb-lg-0">
                    <p class="qc-eyebrow mb-1">Trivia · Banco de preguntas</p>
                    <h2 class="h4 mb-1">Categorías de preguntas</h2>
                    <p class="text-muted mb-0">Diseña y administra el catálogo completo.</p>
                </div>
                <button type="button" class="btn btn-primary" @click="goTo(createUrl)">
                    <i class="feather icon-plus mr-50"></i> Nueva categoría
                </button>
            </div>
        </header>

        <div class="card shadow-sm border-0 qc-list__tools">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-8 mb-1 mb-md-0">
                        <label class="font-weight-semibold">Buscar</label>
                        <input
                            v-model="searchTerm"
                            type="text"
                            class="form-control"
                            placeholder="Buscar por nombre..."
                        >
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-semibold">Estado</label>
                        <select v-model="statusFilter" class="form-control">
                            <option value="all">Todos</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 qc-table">
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th class="text-center actions-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in filteredCategories" :key="category.id">
                            <td class="font-weight-semibold">
                                <a :href="buildUrl(showUrlTemplate, category.id)">{{ category.name }}</a>
                            </td>
                            <td class="qc-status">
                                <div class="qc-status__switch">
                                    <button
                                        type="button"
                                        class="qc-status__option"
                                        :class="{ 'is-selected': category.is_active }"
                                        @click="setStatus(category, true)"
                                    >
                                        Activo
                                    </button>
                                    <span class="qc-status__divider">/</span>
                                    <button
                                        type="button"
                                        class="qc-status__option"
                                        :class="{ 'is-selected': !category.is_active }"
                                        @click="setStatus(category, false)"
                                    >
                                        Inactivo
                                    </button>
                                </div>
                            </td>
                            <td class="text-center position-relative actions-cell">
                                <div class="qc-actions" @click.stop>
                                    <button type="button" class="qc-actions__toggle" @click="toggleMenu(category.id)">
                                        ...
                                    </button>
                                    <div v-if="openMenuId === category.id" class="qc-actions__menu">
                                        <button type="button" class="qc-actions__item" @click="handleAction('view', category)">
                                            Ver preguntas
                                        </button>
                                        <button type="button" class="qc-actions__item" @click="handleAction('add', category)">
                                            Agregar preguntas
                                        </button>
                                        <button type="button" class="qc-actions__item" @click="handleAction('edit', category)">
                                            Editar categoría
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCategories.length === 0">
                            <td colspan="3" class="text-center text-muted py-4">
                                <div class="mb-2 font-weight-semibold">Sin categorías todavía</div>
                                <button type="button" class="btn btn-outline-primary" @click="goTo(createUrl)">
                                    Crea tu primera categoría
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-md-none p-2">
                <div v-if="filteredCategories.length > 0">
                    <div v-for="category in filteredCategories" :key="`mobile-${category.id}`" class="qc-card mb-2 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <a :href="buildUrl(showUrlTemplate, category.id)" class="font-weight-bold">{{ category.name }}</a>
                            <span class="badge" :class="category.is_active ? 'badge-success' : 'badge-secondary'">
                                {{ category.is_active ? 'activo' : 'inactivo' }}
                            </span>
                        </div>
                        <div class="d-flex flex-column qc-card__actions">
                            <div class="mb-1 qc-status__switch">
                                <button
                                    type="button"
                                    class="qc-status__option"
                                    :class="{ 'is-selected': category.is_active }"
                                    @click="setStatus(category, true)"
                                >
                                    Activo
                                </button>
                                <span class="qc-status__divider">/</span>
                                <button
                                    type="button"
                                    class="qc-status__option"
                                    :class="{ 'is-selected': !category.is_active }"
                                    @click="setStatus(category, false)"
                                >
                                    Inactivo
                                </button>
                            </div>
                            <div class="qc-actions" @click.stop>
                                <button type="button" class="qc-actions__toggle" @click="toggleMenu(category.id)">
                                    ...
                                </button>
                                <div v-if="openMenuId === category.id" class="qc-actions__menu qc-actions__menu--mobile">
                                    <button type="button" class="qc-actions__item" @click="handleAction('view', category)">
                                        Ver preguntas
                                    </button>
                                    <button type="button" class="qc-actions__item" @click="handleAction('add', category)">
                                        Agregar preguntas
                                    </button>
                                    <button type="button" class="qc-actions__item" @click="handleAction('edit', category)">
                                        Editar categoría
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-muted py-4">
                    <p class="font-weight-semibold mb-1">Sin categorías todavía</p>
                    <button type="button" class="btn btn-outline-primary" @click="goTo(createUrl)">
                        Crea tu primera categoría
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'QuestionCategoryList',
    props: {
        initialCategories: {
            type: Array,
            default: () => [],
        },
        createUrl: {
            type: String,
            required: true,
        },
        showUrlTemplate: {
            type: String,
            required: true,
        },
        editUrlTemplate: {
            type: String,
            required: true,
        },
        toggleUrlTemplate: {
            type: String,
            required: false,
            default: null,
        },
        questionCreateUrlTemplate: {
            type: String,
            required: false,
            default: null,
        },
    },
    data() {
        return {
            searchTerm: '',
            statusFilter: 'all',
            categories: this.normalizeCategories(this.initialCategories),
            isToggling: false,
            openMenuId: null,
        };
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
    },
    watch: {
        initialCategories: {
            handler(next) {
                this.categories = this.normalizeCategories(next);
            },
            deep: true,
        },
    },
    computed: {
        filteredCategories() {
            return this.categories
                .filter(category => {
                    if (this.statusFilter === 'active') {
                        return category.is_active;
                    }
                    if (this.statusFilter === 'inactive') {
                        return !category.is_active;
                    }
                    return true;
                })
                .filter(category =>
                    category.name.toLowerCase().includes(this.searchTerm.trim().toLowerCase())
                );
        },
    },
    methods: {
        normalizeCategories(list) {
            if (!Array.isArray(list)) {
                return [];
            }
            return list.map(category => ({
                id: category.id,
                name: category.name,
                description: category.description,
                is_active: this.resolveStatus(category),
                created_at: category.created_at ?? '—',
                updated_at: category.updated_at ?? '—',
            }));
        },
        resolveStatus(category) {
            const value = category.is_active ?? category.active ?? category.status ?? 1;
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
        buildUrl(template, id) {
            if (!template) {
                return '#';
            }
            return template.replace('__ID__', id);
        },
        goTo(url) {
            if (!url) {
                return;
            }
            window.location.href = url;
        },
        toggleMenu(categoryId) {
            this.openMenuId = this.openMenuId === categoryId ? null : categoryId;
        },
        handleAction(action, category) {
            if (action === 'view') {
                this.goTo(this.buildUrl(this.showUrlTemplate, category.id));
            } else if (action === 'add') {
                this.goTo(this.resolveAddQuestionsUrl(category));
            } else if (action === 'edit') {
                this.goTo(this.buildUrl(this.editUrlTemplate, category.id));
            }
            this.openMenuId = null;
        },
        handleOutsideClick(event) {
            if (!this.$el.contains(event.target)) {
                this.openMenuId = null;
            }
        },
        setStatus(category, desiredState) {
            if (category.is_active === desiredState) {
                return;
            }
            this.toggleStatus(category);
        },
        resolveAddQuestionsUrl(category) {
            if (this.questionCreateUrlTemplate) {
                return this.buildUrl(this.questionCreateUrlTemplate, category.id);
            }
            return this.buildUrl(this.showUrlTemplate, category.id);
        },
        async toggleStatus(category) {
            if (!this.toggleUrlTemplate || this.isToggling) {
                category.is_active = !category.is_active;
                return;
            }

            const targetUrl = this.buildUrl(this.toggleUrlTemplate, category.id);
            if (!targetUrl) {
                return;
            }

            const nextState = !category.is_active;
            this.isToggling = true;

            try {
                const response = await fetch(targetUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Error al actualizar el estado.');
                }

                const payload = await response.json();
                category.is_active = payload?.category?.is_active ?? nextState;
            } catch (error) {
                console.error(error);
                category.is_active = !nextState;
                alert('No se pudo actualizar el estado. Intenta nuevamente.');
            } finally {
                this.isToggling = false;
            }
        },
    },
};
</script>

<style scoped>
.qc-eyebrow {
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.7rem;
}
.qc-list__header .btn {
    min-width: 190px;
}
.qc-list__tools label,
.qc-table label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.qc-card {
    background-color: #fff;
}
.qc-table .table-responsive {
    overflow: visible;
}
.qc-status__switch {
    display: inline-flex;
    align-items: center;
}
.qc-status__option {
    background: transparent;
    border: none;
    padding: 0.1rem 0.4rem;
    border-radius: 12px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #7b7d88;
    transition: background-color 0.2s ease, color 0.2s ease;
}
.qc-status__option.is-selected {
    background-color: #28c76f;
    color: #fff;
    font-weight: 600;
}
.qc-status__option:focus {
    outline: none;
}
.qc-status__divider {
    margin: 0 0.25rem;
    color: #b7bbc7;
}
.qc-status {
    width: 160px;
}
.qc-table .actions-cell {
    width: 170px;
    padding-right: 0.75rem !important;
}
.qc-table .actions-cell .qc-actions {
    margin-right: 1rem;
}
.qc-table .table td:last-child,
.qc-table .table th:last-child {
    padding-right: 1.5rem;
}
.qc-card__actions .btn-group .btn {
    min-width: 90px;
}
.qc-actions {
    position: relative;
    display: inline-block;
}
.qc-actions__toggle {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    padding: 0;
    color: #6e6b7b;
    cursor: pointer;
}
.qc-actions__toggle:focus {
    outline: none;
}
.qc-actions__menu {
    position: absolute;
    right: 0;
    top: 120%;
    background: #fff;
    border-radius: 0.4rem;
    box-shadow: 0 6px 18px rgba(47, 53, 66, 0.15);
    padding: 0.4rem 0;
    min-width: 180px;
    z-index: 5;
}
.qc-actions__menu--mobile {
    position: static;
    margin-top: 0.5rem;
    box-shadow: none;
    border: 1px solid #eef1f6;
}
.qc-actions__item {
    width: 100%;
    background: transparent;
    border: none;
    text-align: left;
    padding: 0.4rem 1rem;
    font-size: 0.9rem;
    color: #4a4f5c;
    cursor: pointer;
}
.qc-actions__item:hover {
    background-color: #f5f7fb;
}
.qc-actions__item:focus {
    outline: none;
}
</style>
