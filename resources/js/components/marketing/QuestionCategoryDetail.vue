<template>
    <section class="qc-detail">
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body">
                <p class="qc-eyebrow mb-1">Trivia · Categorías de preguntas</p>
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-2">
                    <div>
                        <h2 class="h4 mb-1">{{ categoryData.name }}</h2>
                        <p class="text-muted mb-0">{{ categoryData.description || 'Sin descripción disponible.' }}</p>
                    </div>
                    <span class="badge" :class="categoryData.is_active ? 'badge-success' : 'badge-secondary'">
                        {{ categoryData.is_active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-2">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
                    <div>
                        <h5 class="mb-1">Preguntas registradas</h5>
                    </div>
                    <div class="d-flex flex-wrap mt-2 mt-lg-0">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm mb-1"
                            :disabled="!createQuestionUrl"
                            @click="goTo(createQuestionUrl)"
                        >
                            Nueva pregunta
                        </button>
                    </div>
                </div>

                <div v-if="questionList.length" class="question-stack mt-3">
                    <article
                        v-for="(question, index) in questionList"
                        :key="question.id"
                        class="question-card"
                    >
                        <header class="question-card__header">
                            <div>
                                <p class="question-label mb-1">Pregunta {{ formatOrder(index) }}</p>
                                <h6 class="mb-0">{{ question.title }}</h6>
                            </div>
                            <button
                                type="button"
                                class="question-card__delete"
                                :disabled="deletingQuestionId === question.id"
                                @click="deleteQuestion(question)"
                                aria-label="Eliminar pregunta"
                            >
                                <span v-if="deletingQuestionId === question.id" class="spinner-border spinner-border-sm" role="status"></span>
                                <span v-else aria-hidden="true">×</span>
                            </button>
                        </header>
                        <ul class="list-unstyled mb-0">
                            <li
                                v-for="option in question.options"
                                :key="option.label"
                                class="option-row"
                                :class="{ 'option-row--correct': option.is_correct }"
                            >
                                <div class="option-chip">{{ option.label }}</div>
                                <span class="option-text">{{ option.text }}</span>
                                <span v-if="option.is_correct" class="badge badge-success ml-auto">
                                    Correcta
                                </span>
                            </li>
                        </ul>
                        <div class="question-actions">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                @click="editQuestion(question)"
                            >
                                Editar
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-questions text-center mt-4">
                    <p class="mb-1 font-weight-semibold">Aún no hay preguntas guardadas.</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'QuestionCategoryDetail',
    props: {
        category: {
            type: Object,
            required: true,
        },
        questions: {
            type: Array,
            default: () => [],
        },
        editUrl: {
            type: String,
            required: true,
        },
        indexUrl: {
            type: String,
            required: true,
        },
        createQuestionUrl: {
            type: String,
            default: null,
        },
        questionEditUrlTemplate: {
            type: String,
            default: null,
        },
        questionDeleteUrlTemplate: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            localQuestions: this.cloneQuestions(this.questions),
            deletingQuestionId: null,
        };
    },
    computed: {
        categoryData() {
            return {
                id: this.category?.id ?? '—',
                name: this.category?.name ?? 'Untitled category',
                description: this.category?.description ?? '',
                is_active: this.resolveStatus(this.category),
                created_by: this.category?.creator_name ?? this.category?.created_by ?? '—',
                created_at: this.category?.created_at ?? '—',
                updated_at: this.category?.updated_at ?? '—',
                questions: this.questionList,
            };
        },
        questionList() {
            return this.localQuestions;
        },
    },
    watch: {
        questions: {
            handler(next) {
                this.localQuestions = this.cloneQuestions(next);
            },
            deep: true,
        },
    },
    methods: {
        cloneQuestions(questions) {
            if (!Array.isArray(questions)) {
                return [];
            }
            return questions.map(question => ({
                ...question,
                options: Array.isArray(question.options)
                    ? question.options.map(option => ({ ...option }))
                    : [],
            }));
        },
        resolveStatus(category) {
            if (!category) {
                return true;
            }
            const value = category.is_active ?? category.status ?? 1;
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
        goTo(url) {
            if (url) {
                window.location.href = url;
            }
        },
        formatOrder(index) {
            const order = Number(index) + 1;
            return `#${order}`;
        },
        buildQuestionUrl(template, questionId) {
            if (!template || !questionId) {
                return null;
            }
            return template.replace('__QUESTION__', questionId);
        },
        editQuestion(question) {
            const target = this.buildQuestionUrl(this.questionEditUrlTemplate, question.id);
            if (target) {
                this.goTo(target);
            }
        },
        async deleteQuestion(question) {
            if (!this.questionDeleteUrlTemplate || this.deletingQuestionId === question.id) {
                return;
            }

            const confirmation = window.confirm('¿Deseas eliminar esta pregunta? Esta acción no se puede deshacer.');
            if (!confirmation) {
                return;
            }

            const target = this.buildQuestionUrl(this.questionDeleteUrlTemplate, question.id);
            if (!target) {
                return;
            }

            this.deletingQuestionId = question.id;

            try {
                const response = await fetch(target, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Delete failed');
                }

                this.localQuestions = this.localQuestions.filter(item => item.id !== question.id);
            } catch (error) {
                console.error(error);
                alert('No se pudo eliminar la pregunta. Intenta nuevamente.');
            } finally {
                this.deletingQuestionId = null;
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
.question-stack {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    grid-gap: 1rem;
}
.question-card {
    border: 1px solid #eef0f3;
    border-radius: 12px;
    padding: 1rem;
    background: #fcfdff;
}
.question-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.question-card__delete {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #a3a8b8;
    cursor: pointer;
}
.question-card__delete:hover {
    color: #ea5455;
}
.question-card__delete:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.question-label {
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    color: #9399ad;
}
.option-row {
    display: flex;
    align-items: center;
    padding: 0.45rem 0.6rem;
    border-radius: 8px;
    transition: background 0.2s ease;
}
.option-row + .option-row {
    margin-top: 0.25rem;
}
.option-row--correct {
    background: #f1fff4;
}
.option-chip {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #eff2f7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 0.75rem;
}
.option-text {
    flex: 1;
}
.empty-questions {
    padding: 1.5rem;
    border: 1px dashed #dfe3eb;
    border-radius: 12px;
}
.question-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.question-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: 0.25rem;
}
.question-actions .btn {
    font-size: 0.85rem;
}
</style>
