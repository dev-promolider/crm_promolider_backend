<template>
    <div class="game-container">
        <template v-if="!loadingCardGame">
            <template v-if="!isGameFinish">
                <h1 class="title-cards">Juego de Cartas</h1>
                <div class="controls-container">
                    <div class="control-item">
                        <span class="btn btn-cards d-flex align-items-center gap-2">
                            Turnos:
                            <span class="badge" :class="finish ? 'badge-cards' : 'badge-light'">{{ turns }}</span>
                        </span>
                    </div>
                    <div class="control-item">
                        <span class="btn btn-cards d-flex align-items-center gap-2">
                            Tiempo Total:
                            <span class="badge" :class="finish ? 'badge-cards' : 'badge-light'">
                                {{ min }} : {{ sec }}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="contenedor-cards" :data-cards="memoryCards.length">
                    <div v-for="(card, index) in memoryCards" :key="index" class="flip-container"
                        :class="{ flipped: card.isFlipped, matched: card.isMatched }" @click="flipCard(card)">
                        <div class="memorycard">
                            <div class="front shadow-sm">
                                <i class="fas fa-question"></i>
                            </div>
                            <div class="back shadow-sm">
                                <img :src="card.img" :alt="card.alt" class="card-image" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="botton-start">
                    <button class="btn btn-cards transition-all" @click="_gettingStart" :disabled="isActiveReady">
                        Empezar
                    </button>
                    <button class="btn btn-dark transition-all" @click="resetGame">Reiniciar</button>
                </div>
            </template>

            <Transition name="bounce">
                <template v-if="isGameFinish">
                    <div class="game-finish-card">
                        <v-card elevation="10" color="success" class="text-center" max-width="500">
                            <v-icon class="ma-5" size="100" color="white">mdi-check-circle-outline</v-icon>
                            <v-card-text class="text-h3 font-weight-bold white--text">
                                Ganaste
                                <span v-if="sumPoint">{{ sumPoint }}</span>
                                <v-progress-circular v-if="!sumPoint" indeterminate></v-progress-circular>
                                puntos
                            </v-card-text>
                            <v-card-actions class="justify-center pb-4">
                                <v-btn color="black" class="white--text" @click="comeBack"> Regresar </v-btn>
                            </v-card-actions>
                        </v-card>
                    </div>
                </template>
            </Transition>
        </template>

        <template v-else>
            <div class="loading-container">
                <v-progress-circular indeterminate color="success" size="52"></v-progress-circular>
            </div>
        </template>
    </div>
</template>

<script>
import Vue from 'vue';
import _ from 'lodash';

export default {
    props: {
        datos: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            cards: [],
            isGameFinish: false,
            isActiveReady: false,
            gettingStart: false,
            memoryCards: [],
            flippedCards: [],
            finish: false,
            turns: 0,
            totalTime: {
                minutes: 0,
                seconds: 0,
            },
            loadingCardGame: true,
            data: {},
            productor_id: null,
            sumPoint: null,
            course_game_id: this.datos.game.id,
        };
    },
    computed: {
        sec() {
            return this.totalTime.seconds < 10 ? '0' + this.totalTime.seconds : this.totalTime.seconds;
        },
        min() {
            return this.totalTime.minutes < 10 ? '0' + this.totalTime.minutes : this.totalTime.minutes;
        },
    },
    async created() {
        await this.createCards();
        this.cards.forEach((card) => {
            Vue.set(card, 'isFlipped', false);
            Vue.set(card, 'isMatched', false);
        });

        this.memoryCards = _.shuffle(
            this.memoryCards.concat(_.cloneDeep(this.cards), _.cloneDeep(this.cards))
        );
    },
    methods: {
        comeBack() {
            this.$router.back();
        },
        async createCards() {
            this.data = this.datos;
            let numeroCartas = this.data.detail.length;
            let detailGame = this.data.detail;

            for (let i = 0; i < numeroCartas; i++) {
                this.cards.push({
                    img: detailGame[i].img,
                    alt: detailGame[i].name,
                    name: i,
                });
            }
            this.loadingCardGame = false;
        },
        flipCard(card) {
            if (this.gettingStart) {
                if (card.isMatched || card.isFlipped || this.flippedCards.length === 2) return;
                if (!this.start) {
                    this._startGame();
                }

                card.isFlipped = true;

                if (this.flippedCards.length < 2) this.flippedCards.push(card);
                if (this.flippedCards.length === 2) this._match(card);
            }
        },
        _match() {
            this.turns++;
            if (this.flippedCards[0].name === this.flippedCards[1].name) {
                setTimeout(() => {
                    this.flippedCards.forEach((card) => (card.isMatched = true));
                    this.flippedCards = [];

                    if (this.memoryCards.every((card) => card.isMatched === true)) {
                        this.finish = true;
                        clearInterval(this.interval);
                        this.sumPoint = 10;
                        this.isGameFinish = true;
                        this.totalTime = { minutes: 0, seconds: 0 };
                        this.turns = 0;
                    }
                }, 400);
            } else {
                setTimeout(() => {
                    this.flippedCards.forEach((card) => {
                        card.isFlipped = false;
                    });
                    this.flippedCards = [];
                }, 800);
            }
        },
        _startGame() {
            this._tick();
            this.interval = setInterval(this._tick, 1000);
            this.start = true;
            this.isActiveReady = true;
        },
        _tick() {
            if (this.totalTime.seconds !== 59) {
                this.totalTime.seconds++;
                return;
            }
            this.totalTime.minutes++;
            this.totalTime.seconds = 0;
        },
        _gettingStart() {
            this.gettingStart = true;
            this._startGame();
        },
        resetGame() {
            clearInterval(this.interval);

            this.cards.forEach((card) => {
                Vue.set(card, 'isFlipped', false);
                Vue.set(card, 'isMatched', false);
            });

            setTimeout(() => {
                this.memoryCards = [];
                this.memoryCards = _.shuffle(
                    this.memoryCards.concat(_.cloneDeep(this.cards), _.cloneDeep(this.cards))
                );
                this.totalTime.minutes = 0;
                this.totalTime.seconds = 0;
                this.start = false;
                this.finish = false;
                this.turns = 0;
                this.flippedCards = [];
                this.isActiveReady = false;
            }, 600);
        },
    },
};
</script>

<style scoped>
/* Estilos principales */
.game-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
}

.title-cards {
    padding-top: 15px;
    font-size: 35px;
    color: #2e7d32;
    text-align: center;
    margin-bottom: 1.5rem;
}

/* Controles del juego */
.controls-container {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.btn-cards {
    background-color: #2e7d32;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    transition: background-color 0.3s ease;
}

.btn-cards:hover {
    background-color: #1b5e20;
}

.badge {
    background-color: white;
    color: #2e7d32;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

/* Grid de cartas */
.contenedor-cards {
    display: grid;
    gap: 1rem;
    margin: 0 auto;
    padding: 0 1rem;
    max-width: 800px;
}

/* Ajuste dinámico de columnas basado en número de cartas */
.contenedor-cards[data-cards='4'] {
    grid-template-columns: repeat(2, 1fr);
}

.contenedor-cards[data-cards='6'] {
    grid-template-columns: repeat(3, 1fr);
}

.contenedor-cards[data-cards='8'] {
    grid-template-columns: repeat(4, 1fr);
}

.contenedor-cards[data-cards='10'] {
    grid-template-columns: repeat(5, 1fr);
}

/* Estilos de las cartas */
.flip-container {
    aspect-ratio: 3/4;
    perspective: 1000px;
    cursor: pointer;
}

.memorycard {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
}

.front,
.back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.front {
    background: #1b5e20;
    color: white;
    font-size: 2rem;
}

.back {
    background: white;
    transform: rotateY(180deg);
    padding: 0.5rem;
}

.card-image {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

/* Estados de la carta */
.flip-container.flipped .memorycard {
    transform: rotateY(180deg);
}

.matched {
    opacity: 0.6;
}

/* Botones de control */
.botton-start {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn-dark {
    background-color: #1a1a1a;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.3s ease;
}

.btn-dark:hover {
    background-color: #333;
}

/* Media Queries */
@media (max-width: 768px) {
    .contenedor-cards {
        gap: 0.75rem;
        padding: 0 0.5rem;
    }

    .contenedor-cards[data-cards='8'],
    .contenedor-cards[data-cards='10'] {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 480px) {
    .title-cards {
        font-size: 28px;
    }

    .controls-container {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .contenedor-cards {
        gap: 0.5rem;
    }

    .contenedor-cards[data-cards='6'],
    .contenedor-cards[data-cards='8'],
    .contenedor-cards[data-cards='10'] {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
