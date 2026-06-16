<template>
  <div>
    <div class="card">
      <div class="card-header">
        <div class="col-md-6">
          <h3>Texto</h3>
        </div>
        <div v-if="endCharge" class="col-md-6">
          <button v-if="isNull" type="button" class="btn btn-link float-right" @click="showAddModal">
            (+) Agregar Item4
          </button>
          <button v-else type="button" class="btn btn-link float-right" @click="showAddModal">
            (o) Editar item
          </button>
        </div>
      </div>

      <div class="card-body">
        <ol class="list-group">
          <div>
            <h4>
              <span> {{ textSaved }} </span>
            </h4>
            <div class="mt-1">
              <el-tag v-for="word in wordsSaved" :key="word.word" :type="[word.isHidden ? 'danger' : 'success']"
                class="mx-1 mb-1 pe-auto" effect="dark" round>
                {{ word.word }}
              </el-tag>
            </div>
          </div>
        </ol>
      </div>
      <div id="create-item-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Agregar texto</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div>
                <label for="" class="form-label">Ingrese el texto</label>
                <textarea v-if="showSelectWords" v-model="text" cols="30" rows="5" disabled
                  class="form-control"></textarea>
                <textarea v-else v-model="text" cols="30" rows="5" class="form-control"></textarea>
                <button @click="next()" v-if="(text != '') & !showSelectWords" class="btn btn-warning my-1">
                  Seleccionar palabras
                </button>
                <button v-if="showSelectWords" @click="next()" class="btn btn-warning my-1">
                  Editar texto
                </button>
              </div>
              <br />
              <div v-if="showSelectWords">
                <div>
                  <p><strong>Seleccione las palabras que se ocultarán</strong></p>
                  <el-tag v-for="word in textWords" :key="word.word" style="cursor: pointer"
                    :type="[word.isHidden ? 'danger' : 'success']" class="mx-1 mb-1 pe-auto" effect="dark" round
                    @click="word.isHidden = word.isHidden ? false : true">
                    {{ word.word }}
                  </el-tag>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <slot name="footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" @click="saveQuestion()">
                  Guardar
                </button>
              </slot>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ModalComponent from '../../../components/common/ModalComponent';
import CreateItem from './item/create';

export default {
  props: {
    game: Object,
    detail: Object,
  },
  components: {
    'custom-modal': ModalComponent,
    'create-item': CreateItem,
  },
  data() {
    return {
      text: '',
      textWords: [],
      showSelectWords: false,
      textSaved: '',
      wordsSaved: [],
      endCharge: false,
      isNull: false,
    };
  },
  watch: {
    text: function (newValue) {
      var lastChar = newValue.slice(-1);
      var lastChar2 = newValue.slice(-2);
      if (lastChar == ' ' && lastChar2 == '  ') {
        this.text = this.text.substring(0, this.text.length - 1);
      } else {
        this.textWords = [];
        var aux = newValue.split(' ');
        aux.forEach((element) => {
          var array = {
            word: element,
            isHidden: false,
          };
          this.textWords.push(array);
        });
      }
    },
  },
  created() {
    this.listItems();
  },
  methods: {
    next() {
      this.showSelectWords = this.showSelectWords ? false : true;
      if (this.showSelectWords) {
        if (this.text.slice(-1) == ' ') {
          this.text = this.text.substring(0, this.text.length - 1);
        }
      }
    },
    async saveQuestion() {
      const formdata = {
        text: this.text,
        data: JSON.stringify(this.textWords),
        game_id: this.game.id,
      };
      if (this.validateFields()) {
        await axios.post(`/course/game/item/complete-text/store`, formdata);
        $('#create-item-modal').modal('hide');
        this.wordsSaved = this.textWords;
        this.textSaved = this.text;
        this.showSelectWords = false;
        location.reload();
      }
    },
    clearFields() {
      this.text = '';
      this.textWords = [];
      this.showSelectWords = false;
    },
    validateFields() {
      if (this.textWords.length < 15) {
        this.$message.warning('El texto debe tener al menos 15 palabras');
        return false;
      }
      var counterHiddenWords = 0;
      this.textWords.forEach((obj) => {
        if (obj.isHidden) {
          counterHiddenWords = counterHiddenWords + 1;
        }
      });
      if (counterHiddenWords < 4) {
        this.$message.warning('Debe haber al menos 4 palabras ocultas');
        return false;
      }
      return true;
    },
    showAddModal() {
      $('#create-item-modal').modal('show');
    },

    async listItems() {
      await axios
        .get(`/course/game/item/${this.game.id}/list`)
        .then((response) => {
          if (response.data == null) {
            this.isNull = true;
          } else {
            this.isNull = false;
            if (response.data != null) {
              this.textSaved = response.data.data.text;
              this.wordsSaved = response.data.data.data;
              this.text = response.data.data.text;
              this.textWords = response.data.data.data;
            }
          }
        })
        .catch((error) => {
          console.log(error);
        });
      this.endCharge = true;
    },
  },
};
</script>
