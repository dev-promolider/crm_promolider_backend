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
          <div v-for="(item, index) in this.items" class="p-2">
            <div class="d-flex justify-content-between">
              <div>
                <p class="m-0"><strong>Pregunta: </strong>{{ item.question }}</p>
                <p class="m-0"><strong>Respuesta: </strong>{{ item.answer }}</p>
              </div>
              <div>
                <button class="btn btn-warning" @click="openEditModal(item, index)">Editar</button>
              </div>
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
              <label for="" class="form-label">Respuesta</label>
              <input type="text" v-model="answer" class="form-control" />
            </div>
            <div class="modal-footer">
              <slot name="footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                <button v-if="updateOption" type="button" class="btn btn-primary" @click="updateQuestion()">
                  Actualizar
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
      this.selectedData = data;
      this.position = index;
      this.updateOption = true;
      this.answer = data.answer;
      this.question = data.question;
      $('#create-item-modal').modal('show');
    },
    async updateQuestion() {
      if (this.validateFields()) {
        const formdata = {
          game_id: this.game.id,
          position: this.position,
          data: {
            question: this.question,
            answer: this.answer,
          },
        };
        const data = await axios.post(`/course/game/item/wordWheel/update`, formdata);
        console.log(data);
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
          answer: this.answer,
        },
      };
      if (this.validateFields()) {
        let data = await axios.post(`/course/game/item/wordWheel/store`, formdata);
        console.log(data);
        this.listItems();
        this.clearFields();
      }
    },
    clearFields() {
      (this.question = ''), (this.answer = '');
    },
    validateFields() {
      if (this.question == '') {
        this.$message.warning('Ingrese una pregunta válida');
        return false;
      }
      if (this.answer == '') {
        this.$message.warning('Ingrese una respuesta válida');
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
          console.log('lista');
          console.log(response.data);
          if (response.data.error) {
            this.message = 'No hay items registrados';
          } else {
            this.items = response.data.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    async deleteItem() {
      // const response = await axios.delete(
      //   `/course/game/item/${this.game.id}/${this.mutableItem.index}/delete`
      // );
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
