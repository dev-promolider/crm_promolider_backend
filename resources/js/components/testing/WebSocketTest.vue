<template>
  <div>
    <button @click="triggerEvent">Emitir evento WebSocket</button>
    <div v-if="received">WebSocket OK</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      received: false
    }
  },
  mounted() {
    if (window.Echo) {
      window.Echo.channel('public-test')
        .listen('WebSocketTestEvent', (e) => {
          this.received = true;
        });
    }
  },
  methods: {
    triggerEvent() {
      window.axios.get('/ws-test');
    }
  }
}
</script>
