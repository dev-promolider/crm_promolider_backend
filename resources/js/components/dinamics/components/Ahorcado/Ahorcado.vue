<template>
  <div>
    <Transition name="bounce">
      <template v-if="!isGameFinish">
        <div class="container">
          <div class="card bg-success text-white mx-auto mt-4 text-center animate__animated animate__fadeIn"
            style="max-width: 90%; width: 500px">
            <div class="card-body">
              <i class="fas fa-check-circle fa-5x my-3"></i>
              <h2 class="display-4 fw-bold">
                Ganaste
                <span v-if="sumPoint">{{ sumPoint }}</span>
                <div v-if="!sumPoint" class="spinner-border" role="status"></div>
                puntos
              </h2>
              <button class="btn btn-dark btn-lg my-3" @click="comeBack">Regresar</button>
            </div>
          </div>
        </div>
      </template>
    </Transition>

    <div v-if="isGameFinish" class="container-fluid py-1">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <div class="game-container bg-white rounded-3 shadow-sm p-1">
            <h2 class="text-center mb-1">{{ datos.game.title }}</h2>
            <p class="text-center text-secondary fw-bold mb-1">
              RECUERDA QUE SOLO TIENES 5 INTENTOS
            </p>

            <!-- Palabra a adivinar -->
            <div class="word-container mb-1">
              <div class="letters-container">
                <button v-for="(item, index) in palabra_escrita" :key="index" class="btn btn-success letter-box">
                  {{ item }}
                </button>
              </div>
            </div>

            <div class="text-center text-secondary fw-bold mb-1">
              Pista: {{ datos.detail.description }}
            </div>

            <!-- Teclado -->
            <div class="keyboard-container mb-1">
              <div class="letters-container">
                <button v-for="(letra, index) in letras" :key="index" @click="comparar(letra, index)"
                  :disabled="botones[index]" class="btn keyboard-key" :class="{
                    'btn-success': color_botones[index] === 'verde',
                    'btn-danger': color_botones[index] === 'rojo',
                    'btn-light': !color_botones[index],
                  }">
                  {{ letra }}
                </button>
              </div>
            </div>

            <!-- Contadores -->
            <div class="row justify-content-center">
              <div class="col-12 col-sm-6 col-md-4">
                <div class="mb-1">
                  <label class="form-label text-primary">Aciertos:</label>
                  <input type="text" class="form-control text-center" v-model="contador_aciertos" disabled />
                </div>
                <div class="mb-1">
                  <label class="form-label text-primary">Errores:</label>
                  <input type="text" class="form-control text-center" v-model="contador_errores" disabled />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Ahorcado',
  props: {
    datos: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      centeredStyle: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
      },
      isGameFinish: true,
      mostrar: true,
      game: true,
      win: false,
      lost: false,
      contador_aciertos: 0,
      contador_errores: 0,
      aleatorio: 0,
      palabra_escrita: [],
      botones: [],
      color_botones: [],
      letras: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ ',
      frutas: [],
      contadorFlag: 0,
      productor_id: 1,
      sumPoint: null,
      course_game_id: this.datos.game.id,
    };
  },
  methods: {
    comeBack() {
      this.$router.back();
    },
    senAnswers(value) {
      if (value.data) {
        this.sumPoint = 10;
      } else {
        this.sumPoint = 0;
      }
    },
    async generarAleatorio() {
      this.game = true;
      this.win = false;
      this.lost = false;
      this.palabra_escrita = [];
      this.contador_aciertos = 0;
      this.contador_errores = 0;
      this.botones = [];
      this.color_botones = [];
      this.frutas[0] = this.datos.detail.word.toLowerCase();

      this.aleatorio = Math.floor(Math.random() * this.frutas.length);

      // Modificación para manejar espacios
      for (var i = 0; i < this.frutas[this.aleatorio].length; i++) {
        if (this.frutas[this.aleatorio][i] === ' ') {
          this.palabra_escrita.push(' ');
          this.contador_aciertos++;
        } else {
          this.palabra_escrita.push(' ');
        }
      }

      return this.aleatorio;
    },
    comparar: function (caracter, posicion) {
      if (this.game) {
        this.contadorFlag = 0;

        this.botones[posicion] = true;

        for (let i = 0; i < this.palabra_generada.length; i++) {
          // Modificado para manejar espacios y ser case-insensitive
          if (caracter.toLowerCase() === this.palabra_generada[i].toLowerCase()) {
            this.palabra_escrita[i] = this.palabra_generada[i];
            this.contadorFlag++;
            this.contador_aciertos++;
          }
        }

        // No se encontró la letra
        if (this.contadorFlag == 0) {
          this.color_botones[posicion] = 'rojo';
          this.contador_errores++;
        } else {
          this.color_botones[posicion] = 'verde';
        }

        if (this.contador_aciertos == this.palabra_generada.length) {
          this.win = true;
          this.game = false;
          this.senAnswers({
            data: true,
            productor_id: this.productor_id,
            game_type: 'ahorcado',
            course_game_id: this.course_game_id,
          });
          this.isGameFinish = false;
        }

        if (this.contador_errores == 5) {
          this.lost = true;
          this.game = false;
          this.senAnswers({
            data: false,
            productor_id: this.productor_id,
            game_type: 'ahorcado',
            course_game_id: this.course_game_id,
          });
          this.isGameFinish = false;
        }
      }
    },
  },
  computed: {
    palabra_generada: function () {
      return this.frutas[this.aleatorio];
    },
  },
  created: function () {
    this.generarAleatorio();
  },
};
</script>

<style lang="scss" scoped>
.game-container {
  min-height: auto;
  background: rgb(253, 253, 253);

  @media (max-width: 768px) {
    min-height: auto;
    padding: 1rem;
  }
}

.letters-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 3px;
  /* Espaciado uniforme entre letras */
  padding: 10px;
}

.letter-box {
  width: 40px;
  height: 40px;
  padding: 0;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 3px solid darken(#198754, 10%);
  margin: 0;
  /* Eliminamos márgenes para que el gap funcione correctamente */

  @media (max-width: 576px) {
    width: 30px;
    height: 30px;
    font-size: 1rem;
  }
}

.keyboard-key {
  width: 40px;
  height: 40px;
  padding: 0;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
  margin: 0;
  /* Eliminamos márgenes para que el gap funcione correctamente */

  @media (max-width: 576px) {
    width: 35px;
    height: 35px;
    font-size: 0.9rem;
  }

  &:not(:disabled):hover {
    transform: scale(1.1);
  }

  &:disabled {
    opacity: 0.7;
  }
}

// Animaciones
.bounce-enter-active {
  animation: bounce-in 0.5s;
}

.bounce-leave-active {
  animation: bounce-in 0.5s reverse;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
  }

  50% {
    transform: scale(1.25);
  }

  100% {
    transform: scale(1);
  }
}
</style>
