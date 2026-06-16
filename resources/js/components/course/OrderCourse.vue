<template>
    <el-dialog title="Ordenar clases" :visible="orderModal" @close="close()">
        <div v-loading="loading">
            <draggable v-model="items" group="people" @start="dragging=true" @end="showEnd">
                <div v-for="(element, index) in items" :key="index" >
                    <div v-if="element.type === 'module'" class="drag-module">
                        <div><strong> {{ getModuleNumber(index) }}. {{ element.name }}</strong></div>
                    </div>
                    <div v-else style="padding-left: 20px;">
                        <div class="drag-class">
                            <div class="item">{{ element.name }}</div>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>
    </el-dialog>
</template>

<script>

export default {
    props: {
        courses: {
            type: Object,
            required: true
        },
        orderModal: {
            type: Boolean,
            required: true
        }
    },
    data(){
        return {
            loading: false,
            dragging: false,
            items: [],

        }
    },
    mounted(){
        this.getOrders();
    },
    methods: {
        close(){
            this.$emit('closeOrderModal');
        },
        getModuleNumber(index) {
            if (this.items[index].type === 'module') {
                return this.items.slice(0, index + 1).filter(item => item.type === 'module').length;
            }
            return '';
        },
        async getOrders() {
            await axios.get(`/course/${this.courses.id}/orders`)
            .then((response) => {
                this.items = response.data
            })
        },
        async showEnd() {
            this.loading = true
            this.dragging = false

            try {
                const isMovingModule = this.items.filter(item => item.type === 'module');
                const isMovingClass = this.items.filter(item => item.type === 'class');

                if(isMovingModule.length > 0){
                    var order = JSON.stringify(isMovingModule)
                    const form = {
                        "id": this.courses.id,
                        "order": order
                    }
                    await axios.patch(`/course/change-order-module`, form);
                }

                if(isMovingClass.length > 0){
                    var order = JSON.stringify(isMovingClass)
                    const form = {
                        "id": this.courses.id,
                        "order": order
                    }
                    await axios.patch(`/course/change-order`, form);
                }

                this.items = [];
                this.getOrders();

            } catch (error) {
                console.error('Error al cambiar el orden:', error.message);
            } finally {
                this.loading = false
            }
        },
    },
    computed: {
        draggingInfo() {
            return this.dragging ? "under drag" : "";
        }
    },
}

</script>

<style>
  .drag-module {
    margin: 5px;
    padding: 10px;
    background-color: #F7F7F7;
    border: 1px solid #ddd;
    color: #000;
    border-radius: 4px;
    cursor: grab;
    transition: box-shadow 0.3s ease;
    font-weight: bold;
  }

  .drag-class {
    margin: 5px;
    padding: 0px 10px 0px 10px;
    background-color: #fff;
    border: 1px solid #ddd;
    color: #000;
    border-radius: 4px;
    cursor: grab;
    transition: box-shadow 0.3s ease;
    /* font-weight: bold; */
  }

  .drag-item:hover {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  .item {
    padding: 4px;
    margin: 5px 0;
  }
</style>