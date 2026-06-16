<template>
  <div class="d-inline-block">
    <div
      class="modal fade text-left modal-success"
      id="success-modal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="myModalLabel110"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel110">
              Successful operation
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            The Account type {{ accountType.name }} was
            {{ accountType.isEdit ? 'edited' : 'added' }} correctly.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-dismiss="modal">Accept</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    accountType: {
      type: Object,
      require: true,
    },
  },
  name: 'CustomSuccessModal',
};
</script>

<style scoped></style>
