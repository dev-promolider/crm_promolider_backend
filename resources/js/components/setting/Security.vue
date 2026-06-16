<template>
  <div>
    <div class="card border-primary">
      <h4 class="card-header">Cambiar Contraseña</h4>
      <div class="card-body">
        <div>
          <div class="row">
            <div class="mb-2 col-md-6 form-password-toggle">
              <label class="form-label" for="newPassword">Nueva Contraseña</label>
              <div class="input-group input-group-merge form-password-toggle">
                <input class="form-control" v-model="form_password.new" type="password" id="newPassword"
                  name="newPassword" placehconf_paser="············" />
                <span class="input-group-text cursor-pointer" @click="mostrarContra('newPassword')">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-eye-off">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </span>
              </div>
            </div>

            <div class="mb-2 col-md-6 form-password-toggle">
              <label class="form-label" for="confirmPassword">Confirmar Nueva Contraseña</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" v-model="form_password.conf_pas" name="confirmPassword"
                  id="confirmPassword" placehconf_paser="············" />
                <span class="input-group-text cursor-pointer" @click="mostrarContra('confirmPassword')"><svg
                    xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-eye-off">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg></span>
              </div>
            </div>

            <div class="col-md-12">
              <div class="alert alert-light" role="alert">
                Mínimo 8 caracteres de largo, mayúsculas y símbolos
              </div>
            </div>

            <div>
              <button type="button" @click="changePassword()"
                class="btn btn-primary ml-1 waves-effect waves-float waves-light">
                Cambiar Contraseña
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api/api';

export default {
  name: 'SecurityUser',

  data: () => ({
    form_password: {},
  }),

  methods: {
    initForm() {
      this.form_password = {
        new: '',
        conf_pas: '',
      };
    },

    mostrarContra(id_input) {
      var lblPassword = document.getElementById(id_input);
      if (lblPassword.type == 'password') {
        lblPassword.type = 'text';
        $('.icon').removeClass('fa fa-eye-slash').addClass('fa fa-eye.close');
      } else {
        lblPassword.type = 'password';
        $('.icon').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
      }
    },

    validPassword(new_pas, conf_pas) {
      if (new_pas == '' || new_pas.length <= 0) {
        this.$message.warning('Ingrese una contraseña');
        return false;
      }

      if (new_pas != conf_pas) {
        this.$message.warning('Las contraseñas no coinciden');
        return false;
      }
      if (new_pas.length < 8) {
        this.$message.warning('La contraseña debe tener como mínimo 8 caracteres');
        return false;
      }
      if (!this.useRegex(new_pas)) {
        this.$message.warning(
          'La contraseña debe tener como mínimo 1 letra minúscula,una letra mayúscula, 1 digito y 1 caracter especial'
        );
        return false;
      }
      return true;
    },

    useRegex(input) {
      let regex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])((?=.*\W)|(?=.*_))^[^ ]+$/g;
      return regex.test(input);
    },

    async changePassword() {
      if (this.validPassword(this.form_password.new, this.form_password.conf_pas)) {
        const formdata = new FormData();
        formdata.append('password', this.form_password.new);
        try {
          this.loading = true;
          const request = await axios({
            url: `/users/update-password`,
            method: 'post',
            data: formdata,
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          const response =
            request.status == 200
              ? this.$message.success('La contraseña se ha sido actualizado correctamente')
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
  mounted() {
    this.initForm();
  },
};
</script>

<style lang="css" scoped></style>
