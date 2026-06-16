<template>
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h4 class="card-title mb-0 text-center text-md-start">Mi Historial de Corte Binario.</h4>
            <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                <!-- Buscador -->
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Buscar por rango o fecha" v-model="searchQuery"
                        @input="debouncedSearch" />
                </div>

                <!-- Selector de cantidad de registros -->
                <select class="form-select" v-model="perPage" @change="changePerPage">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            <!-- Estadísticas rápidas -->
            <div class="row g-3 mb-1">
                <div class="col-12 col-md-4">
                    <div class="stat-card bg-light-primary">
                        <div class="stat-content">
                            <h6 class="stat-title">Total de Cortes</h6>
                            <p class="stat-value">{{ totalHistories }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-card bg-light-warning">
                        <div class="stat-content">
                            <h6 class="stat-title">Último Corte</h6>
                            <p class="stat-value">{{ lastCutDate ? formatDate(lastCutDate) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-card bg-light-info">
                        <div class="stat-content">
                            <h6 class="stat-title">Rango Actual</h6>
                            <p class="stat-value">{{ currentRank || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de datos -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th @click="sortBy('rank.name')" class="sortable-header">
                                Rango
                                <i :class="getSortIcon('rank.name')"></i>
                            </th>
                            <th @click="sortBy('left_points')" class="sortable-header">
                                Puntos Izquierda
                                <i :class="getSortIcon('left_points')"></i>
                            </th>
                            <th @click="sortBy('right_points')" class="sortable-header">
                                Puntos Derecha
                                <i :class="getSortIcon('right_points')"></i>
                            </th>
                            <th @click="sortBy('transferred_amount')" class="sortable-header">
                                Monto Transferido
                                <i :class="getSortIcon('transferred_amount')"></i>
                            </th>
                            <th @click="sortBy('created_at')" class="sortable-header">
                                Fecha de Corte
                                <i :class="getSortIcon('created_at')"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="histories.length === 0">
                            <td colspan="5" class="text-center py-4">
                                <div class="alert alert-info mb-0">No hay registros disponibles</div>
                            </td>
                        </tr>
                        <tr v-for="history in histories" :key="history.id">
                            <td>{{ history.rank.name }}</td>
                            <td>{{ formatNumber(history.left_points) }}</td>
                            <td>{{ formatNumber(history.right_points) }}</td>
                            <td>${{ formatNumber(history.transferred_amount) }}</td>
                            <td>{{ formatDate(history.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <nav v-if="pagination.last_page > 1" class="mt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted order-2 order-md-1 text-center text-md-start">
                            Mostrando {{ (pagination.current_page - 1) * perPage + 1 }} -
                            {{ Math.min(pagination.current_page * perPage, pagination.total) }}
                            de {{ pagination.total }} registros
                        </div>
                        <ul class="pagination mb-0 order-1 order-md-2">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li v-for="page in visiblePages" :key="page" class="page-item"
                                :class="{ active: page === currentPage }">
                                <a class="page-link" href="#" @click.prevent="changePage(page)">
                                    {{ page }}
                                </a>
                            </li>
                            <li class="page-item" :class="{ disabled: currentPage === pagination.last_page }">
                                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';

export default {
    name: 'BinaryCutHistory',

    data() {
        return {
            histories: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0,
                per_page: 10,
            },
            loading: false,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            sortKey: 'created_at',
            sortOrder: 'desc',
            totalHistories: 0,
            totalTransferredAmount: 0,
            lastCutDate: null,
            currentRank: null,
            selectedHistory: null,
        };
    },

    computed: {
        visiblePages() {
            const range = 2;
            let start = Math.max(1, this.currentPage - range);
            let end = Math.min(this.pagination.last_page, this.currentPage + range);

            let pages = [];
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
    },

    mounted() {
        this.fetchHistory();
    },

    methods: {
        debouncedSearch: _.debounce(function () {
            this.currentPage = 1;
            this.fetchHistory();
        }, 500),

        async fetchHistory(page = 1) {
            this.loading = true;
            try {
                const response = await axios.get('/reports/get-binary-history', {
                    params: {
                        page,
                        per_page: this.perPage,
                        search: this.searchQuery,
                        sort_key: this.sortKey,
                        sort_order: this.sortOrder,
                    },
                });

                this.histories = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                    per_page: response.data.per_page,
                };
                this.currentPage = page;

                // Calcular estadísticas
                this.totalHistories = response.data.total;
                this.totalTransferredAmount = this.histories.reduce(
                    (sum, history) => sum + history.transferred_amount,
                    0
                );
                this.lastCutDate = this.histories.length > 0 ? this.histories[0].created_at : null;
                this.currentRank = this.histories.length > 0 ? this.histories[0].rank.name : null;
            } catch (error) {
                console.error('Error al obtener el historial:', error);
            } finally {
                this.loading = false;
            }
        },

        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchHistory(page);
            }
        },

        changePerPage() {
            this.currentPage = 1;
            this.fetchHistory();
        },

        sortBy(key) {
            if (this.sortKey === key) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortOrder = 'desc';
            }
            this.fetchHistory();
        },

        getSortIcon(key) {
            if (this.sortKey !== key) return 'fas fa-sort text-muted';
            return this.sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        },

        formatNumber(value) {
            return new Intl.NumberFormat('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(value);
        },

        formatDate(date) {
            return new Date(date).toLocaleString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        showHistoryDetails(history) {
            this.selectedHistory = history;
        },
    },
};
</script>

<style scoped>
.card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: none;
    background: #fff;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1.25rem;
}

.input-group {
    min-width: 250px;
    max-width: 100%;
}

.form-select {
    min-width: 100px;
    max-width: 150px;
}

/* Tarjetas de estadísticas mejoradas */
.stat-card {
    border-radius: 10px;
    padding: 1.25rem;
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-content {
    text-align: center;
}

.stat-title {
    color: #6e6b7b;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.stat-value {
    color: #2c2c2c;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0;
}

/* Colores de fondo para las tarjetas */
.bg-light-primary {
    background-color: rgba(115, 103, 240, 0.12);
}

.bg-light-warning {
    background-color: rgba(255, 159, 67, 0.12);
}

.bg-light-info {
    background-color: rgba(59, 177, 210, 0.12);
}

/* Tabla mejorada */
.table {
    margin-bottom: 0;
}

.sortable-header {
    background-color: #f8f9fa;
    cursor: pointer;
    padding: 1rem;
    font-weight: 600;
    color: #6e6b7b;
    transition: background-color 0.2s ease;
}

.sortable-header:hover {
    background-color: #f1f1f1;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
}

/* Paginación mejorada */
.pagination {
    gap: 0.25rem;
}

.page-link {
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    color: #7367f0;
    border: 1px solid #e9ecef;
    min-width: 38px;
    text-align: center;
}

.page-link:hover {
    background-color: #f8f9fa;
    color: #7367f0;
    border-color: #e9ecef;
}

.page-item.active .page-link {
    background-color: #7367f0;
    border-color: #7367f0;
}

.row.g-3.mb-1 {
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .card-header {
        text-align: center;
    }

    .input-group {
        width: 100%;
    }

    .form-select {
        width: 100%;
        max-width: 100%;
    }

    .table {
        font-size: 0.9rem;
    }

    .stat-card {
        margin-bottom: 1rem;
    }

    .row.g-3.mb-1 {
        gap: 3px;
        margin-top: 1.5rem;
    }
}

@media (max-width: 576px) {
    .pagination {
        justify-content: center;
    }

    .page-link {
        padding: 0.4rem 0.6rem;
        min-width: 34px;
    }
}
</style>
