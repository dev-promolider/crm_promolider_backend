<template>
  <div>
    <li class="list-group-item pt-1 pb-2">
      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 mb-1">
          <h3 class="d-inline">
            <slot class="d-inline"> Módulo {{ index + 1 }}: </slot>
            <input class="form-control d-inline input-module" v-model="namemodule" />
            <button class="btn btn-outline-primary p-0 btn-size" @click="editModule">
              <save-icon size="1.5x" class="custom-class"></save-icon>
            </button>
            <button class="btn btn-outline-danger p-0 btn-size" @click="deleteModule(module)">
              <trash-2-icon size="1.5x" class="custom-class"></trash-2-icon>
            </button>
          </h3>

          <div class="float-right">
            <div class="tooltip-container">
              <button class="btn btn-outline-secondary p-0 btn-size" @click="addClass(module)"
                aria-label="Incorporar clase">
                <plus-square-icon size="1.5x" class="custom-class"></plus-square-icon>
              </button>
              <span class="tooltip-text">Incorporar clase</span>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
          <ol class="list-group">
            <course-module-class @deleteClass="classData" @editClass="classModal" v-for="(clas, index) in classes"
              :key="clas.id" :module="module" :observations="observations" :clas="clas">
              {{ index + 1 }}
            </course-module-class>
          </ol>
        </div>
      </div>
    </li>
  </div>
</template>

<script>
import api from '../../../api/api';
import CourseModuelClass from './classes/CourseModuleClass.vue';
import { SaveIcon, Trash2Icon, PlusSquareIcon } from 'vue-feather-icons';

export default {
  props: {
    module: {
      type: Object,
      required: true,
    },
    course: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
  },

  components: {
    'course-module-class': CourseModuelClass,
    SaveIcon,
    Trash2Icon,
    PlusSquareIcon,
  },

  data() {
    return {
      name: '',
      classes: [],
      observations: [],
      edit: false,
      namemodule: this.module.name,
    };
  },

  mounted() {
    this.listClasses();
    this.listObservations();
  },

  methods: {
    deleteModule(moduleSelected) {
      this.$emit('delete', moduleSelected);
    },

    addClass(moduleSelected) {
      this.$emit('addClass', moduleSelected);
    },

    classData(classSelected) {
      this.$emit('deleteClass', classSelected);
    },

    classModal(classSelected) {
      this.$emit('editClass', classSelected);
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },
    validateFields() {
      if (!this.namemodule || this.namemodule.trim() === '' || this.namemodule.length === 0) {
        this.$message.warning('Titulo esta vacio!');
        return false;
      }
      if (this.namemodule.length > 255) {
        this.$message.warning('Titulo demasiado largo!');
        return false;
      }
      return true;
    },

    editModule() {
      if (this.validateFields()) {
        api
          .put(`/course/module/${this.module.id}/update`, {
            name: this.namemodule,
          })
          .then((response) => {
            this.module.name = response.data;
            this.$message.success('Módulo modificado correctamente');
          });
      }
    },

    listClasses() {
      api
        .get(`/course/module/class/${this.module.id}/classList`)
        .then((response) => {
          this.classes = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    listObservations() {
      api
        .get(`/course/module/class/${this.course.id}/listObservations`)
        .then((response) => {
          this.observations = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style>
.input-module {
  width: 40%;
}

.btn-size {
  width: 80px;
  height: 40px;
}

@media (max-width: 800px) {
  .input-module {
    width: 100%;
    margin: 10px 0px;
  }

  .btn-size {
    width: 80px;
    height: 40px;
  }
}

/* Botón */
.tooltip-container {
  position: relative;
  display: inline-block;
}

/* Tooltip */
.tooltip-container .tooltip-text {
  visibility: hidden;
  width: auto;
  background-color: #ff9f43;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 8px 12px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: opacity 0.3s, visibility 0.3s, transform 0.3s;
  font-size: 11px;
  font-weight: 300;
  white-space: nowrap;
  box-shadow: 0 4px 6px #ff9f43;
}

.tooltip-container .tooltip-text::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: #ff9f43 transparent transparent transparent;
}

/* Mostrar el tooltip */
.tooltip-container:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(-5px);
}
</style>
