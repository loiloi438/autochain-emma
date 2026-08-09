<template>
  <section>
    <div class="page-head">
      <div><h1>Alertes</h1><p class="muted">Alertes ouvertes liées aux véhicules du parc.</p></div>
      <button @click="load" :disabled="loading">{{ loading ? 'Chargement...' : 'Actualiser' }}</button>
    </div>
    <div v-if="error" class="error-box">{{ error }}</div>
    <div v-else-if="!alerts.length && !loading" class="panel muted">Aucune alerte active.</div>
    <div v-else class="cards-list">
      <article v-for="alert in alerts" :key="alert.id" class="panel">
        <strong>{{ alert.title }}</strong>
        <p>{{ alert.message || 'Action requise sur le véhicule.' }}</p>
        <small class="muted">{{ alert.vehicle?.plate_number || 'Véhicule inconnu' }} · {{ formatDate(alert.due_at) }}</small>
      </article>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const alerts = ref([])
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/alerts')
    alerts.value = data
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger les alertes.'
  } finally {
    loading.value = false
  }
}

function formatDate(value) { return value ? new Date(value).toLocaleDateString('fr-FR') : 'Date non définie' }
onMounted(load)
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.cards-list { display: grid; gap: 1rem; }
.panel { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 16px rgba(15, 23, 42, 0.05); }
button { padding: .75rem 1rem; border: 0; border-radius: 8px; background: #2563eb; color: white; cursor: pointer; }
button:disabled { opacity: .6; }
.muted { color: #64748b; }
.error-box { color: #b91c1c; margin-bottom: 1rem; }
@media (max-width: 700px) { .page-head { align-items: stretch; flex-direction: column; } }
</style>
