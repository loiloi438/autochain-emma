import { defineStore } from 'pinia'
import api from '../services/api'

function normalizeRoleNames(roles) {
  return (roles || [])
    .map((role) => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
    .map((role) => role.toLowerCase())
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('autochain_token') || '',
    user: null,
  }),
  getters: {
    roleNames: (state) => normalizeRoleNames(state.user?.roles),
    isAuthenticated: (state) => Boolean(state.token),
    roles: (state) => normalizeRoleNames(state.user?.roles),
    hasRole: (state) => (roleName) => (state.user?.roles || []).some((role) => {
      const name = typeof role === 'string' ? role : role?.name
      return name?.toLowerCase() === roleName.toLowerCase()
    }),
    homeRoute: (state) => {
      const roles = normalizeRoleNames(state.user?.roles)

      if (roles.includes('admin')) return 'admin'
      if (roles.includes('auditor')) return 'auditor'
      if (roles.includes('driver')) return 'driver'
      if (roles.includes('garage')) return 'garage'

      return 'dashboard'
    },
  },
  actions: {
    async login(email, password) {
      const { data } = await api.post('/login', { email, password })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('autochain_token', data.token)
    },
    async loginWithWallet(walletAddress, message, signature) {
      const { data } = await api.post('/login', {
        wallet_address: walletAddress,
        message,
        signature,
      })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('autochain_token', data.token)
    },
    async fetchMe() {
      if (!this.token) return
      const { data } = await api.get('/me')
      this.user = {
        ...data,
        roles: data.roles || [],
      }
    },
    async linkWallet(walletAddress) {
      const { data } = await api.post('/wallet', { wallet_address: walletAddress })
      this.user = data
    },
    logout() {
      this.token = ''
      this.user = null
      localStorage.removeItem('autochain_token')
    },
  },
})
