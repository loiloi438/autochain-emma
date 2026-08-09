<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Véhicules</h1>
        <p>Gérez le parc, ajoutez des véhicules et accédez aux détails de chaque asset certifié.</p>
      </div>
      <div class="actions">
        <span v-if="roleLabel" class="pill">{{ roleLabel }}</span>
        <button v-if="canManage" @click="showForm = !showForm">{{ showForm ? 'Fermer le formulaire' : 'Nouveau véhicule' }}</button>
      </div>
    </div>

    <form v-if="showForm" class="panel form-grid" @submit.prevent="createVehicle">
      <input v-model="form.plate_number" placeholder="Immatriculation" required />
      <input v-model="form.vin" placeholder="VIN (hashé on-chain)" />
      <input v-model="form.brand" placeholder="Marque" required />
      <input v-model="form.model" placeholder="Modèle" required />
      <input v-model.number="form.year" type="number" placeholder="Année" />
      <input v-model.number="form.current_km" type="number" placeholder="Km initial" />
      <button type="submit">Enregistrer</button>
    </form>

    <div v-if="vehicles.length" class="cards-list">
      <RouterLink v-for="vehicle in vehicles" :key="vehicle.id" class="card-link vehicle-card" :to="`/vehicles/${vehicle.id}`">
        <div>
          <h3>{{ vehicle.plate_number }}</h3>
          <p class="muted">{{ vehicle.brand }} {{ vehicle.model }}</p>
          <p class="muted">{{ vehicle.assignments?.[0]?.driver?.name || 'Aucun chauffeur assigné' }}</p>
        </div>
        <div class="card-meta">
          <span>{{ vehicle.current_km }} km</span>
          <span class="badge" :class="vehicle.status === 'assigned' ? 'admin' : 'ok'">{{ vehicle.status }}</span>
        </div>
      </RouterLink>
    </div>
    <div v-else class="panel muted">Aucun véhicule enregistré pour le moment.</div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'

const auth = useAuthStore()
const wallet = useWalletStore()
const vehicles = ref([])
const showForm = ref(false)
const form = reactive({
  plate_number: '',
  vin: '',
  brand: '',
  model: '',
  year: 2024,
  current_km: 0,
})

const canManage = computed(() => auth.hasRole('manager') || auth.hasRole('admin'))
const roleLabel = computed(() => {
  const roles = auth.roleNames
  return roles.length ? roles.join(', ') : 'Aucun rôle'
})

async function load() {
  const { data } = await api.get('/vehicules')
  vehicles.value = data.data || data
}

async function createVehicle() {
  const { data } = await api.post('/vehicles', form)
  try {
    await wallet.registerVehicleOnChain(form.vin || form.plate_number, form.current_km || 0, data.id)
  } catch (error) {
    alert(`Véhicule créé en BDD. Ancrage blockchain: ${error.message}`)
  }
  showForm.value = false
  await load()
}

onMounted(load)
</script>
