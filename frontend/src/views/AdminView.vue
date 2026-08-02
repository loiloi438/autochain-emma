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

      <section class="panel users-panel">
        <h2>Gestion des utilisateurs</h2>
        <button class="ghost" @click="loadUsers" :disabled="loading">Rafraîchir</button>
        <table>
          <thead>
            <tr><th>Utilisateur</th><th>Email</th><th>Wallet</th><th>Rôles</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.wallet_address || '—' }}</td>
              <td>
                <span v-for="role in user.roles" :key="role" class="role-pill">{{ role }}</span>
              </td>
              <td>
                <button class="ghost" @click="editUserRoles(user)">Modifier</button>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="5" class="muted">Aucun utilisateur chargé.</td>
            </tr>
          </tbody>
        </table>
      </section>

      <section v-if="editingUser" class="panel edit-panel">
        <h2>Modifier rôles de {{ editingUser.name }}</h2>
        <div class="roles-grid">
          <label v-for="role in availableRoles" :key="role">
            <input type="checkbox" :value="role" v-model="selectedRoles" />
            {{ role }}
          </label>
        </div>
        <div class="form-actions">
          <button @click="saveUserRoles" :disabled="savingRoles">Enregistrer</button>
          <button class="ghost" @click="cancelEdit">Annuler</button>
        </div>
      </section>

      <div class="contract panel">
        <h2>Configuration Sepolia</h2>
        <div class="form-row">
          <label>Adresse du contrat</label>
          <input v-model="contractAddress" placeholder="0x..." />
        </div>
        <div class="form-row">
          <label>Réseau attendu</label>
          <input v-model="contractNetwork" placeholder="Sepolia" />
        </div>
        <div class="form-actions">
          <button @click="saveContractConfig" :disabled="savingContract">Enregistrer la configuration</button>
        </div>
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
import { computed, onMounted, ref } from 'vue'
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
const contractAddress = ref('')
const contractNetwork = ref('Sepolia')
const savingContract = ref(false)
const users = ref([])
const editingUser = ref(null)
const selectedRoles = ref([])
const availableRoles = ['admin', 'manager', 'driver', 'garage', 'auditor']
const savingRoles = ref(false)

async function loadUsers() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/admin/users')
    users.value = data
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Impossible de charger les utilisateurs.'
  } finally {
    loading.value = false
  }
}

function editUserRoles(user) {
  editingUser.value = user
  selectedRoles.value = [...user.roles]
}

function cancelEdit() {
  editingUser.value = null
  selectedRoles.value = []
}

async function saveUserRoles() {
  if (!editingUser.value) return
  savingRoles.value = true
  error.value = null
  try {
    const { data } = await api.post(`/admin/users/${editingUser.value.id}/roles`, {
      roles: selectedRoles.value,
    })
    editingUser.value.roles = data.roles || []
    const index = users.value.findIndex((user) => user.id === editingUser.value.id)
    if (index !== -1) {
      users.value[index].roles = data.roles || []
    }
    cancelEdit()
    toast.push('Rôles utilisateur enregistrés.', 'success')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Impossible d’enregistrer les rôles.'
  } finally {
    savingRoles.value = false
  }
}

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
    contractAddress.value = data.address || ''
    contractNetwork.value = data.network || 'Sepolia'
    toast.push('Info contrat chargée', 'info')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erreur'
    toast.push(error.value, 'error')
  }
}

async function saveContractConfig() {
  savingContract.value = true
  error.value = null
  try {
    await api.post('/admin/contract/config', {
      address: contractAddress.value,
      network: contractNetwork.value,
    })
    toast.push('Configuration Sepolia enregistrée.', 'success')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erreur'
    toast.push(error.value, 'error')
  } finally {
    savingContract.value = false
  }
}

onMounted(() => {
  loadUsers()
})
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
.role-pill {
  display: inline-flex;
  gap: 0.25rem;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #e2e8f0;
  color: #334155;
  font-size: 0.75rem;
  margin-right: 0.25rem;
}
.users-panel { margin-top: 1rem; }
.edit-panel { margin-top: 1rem; }
.roles-grid { display: grid; grid-template-columns: repeat(3, minmax(120px, 1fr)); gap: 0.75rem; margin: 1rem 0; }
.form-row { margin-bottom: 1rem; display: grid; gap: 0.5rem; }
.form-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
</style>
