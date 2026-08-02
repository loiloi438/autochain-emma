<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Garagiste</h1>
        <p class="muted">Certifiez les opérations de maintenance et enregistrez les pièces changées.</p>
      </div>
    </div>

    <form class="panel form-grid" @submit.prevent="submitMaintenance">
      <h2>Nouvelle opération</h2>
      <label>
        ID véhicule
        <input type="number" v-model.number="vehicleId" min="1" required />
      </label>
      <label>
        Type d’opération
        <input v-model="serviceType" placeholder="Ex: Vidange, Freins, Pneus" required />
      </label>
      <label>
        Pièces changées
        <input v-model="parts" placeholder="huile, filtre, plaquettes" />
      </label>
      <label>
        Commentaire
        <textarea v-model="description" placeholder="Détails de l’intervention"></textarea>
      </label>
      <button type="submit">Envoyer</button>
    </form>

    <section class="panel history-panel">
      <h2>Dernières maintenances</h2>
      <ul class="maintenance-list">
        <li v-for="item in maintenanceHistory" :key="item.id" class="maintenance-item">
          <strong>{{ item.service_type }}</strong>
          <p>{{ item.summary }}</p>
          <small class="muted">{{ formatDate(item.performed_at) }} · {{ item.status || 'Backend' }}</small>
        </li>
        <li v-if="!maintenanceHistory.length" class="muted">Aucune maintenance récente trouvée.</li>
      </ul>
    </section>

    <div v-if="error" class="error-box">{{ error }}</div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const vehicleId = ref(null)
const serviceType = ref('Vidange')
const parts = ref('')
const description = ref('')
const maintenanceHistory = ref([])
const error = ref('')

async function loadHistory() {
  try {
    const { data } = await api.get('/garage/maintenances')
    maintenanceHistory.value = data || []
  } catch {
    maintenanceHistory.value = []
  }
}

async function submitMaintenance() {
  error.value = ''
  try {
    await api.post(`/garage/vehicles/${vehicleId.value}/maintenance`, {
      service_type: serviceType.value,
      description: description.value,
      parts: parts.value.split(',').map((part) => part.trim()).filter(Boolean),
    })
    await loadHistory()
    serviceType.value = 'Vidange'
    parts.value = ''
    description.value = ''
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible d’enregistrer la maintenance.'
  }
}

function formatDate(value) {
  if (!value) return 'Date inconnue'
  return new Date(value).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(loadHistory)
</script>

<style scoped>
.page-head { margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.panel { background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 18px rgba(15, 23, 42, 0.05); margin-bottom: 1rem; }
.form-grid { display: grid; gap: 1rem; }
input, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 0.9rem; }
button { padding: 0.95rem 1.2rem; border: none; border-radius: 0.85rem; background: #10b981; color: white; cursor: pointer; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.maintenance-list { list-style: none; margin: 0; padding: 0; }
.maintenance-item { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
.maintenance-item:last-child { border-bottom: none; }
.error-box { color: #b91c1c; margin-top: 1rem; }
.muted { color: #64748b; }
</style>
