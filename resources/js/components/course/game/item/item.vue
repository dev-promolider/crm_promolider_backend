<template>
  <li class="list-group-item">
    <div class="row">
      <div class="col-md-8 col-lg-8">
        <img
          class="img-thumbnail image-card"
          :src="'https://promolider-storage-user.s3-accelerate.amazonaws.com/' + item.img"
          :alt="item.img"
        />
        <b class="ml-1">{{ item.name }}</b>
      </div>
      <div class="col-md-4 col-lg-4 d-flex justify-content-end">
        <button class="btn btn_customize btn-link" @click="deleteItem(item, index)">
          Eliminar
        </button>
      </div>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
  },
  methods: {
    deleteItem(item, index) {
      this.$emit('deleteItem', item, index);
    },
  },
};
</script>

<style>
.btn_customize {
  padding: 0 1.5rem !important;
}
.image-card {
  max-height: 70px;
  min-height: 70px;
}
</style>
