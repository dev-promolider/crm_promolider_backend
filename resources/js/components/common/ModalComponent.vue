<template>
  <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" :class="colorModal" data-backdrop="static"
    data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" :class="sizeModal" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <slot name="title">Title Component </slot>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <slot name="body">
            <p>The body Component</p>
          </slot>
        </div>
        <div class="modal-footer">
          <slot name="footer"> </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalComponent',
  props: {
    color: {
      type: String,
      default: 'primary',
    },
    size: {
      type: String,
      default: 'default',
    },
    message: {
      type: String,
      default: '',
    },
    supportImage: {
      type: String,
      default: '',
    },
  },
  computed: {
    colorModal() {
      const COLORS_MODAL = {
        primary: 'modal-primary',
        success: 'modal-success',
        info: 'modal-info',
        warning: 'modal-warning',
        danger: 'modal-danger',
      };
      return COLORS_MODAL[this.color];
    },
    sizeModal() {
      const SIZES_MODAL = {
        small: 'modal-sm',
        medium: 'modal-md',
        large: 'modal-lg',
        default: 'modal-default',
        extralarge: 'modal-xl',
      };
      return SIZES_MODAL[this.size];
    },
  },
};
</script>
