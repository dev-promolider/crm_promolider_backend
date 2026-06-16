<template>
	<div class="trivia-dynamic-designer">
		<section class="card shadow-sm mb-3">
			<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
				<div>
					<small class="text-uppercase text-muted">Modulo 1</small>
					<h4 class="mb-0">Categorias de preguntas</h4>
				</div>
				<div class="header-actions d-flex flex-wrap align-items-center">
					<button type="button" class="btn btn-outline-secondary mr-1 mb-1" @click="goToCategoriesList">Ver categorias</button>
					<button type="button" class="btn btn-primary mb-1" @click="goToCategoriesCreate">Nueva categoria</button>
				</div>
			</div>
			<div class="card-body">
				<div v-if="activeCategories.length" class="active-categories">
					<p class="mb-1 text-muted small font-weight-semibold">Categorias activas disponibles</p>
					<div class="d-flex flex-wrap">
						<span
							v-for="category in activeCategories"
							:key="`active-${category.id}`"
							class="badge badge-pill badge-primary mr-1 mb-1"
						>
							{{ category.name }}
						</span>
					</div>
				</div>
				<p v-else class="mb-0 text-muted small">
					Aun no hay categorias activas. Usa los botones del encabezado para crear una nueva.
				</p>
			</div>
		</section>

		<section class="card shadow-sm mb-3">
			<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
				<div>
					<small class="text-uppercase text-muted">Modulo 1.5</small>
					<h4 class="mb-0">Configuracion de inscripcion</h4>
				</div>
				<span class="badge badge-light">Requisitos previos</span>
			</div>
			<div class="card-body">
				<div class="registration-config mt-2">
					<div class="form-row">
						<div class="form-group col-sm-6 col-md-4 col-lg-3">
							<label class="font-weight-semibold">Limite de participantes</label>
							<input
								type="number"
								class="form-control"
								min="1"
								v-model.number="registrationConfig.participantsLimit"
								placeholder="Ej: 150"
							>
						</div>
						<div class="form-group col-sm-6 col-md-4 col-lg-4">
							<label class="font-weight-semibold">Cierre del registro (hora exacta)</label>
							<input
								type="time"
								class="form-control"
								v-model="registrationConfig.closingTime"
								placeholder="22:30"
								@change="handleClosingTimeChange"
							>
							<small class="form-text text-muted">Puedes modificarlo en cualquier momento para alargar o acortar inscripciones.</small>
						</div>
					</div>
				</div>
				<p class="text-muted small mt-2 mb-0">Define un cierre exacto y, si quieres, combínalo con el límite de participantes.</p>
			</div>
		</section>

		<section class="card shadow-sm">
			<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
				<div>
					<small class="text-uppercase text-muted">Modulo 2</small>
					<h4 class="mb-0">Dinamica de trivia</h4>
				</div>
				<span class="badge badge-light">Diseno visual, sin logica</span>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-lg-7 mb-3">
						<div class="configurator card border h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-start mb-3">
									<div>
										<h5 class="mb-1">Configuracion principal</h5>
										<p class="text-muted small mb-0">Define lo que veran los jugadores antes de jugar.</p>
									</div>
								</div>
								<div class="form-group">
									<label class="font-weight-semibold">Nombre publico</label>
									<input
										type="text"
										class="form-control"
										v-model="triviaConfig.name"
										placeholder="Ej: Trivia Bienestar Corporativo"
									>
									<small class="form-text text-muted">Visible en la pantalla inicial y comunicaciones.</small>
								</div>
								<div class="form-group">
									<label class="font-weight-semibold">Descripcion</label>
									<textarea class="form-control" rows="3" v-model="triviaConfig.description" placeholder="Describe el objetivo o los premios"></textarea>
									<small class="form-text text-muted">Opcional. Sirve como copy introductorio.</small>
								</div>
								<div class="form-group">
									<label class="font-weight-semibold">Slug (opcional)</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text text-muted">marketing/trivia/</span>
										</div>
										<input type="text" class="form-control" v-model="triviaConfig.slug" placeholder="bienestar-2026">
									</div>
									<small class="form-text text-muted">Personaliza la URL si lo necesitas.</small>
								</div>
								<div class="form-group">
									<label class="font-weight-semibold">Rango de puntos</label>
									<div class="form-row align-items-center">
										<div class="col">
											<label class="sr-only" for="points-min">Puntos minimos</label>
											<input id="points-min" type="number" class="form-control" min="1" v-model.number="triviaConfig.pointsMin" placeholder="Min">
										</div>
										<div class="col-auto text-muted font-weight-semibold">—</div>
										<div class="col">
											<label class="sr-only" for="points-max">Puntos maximos</label>
											<input id="points-max" type="number" class="form-control" :min="triviaConfig.pointsMin" v-model.number="triviaConfig.pointsMax" placeholder="Max">
										</div>
									</div>
									<small class="form-text text-muted">Cada jugador apuesta entre estos valores antes de responder cada pregunta.</small>
								</div>

								<div class="game-blocks mt-4">
									<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
										<div>
											<h6 class="mb-1">Bloques y categorias en juego</h6>
											<p class="text-muted small mb-0">Define el orden exacto en que se jugaran las categorias. Sin modales, todo en este constructor.</p>
										</div>
										<div class="btn-group mt-2 mt-md-0">
											<button
												type="button"
												class="btn btn-sm btn-outline-primary dropdown-toggle"
												:class="{ disabled: !availableCategoryOptions.length }"
												:disabled="!availableCategoryOptions.length"
												@click="toggleCategoryPicker"
											>
												{{ categoryPickerLabel }}
											</button>
										</div>
									</div>

									<div v-if="isCategoryPickerOpen" class="category-picker card border mb-3">
										<div class="card-body p-2">
											<p class="text-muted small mb-2">Selecciona una categoria activa para crear un bloque al instante.</p>
											<div class="d-flex flex-wrap">
												<button
													v-for="option in availableCategoryOptions"
													:key="`picker-${option.id}`"
													type="button"
													class="btn btn-light btn-sm mr-1 mb-1"
													@click="addBlockFromCategory(option)"
												>
													{{ option.name }}
												</button>
											</div>
											<p v-if="!availableCategoryOptions.length" class="text-muted small mb-0">No hay categorias activas para asignar.</p>
										</div>
									</div>

									<div v-if="gameBlocks.length" class="game-block-list">
										<article v-for="(block, index) in gameBlocks" :key="block.id" class="game-block-card">
											<header class="game-block-head">
												<div>
													<p class="game-block-label mb-1">Bloque {{ index + 1 }}</p>
													<input type="text" class="form-control form-control-sm" v-model="block.title" placeholder="Ej: Juego base">
												</div>
												<div class="game-block-controls">
													<button
														type="button"
														class="game-block-remove"
														@click="removeGameBlock(block.id)"
														aria-label="Eliminar bloque"
													>
														X
													</button>
												</div>
											</header>
											<div class="form-row">
												<div class="form-group col-md-8">
													<label>Categoria principal *</label>
													<select class="custom-select" v-model="block.categoryId">
														<option disabled value="">Selecciona una categoria</option>
														<option v-for="option in getCategoryOptionsForBlock(block)" :key="option.id" :value="option.id">
															{{ option.name }}
														</option>
													</select>
													<small class="form-text text-muted">
														Preguntas configuradas en la categoria:
														<span v-if="getCategoryQuestionCount(block.categoryId)">
															{{ getCategoryQuestionCount(block.categoryId) }} disponibles
														</span>
														<span v-else>Define la cantidad desde la categoria</span>
													</small>
												</div>
												<div class="form-group col-md-4">
													<label>Orden en la experiencia</label>
													<input type="number" min="1" class="form-control" v-model.number="block.order" @change="syncGameBlockOrder">
												</div>
											</div>
											<div class="form-row align-items-center">
												<div class="form-group col-md-5 col-lg-4">
													<label class="d-block">Estado</label>
													<div class="custom-control custom-switch">
														<input :id="`block-${block.id}-active`" type="checkbox" class="custom-control-input" v-model="block.isActive">
														<label class="custom-control-label" :for="`block-${block.id}-active`">{{ block.isActive ? 'Incluida' : 'Omitida' }}</label>
													</div>
												</div>
												<div class="form-group col-md-7 col-lg-8 mb-0">
													<p class="text-muted small mb-0">Las preguntas y notas del bloque se administran directamente desde la categoria seleccionada.</p>
												</div>
											</div>
										</article>
									</div>
									<div v-else class="game-block-empty text-muted small">
										Aun no defines bloques de juego. Usa "Agregar bloque" para ordenar categorias y revanchas.
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-5">
						<div class="insight-panel card border h-100">
							<div class="card-body">
								<p class="text-uppercase small text-muted mb-1">Estado y checklist</p>
								<ul class="list-unstyled status-summary mb-3">
									<li class="status-pill status-pill-success">Activa</li>
									<li class="status-pill">Ultima edicion hace 2h (mock)</li>
								</ul>
								<ul class="list-unstyled status-checklist mb-4">
									<li>Categorias conectadas</li>
									<li>Preguntas activas disponibles</li>
								</ul>
								<hr>
								<p class="text-uppercase small text-muted mb-2">Como se juega</p>
								<ol class="how-it-works pl-3 mb-0">
									<li>El jugador se registra una sola vez.</li>
									<li>Al entrar ve casillas de preguntas (ej. 8) pero cada casilla oculta su valor en puntos hasta el momento de responder.</li>
									<li>Antes de revelar la pregunta apuesta entre {{ triviaConfig.pointsMin }} y {{ triviaConfig.pointsMax }} puntos; el sistema asigna al azar el valor real de esa casilla.</li>
									<li>Solo se define el puntaje final objetivo; gana quien alcanza o supera ese objetivo antes de agotar las casillas.</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-primary btn-lg px-4" @click="submitTrivia" :disabled="saving">
				<span v-if="saving"><i class="feather icon-loader mr-1"></i> Guardando…</span>
				<span v-else>{{ submitLabel }}</span>
			</button>
		</div>
	</div>
</template>

<script>
import axios from 'axios';

export default {
	name: 'TriviaDynamicDesigner',
	props: {
		initialCategories: {
			type: Array,
			default: () => []
		},
		categoriesIndexUrl: {
			type: String,
			default: '/marketing/dinamica/trivia/question-categories'
		},
		categoriesCreateUrl: {
			type: String,
			default: '/marketing/dinamica/trivia/question-categories/create'
		},
		storeUrl: {
			type: String,
			default: '/marketing/dinamica/trivia'
		},
		redirectUrl: {
			type: String,
			default: '/marketing/dinamica/create'
		},
		mode: {
			type: String,
			default: 'create'
		},
		initialRegistrationConfig: {
			type: Object,
			default: () => ({})
		},
		initialTriviaConfig: {
			type: Object,
			default: () => ({})
		},
		initialGameBlocks: {
			type: Array,
			default: () => []
		},
		dinamicaId: {
			type: [Number, String],
			default: null
		}
	},
	data() {
		return {
			categories: [],
			isCategoryPickerOpen: false,
			registrationConfig: this.buildRegistrationConfig(this.initialRegistrationConfig),
			triviaConfig: this.buildTriviaConfig(this.initialTriviaConfig),
			gameBlocks: this.hydrateGameBlocks(this.initialGameBlocks),
			saving: false,
			errors: {}
		};
	},
	computed: {
		activeCategories() {
			return this.categories.filter(category => category.status === 'active');
		},
		categoryOptions() {
			return this.activeCategories.map(category => ({
				id: category.id,
				name: category.name,
				questions: category.questions ?? 0
			}));
		},
		usedCategoryIds() {
			return this.gameBlocks
				.map(block => this.normalizeCategoryId(block.categoryId))
				.filter(Boolean);
		},
		availableCategoryOptions() {
			const usedSet = new Set(this.usedCategoryIds);
			return this.categoryOptions.filter(option => !usedSet.has(this.normalizeCategoryId(option.id)));
		},
		categoryPickerLabel() {
			return this.availableCategoryOptions.length ? 'Agregar bloque' : 'Sin categorias activas';
		},
		isEditMode() {
			return this.mode === 'edit' || Boolean(this.dinamicaId);
		},
		submitLabel() {
			return this.isEditMode ? 'Actualizar trivia' : 'Crear trivia';
		}
	},
	created() {
		this.categories = this.normalizeCategories(this.initialCategories);
	},
	watch: {
		initialCategories: {
			handler(value) {
				this.categories = this.normalizeCategories(value);
			},
			deep: true
		},
		initialRegistrationConfig: {
			handler(value) {
				this.registrationConfig = this.buildRegistrationConfig(value);
			},
			deep: true
		},
		initialTriviaConfig: {
			handler(value) {
				this.triviaConfig = this.buildTriviaConfig(value);
			},
			deep: true
		},
		initialGameBlocks: {
			handler(value) {
				this.gameBlocks = this.hydrateGameBlocks(value);
				this.syncGameBlockOrder();
			},
			deep: true
		}
	},
	methods: {
		normalizeCategoryId(value) {
			if (value === '' || value === null || value === undefined) {
				return null;
			}
			return String(value);
		},
		buildRegistrationConfig(config = {}) {
			const defaults = {
				participantsLimit: null,
				timeLimitMinutes: null,
				closingDateTime: null,
				closingTime: null
			};
			const merged = {
				...defaults,
				...(config || {})
			};
			const sanitizePositiveInt = value => {
				if (value === '' || value === undefined || value === null) {
					return null;
				}
				const parsed = parseInt(value, 10);
				return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
			};
			merged.participantsLimit = sanitizePositiveInt(merged.participantsLimit);
			merged.timeLimitMinutes = sanitizePositiveInt(merged.timeLimitMinutes);
			const closingTime = typeof merged.closingTime === 'string' ? merged.closingTime.trim() : '';
			const derivedTime = closingTime.length
				? closingTime
				: this.extractTimeComponent(merged.closingDateTime);
			merged.closingTime = derivedTime || null;

			if (merged.closingTime) {
				merged.closingDateTime = this.combineDateAndTime(merged.closingDateTime, merged.closingTime);
			} else {
				merged.closingDateTime = null;
			}
			return merged;
		},
		buildTriviaConfig(config = {}) {
			const defaults = {
				name: '',
				description: '',
				slug: '',
				pointsMin: 1,
				pointsMax: 10
			};
			const merged = {
				...defaults,
				...(config || {})
			};
			const parseNumeric = value => {
				if (value === '' || value === null || value === undefined) {
					return null;
				}
				const parsed = parseInt(value, 10);
				return Number.isFinite(parsed) ? parsed : null;
			};
			const parsedMin = parseNumeric(merged.pointsMin);
			const parsedMax = parseNumeric(merged.pointsMax);
			merged.pointsMin = parsedMin ?? defaults.pointsMin;
			merged.pointsMax = parsedMax ?? defaults.pointsMax;
			return merged;
		},
		hydrateGameBlocks(blocks = []) {
			if (!Array.isArray(blocks) || !blocks.length) {
				return [];
			}
			return blocks.map((block, index) => this.createGameBlock({
				id: block.id ?? block.blockId ?? block.clientId ?? block.uuid,
				title: block.title ?? `Bloque ${index + 1}`,
				categoryId: block.categoryId ?? block.category_id ?? '',
				order: block.order ?? index + 1,
				isActive: typeof block.isActive === 'boolean'
					? block.isActive
					: (typeof block.is_active === 'boolean' ? block.is_active : true)
			}));
		},
		normalizeCategories(input) {
			if (!Array.isArray(input) || input.length === 0) {
				return [];
			}
			return input.map((category, index) => ({
				id: category.id || index + 1,
				name: category.name || category.title || `Categoria ${index + 1}`,
				slugline: category.slugline || 'Banco reutilizable',
				description: category.description || category.descripcion || 'Sin descripcion',
				status: category.is_active === false || category.active === false ? 'inactive' : 'active',
				questions: category.questions_count || category.questions || 0
			}));
		},
		getCategoryQuestionCount(categoryId) {
			if (!categoryId) {
				return null;
			}
			const option = this.categoryOptions.find(candidate => candidate.id === categoryId);
			return option && typeof option.questions === 'number' ? option.questions : null;
		},
		goToCategoriesList() {
			window.location.href = this.categoriesIndexUrl;
		},
		goToCategoriesCreate() {
			window.location.href = this.categoriesCreateUrl;
		},
		toggleCategoryPicker() {
			if (!this.availableCategoryOptions.length) {
				this.isCategoryPickerOpen = false;
				return;
			}
			this.isCategoryPickerOpen = !this.isCategoryPickerOpen;
		},
		addBlockFromCategory(option) {
			if (!option) {
				return;
			}
			this.addGameBlock({
				categoryId: option.id,
				title: option.name
			});
			this.isCategoryPickerOpen = false;
		},
		addGameBlock(config = {}) {
			const preparedConfig = { ...config };
			if (preparedConfig.categoryId && this.isCategoryAlreadyUsed(preparedConfig.categoryId)) {
				preparedConfig.categoryId = '';
			}
			const nextOrder = this.gameBlocks.length + 1;
			const block = this.createGameBlock({
				order: preparedConfig.order ?? nextOrder,
				categoryId: preparedConfig.categoryId,
				title: preparedConfig.title,
				isActive: preparedConfig.isActive
			});
			this.gameBlocks = [...this.gameBlocks, block];
		},
		getCategoryOptionsForBlock(block) {
			if (!block) {
				return this.categoryOptions;
			}
			const usedByOthers = new Set(
				this.gameBlocks
					.filter(candidate => candidate.id !== block.id)
					.map(candidate => this.normalizeCategoryId(candidate.categoryId))
					.filter(Boolean)
			);
			return this.categoryOptions.filter(option => {
				const optionId = this.normalizeCategoryId(option.id);
				return optionId === this.normalizeCategoryId(block.categoryId) || !usedByOthers.has(optionId);
			});
		},
		isCategoryAlreadyUsed(categoryId, excludeBlockId = null) {
			const normalizedId = this.normalizeCategoryId(categoryId);
			if (!normalizedId) {
				return false;
			}
			return this.gameBlocks.some(block => {
				if (excludeBlockId && block.id === excludeBlockId) {
					return false;
				}
				return this.normalizeCategoryId(block.categoryId) === normalizedId;
			});
		},
		removeGameBlock(blockId) {
			this.gameBlocks = this.gameBlocks.filter(block => block.id !== blockId);
			this.syncGameBlockOrder();
		},
		syncGameBlockOrder() {
			this.gameBlocks = [...this.gameBlocks]
				.sort((a, b) => a.order - b.order)
				.map((block, index) => ({
					...block,
					order: index + 1
				}));
		},
		createGameBlock(config = {}) {
			const parsedOrder = typeof config.order === 'number'
				? config.order
				: (config.order ? parseInt(config.order, 10) : null);
			const order = Number.isFinite(parsedOrder) && parsedOrder > 0 ? parsedOrder : 1;
			return {
				id: config.id ?? this.generateId(),
				title: config.title && config.title.length ? config.title : `Bloque ${order}`,
				categoryId: config.categoryId ?? '',
				order,
				isActive: typeof config.isActive === 'boolean' ? config.isActive : true
			};
		},
		generateId() {
			return Date.now() + Math.floor(Math.random() * 1000);
		},
		serializeGameBlocks() {
			return this.gameBlocks.map((block, index) => {
				const hasCategory = block.categoryId !== '' && block.categoryId !== null && block.categoryId !== undefined;
				const resolvedOrder = typeof block.order === 'number'
					? block.order
					: (block.order ? parseInt(block.order, 10) : null);
				return {
					title: block.title,
					categoryId: hasCategory ? Number(block.categoryId) : null,
					order: Number.isFinite(resolvedOrder) && resolvedOrder > 0 ? resolvedOrder : index + 1,
					isActive: Boolean(block.isActive)
				};
			});
		},
		handleClosingTimeChange() {
			this.registrationConfig.closingDateTime = this.combineDateAndTime(
				this.registrationConfig.closingDateTime,
				this.registrationConfig.closingTime
			);
		},
		extractTimeComponent(value) {
			if (!value || typeof value !== 'string') {
				return null;
			}
			const match = value.match(/T(\d{2}:\d{2})/);
			return match ? match[1] : null;
		},
		combineDateAndTime(existingDateTime, timeValue) {
			if (!timeValue) {
				return null;
			}
			const sanitized = timeValue.length === 5 && timeValue.includes(':') ? `${timeValue}` : null;
			if (!sanitized) {
				return null;
			}
			if (existingDateTime && typeof existingDateTime === 'string') {
				return existingDateTime.replace(/T\d{2}:\d{2}.*/, `T${sanitized}`);
			}
			const now = new Date();
			const datePart = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
				.toISOString()
				.slice(0, 10);
			return `${datePart}T${sanitized}`;
		},
		async submitTrivia() {
			if (!this.gameBlocks.length) {
				await this.showAlert('Agrega al menos un bloque de juego para continuar.', 'error');
				return;
			}

			this.saving = true;
			this.errors = {};
			this.syncGameBlockOrder();

			try {
				const registrationConfig = {
					...this.registrationConfig,
					closingDateTime: this.combineDateAndTime(
						this.registrationConfig.closingDateTime,
						this.registrationConfig.closingTime
					)
				};

				const payload = {
					dinamicaId: this.dinamicaId,
					registrationConfig,
					triviaConfig: this.triviaConfig,
					gameBlocks: this.serializeGameBlocks()
				};
				const { data } = await axios.post(this.storeUrl, payload);

				await this.showAlert(data.message || 'Trivia guardada correctamente.', 'success');
				const redirectTarget = data.redirect || this.redirectUrl;
				if (redirectTarget) {
					window.location.href = redirectTarget;
				}
			} catch (error) {
				if (error.response?.data?.errors) {
					this.errors = error.response.data.errors;
				}
				const message = error.response?.data?.message || 'No se pudo guardar la trivia. Revisa los datos e intenta de nuevo.';
				await this.showAlert(message, 'error');
			} finally {
				this.saving = false;
			}
		},
		showAlert(message, type = 'success') {
			const globalSwal = typeof window !== 'undefined' ? window.Swal : null;
			const swal = this.$swal || globalSwal;
			if (swal && typeof swal.fire === 'function') {
				return swal.fire({
					title: type === 'success' ? 'Listo' : 'Revisa los datos',
					text: message,
					icon: type,
					confirmButtonText: 'Entendido'
				});
			}

			alert(message);
			return Promise.resolve();
		}
	}
};
</script>

<style scoped>
.trivia-dynamic-designer .border-lg-right {
	border-right: 1px solid #eef1f4;
}

@media (max-width: 991.98px) {
	.trivia-dynamic-designer .border-lg-right {
		border-right: none;
	}
}

.trivia-dynamic-designer .header-actions > * {
	margin-left: 0;
}

.trivia-dynamic-designer .header-actions > * + * {
	margin-left: 0.5rem;
}

.insight-panel {
	background: #fdfdfd;
}

.registration-config {
	border: 1px solid #e4e7ec;
	border-radius: 12px;
	padding: 1.25rem 1.5rem;
	background: #fff;
	box-shadow: 0 8px 20px rgba(15, 30, 65, 0.04);
}

.status-summary {
	display: flex;
	flex-wrap: wrap;
	gap: 0.35rem;
	margin-bottom: 0;
}

.status-pill {
	background: #eef1f4;
	border-radius: 999px;
	font-size: 0.75rem;
	padding: 0.15rem 0.75rem;
	color: #5d6478;
}

.status-pill-success {
	background: #e4f6ef;
	color: #1f8a5b;
}

.status-checklist li {
	padding: 0.2rem 0;
	font-size: 0.9rem;
}

.how-it-works li {
	margin-bottom: 0.25rem;
	line-height: 1.4;
}

.game-blocks {
	border-top: 1px solid #f2f4f7;
	padding-top: 1.5rem;
}

.game-block-list {
	display: flex;
	flex-direction: column;
	gap: 0.9rem;
}

.game-block-card {
	border: 1px solid #eef1f4;
	border-radius: 12px;
	padding: 1rem 1.2rem;
	background: #fff;
	box-shadow: 0 10px 25px rgba(15, 30, 65, 0.05);
}

.game-block-head {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 0.75rem;
}

.game-block-controls {
	display: flex;
	align-items: flex-start;
}

.game-block-remove {
	border: none;
	background: transparent;
	color: #a0a6b8;
	font-weight: 600;
	font-size: 1.2rem;
	padding: 0 0.25rem;
	line-height: 1;
	cursor: pointer;
}

.game-block-remove:hover {
	color: #f05b5b;
}

.game-block-label {
	text-transform: uppercase;
	font-size: 0.7rem;
	letter-spacing: 0.08em;
	color: #9399ad;
}

.game-block-empty {
	border: 1px dashed #dfe3eb;
	border-radius: 12px;
	padding: 1rem;
	text-align: center;
}

.category-picker {
	border-radius: 12px;
	background: #f8fafc;
}

.category-picker .btn {
	border: 1px solid #dfe3eb;
}
</style>

