<template>
  <div class="login-container">
    <!-- Navbar -->
    <nav class="navbar">
      <div class="nav-content">
        <RouterLink class="logo" to="/">
          <span class="logo-icon">🔗</span>
          <span class="logo-text">AutoChain Emma+</span>
        </RouterLink>
        <RouterLink class="nav-back" to="/">
          <span>← Retour à l'accueil</span>
        </RouterLink>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="login-wrapper">
      <div class="login-left">
        <div class="left-content">
          <h1>Gestion de flotte sécurisée</h1>
          <p>Accédez à votre tableau de bord et gérez votre parc avec la puissance de la blockchain.</p>

          <div class="benefits-list">
            <div class="benefit-item">
              <div class="benefit-icon">🔐</div>
              <div>
                <strong>Flux de travail fluide</strong>
                <p>Des tableaux de bord clairs et des rôles adaptés à chaque utilisateur.</p>
              </div>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">✓</div>
              <div>
                <strong>Audit et conformité</strong>
                <p>Suivi complet avec preuves immuables.</p>
              </div>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">🔗</div>
              <div>
                <strong>Sécurité maximale</strong>
                <p>Authentification Web3 et permissions granulaires.</p>
              </div>
            </div>
          </div>

          <div class="trust-box">
            <p>✓ Utilisé par les plus grands gestionnaires de flotte</p>
          </div>
        </div>
      </div>

      <div class="login-right">
        <div class="login-card">
          <h2>Connexion</h2>
          <p>Accédez à votre tableau de bord de flotte</p>

          <form @submit.prevent="submit">
            <div class="form-group">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="votre@email.com"
                required
              />
            </div>

            <div class="form-group">
              <label for="password">Mot de passe</label>
              <input
                id="password"
                v-model="password"
                type="password"
                placeholder="••••••••"
                required
              />
            </div>

            <button class="button primary btn-full" type="submit" :disabled="isConnecting || isSigning">Se connecter</button>
            <button
              class="button ghost btn-full"
              type="button"
              @click="loginWithWallet"
              :disabled="isConnecting || isSigning"
            >
              <template v-if="isConnecting">Connecting…</template>
              <template v-else-if="isSigning">Signing…</template>
              <template v-else>🦊 Connexion MetaMask</template>
            </button>

            <p v-if="walletAddress" class="wallet-info">
              Wallet détecté : <strong>{{ short(walletAddress) }}</strong>
            </p>
            <p v-if="error" class="error-message">{{ error }}</p>
          </form>

          <div class="divider">
            <span>ou tester avec un compte démo</span>
          </div>

          <div class="demo-section">
            <p class="demo-info">Mot de passe : <strong>password</strong></p>
            <div class="demo-accounts">
              <div
                v-for="(account, index) in demoAccounts"
                  :key="index"
                  class="demo-account"
                  @click="fillDemoAccount(account)"
              >
                <span class="account-emoji">{{ accountEmojis[index] }}</span>
                <div class="account-label">{{ accountLabels[index] }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import { useToastStore } from '../stores/toast'

const auth = useAuthStore()
const wallet = useWalletStore()
const router = useRouter()
const toast = useToastStore()

const email = ref('gestionnaire@autochain.test')
const password = ref('password')
const error = ref('')
const isSigning = ref(false)
const isConnecting = ref(false)

const demoAccounts = ref([
  'admin@autochain.test',
  'gestionnaire@autochain.test',
  'chauffeur@autochain.test',
  'garage@autochain.test',
  'auditeur@autochain.test',
])

const accountEmojis = ['👨‍💼', '👨‍💻', '🚗', '🔧', '📋']
const accountLabels = ['Admin', 'Gestionnaire', 'Chauffeur', 'Garage', 'Auditeur']

const walletAddress = computed(() => wallet.address)

function short(value) {
  return value ? `${value.slice(0, 6)}...${value.slice(-4)}` : ''
}

async function submit() {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    toast.push('Connexion réussie', 'info')
    router.push({ name: auth.homeRoute })
  } catch (e) {
    const msg = e.response?.data?.message || 'Connexion impossible'
    error.value = msg
    toast.push(msg, 'error')
  }
}

async function loginWithWallet() {
  error.value = ''
  isConnecting.value = true
  try {
    const address = await wallet.connect()
    isConnecting.value = false
    const message = `AutoChain Emma+ authentication request for ${address}`
    toast.push('Demande de signature envoyée à MetaMask', 'info', 5000)
    isSigning.value = true
    const signature = await wallet.signMessage(message)
    isSigning.value = false
    toast.push('Signature reçue — connexion en cours', 'info')
    await auth.loginWithWallet(address, message, signature)
    toast.push('Connexion via MetaMask réussie', 'info')
    router.push({ name: auth.homeRoute })
  } catch (e) {
    isConnecting.value = false
    isSigning.value = false
    const msg = e.response?.data?.message || e.message || 'Impossible de se connecter avec MetaMask.'
    error.value = msg
    toast.push(msg, 'error')
  }
}

function fillDemoAccount(account) {
  email.value = account
  password.value = 'password'
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.login-container {
  width: 100%;
  min-height: 100vh;
  background-color: white;
  display: flex;
  flex-direction: column;
}

/* NAVBAR */
.navbar {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 0 20px;
}

.nav-content {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 0;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  font-weight: 700;
  font-size: 18px;
  text-decoration: none;
  transition: opacity 0.3s;
}

.logo:hover {
  opacity: 0.9;
}

.logo-icon {
  font-size: 24px;
}

.nav-back {
  color: white;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: opacity 0.3s;
}

.nav-back:hover {
  opacity: 0.8;
}

/* LOGIN WRAPPER */
.login-wrapper {
  flex: 1;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}

@media (max-width: 1024px) {
  .login-wrapper {
    grid-template-columns: 1fr;
  }
}

/* LOGIN LEFT */
.login-left {
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
  padding: 60px 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 1024px) {
  .login-left {
    display: none;
  }
}

.left-content h1 {
  font-size: 36px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 16px 0;
  line-height: 1.3;
}

.left-content p {
  font-size: 16px;
  color: #6b7280;
  margin: 0 0 40px 0;
  line-height: 1.6;
}

.benefits-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-bottom: 32px;
}

.benefit-item {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.benefit-icon {
  width: 40px;
  height: 40px;
  background-color: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}

.benefit-item strong {
  display: block;
  font-size: 16px;
  color: #1f2937;
  margin-bottom: 4px;
}

.benefit-item p {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

.trust-box {
  padding: 16px;
  background-color: rgba(16, 185, 129, 0.1);
  border-left: 4px solid #10b981;
  border-radius: 6px;
}

.trust-box p {
  margin: 0;
  font-size: 14px;
  color: #047857;
  font-weight: 500;
}

/* LOGIN RIGHT */
.login-right {
  padding: 60px 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 1024px) {
  .login-right {
    padding: 40px 20px;
  }
}

.login-card {
  width: 100%;
  max-width: 420px;
}

.login-card h2 {
  font-size: 28px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.login-card p {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 32px 0;
}

/* FORM */
.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
  display: block;
}

.form-group input {
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s;
  background-color: #f9fafb;
}

.form-group input:focus {
  outline: none;
  border-color: #3b82f6;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input::placeholder {
  color: #9ca3af;
}

/* BUTTONS */
.button {
  padding: 12px 32px;
  border-radius: 8px;
  border: none;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
}

.button.primary {
  background-color: #3b82f6;
  color: white;
}

.button.primary:hover {
  background-color: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
}

.button.ghost {
  background-color: transparent;
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.button.ghost:hover {
  background-color: #eff6ff;
}

.btn-full {
  width: 100%;
  margin-bottom: 12px;
}

.wallet-info {
  margin: 16px 0 0 0;
  font-size: 13px;
  color: #6b7280;
  padding: 12px;
  background-color: #f0fdf4;
  border-radius: 6px;
}

.error-message {
  margin: 16px 0 0 0;
  font-size: 13px;
  color: #991b1b;
  padding: 12px;
  background-color: #fee2e2;
  border-radius: 6px;
}

/* DIVIDER */
.divider {
  margin: 32px 0;
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background-color: #e5e7eb;
}

.divider span {
  background-color: white;
  padding: 0 12px;
  position: relative;
}

/* DEMO SECTION */
.demo-section {
  padding: 20px;
  background-color: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.demo-info {
  text-align: center;
  font-size: 13px;
  color: #6b7280;
  margin: 0 0 16px 0;
}

.demo-info strong {
  color: #374151;
  font-family: 'Courier New', monospace;
  background-color: white;
  padding: 2px 6px;
  border-radius: 3px;
}

.demo-accounts {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
}

.demo-account {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 13px;
  color: #374151;
}

.demo-account:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
  transform: translateX(4px);
}

.account-emoji {
  font-size: 18px;
  flex-shrink: 0;
}

.account-label {
  flex: 1;
  font-weight: 500;
}
</style>
