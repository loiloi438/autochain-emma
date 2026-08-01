<template>
  <div class="admin-view">
    <div class="page-head">
      <div>
        <h1>Administration</h1>
        <p>Supervision des rôles, du contrat déployé et des états de synchronisation blockchain.</p>
      </div>
      <button class="ghost" @click="fetchContract">Charger l'adresse du contrat</button>
    </div>

    <div v-if="!auth.isAuthenticated" class="alert-panel">
      <p>Connecte-toi pour utiliser cette fonction.</p>
    </div>

    <div v-else-if="!auth.hasRole('admin')" class="alert-panel">
      <p>Accès refusé — rôle <code>admin</code> requis.</p>
    </div>

    <div v-else>
      <div class="controls">
        <button @click="syncRoles" :disabled="loading">{{ loading ? 'Synchronisation...' : 'Synchroniser les rôles' }}</button>
        <button @click="fetchContract" class="ghost">Charger info contrat</button>
      </div>

      <div class="grid cards">
        <article>
          <span class="stat-label">Profil courant</span>
          <p>{{ auth.user?.name || 'Admin' }}</p>
        </article>
        <article>
          <span class="stat-label">Rôles actifs</span>
          <p>{{ activeRoles.length ? activeRoles.join(', ') : 'Aucun' }}</p>
        </article>
        <article>
          <span class="stat-label">Permissions</span>
          <p>{{ permissionsCount }}</p>
        </article>
      </div>

      <div v-if="contract" class="contract panel">
        <h2>Contrat déployé</h2>
        <p><strong>Adresse :</strong> {{ contract.address }}</p>
        <p><strong>Réseau :</strong> {{ contract.network || 'inconnu' }}</p>
        <p v-if="contract.artifact?.roles">
          <strong>Rôles on-chain :</strong> {{ Object.keys(contract.artifact.roles).join(', ') }}
        </p>
      </div>

      <div v-if="mapping" class="panel result">
        <h2>Rôles synchronisés</h2>
        <table>
          <thead><tr><th>Role</th><th>User ID</th><th>Statut</th></tr></thead>
          <tbody>
            <tr v-for="(uid, role) in mapping" :key="role">
              <td>{{ role }}</td>
              <td>{{ uid ?? '— aucun utilisateur' }}</td>
              <td>{{ uid ? 'assigné' : 'non trouvé' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="error" class="error panel">{{ error }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import { useToastStore } from '../stores/toast'

const auth = useAuthStore()
const loading = ref(false)
const mapping = ref(null)
const error = ref(null)
const contract = ref(null)
const toast = useToastStore()

const activeRoles = computed(() => (auth.user?.roles || []).map((role) => role.name).filter(Boolean))
const permissionsCount = computed(() => (auth.user?.permissions || []).length)

async function syncRoles() {
  loading.value = true
  error.value = null
  mapping.value = null
  try {
    const { data } = await api.post('/blockchain/sync-roles')
    mapping.value = data.mapping || data
    toast.push('Synchronisation terminée', 'success')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erreur'
    toast.push(error.value, 'error')
  } finally {
    loading.value = false
  }
}

async function fetchContract() {
  try {
    const { data } = await api.get('/blockchain/contract')
    contract.value = data
    toast.push('Info contrat chargée', 'info')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erreur'
    toast.push(error.value, 'error')
  }
}
</script>

<style scoped>
.admin-view { padding: 1rem }
.controls { display:flex; gap:0.5rem; margin-bottom:0.5rem }
.ghost { background:transparent; border:1px solid #ccc; padding:0.4rem 0.6rem }
.result { margin-top: 1rem; background:#f7f7f7; padding:0.5rem }
.contract { margin-top:0.5rem }
.error { color: #b00020; margin-top: 0.5rem }
table { width:100%; border-collapse: collapse }
th, td { padding: 0.4rem; border-bottom:1px solid #eee; text-align:left }
</style>
