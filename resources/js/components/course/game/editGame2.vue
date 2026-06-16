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
          <div v-if="this.items.length === 0" class="text-center mt-3 mb-3">
            <h4 class="d-inline">
              <span> {{ message }} </span>
            </h4>
          </div>

          <div v-if="this.items.length > 0">
            <course-game-item v-for="(item, index) in this.items" ref="items" :item="item" :key="index" :index="index"
              @deleteItem="showDeleteItemModal"></course-game-item>
          </div>
        </ol>
      </div>

      <custom-modal id="create-item-modal" color="primary">
        <template #title>Crear Item</template>
        <template #body>
          <create-item :game="game" @listenStore="listItems(game.id)"></create-item>
        </template>
      </custom-modal>

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
    };
  },
  created() {
    if (this.detail == null) {
      this.mutableDetail = {};
    } else {
      this.listItems(this.game.id);
    }
  },
  methods: {
    showAddModal() {
      $('#create-item-modal').modal('show');
    },

    showDeleteItemModal(value, index) {
      this.mutableItem = value;
      this.mutableItem.index = index;
      $('#delete-item-modal').modal('show');
    },

    listItems(id) {
      axios
        .get(`/course/game/item/${id}/list`)
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
        this.listItems(this.game.id);
      } else {
        await this.$message.warning('Error al validar datos');
      }

      $('#delete-item-modal').modal('hide');
    },
  },
};
</script>
