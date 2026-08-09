<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Entretien</h1>
        <p class="muted">Suivez les opérations de maintenance enregistrées sur le parc.</p>
      </div>
      <button @click="load" :disabled="loading">{{ loading ? 'Chargement...' : 'Actualiser' }}</button>
    </div>

    <div v-if="error" class="error-box">{{ error }}</div>
    <div v-else-if="!maintenances.length && !loading" class="panel muted">Aucune maintenance enregistrée.</div>
    <div v-else class="panel table-wrap">
      <table>
        <thead>
          <tr><th>Véhicule</th><th>Opération</th><th>Description</th><th>Date</th><th>Statut</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in maintenances" :key="item.id">
            <td>{{ item.vehicle?.plate_number || `#${item.vehicle_id}` }}</td>
            <td>{{ item.service_type }}</td>
            <td>{{ item.description || '—' }}</td>
            <td>{{ formatDate(item.performed_at) }}</td>
            <td>{{ item.tx_hash ? 'Blockchain' : 'Backend' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const maintenances = ref([])
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/entretien')
    maintenances.value = data.data || data
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger les maintenances.'
  } finally {
    loading.value = false
  }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString('fr-FR') : '—'
}

onMounted(load)
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.panel { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 16px rgba(15, 23, 42, 0.05); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 650px; }
th, td { padding: 0.85rem; border-bottom: 1px solid #eef2f7; text-align: left; }
th { color: #475569; font-size: 0.85rem; }
button { padding: 0.75rem 1rem; border: 0; border-radius: 8px; background: #2563eb; color: white; cursor: pointer; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.muted { color: #64748b; }
.error-box { color: #b91c1c; margin-bottom: 1rem; }
@media (max-width: 700px) { .page-head { align-items: stretch; flex-direction: column; } }
</style>
