<template>
  <div class="row">
    <div class="col-12">
      <form @submit.prevent="addModule" action="post">
        <div class="row">
          <div class="col-4">
            <div class="form-group">
              <input
                type="text"
                class="form-control"
                placeholder="enter name module"
                aria-describedby="button-addon2"
                v-model="name"
                required
              />
            </div>
          </div>
          <div class="col-4">
            <div class="form-group">
              <textarea class="form-control" v-model="description" placeholder="Enter description" required></textarea>
            </div>
          </div>
          <div class="col-4">
            <div class="form-group">
              <button class="btn btn-outline-primary" type="submit">Add Module</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12">
      <course-module-list :modules="modules" :course="course"  
      @module-updated="listModules" @module-deleted="listModules"
      ></course-module-list>
    </div>
  </div>
</template>

<script>
import api from '@/api/api';
import CourseModuleList from './modules/CourseModuleList.vue';

export default {
  props: {
    course: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      name: '',
      description: '',
      modules: [],
    };
  },
  components: {
    CourseModuleList,
  },
  mounted() {
    this.listModules();
  },
  methods: {
    addModule() {
      api
        .post(`/creator/courses/${this.course.id}/modules`, {
          name: this.name,
          description: this.description,
        })
        .then((response) => {
          console.log(response);
          this.listModules();
        });
      this.name = '';
      this.description = '';
    },
    listModules() {
      api
        .get(`/creator/courses/${this.course.id}/modules`)
        .then((response) => {
          console.log(response);
          this.modules = response;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style>
</style>