import { createRouter, createWebHistory } from 'vue-router'
import ClientBookingPage from '../pages/ClientBookingPage.vue'
import AdminRulesPage from '../pages/AdminRulesPage.vue'

const routes = [
  { path: '/', component: ClientBookingPage },
  { path: '/admin/rules', component: AdminRulesPage }
]

export default createRouter({
  history: createWebHistory(),
  routes
})
