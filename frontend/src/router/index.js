import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LandingView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import VehiclesView from '../views/VehiclesView.vue'
import VehicleDetailView from '../views/VehicleDetailView.vue'
import AuditorView from '../views/AuditorView.vue'
import AdminView from '../views/AdminView.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingView, meta: { guest: true } },
  { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { auth: true } },
  { path: '/vehicles', name: 'vehicles', component: VehiclesView, meta: { auth: true } },
  { path: '/vehicles/:id', name: 'vehicle-detail', component: VehicleDetailView, meta: { auth: true } },
  { path: '/auditor', name: 'auditor', component: AuditorView, meta: { auth: true } },
  { path: '/admin', name: 'admin', component: AdminView, meta: { auth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (auth.token && !auth.user) {
    try {
      await auth.fetchMe()
    } catch {
      auth.logout()
    }
  }

  if (to.meta.auth && !auth.isAuthenticated) return { name: 'login' }
  if (to.meta.guest && auth.isAuthenticated) return { name: 'dashboard' }
  return true
})

export default router
