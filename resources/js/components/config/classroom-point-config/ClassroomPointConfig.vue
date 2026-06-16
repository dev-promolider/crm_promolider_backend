<script>
export default {
  props: ['id', 'passed_course', 'daily_question', 'achievement'],
  mounted() {
    document.getElementById('passed_course').value = this.passed_course;
    document.getElementById('daily_question').value = this.daily_question;
    document.getElementById('achievement').value = this.achievement;
  },

  data() {
    return {
      isSubmitting: false,
      loading: false,
    };
  },

  methods: {
    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },
    validateFields() {
      if (
        (!this.passed_course && this.passed_course !== 1) ||
        this.passed_course.toString().trim() === '' ||
        this.passed_course === 0
      ) {
        this.$message.warning('Curso aprobado esta vacio o su valor es 0!');
        return false;
      }

      if (this.passed_course.charAt(0) == 0 || isNaN(this.passed_course) == true) {
        this.$message.warning('Verifique el campo de curso aprobado');
        return false;
      }

      if (this.passed_course.length > 7) {
        this.$message.warning('El campo de curso aprobado es muy largo');
        return false;
      }

      if (
        (!this.daily_question && this.daily_question !== 1) ||
        this.daily_question.toString().trim() === '' ||
        this.daily_question === 0
      ) {
        this.$message.warning('Pregunta diaria esta vacio o su valor es 0!');
        return false;
      }

      if (this.daily_question.charAt(0) == 0 || isNaN(this.daily_question) == true) {
        this.$message.warning('Verifique el campo de pregunta diaria');
        return false;
      }

      if (this.daily_question.length > 7) {
        this.$message.warning('El campo de pregunta diaria es muy largo');
        return false;
      }

      if (
        (!this.achievement && this.achievement !== 1) ||
        this.achievement.toString().trim() === '' ||
        this.achievement === 0
      ) {
        this.$message.warning('Logros esta vacio o su valor es 0!');
        return false;
      }

      if (this.achievement.charAt(0) == 0 || isNaN(this.achievement) == true) {
        this.$message.warning('Verifique el campo de logros');
        return false;
      }

      if (this.achievement.length > 7) {
        this.$message.warning('El campo de logros es muy largo');
        return false;
      }

      return true;
    },
    async update() {
      if (true) {
        this.isSubmitting = true;

        this.passed_course = document.getElementById('passed_course').value;
        this.daily_question = document.getElementById('daily_question').value;
        this.achievement = document.getElementById('achievement').value;

        const formdata = new FormData();
        formdata.append('passed_course', this.passed_course);
        formdata.append('daily_question', this.daily_question);
        formdata.append('achievement', this.achievement);

        try {
          this.loading = true;
          const request = await axios({
            url: `${this.id}/update`,
            method: 'post',
            data: formdata,
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          const response =
            request.status == 200
              ? this.$message.success('Puntos de la clase han sido actualizados correctamente')
              : await this.$message.warning('Error al validar datos');
          this.isSubmitting = false;
          this.loading = false;
          location.reload();
        } catch (error) {
          this.isSubmitting = false;
          this.loading = false;
          console.log(error);
        }
      }
    },
  },
};
</script>
<template>
  <div>
    <section id="basic-horizontal-layouts">
      <div class="row">
        <div class="col-md-12 col-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Modificar Puntos del classroom</h4>
            </div>

            <div class="card-body">
              <form @submit.prevent="update" class="form">
                <div class="row">
                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="passed_course">Curso aprobado</label>

                      <input type="text" id="passed_course" class="form-control" name="passed_course"
                        placeholder="passed_course" autocomplete="false" ref="passed-course-classroom-point" />
                    </div>
                  </div>
                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="daily_question">Pregunta diaria</label>
                      <input type="text" id="daily_question" class="form-control" name="daily_question"
                        placeholder="daily_question (optional)" autocomplete="false"
                        ref="daily-question-classroom-point" />
                    </div>
                  </div>
                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="achievement">Logros</label>
                      <input type="text" id="achievement" class="form-control" name="achievement"
                        placeholder="achievement(optional)" autocomplete="false" ref="achievement-classroom-point" />
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="d-flex justify-content-center">
                      <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                        Actualizar
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
