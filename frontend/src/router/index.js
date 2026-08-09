import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LandingView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import VehiclesView from '../views/VehiclesView.vue'
import VehicleDetailView from '../views/VehicleDetailView.vue'
import AuditorView from '../views/AuditorView.vue'
import AdminView from '../views/AdminView.vue'
import DriverView from '../views/DriverView.vue'
import GarageView from '../views/GarageView.vue'
import DriversView from '../views/DriversView.vue'
import MaintenanceView from '../views/MaintenanceView.vue'
import AlertsView from '../views/AlertsView.vue'
import DocumentsView from '../views/DocumentsView.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingView, meta: { guest: true } },
  { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { auth: true } },
  { path: '/vehicles', name: 'vehicles', component: VehiclesView, meta: { auth: true, roles: ['manager', 'admin', 'driver', 'garage', 'auditor'] } },
  { path: '/vehicules', name: 'vehicules', component: VehiclesView, meta: { auth: true, roles: ['manager', 'admin', 'driver', 'garage', 'auditor'] } },
  { path: '/chauffeurs', name: 'chauffeurs', component: DriversView, meta: { auth: true, roles: ['manager', 'admin'] } },
  { path: '/entretien', name: 'entretien', component: MaintenanceView, meta: { auth: true, roles: ['manager', 'admin', 'garage'] } },
  { path: '/alerts', name: 'alerts', component: AlertsView, meta: { auth: true, roles: ['manager', 'admin', 'driver', 'auditor'] } },
  { path: '/documents', name: 'documents', component: DocumentsView, meta: { auth: true, roles: ['manager', 'admin', 'driver', 'garage'] } },
  { path: '/vehicles/:id', name: 'vehicle-detail', component: VehicleDetailView, meta: { auth: true, roles: ['manager', 'admin', 'driver', 'garage', 'auditor'] } },
  { path: '/driver', name: 'driver', component: DriverView, meta: { auth: true, roles: ['driver', 'manager', 'admin'] } },
  { path: '/garage', name: 'garage', component: GarageView, meta: { auth: true, roles: ['garage', 'admin'] } },
  { path: '/auditor', name: 'auditor', component: AuditorView, meta: { auth: true, roles: ['auditor', 'admin'] } },
  { path: '/admin', name: 'admin', component: AdminView, meta: { auth: true, roles: ['admin'] } },
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
  if (to.meta.guest && auth.isAuthenticated) return { name: auth.homeRoute }

  if (to.meta.roles && auth.isAuthenticated) {
    const allowed = to.meta.roles.some((role) => auth.hasRole(role))
    if (!allowed) return { name: auth.homeRoute }
  }

  return true
})

export default router
