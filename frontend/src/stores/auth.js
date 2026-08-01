import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('autochain_token') || '',
    user: null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    roles: (state) => state.user?.roles?.map((role) => role.name) || [],
    hasRole: (state) => (roleName) => state.user?.roles?.some((role) => role.name === roleName),
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
