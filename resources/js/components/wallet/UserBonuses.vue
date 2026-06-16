<template>
  <div class="col-xl-8 col-md-8 col-12">
    <div class="card card-statistics">
      <!-- Sección de resumen mensual -->
      <div class="card-header">
        <h4 class="card-title">Indicadores de resumen mensual</h4>
        <div class="d-flex align-items-center">
          <p class="card-text font-small-2 mr-25 mb-0">Actualizado este mes</p>
        </div>
      </div>
      <div class="card-body statistics-body pb-0">
        <div class="row">
          <bonus-item
            v-for="bonus in monthlyBonuses"
            :key="bonus.key"
            :icon="bonus.icon"
            :value="bonus.value"
            :label="bonus.label"
            :bg-class="bonus.bgClass"
          />
        </div>
      </div>

      <!-- Sección acumulativa -->
      <div class="card-header pt-0">
        <h4 class="card-title">Indicadores acumulativos</h4>
        <div class="d-flex align-items-center">
          <p class="card-text font-small-2 mr-25 mb-0">
            Actualizado desde el último corte binario
          </p>
        </div>
      </div>
      <div class="card-body statistics-body">
        <div class="row">
          <bonus-item
            v-for="bonus in cumulativeBonuses"
            :key="bonus.key"
            :icon="bonus.icon"
            :value="bonus.value"
            :label="bonus.label"
            :bg-class="bonus.bgClass"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api';
import {
  TrendingUpIcon,
  PieChartIcon,
  AwardIcon,
  UserPlusIcon,
  VideoIcon,
  PlayCircleIcon,
} from 'vue-feather-icons';

// Componente reutilizable para cada bono
const BonusItem = {
  name: 'BonusItem',
  props: {
    icon: { type: String, required: true },
    value: { type: [String, Number], required: true },
    label: { type: String, required: true },
    bgClass: { type: String, default: 'bg-light-primary' },
  },
  components: {
    TrendingUpIcon,
    PieChartIcon,
    AwardIcon,
    UserPlusIcon,
    VideoIcon,
    PlayCircleIcon,
  },
  template: `
    <div class="col-xl-4 col-sm-6 col-12 pb-1">
      <div class="media">
        <div class="avatar mr-2" :class="bgClass">
          <div class="avatar-content">
            <component :is="icon + 'Icon'" size="2x" class="custom-class" />
          </div>
        </div>
        <div class="media-body my-auto">
          <h4 class="font-weight-bolder mb-0">{{ value }}</h4>
          <p class="card-text font-small-3 mb-0">{{ label }}</p>
        </div>
      </div>
    </div>
  `,
};

export default {
  name: 'UserBonuses',
  components: {
    BonusItem,
  },
  data() {
    return {
      bonuses: {
        fast_cash_bonus: 0,
        start_bonus: 0,
        marketing_bonus: 0,
        producer_bonus: 0,
        course_sale_bonus: 0,
        binary_bonus: 0,
        rank_bonus: 0,
        expansion_bonus: 0,
      },
      loading: false,
    };
  },
  computed: {
    // Bonos mensuales
    monthlyBonuses() {
      return [
        {
          key: 'expansion',
          icon: 'TrendingUp',
          value: `$ ${this.bonuses.expansion_bonus}`,
          label: 'Bono de expansión',
          bgClass: 'bg-light-primary',
        },
        {
          key: 'binary',
          icon: 'PieChart',
          value: this.bonuses.binary_bonus,
          label: 'Bono binario',
          bgClass: 'bg-light-info',
        },
        {
          key: 'rank',
          icon: 'Award',
          value: `$ ${this.bonuses.rank_bonus}`,
          label: 'Bono generacional',
          bgClass: 'bg-light-info',
        },
      ];
    },
    // Bonos acumulativos
    cumulativeBonuses() {
      return [
        {
          key: 'fast_cash',
          icon: 'UserPlus',
          value: `$ ${this.bonuses.fast_cash_bonus}`,
          label: 'Bono de efectivo rápido',
          bgClass: 'bg-light-danger',
        },
        {
          key: 'producer',
          icon: 'Video',
          value: `$ ${this.bonuses.producer_bonus}`,
          label: 'Bono de productor',
          bgClass: 'bg-light-danger',
        },
        {
          key: 'course_sale',
          icon: 'PlayCircle',
          value: `$ ${this.bonuses.course_sale_bonus}`,
          label: 'Bono por venta de curso',
          bgClass: 'bg-light-success',
        },
      ];
    },
  },
  mounted() {
    this.getBonuses();
  },
  methods: {
    async getBonuses() {
      if (this.loading) return;
      
      this.loading = true;
      
      try {
        const { data } = await api.get('/users/get-bonuses');
        this.bonuses = data;
        
        // Solo log en desarrollo
        if (process.env.NODE_ENV === 'development') {
          console.log('Bonos actualizados:', data);
        }
      } catch (error) {
        console.error('Error al obtener bonos:', error.message);
        
        // Aquí podrías mostrar un toast o notificación al usuario
        // this.$toast.error('Error al cargar los bonos');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Opcional: Animación suave al cargar los bonos */
.media {
  transition: transform 0.2s ease;
}

.media:hover {
  transform: translateY(-2px);
}
</style>