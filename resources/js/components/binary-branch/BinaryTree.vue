<template>
  <div class="">
    <section v-if="!loading">
      <div class="text-center">
        <h1 class="level-1 rectangle">
          <a v-if="binary.hasOwnProperty('c')" @click="viewUser(binary.c.username)">
            <img :src="binary.c.photo" data-toggle="tooltip" data-placement="bottom" title="" class="rounded-circle"
              alt="binary.c.fullName " style="max-width: 120px; height: 120px; flex: 1"
              :data-original-title="binary.c.fullName" />
          </a>
        </h1>
        <ol class="level-2-wrapper">
          <li>
            <h2 class="level-2 rectangle">
              <a v-if="binary.hasOwnProperty('a')" @click="viewUser(binary.a.user.username)">
                <img :src="binary.a.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                  class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                  :data-original-title="binary.a.user.fullName" />
              </a>
            </h2>
            <ol class="level-3-wrapper">
              <li>
                <h3 class="level-3 rectangle">
                  <a v-if="binary.hasOwnProperty('aa')" @click="viewUser(binary.aa.user.username)">
                    <img :src="binary.aa.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                      class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                      :data-original-title="binary.aa.user.fullName" />
                  </a>
                </h3>
              </li>
              <li>
                <h3 class="level-3 rectangle">
                  <a v-if="binary.hasOwnProperty('ab')" @click="viewUser(binary.ab.user.username)">
                    <img :src="binary.ab.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                      class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                      :data-original-title="binary.ab.user.fullName" />
                  </a>
                </h3>
              </li>
            </ol>
          </li>
          <li>
            <h2 class="level-2 rectangle">
              <a v-if="binary.hasOwnProperty('b')" @click="viewUser(binary.b.user.username)">
                <img :src="binary.b.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                  class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                  :data-original-title="binary.b.user.fullName" />
              </a>
            </h2>
            <ol class="level-3-wrapper">
              <li>
                <h3 class="level-3 rectangle">
                  <a v-if="binary.hasOwnProperty('ba')" @click="viewUser(binary.ba.user.username)">
                    <img :src="binary.ba.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                      class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                      :data-original-title="binary.ba.user.fullName" />
                  </a>
                </h3>
              </li>
              <li>
                <h3 class="level-3 rectangle">
                  <a v-if="binary.hasOwnProperty('bb')" @click="viewUser(binary.bb.user.username)">
                    <img :src="binary.bb.user.photo" data-toggle="tooltip" data-placement="bottom" title=""
                      class="rounded-circle" alt="User Image" style="max-width: 80px; height: 80px; flex: 1"
                      :data-original-title="binary.bb.user.fullName" />
                  </a>
                </h3>
              </li>
            </ol>
          </li>
        </ol>
      </div>
      <modal-user :user="user"> </modal-user>
    </section>
    <custom-spinner v-else></custom-spinner>
  </div>
</template>

<script>
import api from '@/api/api';
import CustomSpinner from '@/common/custom-spinner/CustomSpinner.vue';
export default {
  components: { CustomSpinner },

  data() {
    return {
      loading: false,
      binary: [],
      user: {
        account_type: {},
      },
    };
  },

  mounted() {
    this.get_binary_tree();
  },

  methods: {
    get_binary_tree() {
      this.loading = true;
      api.get(`/listbinary`).then((response) => {
        console.log(response)
        this.binary = response.data;
        this.loading = false;
      });
    },

    viewUser(user) {
      api
        .get(`/users/get-data-user/${user}`)
        .then((response) => {
          console.log(response);
          this.user = response;
          $(`#viewUser`).modal('show');
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style>
:root {
  --black: #9D9D9D;
}

* {
  padding: 0;
  margin: 0;
  box-sizing: border-box;
}

ol {
  list-style: none;
}

.rectangle {
  position: relative;
}

.level-1 {
  width: 50%;
  margin: 0 auto 40px;
}

.level-1::before {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 20px;
  background: var(--black);
}

.level-2-wrapper {
  position: relative;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
}

.level-2-wrapper::before {
  content: '';
  position: absolute;
  top: -20px;
  left: 25%;
  width: 50%;
  height: 2px;
  background: var(--black);
}

.level-2-wrapper::after {
  display: none;
  content: '';
  position: absolute;
  left: -20px;
  bottom: -20px;
  width: 100%;
  height: 2px;
  background: var(--black);
}

.level-2-wrapper li {
  position: relative;
}

.level-2-wrapper>li::before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 20px;
  background: var(--black);
}

.level-2 {
  width: 70%;
  margin: 0 auto 40px;
}

.level-2::before {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 20px;
  background: var(--black);
}

.level-2::after {
  display: none;
  content: '';
  position: absolute;
  top: 50%;
  left: 0%;
  transform: translate(-100%, -50%);
  width: 20px;
  height: 2px;
  background: var(--black);
}

.level-3-wrapper {
  position: relative;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-column-gap: 20px;
  width: 90%;
  margin: 0 auto;
}

.level-3-wrapper::before {
  content: '';
  position: absolute;
  top: -20px;
  left: calc(25% - 5px);
  width: calc(50% + 10px);
  height: 2px;
  background: var(--black);
}

.level-3-wrapper>li::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translate(-50%, -100%);
  width: 2px;
  height: 20px;
  background: var(--black);
}

.level-3 {
  margin-bottom: 20px;
}
</style>
