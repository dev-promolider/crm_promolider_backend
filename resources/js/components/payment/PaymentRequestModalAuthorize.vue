<template>
  <div>
      <custom-modal v-bind:id="'viewauthorize'" color="warning" size="small">
      <template #title>Authorize payment? </template>
      <p>Do you want to authorize the payment?</p>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" @click="authorizePayment(payment)">
          <span
            class="spinner-border spinner-border-sm text-danger"
            role="status"
            aria-hidden="true"
            v-if="loading"
          ></span>
          <span class="ml-25 align-middle"> Accept </span>
        </button>
      </template>
    </custom-modal>

  </div>
</template>

<script>
import api from '@/api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  props:['payment'],
    components: {
        'custom-modal': ModalComponent
    },
    data() {
      return {
        loading: false
      }
    },
    methods: {
      authorizePayment(payment) {
      this.loading = true;
      api.put(`/requests/authorizePayment/${payment.id}`).then((response) => {
        this.loading = false;
        $('#viewauthorize').modal('hide');
        //emit to parent
        this.$emit('payment-authorized', payment);
        // this.listPayments();
      });
    },
    },
}
</script>

<style>

</style>