<template>
  <section>
    <div class="page-head">
      <div>
        <h1>Consultation auditeur</h1>
        <p class="muted">Accédez à l’historique certifié en lecture seule sans données nominatives.</p>
      </div>
    </div>

    <form class="panel form-grid" @submit.prevent="load">
      <input v-model.number="vehicleId" type="number" min="1" placeholder="ID véhicule" required />
      <button type="submit">Consulter</button>
    </form>

    <div v-if="history" class="panel history-panel">
      <div class="page-head">
        <div>
          <h2>{{ history.vehicle.plate_number }}</h2>
          <p class="muted">{{ history.vehicle.brand }} {{ history.vehicle.model }} · {{ history.vehicle.current_km }} km</p>
        </div>
      </div>

      <div class="grid cards">
        <article>
          <span class="stat-label">Événements</span>
          <p>{{ history.summary?.total || 0 }}</p>
        </article>
        <article>
          <span class="stat-label">Certifiés</span>
          <p>{{ history.summary?.certified || 0 }}</p>
        </article>
        <article>
          <span class="stat-label">En attente</span>
          <p>{{ history.summary?.pending || 0 }}</p>
        </article>
      </div>

      <p class="muted">VIN hash: <code>{{ history.vehicle.vin_hash }}</code></p>

      <h3>Historique certifié</h3>
      <ul class="list timeline-list">
        <li v-for="(item, index) in history.certified" :key="index">
          <div class="row">
            <strong>{{ item.label }}</strong>
            <span class="badge ok">Certifié</span>
          </div>
          <span>{{ item.summary }}</span>
          <small class="muted">{{ item.tx_hash }}</small>
        </li>
        <li v-if="!(history.certified || []).length" class="muted">Aucun événement certifié pour le moment.</li>
      </ul>

      <h3 v-if="(history.pending || []).length">Événements en attente</h3>
      <ul v-if="(history.pending || []).length" class="list timeline-list">
        <li v-for="(item, index) in history.pending" :key="index">
          <div class="row">
            <strong>{{ item.label }}</strong>
            <span class="badge admin">En attente</span>
          </div>
          <span>{{ item.summary }}</span>
          <small class="muted">{{ item.tx_hash }}</small>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import api from '../services/api'

const vehicleId = ref(1)
const history = ref(null)

async function load() {
  const { data } = await api.get(`/public/vehicles/${vehicleId.value}/history`)
  history.value = data
}
</script>
