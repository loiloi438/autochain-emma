<template>
  <div v-if="auth.isAuthenticated" class="app-layout">
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
        <RouterLink to="/dashboard" class="menu-item" :class="{ active: isActive('/dashboard') }">
          <span class="icon">🏠</span>
          <span>Tableau de Bord</span>
        </RouterLink>
        <RouterLink to="/vehicles" class="menu-item" :class="{ active: isActive('/vehicles') }">
          <span class="icon">🚗</span>
          <span>Véhicules</span>
        </RouterLink>
        <RouterLink to="/dashboard" class="menu-item" :class="{ active: isActive('/drivers') }">
          <span class="icon">👤</span>
          <span>Chauffeurs</span>
        </RouterLink>
        <RouterLink to="/dashboard" class="menu-item" :class="{ active: isActive('/maintenance') }">
          <span class="icon">🔧</span>
          <span>Entretien</span>
        </RouterLink>
        <RouterLink to="/dashboard" class="menu-item" :class="{ active: isActive('/alerts') }">
          <span class="icon">⚠️</span>
          <span>Alertes</span>
          <span v-if="alertCount > 0" class="badge">{{ alertCount }}</span>
        </RouterLink>
        <RouterLink to="/dashboard" class="menu-item" :class="{ active: isActive('/documents') }">
          <span class="icon">📋</span>
          <span>Documents</span>
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
        <h1 class="page-title">Tableau de Bord</h1>

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
import { computed, ref } from 'vue'
import { useAuthStore } from './stores/auth'
import { useWalletStore } from './stores/wallet'
import { useRouter, useRoute } from 'vue-router'
import GlobalToasts from './components/GlobalToasts.vue'

const auth = useAuthStore()
const wallet = useWalletStore()
const router = useRouter()
const route = useRoute()
const alertCount = ref(3)

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
  background-color: #f5f7fa;
}

/* ============ SIDEBAR ============ */
.sidebar {
  width: 280px;
  background-color: #1e3a5f;
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-icon {
  font-size: 32px;
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
  padding: 20px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
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
  background-color: #3b82f6;
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
  padding: 16px;
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
  background-color: #3b82f6;
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
  padding: 20px 30px;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

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
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background-color 0.2s;
}

.wallet-button:hover {
  background-color: #2563eb;
}

.profile-menu {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-badge {
  padding: 6px 12px;
  background-color: #e5e7eb;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

/* ============ PAGE CONTENT ============ */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 30px;
}

/* ============ AUTH LAYOUT ============ */
.auth-layout {
  width: 100%;
  height: 100%;
}
</style>
