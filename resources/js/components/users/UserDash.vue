<template>
  <div class="row">
    <div class="col-12">
      <user-status :user="user"></user-status>
    </div>
  </div>
</template>

<script>
import api from '@/api/api';
import AdjustLeg from '@/components/binary-branch/AdjustLeg.vue';
import UserStatus from '@/components/users/UserStatus.vue';
export default {
  name: 'user-dash',
  components: {
    'user-status': UserStatus
  },
  data: () => ({
    user: {},
  }),
  mounted() {
    this.listUser();
  },
  methods: {
    listUser() {
      api
        .get(`/users/get-data-currentuser`)
        .then((response) => {
          this.user = response;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style>
</style>