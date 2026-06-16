<template>
  <div>
    <custom-modal v-bind:id="'viewDisavow'" color="danger" size="large">
      <template #title>Deny purchase of {{ payment.user_membreship.fullName }} </template>
      <p>Do you want to disavow the payment?</p>
      <div class="form-group">
        <textarea
          name="mensaje"
          required=""
          class="form-control"
          placeholder="Enter why the purchase is declined"
          v-model="justification"
        ></textarea>
      </div>
      <template #footer>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" @click="denyPayment(payment)">
          <span
            class="spinner-border spinner-border-sm text-danger"
            role="status"
            aria-hidden="true"
            v-if="loading"
          ></span>
          <span class="ml-25 align-middle"> Deny purchase </span>
        </button>
      </template>
    </custom-modal>
  </div>
</template>

<script>
import api from '@/api/api';
import ModalComponent from '@/components/common/ModalComponent.vue';

export default {
  props: ['payment'],
  components: {
    'custom-modal': ModalComponent,
  },
  data() {
    return {
      loading: false,
      justification: '',
    };
  },
  methods: {
    denyPayment(payment) {
      this.loading = true;
      api
        .put(`/requests/denypayment/${payment.id}`, {
          justification: this.justification,
        })
        .then((response) => {
          this.justification = '';
          this.loading = false;
          $('#viewDisavow').modal('hide');
          this.$emit('payment-denied', payment);
         
        });
    },
  },
};
</script>

<style>
</style>