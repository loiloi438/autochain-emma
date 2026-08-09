<template>
  <div v-if="auth.isAuthenticated" class="app-layout" :class="`role-${auth.homeRoute}`">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="logo-icon">⛓️</div>
        <div class="brand-text">
          <strong>AutoChain</strong>
          <span>Emma+</span>
        </div>
      </div>

      <nav class="sidebar-menu">
        <span class="menu-caption">Navigation {{ roleProfile.short }}</span>
        <RouterLink
          v-for="item in navigationItems"
          :key="item.to"
          :to="item.to"
          class="menu-item"
          :class="{ active: isActive(item.match || item.to) }"
        >
          <span class="icon">{{ item.icon }}</span>
          <span>{{ item.label }}</span>
          <span v-if="item.to === '/alerts' && alertCount > 0" class="badge">{{ alertCount }}</span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="avatar">{{ (auth.user?.name || 'A')[0].toUpperCase() }}</div>
          <div class="user-details">
            <strong>{{ auth.user?.name }}</strong>
            <span>{{ auth.user?.email }}</span>
          </div>
        </div>
        <button class="menu-item logout" @click="logout">
          <span class="icon">🚪</span>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
      <!-- Header -->
      <header class="header">
        <div>
          <span class="header-kicker">{{ currentSection }}</span>
          <h1 class="page-title">{{ roleProfile.title }}</h1>
          <p class="page-context">{{ roleProfile.context }}</p>
        </div>

        <div class="header-actions">
          <div class="notifications">
            <button class="notification-bell">
              🔔
              <span v-if="alertCount > 0" class="badge">{{ alertCount }}</span>
            </button>
          </div>

          <button class="wallet-button" @click="connectWallet">
            💳
            {{ wallet.address ? short(wallet.address) : 'Se connecter via Wallet' }}
          </button>

          <div class="profile-menu">
            <span class="user-badge">{{ auth.user?.name }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
        <RouterView />
      </main>
    </div>

    <GlobalToasts />
  </div>

  <!-- Login/Unauthenticated Layout -->
  <div v-else class="auth-layout">
    <RouterView />
    <GlobalToasts />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useAuthStore } from './stores/auth'
import { useWalletStore } from './stores/wallet'
import api from './services/api'
import { useRouter, useRoute } from 'vue-router'
import GlobalToasts from './components/GlobalToasts.vue'

const auth = useAuthStore()
const wallet = useWalletStore()
const router = useRouter()
const route = useRoute()
const alertCount = ref(0)

const roleProfiles = {
  admin: {
    short: 'admin',
    title: 'Centre de pilotage',
    context: 'Administration, rôles et supervision de la plateforme',
    navigation: [
      { to: '/dashboard', label: 'Tableau de bord', icon: '📊' },
      { to: '/admin', label: 'Administration', icon: '⚙️' },
      { to: '/vehicles', label: 'Parc automobile', icon: '🚗' },
      { to: '/alerts', label: 'Alertes', icon: '⚠️' },
      { to: '/documents', label: 'Documents', icon: '📋' },
    ],
  },
  manager: {
    short: 'gestionnaire',
    title: 'Pilotage de flotte',
    context: 'Décisions opérationnelles et suivi des performances',
    navigation: [
      { to: '/dashboard', label: 'Synthèse', icon: '📊' },
      { to: '/vehicles', label: 'Parc automobile', icon: '🚗' },
      { to: '/chauffeurs', label: 'Chauffeurs', icon: '👤' },
      { to: '/entretien', label: 'Maintenance', icon: '🔧' },
      { to: '/alerts', label: 'Alertes', icon: '⚠️' },
      { to: '/documents', label: 'Documents', icon: '📋' },
    ],
  },
  driver: {
    short: 'conducteur',
    title: 'Espace conducteur',
    context: 'Votre mission, votre véhicule et vos relevés',
    navigation: [
      { to: '/dashboard', label: 'Tableau de bord', icon: '📊' },
      { to: '/driver', label: 'Ma mission', icon: '🛣️' },
      { to: '/vehicles', label: 'Mon véhicule', icon: '🚗' },
      { to: '/alerts', label: 'Mes alertes', icon: '⚠️' },
      { to: '/documents', label: 'Mes documents', icon: '📋' },
    ],
  },
  garage: {
    short: 'atelier',
    title: 'Atelier de maintenance',
    context: 'Interventions, pièces et historique technique',
    navigation: [
      { to: '/dashboard', label: 'Tableau de bord', icon: '📊' },
      { to: '/garage', label: 'Nouvelle intervention', icon: '🔧' },
      { to: '/vehicles', label: 'Véhicules', icon: '🚗' },
      { to: '/entretien', label: 'Historique atelier', icon: '🧰' },
      { to: '/documents', label: 'Documents techniques', icon: '📋' },
    ],
  },
  auditor: {
    short: 'audit',
    title: 'Espace audit',
    context: 'Vérification des preuves et historique certifié',
    navigation: [
      { to: '/dashboard', label: 'Tableau de bord', icon: '📊' },
      { to: '/auditor', label: 'Audit véhicule', icon: '🔎' },
      { to: '/vehicles', label: 'Parc consultable', icon: '🚗' },
      { to: '/alerts', label: 'Événements', icon: '⚠️' },
    ],
  },
}

const roleProfile = computed(() => roleProfiles[auth.homeRoute] || roleProfiles.manager)
const navigationItems = computed(() => roleProfile.value.navigation)
const currentSection = computed(() => {
  const item = navigationItems.value.find((entry) => isActive(entry.match || entry.to))
  return item?.label || 'Vue d’ensemble'
})

async function loadAlertCount() {
  if (!auth.isAuthenticated) {
    alertCount.value = 0
    return
  }

  try {
    const { data } = await api.get('/alerts')
    alertCount.value = Array.isArray(data) ? data.length : 0
  } catch {
    alertCount.value = 0
  }
}

watch(() => auth.isAuthenticated, loadAlertCount, { immediate: true })

function isActive(path) {
  return route.path.startsWith(path)
}

const rolesText = computed(() => (auth.user?.roles || []).map((role) => role.name).join(', '))

function short(value) {
  return `${value.slice(0, 6)}...${value.slice(-4)}`
}

async function connectWallet() {
  try {
    const address = await wallet.connect()
    await auth.linkWallet(address)
  } catch (error) {
    alert(error.message)
  }
}

function logout() {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  background: transparent;
}

/* ============ SIDEBAR ============ */
.sidebar {
  width: 272px;
  flex: 0 0 272px;
  background: #172b35;
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 12px 0 30px rgba(23, 43, 53, 0.12);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 24px 22px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-icon {
  display: grid;
  width: 42px;
  height: 42px;
  place-items: center;
  border-radius: 12px;
  background: var(--role-accent);
  font-size: 24px;
  box-shadow: 0 8px 18px color-mix(in srgb, var(--role-accent) 35%, transparent);
}

.brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1;
}

.brand-text strong {
  font-size: 18px;
  font-weight: 700;
}

.brand-text span {
  font-size: 12px;
  opacity: 0.7;
}

.sidebar-menu {
  flex: 1;
  padding: 24px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.menu-caption {
  padding: 0 12px 10px;
  color: rgba(255, 255, 255, .42);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .14em;
  text-transform: uppercase;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 13px 14px;
  border-radius: 11px;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  transition: all 0.2s;
  position: relative;
  font-size: 14px;
  background: none;
  border: none;
  cursor: pointer;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.menu-item.active {
  background: linear-gradient(100deg, var(--role-accent), color-mix(in srgb, var(--role-accent) 72%, #172b35));
  color: white;
  font-weight: 600;
}

.menu-item .icon {
  font-size: 18px;
  min-width: 24px;
}

.menu-item .badge {
  margin-left: auto;
  background-color: #ef4444;
  color: white;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 700;
}

.sidebar-footer {
  padding: 18px 14px 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--role-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
}

.user-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.user-details strong {
  font-size: 13px;
}

.user-details span {
  font-size: 11px;
  opacity: 0.6;
}

.logout {
  width: 100%;
}

/* ============ MAIN WRAPPER ============ */
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ============ HEADER ============ */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 22px 34px;
  background: rgba(255, 255, 255, .88);
  border-bottom: 1px solid #e4e8e1;
  box-shadow: 0 8px 24px rgba(30, 50, 42, .04);
  backdrop-filter: blur(14px);
}

.header-kicker {
  display: block;
  margin-bottom: 3px;
  color: var(--role-accent);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .13em;
  text-transform: uppercase;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #172033;
  margin: 0;
}

.page-context {
  margin: 4px 0 0;
  color: #6c7585;
  font-size: 13px;
}

.role-admin { --role-accent: #c97729; }
.role-manager { --role-accent: #167d72; }
.role-driver { --role-accent: #2879a7; }
.role-garage { --role-accent: #4b8f55; }
.role-auditor { --role-accent: #8065a8; }

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.notifications {
  position: relative;
}

.notification-bell {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  position: relative;
  padding: 0;
}

.notification-bell .badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #ef4444;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 700;
}

.wallet-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background-color: var(--role-accent);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background-color 0.2s;
}

.wallet-button:hover {
  filter: brightness(.92);
}

.profile-menu {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-badge {
  padding: 6px 12px;
  background-color: #eef2ed;
  border: 1px solid #e0e7df;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #364238;
}

/* ============ PAGE CONTENT ============ */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 34px;
  min-width: 0;
}

/* ============ AUTH LAYOUT ============ */
.auth-layout {
  width: 100%;
  height: 100%;
}

@media (max-width: 900px) {
  .sidebar { width: 86px; flex-basis: 86px; }
  .brand-text, .menu-caption, .menu-item span:not(.icon), .user-details, .logout span:not(.icon) { display: none; }
  .sidebar-brand { justify-content: center; padding: 18px 12px; }
  .sidebar-menu { padding-inline: 10px; }
  .menu-item { justify-content: center; padding-inline: 10px; }
  .menu-item .badge { position: absolute; top: 3px; right: 4px; margin: 0; padding: 1px 5px; }
  .sidebar-footer { padding-inline: 10px; }
  .user-info { justify-content: center; }
  .header { padding: 18px 22px; }
  .header-actions { gap: 10px; }
  .wallet-button { padding: 10px; font-size: 0; }
  .wallet-button:first-letter { font-size: 16px; }
  .user-badge { max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .page-content { padding: 22px; }
}

@media (max-width: 560px) {
  .app-layout { display: block; }
  .sidebar { width: 100%; height: auto; display: block; }
  .sidebar-brand { justify-content: flex-start; padding: 12px 16px; }
  .sidebar-menu { flex-direction: row; overflow-x: auto; padding: 8px 10px 10px; }
  .menu-item { flex: 0 0 auto; gap: 7px; padding: 9px 11px; }
  .menu-item span:not(.icon) { display: inline; font-size: 12px; }
  .sidebar-footer { display: none; }
  .main-wrapper { min-height: calc(100vh - 72px); }
  .header { padding: 16px; align-items: flex-start; }
  .page-title { font-size: 21px; }
  .page-context { max-width: 210px; font-size: 12px; }
  .notifications, .profile-menu { display: none; }
  .page-content { padding: 16px; }
}
</style>
