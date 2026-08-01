<template>
  <section v-if="loading" class="panel">Chargement du véhicule…</section>
  <section v-else-if="error" class="panel error">{{ error }}</section>
  <section v-else-if="vehicle">
    <div class="page-head">
      <div>
        <h1>{{ vehicle.plate_number }}</h1>
        <p class="muted">{{ vehicle.brand }} {{ vehicle.model }} · {{ vehicle.current_km }} km · {{ vehicle.status }}</p>
      </div>
      <RouterLink class="ghost" to="/vehicles">Retour au parc</RouterLink>
    </div>

    <div class="detail-grid">
      <section class="panel detail-overview">
        <h2>Résumé</h2>
        <div class="stack">
          <div><strong>Immatriculation</strong><p>{{ vehicle.plate_number }}</p></div>
          <div><strong>VIN</strong><p>{{ vehicle.vin || 'Non renseigné' }}</p></div>
          <div><strong>Année</strong><p>{{ vehicle.year || '—' }}</p></div>
          <div><strong>Kilométrage</strong><p>{{ vehicle.current_km }} km</p></div>
          <div><strong>Statut</strong><p class="status-pill">{{ vehicle.status }}</p></div>
        </div>
      </section>

      <section class="panel detail-actions">
        <h2>Actions</h2>

        <MileageForm
          v-if="canDrive"
          :vehicle-id="vehicle.id"
          :plate="vehicle.plate_number"
          :current-km="vehicle.current_km"
          @cancel="activeForm = null"
          @success="handleFormSuccess"
        />

        <MaintenanceForm
          v-if="canGarage"
          :vehicle-id="vehicle.id"
          :plate="vehicle.plate_number"
          @cancel="activeForm = null"
          @success="handleFormSuccess"
        />

        <DocumentForm
          v-if="canManage"
          :vehicle-id="vehicle.id"
          :plate="vehicle.plate_number"
          @cancel="activeForm = null"
          @success="handleFormSuccess"
        />
      </section>
    </div>

    <section class="panel timeline-panel">
      <h2>Timeline du véhicule</h2>
      <ul class="list timeline-list">
        <li v-for="(item, index) in timeline" :key="index">
          <div class="row">
            <strong>{{ item.label }}</strong>
            <span class="badge" :class="statusClass(item)">
              {{ statusLabel(item) }}
            </span>
          </div>
          <span>{{ item.summary }}</span>
          <small v-if="item.tx_hash" class="muted">tx: {{ item.tx_hash }}</small>
          <small v-else class="muted">Aucune trace blockchain associée.</small>
        </li>
      </ul>
    </section>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import { useToastStore } from '../stores/toast'
import MileageForm from '../components/MileageForm.vue'
import MaintenanceForm from '../components/MaintenanceForm.vue'
import DocumentForm from '../components/DocumentForm.vue'

const route = useRoute()
const auth = useAuthStore()
const wallet = useWalletStore()
const toast = useToastStore()

const vehicle = ref(null)
const timeline = ref([])
const loading = ref(true)
const error = ref('')
const mileageKm = ref(0)
const driverId = ref(3)
const useChain = ref(true)
const onChainId = ref(1)
const maintenance = reactive({ service_type: 'Vidange', description: '', parts: 'huile,filtre' })
const document = reactive({ title: '', document_type: 'assurance', is_public: false, file: null })

const canManage = computed(() => auth.hasRole('manager') || auth.hasRole('admin'))
const canDrive = computed(() => canManage.value || auth.hasRole('driver'))
const canGarage = computed(() => auth.hasRole('garage') || auth.hasRole('admin'))
const activeForm = ref(null)

async function handleFormSuccess() {
  toast.push('Action enregistrée avec succès !', 'success')
  await load()
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const id = route.params.id
    const [vehicleRes, timelineRes] = await Promise.all([
      api.get(`/vehicles/${id}`),
      api.get(`/vehicles/${id}/timeline`),
    ])
    vehicle.value = vehicleRes.data
    timeline.value = timelineRes.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger ce véhicule.'
  } finally {
    loading.value = false
  }
}

async function submitMileage() {
  try {
    let txHash = null
    if (useChain.value) {
      txHash = await wallet.recordMileageOnChain(onChainId.value, mileageKm.value, vehicle.value.id)
    }
    await api.post(`/vehicles/${vehicle.value.id}/mileage`, { km: mileageKm.value, tx_hash: txHash })
    toast.push('Relevé kilométrique enregistré.', 'success')
    await load()
  } catch (e) {
    toast.push(e.response?.data?.message || 'Échec de l’enregistrement du relevé.', 'error')
  }
}

async function assign() {
  try {
    await api.post(`/vehicles/${vehicle.value.id}/assign`, { user_id: driverId.value })
    toast.push('Affectation enregistrée.', 'success')
    await load()
  } catch (e) {
    toast.push(e.response?.data?.message || 'Échec de l’affectation.', 'error')
  }
}

async function submitMaintenance() {
  try {
    const parts = maintenance.parts.split(',').map((part) => part.trim()).filter(Boolean)
    let txHash = null
    if (useChain.value) {
      txHash = await wallet.recordMaintenanceOnChain(onChainId.value, maintenance.service_type, parts, vehicle.value.id)
    }
    await api.post(`/vehicles/${vehicle.value.id}/maintenance`, {
      service_type: maintenance.service_type,
      description: maintenance.description,
      parts,
      tx_hash: txHash,
    })
    toast.push('Maintenance certifiée.', 'success')
    await load()
  } catch (e) {
    toast.push(e.response?.data?.message || 'Échec de la maintenance.', 'error')
  }
}

function onFile(event) {
  document.file = event.target.files[0]
}

function statusClass(item) {
  if (item.certified) return 'ok'
  if (item.status === 'pending') return 'admin'
  return 'admin'
}

function statusLabel(item) {
  if (item.certified) return 'Certifié blockchain'
  if (item.status === 'pending') return 'En attente blockchain'
  return 'Administratif'
}

async function uploadDocument() {
  try {
    const formData = new FormData()
    formData.append('document', document.file)
    formData.append('title', document.title)
    formData.append('document_type', document.document_type)
    formData.append('is_public', document.is_public ? '1' : '0')

    const { data } = await api.post(`/vehicles/${vehicle.value.id}/documents`, formData)
    if (useChain.value) {
      await wallet.recordDocumentHashOnChain(onChainId.value, data.sha256_hash, document.document_type, vehicle.value.id)
    }
    toast.push('Document enregistré.', 'success')
    await load()
  } catch (e) {
    toast.push(e.response?.data?.message || 'Échec de l’envoi du document.', 'error')
  }
}

onMounted(load)
</script>
