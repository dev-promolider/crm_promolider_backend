<template>
  <div>
    <div class="card border-primary">
      <h4 class="card-header">Editar Perfil</h4>
      <div class="card-body">
        <div>
          <div class="row">
            <div class="mb-1 col-md-6">
              <label class="form-label" for="name">Nombre:</label>
              <div class="input-group input-group-merge">
                <input
                  class="form-control"
                  type="text"
                  id="name"
                  v-model="userCurrent.name"
                  name="name"
                  placehconf_paser="············"
                />
              </div>
            </div>

            <div class="mb-1 col-md-6">
              <label class="form-label" for="lastname">Apellido:</label>
              <div class="input-group input-group-merge">
                <input
                  class="form-control"
                  type="text"
                  id="lastname"
                  v-model="userCurrent.last_name"
                  name="lastname"
                  placehconf_paser="············"
                />
              </div>
            </div>

            <div class="mb-1 col-md-6">
              <label class="form-label" for="email">Correo:</label>
              <div class="input-group input-group-merge">
                <input
                  class="form-control"
                  type="email"
                  id="email"
                  name="cemail"
                  v-model="userCurrent.email"
                  placehconf_paser="············"
                />
              </div>
            </div>

            <div class="mb-1 col-md-6">
              <label class="form-label" for="phone">N° Celular:</label>
              <div class="input-group input-group-merge">
                <input
                  class="form-control"
                  type="tel"
                  name="phone"
                  id="phone"
                  v-model="userCurrent.phone"
                  placehconf_paser="············"
                />
              </div>
            </div>

            <div class="mb-1 col-md-12">
              <label class="form-label" for="biography">Biografia:</label>
              <div class="input-group input-group-merge">
                <textarea
                  class="form-control"
                  name="biography"
                  id="biography"
                  rows="3"
                  style="resize: none"
                  v-model="userCurrent.biography"
                ></textarea>
              </div>
            </div>

            <div class="mb-1 col-md-6">
              <label class="form-label" for="date_birth">Dia de cumpleaños:</label>
              <div class="input-group input-group-merge">
                <input 
                type="date" 
                id="date_birth" 
                class="form-control" 
                name="date_birth"
                min="1950-01-01" 
                max="2004-01-01" 
                v-model="userCurrent.date_birth">
              </div>
            </div>
            
            <div class="mb-1 col-md-6">
              <label class="form-label" for="id_country">Pais:</label>
              <el-select
                  v-model="userCurrent.id_country"
                  class="d-inline"
                  name="id_country"
                  id="id_country"
                >
                  <el-option
                    v-for="country in countrys"
                    :key="country.id"
                    :label="country.name"
                    :value="country.id"
                  >
                  </el-option>
                </el-select>
            </div>

            <div class="mb-1 col-md-6">
              <label class="form-label" for="city">Ciudad:</label>
              <div class="input-group input-group-merge">
                <input
                  class="form-control"
                  type="text"
                  id="city"
                  v-model="userCurrent.city"
                  name="city"
                  placehconf_paser="············"
                />
              </div>
            </div>

            <div class="mb-1 col-md-6">
            </div>

            <div class="d-flex justify-content-center">
              <el-button class="btn btn-primary ml-1 waves-effect waves-float waves-light" :disabled="isSubmitting" @click.prevent="update()">Guardar Cambios</el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CustomSpinner from '../../common/custom-spinner/CustomSpinner.vue';
export default {
  name: 'AccountUserProfile',
  props: {
    userCurrent: {
      type: Object,
      required: true,
    },
    countrys:{
      type:Array,
      required:true,
    }
  },
  components: { CustomSpinner },

  data() {
    return {
      initialLoading: true,
      isSubmitting: false,
      loading: false,
      falseCount: 0,
      form_user:[],
      war: '',
      country_select:""
    };
  },
  methods: {

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },

    validateMethod(str,id) {
      if (
        !str ||
        str.trim() === '' ||
        str.length === 0
      ) {
        this.war=id + ' esta vacio';
        this.falseCount=this.falseCount+1;
        return false;
      }
      if(id != 'Correo'){
        if (!this.hasEspecialCharacters(str)) {
           this.war=id + ' no puede contener caracteres especiales';
           this.falseCount=this.falseCount+1;
           return false;
        } 
      }

      if (str.length > 255) {
        this.war=id + ' demasiado largo!';
        this.falseCount=this.falseCount+1;
        return false;
      }
      return true;
    },

    validateFields() {
      this.falseCount=0;
      this.war='';
      var trueFalse = true;

      //Poner aqui los campos que se desea validar--------------------------------------------
      //como parametro primero va el valor, y luego el nombre del campo

      trueFalse = this.validateMethod(document.getElementById('name').value,'Nombre');
      trueFalse = this.validateMethod(document.getElementById('lastname').value,'Apellido');
      trueFalse = this.validateMethod(document.getElementById('email').value, 'Correo');
      trueFalse = this.validateMethod(document.getElementById('phone').value, 'Teléfono');
      trueFalse = this.validateMethod(document.getElementById('id_country').value,'País');
      trueFalse = this.validateMethod(document.getElementById('city').value,'Ciudad');
      //--------------------------------------------------------------------------------------

      if(this.falseCount > 0){
        trueFalse = false;
        this.$message.warning(this.war);
        }
      return trueFalse;
    },

    async update() {
      if (this.validateFields()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('name', this.userCurrent.name);
        formdata.append('last_name', this.userCurrent.last_name);
        formdata.append('email', this.userCurrent.email);
        formdata.append('phone', this.userCurrent.phone);
        formdata.append('biography', this.userCurrent.biography);
        formdata.append('date_birth', this.userCurrent.date_birth);
        formdata.append('id_country', this.userCurrent.id_country);
        formdata.append('city', this.userCurrent.city);

        try {
          this.loading = true;
          const request = await axios({
            url: `/users/update-user`,
            method: 'post',
            data: formdata,
            headers: { 'Content-Type': 'multipart/form-data' },
          });
          const response =
            request.status == 200
              ? this.$message.success('El perfil ha sido actualizado correctamente')
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
