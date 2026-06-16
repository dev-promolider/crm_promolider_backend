<template>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <div class="class-info">
      <h5 class="mb-0">
        <span :class="['lesson-name', { observation: hasObservation(clas.id) }]">
          {{ clas.name }}
          <i v-if="hasObservation(clas.id)" class="el-icon-warning-outline"></i>
        </span>
      </h5>
    </div>
    <div class="class-actions">
      <button class="btn" :class="clas.has_video ? 'btn-edit' : 'btn-add'" @click="editClass(clas)">
        {{ clas.has_video ? 'Editar' : 'Video Clase' }}
      </button>
      <button class="btn btn-delete" @click="deleteClass(clas)">Eliminar</button>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    module: {
      type: Object,
      required: true,
    },
    clas: {
      type: Object,
      required: true,
    },
    observations: {
      type: Array,
      required: true,
    },
  },
  methods: {
    editClass(classSelected) {
      this.$emit('editClass', classSelected);
    },
    deleteClass(classSelected) {
      this.$emit('deleteClass', classSelected);
    },
    hasObservation(class_id) {
      return this.observations.some((observation) => observation.id_class === class_id);
    },
  },
};
</script>

<style scoped>
.list-group-item {
  padding: 15px;
  border: 1px solid #e0e0e0;
  margin-bottom: 10px;
  border-radius: 5px;
}

.lesson-name {
  text-transform: capitalize;
  font-weight: bold;
}

.observation {
  color: #ff9f43;
}

.class-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-add {
  background-color: #ff9f43;
  color: white;
}

.btn-edit {
  background-color: #1ae600;
  color: white;
}

.btn-delete {
  background-color: #ff4757;
  color: white;
}

.btn:hover {
  opacity: 0.8;
}

.el-icon-warning-outline {
  margin-left: 5px;
  color: #ff9f43;
}

.loader {
  display: block;
  position: relative;
  height: 12px;
  width: 80%;
  border: 1px solid #fff;
  border-radius: 10px;
  overflow: hidden;
}

.loader::after {
  content: '';
  width: 40%;
  height: 100%;
  background: #1ae600;
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: border-box;
  animation: animloader 2s linear infinite;
}

@keyframes animloader {
  0% {
    left: 0;
    transform: translateX(-100%);
  }

  100% {
    left: 100%;
    transform: translateX(0%);
  }
}
</style>
