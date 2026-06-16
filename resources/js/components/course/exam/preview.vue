<template>
  <div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-12" v-for="(question, index) in this.questions" :key="question.id">
          <div class="p-1 d-flex justify-content-between">
            <div>
              <p>
                <b> {{ (index = index + 1) }} ) {{ question.title }}</b>
              </p>
            </div>
            <div>
              <p>
                <b>{{ question.points }} puntos</b>
              </p>
            </div>
          </div>

          <div class="row justify-content-center">
            <div
              class="col-lg-5 col-md-5"
              :class="answer(subindex, question.correct)"
              v-for="(option, subindex) in buildArray(question.options)"
              :key="subindex"
            >
              {{ option }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    exam: Object,
  },

  data() {
    return {
      questions: [],
    };
  },

  mounted() {
    this.listExams();
  },

  methods: {
    listExams() {
      axios
        .get(`/course/exam/preview/${this.exam.id}/`)
        .then((response) => {
          if (response.data.status==='success') {
            this.questions = response.data.data;
          } else {
            this.message = 'Sin examenes registrados';
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },

    answer(optionIndex, answer) {
      if (answer.includes(optionIndex)) {
        return 'correct_answers';
      } else {
        return 'option';
      }
    },
    buildArray(data) {
      let clearArray = data.filter(function (f) {
        return f !== null && f !== 'undefined' && f !== undefined;
      });

      return clearArray;
    }

  },
};
</script>
<style>
.option {
  margin: 10px;
  padding: 10px;
  background-color: #c9d3d3;
  color: #000000;
  text-align: center;
}
.correct_answers {
  margin: 10px;
  padding: 10px;
  text-align: center;
  background-color: #45c77f !important;
  color: #000000;
}
</style>
