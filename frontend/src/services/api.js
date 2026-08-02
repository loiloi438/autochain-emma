import axios from 'axios'
import { useToastStore } from '../stores/toast'

const apiBaseURL =
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  'http://127.0.0.1:8000/api'

axios.defaults.baseURL = apiBaseURL

const api = axios.create({
  baseURL: apiBaseURL,
  withCredentials: true,
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('autochain_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('autochain_token')
    }

    if (error.response?.status === 403) {
      const message = error.response?.data?.message || 'Vous n’êtes pas autorisé à effectuer cette action.'
      const toastStore = useToastStore()
      toastStore.push(message, 'error', 4000)
    }

    if (error.code === 'ERR_NETWORK') {
      const toastStore = useToastStore()
      toastStore.push('Le backend n’est pas disponible. Vérifiez que l’API est bien lancée.', 'error', 4000)
    }

    return Promise.reject(error)
  },
)

export default api
