<template>
  <div>
    <section id="basic-horizontal-layouts">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title"></h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 col-12">
              <el-tabs type="border-card" v-model="activeName">
                <el-tab-pane label="General" name="first">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label for="passed_course">Avatar Predeterminado</label>

                      <el-select v-model="avatar" placeholder="Seleccione un avatar" class="d-inline" id="combovalue">
                        <el-option v-for="l in this.avatars" :key="l.id" :label="l.link" :value="l.link">
                        </el-option>
                      </el-select>
                      <br /><br />
                      <div id="btnAdministrar" class="mb-3">
                        <button class="btn btn-primary" v-on:click.prevent="changeOptionModal">
                          Administrar imagenes
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <figure class="image">
                      <img :src="imagePath" style="max-width: 100px; height: 100px; flex: 1" />
                    </figure>
                  </div>

                  <div class="col-12">
                    <div class="d-flex justify-content-center">
                      <button id="btnActualizar" @click="update()" class="btn btn-success">
                        Actualizar
                      </button>
                    </div>
                  </div>
                </el-tab-pane>

                <el-tab-pane class="row" label="Puntos" name="second">
                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="daily_question">Pregunta diaria</label>
                      <input type="number" id="daily_question" class="form-control" name="daily_question" placeholder=""
                        autocomplete="false" v-model="form.daily_question" />
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="achievement">Logros</label>
                      <input type="number" id="achievement" class="form-control" name="achievement" placeholder=""
                        autocomplete="false" v-model="form.achievement" />
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="badges_level_one">Insignia nivel 1</label>
                      <input type="number" id="badges_level_one" class="form-control" name="badges_level_one"
                        placeholder="" autocomplete="false" v-model="form.badges_level_one" />
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="badges_level_two">Insignia nivel 2</label>
                      <input type="number" id="badges_level_two" class="form-control" name="badges_level_two"
                        placeholder="" autocomplete="false" v-model="form.badges_level_two" />
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label for="badges_level_three">Insignia nivel 3</label>
                      <input type="number" id="badges_level_three" class="form-control" name="badges_level_three"
                        placeholder="" autocomplete="false" v-model="form.badges_level_three" />
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="d-flex justify-content-center">
                      <button id="btnActualizar" @click="update()" class="btn btn-success">
                        Actualizar
                      </button>
                    </div>
                  </div>
                </el-tab-pane>

                <el-tab-pane label="Niveles" name="third">
                  <user-level></user-level>
                </el-tab-pane>

                <el-tab-pane label="Logros" name="fourth">
                  <badges-table></badges-table>
                </el-tab-pane>

                <el-tab-pane label="Categorías" name="five">
                  <categories-table></categories-table>
                </el-tab-pane>

                <el-tab-pane label="Bancos" name="six">
                  <bank-table></bank-table>
                </el-tab-pane>

                <el-tab-pane label="Métodos de pago" name="seven">
                  <payment-method></payment-method>
                </el-tab-pane>

                <el-tab-pane label="Roles" name="eight">
                  <roles-table></roles-table>
                </el-tab-pane>

                <el-tab-pane label="Certificados" name="nine">
                  <certificates-table></certificates-table>
                </el-tab-pane>

                <el-tab-pane label="Rango Bonos" name="eleven">
                  <RankBonus />
                </el-tab-pane>

                <el-tab-pane label="Curso" name="prices">
                  <div class="row">
                    <div class="col-md-6 col-12">
                      <div class="form-group">
                        <label for="iva_rate">Tasa de IVA (%)</label>
                        <input type="number" id="iva_rate" class="form-control" name="iva_rate" placeholder=""
                          v-model.number="form.iva_rate"
                          @input="(e) => console.log('Input IVA changed:', e.target.value)" autocomplete="false" />
                      </div>
                    </div>
                    <div class="col-md-6 col-12">
                      <div class="form-group">
                        <label for="money_points">Puntos/Monto por Compra de Curso</label>
                        <input type="number" id="money_points" class="form-control" name="money_points" placeholder=""
                          v-model="form.money_points" autocomplete="false" />
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="d-flex justify-content-center">
                        <button id="btnActualizar" @click="update()" class="btn btn-success">
                          Actualizar
                        </button>
                      </div>
                    </div>
                  </div>
                </el-tab-pane>

                <el-tab-pane label="GenerationalBonus" name="thirty">
                  <GenerationalBonus />
                </el-tab-pane>

                <el-tab-pane label="Gestión de Premios" name="referrals">
                  <referral-system></referral-system>
                </el-tab-pane>
              </el-tabs>
            </div>
          </div>
        </div>
      </div>
    </section>
    <custom-modal id="level-modal" color="primary" v-if="avatars" size="large">
      <template #title>{{ 'Avatars' }}</template>
      <template #body>
        <div class="row">
          <div class="col-12">
            <div class="col-6 form-group">
              <label>Subir Avatar</label>
              <el-upload class="picture-card" ref="courseUpload" action="" drag accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG"
                :on-change="onUploadChange" :multiple="false" :limit="1" :auto-upload="false" :file-list="fileList"
                :on-exceed="handleExceed" :on-remove="handleRemove" list-type="picture">
                <i class="el-icon-upload"></i>
                <div class="el-upload__text">
                  Suelta tu archivo aquí o <em>haz clic para cargar</em>
                </div>
                <div slot="tip" class="el-upload__tip">
                  Solo archivos jpg/png con un tamaño menor de 100kb
                </div>
              </el-upload>
            </div>
            <br />
            <div class="col-12">
              <div class="d-flex justify-content-center">
                <el-button class="btn btn-primary" :loading="requestLoading" @click="saveAvatar()">Cargar Avatar
                </el-button>
              </div>
              <br />
            </div>

            <div class="table-responsive">
              <table id="data-table-list-courses" class="table table-hover-animation table-striped table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Imagen</th>
                    <th>Borrar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(avatar, index) in avatars" v-bind:key="avatar.id" v-bind:index="index">
                    <td>
                      <p style="width: 15px">
                        {{ (index = index + 1) }}
                      </p>
                    </td>
                    <td>
                      <p style="width: 220px">
                        {{ avatar.link }}
                      </p>
                    </td>
                    <td>
                      <p style="width: 50px">
                        <img :src="imagePathTable(avatar.link)" style="max-width: 40px; height: 40px; flex: 1" />
                      </p>
                    </td>
                    <td>
                      <div class="mb-3">
                        <button class="btn btn-primary" @click="deleteAvatar(avatar.id, avatar.link)">
                          Borrar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>
      <template #footer> </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '../../../api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';
import UserLevel from '../../gamification/user-levels/userLevelTable.vue';
import BadgesTable from '../../gamification/badges/badgesTable.vue';
import CategoriesTable from '../../category/CategoriesTable.vue';
import Bank from '../bank/Bank.vue';
import PaymentMethod from '../payment-method/PaymentMethod.vue';
import Roles from '@/components/role/Roles.vue';
import Certificates from '../certificates/certificates.vue';
import RankBonus from '../rank-bonus/RankBonus.vue';
import GenerationalBonus from '../generational-bonus/generationalBonus.vue';
import ReferralSystem from '../rewards/ReferralSystem.vue';

export default {
  props: {
    default_avatar: {
      type: String,
      required: true,
    },
    daily_question: {
      type: [Number, String],
      required: true,
    },
    achievement: {
      type: [Number, String],
      required: true,
    },
    badges_level_one: {
      type: [Number, String],
      required: true,
    },
    badges_level_two: {
      type: [Number, String],
      required: true,
    },
    badges_level_three: {
      type: [Number, String],
      required: true,
    },
    money_points: {
      type: [Number, String],
      required: true,
    },
    iva_rate: {
      type: [Number, String],
      required: true,
      default: 0,
    },
    referral_commission: { type: [Number, String], required: true, default: 0 },
  },

  components: {
    'custom-modal': ModalComponent,
    'user-level': UserLevel,
    'badges-table': BadgesTable,
    'categories-table': CategoriesTable,
    'bank-table': Bank,
    'payment-method': PaymentMethod,
    'roles-table': Roles,
    'certificates-table': Certificates,
    GenerationalBonus,
    RankBonus,
    'referral-system': ReferralSystem,
  },

  data() {
    return {
      form: {
        iva_rate: 0,
        money_points: 0,
        daily_question: 0,
        achievement: 0,
        badges_level_one: 0,
        badges_level_two: 0,
        badges_level_three: 0,
        referral_commission: 0,
      },
      initialLoading: false,
      requestLoading: false,
      isSubmitting: false,
      loading: false,
      avatar: this.default_avatar,
      activeName: 'first',
      v1: 0,
      v2: 0,
      v3: 0,
      v4: 0,
      v5: 0,
      v6: 0,
      v7: 0,
      v8: 0,
      v9: 0, // NUEVA VARIABLE
      path: 'https://promolider-storage-user.s3-accelerate.amazonaws.com/images/',
      avatars: [],
      fileList: [],
    };
  },

  created() {
    this.initializeForm();
  },

  mounted() {
    this.listAvatars();
  },

  methods: {
    initializeForm() {
      console.log('Props recibidas:', {
        iva_rate: this.iva_rate,
        type: typeof this.iva_rate,
      });

      this.form = {
        iva_rate: Number(this.iva_rate) || 0,
        money_points: Number(this.money_points) || 0,
        daily_question: Number(this.daily_question) || 0,
        achievement: Number(this.achievement) || 0,
        badges_level_one: Number(this.badges_level_one) || 0,
        badges_level_two: Number(this.badges_level_two) || 0,
        badges_level_three: Number(this.badges_level_three) || 0,
        referral_commission: Number(this.referral_commission) || 0,
      };

      console.log('Form inicializado:', this.form);
    },

    hasEspecialCharacters(text) {
      const regex = new RegExp(/^[A-Za-z0-9\s\u00f1\u00d1-áéíóúÁÉÍÓÚ]+$/g);
      return regex.test(text);
    },

    listAvatars() {
      this.initialLoading = true;
      api
        .get(`/config/option/avatars/list`)
        .then((response) => {
          this.avatars = response.data;
          this.initialLoading = false;
        })
        .catch()
        .finally();
    },

    changeOptionModal() {
      $('#level-modal').modal('show');
    },

    handleRemove(fileremoved, file) {
      this.fileList = file;
    },

    handleExceed(file) {
      if (this.fileList.length >= 1) {
        this.$message.error('Solo puede seleccionar 1 archivo!');
        return false;
      }

      const isLt1M = file.size < 100000;
      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        return false;
      }
    },

    onUploadChange(file) {
      this.fileList = [];

      const isImage =
        file.raw.type === 'image/jpeg' ||
        file.raw.type === 'image/png' ||
        file.raw.type === 'image/jpg';

      const isLt1M = file.size < 100000;

      if (!isImage) {
        this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png.');
        this.$refs.courseUpload.clearFiles();
        return false;
      }

      if (!isLt1M) {
        this.$message.error('El archivo no debe pesar más de 100kb!');
        this.$refs.courseUpload.clearFiles();
        return false;
      }

      this.fileList.push(file);
    },

    imagePathTable(link) {
      return `https://promolider-storage-user.s3-accelerate.amazonaws.com/images/${link}`;
    },

    validateFields() {
      if (document.getElementById('combovalue').value.toString() == 'seleccione nueva imagen') {
        this.$message.warning('Seleccione nueva imagen avatar');
        return false;
      }

      if (!this.form.daily_question || this.form.daily_question <= 0) {
        this.$message.warning('El campo de pregunta diaria está vacío o su valor es 0!');
        return false;
      }

      if (!this.form.achievement || this.form.achievement <= 0) {
        this.$message.warning('El campo de logros está vacío o su valor es 0!');
        return false;
      }

      if (!this.form.badges_level_one || this.form.badges_level_one <= 0) {
        this.$message.warning('El campo de Insignia nivel 1 está vacío o su valor es 0!');
        return false;
      }

      if (!this.form.badges_level_two || this.form.badges_level_two <= 0) {
        this.$message.warning('El campo de Insignia nivel 2 está vacío o su valor es 0!');
        return false;
      }

      if (!this.form.badges_level_three || this.form.badges_level_three <= 0) {
        this.$message.warning('El campo de Insignia nivel 3 está vacío o su valor es 0!');
        return false;
      }

      if (!this.form.money_points || this.form.money_points <= 0) {
        this.$message.warning(
          'El campo de Puntos/Monto por compra de curso está vacío o su valor es 0!'
        );
        return false;
      }

      if (!this.form.iva_rate || this.form.iva_rate < 0 || this.form.iva_rate > 100) {
        this.$message.warning('El IVA debe estar entre 0 y 100');
        return false;
      }

      // NUEVA VALIDACIÓN
      if (this.form.referral_commission < 0 || this.form.referral_commission > 100) {
        this.$message.warning('La comisión debe estar entre 0 y 100');
        return false;
      }

      return true;
    },

    async update() {
      if (this.validateFields()) {
        this.isSubmitting = true;

        // Verificar cambios comparando con los valores originales convertidos a número
        this.v1 = this.avatar !== this.default_avatar ? 1 : 0;
        this.v2 = this.form.daily_question !== parseInt(this.daily_question) ? 1 : 0;
        this.v3 = this.form.achievement !== parseInt(this.achievement) ? 1 : 0;
        this.v4 = this.form.badges_level_one !== parseInt(this.badges_level_one) ? 1 : 0;
        this.v5 = this.form.badges_level_two !== parseInt(this.badges_level_two) ? 1 : 0;
        this.v6 = this.form.badges_level_three !== parseInt(this.badges_level_three) ? 1 : 0;
        this.v7 = this.form.money_points !== parseFloat(this.money_points) ? 1 : 0;
        this.v8 = Number(this.form.iva_rate) !== Number(this.iva_rate) ? 1 : 0;
        // NUEVA VARIABLE
        this.v9 = Number(this.form.referral_commission) !== Number(this.referral_commission) ? 1 : 0;


        if (this.v1 === 1 || this.v2 === 1 || this.v3 === 1 || this.v4 === 1 || 
            this.v5 === 1 || this.v6 === 1 || this.v7 === 1 || this.v8 === 1 || 
            this.v9 === 1) {
            
          const formdata = new FormData();
          formdata.append('default_avatar', this.avatar);
          formdata.append('daily_question', this.form.daily_question);
          formdata.append('achievement', this.form.achievement);
          formdata.append('badges_level_one', this.form.badges_level_one);
          formdata.append('badges_level_two', this.form.badges_level_two);
          formdata.append('badges_level_three', this.form.badges_level_three);
          formdata.append('money_points', this.form.money_points);
          formdata.append('iva_rate', Number(this.form.iva_rate));
          // NUEVO CAMPO
          formdata.append('referral_commission', Number(this.form.referral_commission));
            
          formdata.append('v1', this.v1);
          formdata.append('v2', this.v2);
          formdata.append('v3', this.v3);
          formdata.append('v4', this.v4);
          formdata.append('v5', this.v5);
          formdata.append('v6', this.v6);
          formdata.append('v7', this.v7);
          formdata.append('v8', this.v8);
          // NUEVA VARIABLE
          formdata.append('v9', this.v9);

          try {
            this.loading = true;
            const response = await axios({
              url: 'update',
              method: 'post',
              data: formdata,
              headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (response.status === 200) {
              this.$message.success('Las opciones han sido actualizadas correctamente');
              // Forzar la recarga de los datos desde el servidor
              window.location.reload();
            } else {
              this.$message.warning('Error al validar datos');
            }
          } catch (error) {
            console.error(error);
            this.$message.error('Error al actualizar las opciones');
          } finally {
            this.isSubmitting = false;
            this.loading = false;
          }
        } else {
          this.$message.warning('No se realizaron cambios en ninguno de los campos');
          this.isSubmitting = false;
          this.loading = false;
        }
      }
    },

    validateAvatar() {
      if (this.fileList.length === 0) {
        this.$message.warning('No se ha cargado una imagen para el avatar');
        return false;
      }
      return true;
    },

    async saveAvatar() {
      if (this.validateAvatar()) {
        this.isSubmitting = true;
        const formdata = new FormData();
        formdata.append('file', this.fileList[0].raw);

        try {
          this.loading = true;
          const response = await axios({
            url: 'avatars/add',
            method: 'post',
            data: formdata,
            headers: { 'Content-Type': 'multipart/form-data' },
          });

          if (response.status === 200) {
            this.$message.success('El avatar ha sido registrado correctamente');
            this.fileList = [];
            await this.listAvatars();
          } else {
            this.$message.warning('Error al validar datos');
          }
        } catch (error) {
          console.error(error);
          this.$message.error('Error al guardar el avatar');
        } finally {
          this.isSubmitting = false;
          this.loading = false;
        }
      }
    },

    async deleteAvatar(avatar_id, avatar_link) {
      try {
        const response = await axios.delete(`/config/option/avatars/delete/${avatar_id}`);
        if (response.data.status === 'ok') {
          this.$message.success('Se ha eliminado el avatar correctamente');
          if (avatar_link === this.avatar) {
            this.avatar = 'seleccione nueva imagen';
          }
          await this.listAvatars();
        } else {
          this.$message.warning('Error al eliminar el avatar');
        }
      } catch (error) {
        console.error(error);
        this.$message.error('Error al eliminar el avatar');
      }
    },
  },

  computed: {
    imagePath() {
      return `https://promolider-storage-user.s3-accelerate.amazonaws.com/images/${this.avatar}`;
    },
    displayIvaRate() {
      console.log('IVA Rate en computed:', this.form.iva_rate);
      return this.form.iva_rate;
    },
  },

  watch: {
    iva_rate: {
      immediate: true,
      handler(newValue) {
        this.form.iva_rate = Number(newValue);
      },
    },
    money_points: {
      immediate: true,
      handler(newValue) {
        this.form.money_points = parseFloat(newValue);
      },
    },
    daily_question: {
      immediate: true,
      handler(newValue) {
        this.form.daily_question = parseInt(newValue);
      },
    },
    achievement: {
      immediate: true,
      handler(newValue) {
        this.form.achievement = parseInt(newValue);
      },
    },
    badges_level_one: {
      immediate: true,
      handler(newValue) {
        this.form.badges_level_one = parseInt(newValue);
      },
    },
    badges_level_two: {
      immediate: true,
      handler(newValue) {
        this.form.badges_level_two = parseInt(newValue);
      },
    },
    badges_level_three: {
      immediate: true,
      handler(newValue) {
        this.form.badges_level_three = parseInt(newValue);
      },
    },
    referral_commission: {
      immediate: true,
      handler(newValue) {
        this.form.referral_commission = Number(newValue);
      },
    },
  },
};
</script>
