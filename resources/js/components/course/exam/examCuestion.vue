<template>
  <li class="list-group-item">
    <div class="row">
      <div class="col-md-8 col-lg-8">
        <b>{{ question.title }}</b>
      </div>
      <div class="col-md-4 col-lg-4 d-flex justify-content-end">
        <b>{{ question.points }} Pts.</b>
        <button class="btn btn_customize btn-link" @click="editQuestion(question)" v-if="exam.status != 1">Editar</button>
        <button class="btn btn_customize btn-link" @click="deleteQuestion(question)" v-if="exam.status != 1">Eliminar</button>
      </div>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    exam: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: true,
    },
  },
  methods: {
    editQuestion(question) {
      document.location.href = `/course/exam/question/${question.id}/edit`;
    },

    deleteQuestion(classSelected) {
      this.$emit('deleteQuestion', classSelected);
    },
  },
};
</script>

<style>
.btn_customize {
  padding: 0 1.5rem !important;
}
</style>
