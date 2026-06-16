<template>
  <div class="d-inline-block">
    <div
      class="modal fade text-left modal-danger"
      id="delete-modal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="myModalLabel140"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel140">
              {{ accountType.status === '1' ? 'Restore' : 'Delete' }} Account Type</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            You want to {{ accountType.status === '1' ? 'Activate' : 'Delete' }} the account type {{ accountType.name }}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="onDelete">
              <span
                class="spinner-border spinner-border-sm text-danger"
                role="status"
                aria-hidden="true"
                v-if="loading"
              ></span>
              <span class="ml-25 align-middle"> Accept </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiAccountType from '../../../../api/api.account-type';

export default {
  props: {
    accountType: {
      type: Object,
      require: true
    }
  },
  emits: {
    'confirm-delete': function(bool, status) {
      /*Recibe un campo boolean y un campo status '1' o '0'*/
    }
  },
  data() {
    return {
      loading: false
    };
  },
  methods: {
    onDelete() {
      if (this.accountType) {
        this.loading = true;
        const params = {
          status: this.accountType.status === '1' ? '0' : '1'
        };
        apiAccountType.delete(this.accountType .id, params).then(response => {
          this.loading = false;
          $('#delete-modal').modal('hide');
          this.$emit('confirm-delete', true);
        });
      }
    }
  },
  name: 'CustomDeleteModal'
};
</script>

<style scoped></style>
