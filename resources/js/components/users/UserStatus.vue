<template>
  <div class="card p-0">
    <div class="card-header"><b>Condición</b></div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex justify-content-between pt-0">
          Membresía activa:
          <span class="badge badge-pill" :class="user.membershipActive ? 'badge-success' : 'badge-danger'">{{
            user.membershipActive ? '&#x2714;' : '&#x2716;' }}</span>
        </div>
        <div class="col-12 d-flex justify-content-between pt-1">
          OPC activos:
          <span class="badge badge-pill" :class="opcStatusClass">{{
            opcStatusIcon
          }}</span>
        </div>
        <div class="col-12 d-flex justify-content-between pt-1">
          Calificado:
          <span class="badge badge-pill" :class="user.qualified ? 'badge-success' : 'badge-danger'">{{ user.qualified ?
            '&#x2714;' : '&#x2716;' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserStatus',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      opcStatus: null, // 'indefinite', 'finite', o null
    };
  },
  computed: {
    opcStatusClass() {
      // Si es indefinido, siempre verde
      if (this.opcStatus === 'indefinite') {
        return 'badge-success';
      }
      // Si es finito, evaluar si está activo
      return this.user.active ? 'badge-success' : 'badge-danger';
    },
    opcStatusIcon() {
      // Si es indefinido, siempre check
      if (this.opcStatus === 'indefinite') {
        return '✔';
      }
      // Si es finito, evaluar si está activo
      return this.user.active ? '✔' : '✖';
    },
  },
  mounted() {
    this.checkOpcStatus();
  },
  watch: {
    // Vigilar cambios en el usuario para re-verificar
    'user.id': {
      handler() {
        this.checkOpcStatus();
      },
    },
  },
  methods: {
    async checkOpcStatus() {
      try {
        const response = await fetch('/users/check-opc');
        
        if (!response.ok) {
          throw new Error('Error al verificar estado de OPC');
        }
        
        const data = await response.json();
        this.opcStatus = data.opc_status;
        
      } catch (error) {
        console.error('Error al verificar el estado de OPC:', error);
        // En caso de error, usar el estado original del usuario
        this.opcStatus = 'finite';
      }
    },
  },
};
</script>