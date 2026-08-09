<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Chauffeurs</h1>
        <p class="muted">Consultez les chauffeurs et leur véhicule actuellement assigné.</p>
      </div>
      <button @click="load" :disabled="loading">{{ loading ? 'Chargement...' : 'Actualiser' }}</button>
    </div>

    <div v-if="error" class="error-box">{{ error }}</div>
    <div v-else-if="!drivers.length && !loading" class="panel muted">Aucun chauffeur trouvé.</div>
    <div v-else class="cards-list">
      <article v-for="driver in drivers" :key="driver.id" class="panel driver-card">
        <div>
          <h2>{{ driver.name }}</h2>
          <p class="muted">{{ driver.email }}</p>
        </div>
        <div class="assignment">
          <strong>Véhicule assigné</strong>
          <span v-if="driver.assignments?.[0]?.vehicle">
            {{ driver.assignments[0].vehicle.plate_number }} · {{ driver.assignments[0].vehicle.brand }} {{ driver.assignments[0].vehicle.model }}
          </span>
          <span v-else class="muted">Aucun véhicule assigné</span>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const drivers = ref([])
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/chauffeurs')
    drivers.value = data.data || data
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger les chauffeurs.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.cards-list { display: grid; gap: 1rem; }
.panel { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 16px rgba(15, 23, 42, 0.05); }
.driver-card { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
.driver-card h2 { margin: 0; font-size: 1.1rem; }
.assignment { display: grid; gap: 0.35rem; text-align: right; }
button { padding: 0.75rem 1rem; border: 0; border-radius: 8px; background: #2563eb; color: white; cursor: pointer; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.muted { color: #64748b; }
.error-box { color: #b91c1c; margin-bottom: 1rem; }
@media (max-width: 700px) { .page-head, .driver-card { align-items: stretch; flex-direction: column; } .assignment { text-align: left; } }
</style>
