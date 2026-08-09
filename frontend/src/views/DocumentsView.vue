<template>
  <section>
    <div class="page-head">
      <div><h1>Documents</h1><p class="muted">Historique des documents associés aux véhicules.</p></div>
      <button @click="load" :disabled="loading">{{ loading ? 'Chargement...' : 'Actualiser' }}</button>
    </div>
    <div v-if="error" class="error-box">{{ error }}</div>
    <div v-else-if="!documents.length && !loading" class="panel muted">Aucun document enregistré.</div>
    <div v-else class="panel table-wrap">
      <table>
        <thead><tr><th>Document</th><th>Véhicule</th><th>Type</th><th>Date</th><th>Statut</th></tr></thead>
        <tbody>
          <tr v-for="document in documents" :key="`${document.tx_hash || document.date}-${document.vehicle}`">
            <td>{{ document.label }}</td>
            <td>{{ document.vehicle || '—' }}</td>
            <td>{{ document.summary || '—' }}</td>
            <td>{{ formatDate(document.date) }}</td>
            <td>{{ document.certified ? 'Certifié' : 'En attente' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const documents = ref([])
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/timeline')
    documents.value = data.filter((item) => item.type === 'document')
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger les documents.'
  } finally {
    loading.value = false
  }
}

function formatDate(value) { return value ? new Date(value).toLocaleDateString('fr-FR') : 'Date inconnue' }
onMounted(load)
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.panel { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 16px rgba(15, 23, 42, 0.05); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 650px; }
th, td { padding: .85rem; border-bottom: 1px solid #eef2f7; text-align: left; }
th { color: #475569; font-size: .85rem; }
button { padding: .75rem 1rem; border: 0; border-radius: 8px; background: #2563eb; color: white; cursor: pointer; }
button:disabled { opacity: .6; }
.muted { color: #64748b; }
.error-box { color: #b91c1c; margin-bottom: 1rem; }
@media (max-width: 700px) { .page-head { align-items: stretch; flex-direction: column; } }
</style>
