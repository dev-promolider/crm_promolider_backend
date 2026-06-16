<template>
  <el-dialog :visible="show" @close="close" @open="load" :show-close="false" width="780px">
    <div>
      <p>
        Tipo de rol: <strong>{{ rolForm.name }}</strong>
      </p>
    </div>
    <div class="wrapper-modules">
      <perfect-scrollbar class="modules">
        <div class="p-2">
          <el-button size="small" class="w-100" @click="onSelectAllElements(sections)"
            >Seleccionar Todo</el-button
          >
        </div>
        <ul>
          <li v-for="section in sections" :key="section.id">
            <template v-if="rolForm.id == 1 && section.name == 'config'">
              <span class="btn-module"
                >{{ section.description }}
              </span>
              <el-switch v-model="section.check" disabled></el-switch>
            </template>
            <template v-else>
              <span class="btn-module" @click="getModules(section, rolForm.id)"
                >{{ section.description }}
              </span>
              <el-switch @change="updateCheck($event, section, isSection)" v-model="section.check"></el-switch>
            </template>
            
          </li>
        </ul>
      </perfect-scrollbar>
      <perfect-scrollbar class="modules">
        <div class="p-2">
          <el-button size="small" class="w-100" @click="onSelectAllElements(modules)">Seleccionar Todo</el-button>
        </div>
        <ul>
          <li v-for="module in modules" :key="module.id">
            <span class="btn-module" @click="getActions(module, rolForm.id)">{{
              module.description
            }}</span>
            <el-switch
              @change="updateCheck($event, module, isModule)"
              v-model="module.check"
            ></el-switch>
          </li>
        </ul>
      </perfect-scrollbar>
      <perfect-scrollbar class="modules">
        <div class="p-2">
          <el-button size="small" class="w-100" @click="onSelectAllElements(actions)">Seleccionar Todo</el-button>
        </div>
        <ul>
          <li v-for="action in actions" :key="action.id">
            <span class="btn-module">{{ action.description }}</span>
            <el-switch
              @change="updateCheck($event, action, isAction)"
              v-model="action.check"
            ></el-switch>
          </li>
        </ul>
      </perfect-scrollbar>
    </div>
    <span slot="footer" class="dialog-footer">
      <el-button @click.prevent="close()">Cancel</el-button>
    </span>
  </el-dialog>
</template>
<script>
export default {
  props: ['show', 'rol'],
  data() {
    return {
      sections: [],
      rolForm: {},
      modules: [],
      actions: [],
      isSection: "section",
      isModule: "module",
      isAction: "action"
    };
  },
  methods: {
    initForm() {
      this.rolForm = {
        id: null,
        name: null,
      };
    },
    async load() {
      await this.initForm();
      this.rolForm = this.rol;
      this.getSections();
    },
    close(){
            this.$emit('update:show', false);
        },
    getSections() {
      axios.get(`/admin/role/get-sections/${this.rolForm.id}`).then((response) => {
        this.sections = response.data;
      });
    },
    getModules(section, role) {
      axios.get(`/admin/role/get-modules?section=${section.name}&role=${role}`).then((response) => {
        this.modules = response.data;
      });
    },
    getActions(module, role) {
      axios.get(`/admin/role/get-actions?module=${module.name}&role=${role}`).then((response) => {
        this.actions = response.data;
      });
    },
    async getResponseModules(section) {
      let response = axios.get(`/admin/role/get-modules?section=${section.name}&role=${this.rolForm.id}`);
      return await response;
    },
    async getResponseActions(module) {
      let response = axios.get(`/admin/role/get-actions?module=${module.name}&role=${this.rolForm.id}`);
      return await response;
    },
    onSelectAllElements(permissionList) {
        permissionList.forEach(element => {
            if(!element.check){
                const parament = { role: this.rolForm.id, permission: element.id };
                axios.post(`/admin/role/add/permission`, parament)
                .then(r => element.check = r.data.check)
            }
        });
    },
    deselectAllElements(permissionList){
        permissionList.data.forEach(element => {
            if(element.check){
                const parament = { role: this.rolForm.id, permission: element.id };
                axios.post(`/admin/role/remove/permission`, parament)
                .then(r => element.check = r.data.check)
            }
        });
    },
    async updateCheck(e, item, type) {
      let typePost = item.check ? 'add' : 'remove';
      const parament = { role: this.rolForm.id, permission: item.id };
      let response = await axios.post(`/admin/role/${typePost}/permission`, parament);

      if(response){
        if(type == "section" && item.check == false){
            let modulesList = await this.getResponseModules(item);
            this.deselectAllElements(modulesList);
            modulesList.data.forEach(async element => {
                let actionsList = await this.getResponseActions(element);
                this.deselectAllElements(actionsList);
            });
        }else if(type == "module" && item.check == false){
            let actionsList = await this.getResponseActions(item);
            this.deselectAllElements(actionsList);
        }
      }
    }
  },
};
</script>
<style>
  .btn-module{
    cursor: pointer;
  }
</style>