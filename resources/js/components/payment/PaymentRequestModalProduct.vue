<template>
    <div>
       <custom-modal v-bind:id="'viewPurchase'" size="large">
      <template #title>Productos comprados</template>
      <template #body>
        <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
          <thead>
            <tr>
              <th class="col" >Producto</th>
              <th class="col">Cantidad</th>
              <th class="col">Precio</th>
              <th class="col">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products" v-bind:key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.pivot.quantity }}</td>
                <td class="text-right">{{ product.price.toFixed(2) }}</td>
                <td class="text-right">{{ (product.pivot.quantity * product.price ).toFixed(2) }}</td>
            </tr> 
            </tbody>
          <tfoot>
            <th colspan="2" class="text-center">Total</th>
            <td colspan="2" class="font-weight-bold text-right">{{ total }}</td>
          </tfoot>
        </table>
      </div>
      </template>
    </custom-modal>
    </div>
</template>
<script>
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {

    components: {
        'custom-modal': ModalComponent
    },
    props:['products'],
    computed: {
        total() {
            let total = 0;
            this.products.forEach(product => {
                total +=  product.pivot.quantity  *  product.price;
            });
            return  total.toFixed(2) ;
        }
    },
}
</script>
