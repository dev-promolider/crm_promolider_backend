<template>
  <div>
    <div class="card">
      <div class="card-header">
        <div class="col-md-6">
          <h3>
            Lista de Items <span> ({{ this.items.length }}) </span>
          </h3>
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-link float-right" @click="showAddModal">
            (+) Agregar Item
          </button>
        </div>
      </div>

      <div class="card-body">
        <ol class="list-group">
          <div v-if="this.items.length == 0" class="text-center mt-3 mb-3">
            <h4 class="d-inline">
              <span> {{ message }} </span>
            </h4>
          </div>

          <div v-if="this.items.length > 0">
            <div v-for="(item, index) in this.items" class="p-2">
              <div class="d-flex justify-content-between">
                <p class="m-0">
                  <strong>{{ item.question }}</strong>
                </p>
                <button class="btn btn-warning" @click="openEditModal(item, index)">Editar</button>
              </div>
              <p class="px-2 text-success py-0 m-0" v-if="item.answer == 0">
                {{ item.alternative1 }}
              </p>
              <p class="px-2 py-0 m-0" v-else>{{ item.alternative1 }}</p>
              <p class="px-2 text-success py-0 m-0" v-if="item.answer == 1">
                {{ item.alternative2 }}
              </p>
              <p class="px-2 py-0 m-0" v-else>{{ item.alternative2 }}</p>
              <p class="px-2 text-success py-0 m-0" v-if="item.answer == 2">
                {{ item.alternative3 }}
              </p>
              <p class="px-2 py-0 m-0" v-else>{{ item.alternative3 }}</p>
              <p class="px-2 text-success py-0 m-0" v-if="item.answer == 3">
                {{ item.alternative4 }}
              </p>
              <p class="px-2 py-0 m-0" v-else>{{ item.alternative4 }}</p>
            </div>
          </div>
        </ol>
      </div>
      <!-- <custom-modal id="create-item-modal" color="primary">
        <template #title>Crear Item</template>
<template #body>
          <create-item :game="game" @listenStore="listItems(game.id)"></create-item>
        </template>
</custom-modal> -->
      <div id="create-item-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 v-if="updateOption" class="modal-title">Editar pregunta</h5>
              <h5 v-else class="modal-title">Agregar pregunta</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <label for="" class="form-label">Pregunta</label>
              <input type="text" v-model="question" class="form-control" />
              <br />
              <label for="" class="form-label">Alternativa 1</label>
              <input type="text" v-model="alternative1" class="form-control" />
              <label for="" class="form-label">Alternativa 2</label>
              <input type="text" v-model="alternative2" class="form-control" />
              <label for="" class="form-label">Alternativa 3</label>
              <input type="text" v-model="alternative3" class="form-control" />
              <label for="" class="form-label">Alternativa 4</label>
              <input type="text" v-model="alternative4" class="form-control" />
              <label for="">Alternativa correcta</label>
              <select name="" id="" v-model="answer" class="form-control">
                <option value="" disabled>Seleccione</option>
                <option value="0">Alternativa 1</option>
                <option value="1">Alternativa 2</option>
                <option value="2">Alternativa 3</option>
                <option value="3">Alternativa 4</option>
              </select>
            </div>
            <div class="modal-footer">
              <slot name="footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button v-if="updateOption" type="button" class="btn btn-primary" @click="updateQuestion()">
                  Guardar
                </button>
                <button v-else type="button" class="btn btn-primary" @click="saveQuestion()">
                  Guardar
                </button>
              </slot>
            </div>
          </div>
        </div>
      </div>

      <custom-modal id="delete-item-modal" color="danger">
        <template #title>Borrar Pregunta <u> {{ mutableItem.name }} </u></template>
        <template #body>¿Seguro que desea eliminar este item?</template>
        <template #footer>
          <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="deleteItem">Eliminar</button>
        </template>
      </custom-modal>
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
      message: null,
      loading: false,
      isSubmitting: false,
      mutableDetail: {},
      mutableItem: {},
      items: [],
      question: '',
      alternative1: '',
      alternative2: '',
      alternative3: '',
      alternative4: '',
      answer: '',
      selectedData: [],
      updateOption: false,
      position: null,
    };
  },
  created() {
    if (this.detail == null) {
      this.mutableDetail = {};
    } else {
      this.listItems();
    }
  },
  methods: {
    openEditModal(data, index) {
      console.log(index);
      this.updateOption = true;
      this.question = data.question;
      this.alternative1 = data.alternative1;
      this.alternative2 = data.alternative2;
      this.alternative3 = data.alternative3;
      this.alternative4 = data.alternative4;
      this.answer = data.answer;
      this.position = index;

      $('#create-item-modal').modal('show');
    },
    async updateQuestion() {
      if (this.validateFields()) {
        const formdata = {
          game_id: this.game.id,
          position: this.position,
          data: {
            answer: this.answer,
            question: this.question,
            alternative1: this.alternative1,
            alternative2: this.alternative2,
            alternative3: this.alternative3,
            alternative4: this.alternative4,
          },
        };
        const data = await axios.post(`/course/game/item/owl/update`, formdata);
        $('#create-item-modal').modal('hide');
        this.listItems();
        this.updateOption = false;
        this.clearFields();
      }
    },
    async saveQuestion() {
      const formdata = {
        game_id: this.game.id,
        data: {
          question: this.question,
          alternative1: this.alternative1,
          alternative2: this.alternative2,
          alternative3: this.alternative3,
          alternative4: this.alternative4,
          answer: this.answer,
        },
      };
      if (this.validateFields()) {
        await axios.post(`/course/game/item/owl/store`, formdata);
        this.listItems();
        this.clearFields();
      }
    },
    clearFields() {
      (this.question = ''),
        (this.alternative1 = ''),
        (this.alternative2 = ''),
        (this.alternative3 = ''),
        (this.alternative4 = ''),
        (this.answer = '');
    },
    validateFields() {
      if (this.question == '') {
        this.$message.warning('Ingrese una pregunta válida');
        return false;
      }
      if (this.alternative1 == '') {
        this.$message.warning('La alternativa 1 está vacía');
        return false;
      }
      if (this.alternative2 == '') {
        this.$message.warning('La alternativa 2 está vacía');
        return false;
      }
      if (this.alternative3 == '') {
        this.$message.warning('La alternativa 3 está vacía');
        return false;
      }
      if (this.alternative4 == '') {
        this.$message.warning('La alternativa 4 está vacía');
        return false;
      }
      if (this.answer == '') {
        this.$message.warning('Indique la alternativa correcta');
        return false;
      }
      return true;
    },
    showAddModal() {
      this.updateOption = false;
      this.clearFields();
      $('#create-item-modal').modal('show');
    },

    showDeleteItemModal(value, index) {
      this.mutableItem = value;
      this.mutableItem.index = index;
      $('#delete-item-modal').modal('show');
    },

    listItems() {
      axios
        .get(`/course/game/item/${this.game.id}/list`)
        .then((response) => {
          if (response.data.error) {
            this.message = 'No hay items registrados';
          } else {
            this.items = response.data.data.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    async deleteItem() {
      const response = await axios.delete(
        `/course/game/item/${this.game.id}/${this.mutableItem.index}/delete`
      );
      if (response.status == 200) {
        this.$message.success('Item eliminado correctamente');
        this.listItems();
      } else {
        await this.$message.warning('Error al validar datos');
      }

      $('#delete-item-modal').modal('hide');
    },
  },
};
</script>
