<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Chauffeur</h1>
        <p class="muted">Déclarez la prise en charge et enregistrez le kilométrage de fin de trajet.</p>
      </div>
    </div>

    <div class="panel driver-grid">
      <div class="panel-block">
        <h2>Véhicule assigné</h2>
        <div v-if="loading" class="muted">Chargement...</div>
        <div v-else-if="!assignment" class="muted">Aucun véhicule assigné.</div>
        <div v-else class="vehicle-card">
          <p><strong>{{ assignment.vehicle.plate_number }}</strong></p>
          <p>{{ assignment.vehicle.brand }} {{ assignment.vehicle.model }}</p>
          <p>Status : {{ assignment.vehicle.status }}</p>
          <p>Kilométrage actuel : {{ assignment.vehicle.current_km }} km</p>
        </div>
      </div>

      <form class="panel-block form-grid" @submit.prevent="submitMileage">
        <h2>Fin de trajet</h2>
        <label>
          Kilométrage final
          <input type="number" min="0" v-model.number="mileageKm" required />
        </label>
        <label>
          Commentaire
          <textarea v-model="comment" placeholder="État du véhicule, remarques"></textarea>
        </label>
        <button type="submit" :disabled="!assignment">Enregistrer</button>
      </form>
    </div>

    <div class="panel actions-panel">
      <button @click="checkIn" :disabled="!assignment">Confirmer prise en charge</button>
      <p class="muted">Une fois le kilométrage envoyé, l’état sera transmis au gestionnaire.</p>
    </div>

    <div v-if="error" class="error-box">{{ error }}</div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const assignment = ref(null)
const mileageKm = ref(0)
const comment = ref('')
const loading = ref(false)
const error = ref('')

async function loadAssignment() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/driver/assignments')
    assignment.value = data.current || data[0] || null
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger l’affectation.'
  } finally {
    loading.value = false
  }
}

async function submitMileage() {
  if (!assignment.value) return
  error.value = ''
  try {
    await api.post(`/driver/vehicles/${assignment.value.vehicle.id}/mileage`, {
      km: mileageKm.value,
      note: comment.value,
    })
    await loadAssignment()
    comment.value = ''
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible d’enregistrer le kilométrage.'
  }
}

async function checkIn() {
  if (!assignment.value) return
  error.value = ''
  try {
    await api.post(`/driver/vehicles/${assignment.value.vehicle.id}/checkin`, {})
    await loadAssignment()
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de confirmer la prise en charge.'
  }
}

onMounted(loadAssignment)
</script>

<style scoped>
.page-head { margin-bottom: 1rem; }
.page-head h1 { margin: 0; }
.panel { background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 16px rgba(15, 23, 42, 0.04); margin-bottom: 1rem; }
.driver-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.panel-block { padding: 1rem; border: 1px solid #eaecf0; border-radius: 12px; }
.form-grid { display: grid; gap: 1rem; }
input, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 0.8rem; }
button { padding: 0.9rem 1.2rem; border: none; border-radius: 0.8rem; background: #2563eb; color: white; cursor: pointer; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.vehicle-card { background: #f8fafc; padding: 1rem; border-radius: 0.9rem; }
.error-box { color: #b91c1c; margin-top: 1rem; }
.muted { color: #64748b; }
</style>
