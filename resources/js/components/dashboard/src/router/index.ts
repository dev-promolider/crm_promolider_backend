import { createRouter, createWebHashHistory } from 'vue-router'
import AppLayout from '@/component/AppLayout.vue'
import PromoliderDashboard from '@/component/PromoliderDashboard.vue'
import LiderbotPage from '@/component/LiderbotPage.vue'
import LeadBoostPage from '@/component/LeadBoostPage.vue'
import SmartFunnelPage from '@/component/SmartFunnelPage.vue'
import AppEcomerAcademicoPage from '@/component/AppEcomerAcademicoPage.vue'
import PagoView from '@/views/PagoView.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      component: AppLayout,
      children: [
        { path: '',              component: PromoliderDashboard },
        { path: 'liderbot',     component: LiderbotPage },
        { path: 'leadboost',    component: LeadBoostPage },
        { path: 'smartfunnel',  component: SmartFunnelPage },
        { path: 'ecomercademico', component: AppEcomerAcademicoPage },
        { path: 'pago',         component: PagoView },
      ]
    }
  ]
})

export default router
