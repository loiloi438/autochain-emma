<template>
  <div v-if="loading" class="loading">Chargement du tableau de bord…</div>
  <div v-else-if="error" class="error-box">{{ error }}</div>
  <div v-else class="dashboard-container">
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card available">
        <span class="stat-number">{{ dashboard.fleet?.total || 0 }}</span>
        <span class="stat-label">Véhicules<br />Disponibles</span>
      </div>

      <div class="stat-card assigned">
        <span class="stat-number">{{ dashboard.fleet?.assigned || 0 }}</span>
        <span class="stat-label">En Mission</span>
      </div>

      <div class="stat-card maintenance">
        <span class="stat-number">{{ dashboard.fleet?.maintenance || 0 }}</span>
        <span class="stat-label">En Maintenance</span>
      </div>

      <div class="stat-card alerts">
        <span class="stat-number">{{ dashboard.alerts?.length || 0 }}</span>
        <span class="stat-label">Alertes</span>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="dashboard-grid">
      <!-- Left Column -->
      <div class="dashboard-col">
        <!-- Alerts Section -->
        <div class="panel alert-section">
          <div class="panel-header">
            <h3>🏠 Alertes à Traiter</h3>
            <button class="refresh-btn" @click="load">↻</button>
          </div>

          <ul class="alert-list">
            <li v-for="alert in (dashboard.alerts || []).slice(0, 3)" :key="alert.id" class="alert-item">
              <span class="alert-icon">⚠️</span>
              <div class="alert-content">
                <strong>{{ alert.title }}</strong>
                <p>{{ alert.message }}</p>
              </div>
            </li>
            <li v-if="!(dashboard.alerts || []).length" class="empty">Aucune alerte active</li>
          </ul>
        </div>

        <!-- Vehicles Table -->
        <div class="panel vehicles-table">
          <div class="panel-header">
            <h3>🚗 Liste des Véhicules</h3>
          </div>

          <table v-if="dashboard.vehicles?.length">
            <thead>
              <tr>
                <th>Immatriculation</th>
                <th>Modèle</th>
                <th>Statut</th>
                <th>Kilométrage</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="vehicle in (dashboard.vehicles || []).slice(0, 5)" :key="vehicle.id">
                <td><strong>{{ vehicle.plate_number }}</strong></td>
                <td>{{ vehicle.brand }} {{ vehicle.model }}</td>
                <td>
                  <span class="status-badge" :class="vehicle.status">
                    {{ vehicle.status }}
                  </span>
                </td>
                <td>{{ vehicle.current_km }} km</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Document Management -->
        <div class="panel document-section">
          <div class="panel-header">
            <h3>📋 Gestion Documentaire</h3>
          </div>

          <div class="document-cards">
            <div class="doc-card">
              <div class="doc-icon">📄</div>
              <strong>Carte Grise</strong>
              <div class="doc-actions">
                <button class="btn-small">Voir</button>
                <button class="btn-small primary">Télécharger</button>
              </div>
            </div>

            <div class="doc-card">
              <div class="doc-icon">📋</div>
              <strong>Contrat d'Assurance</strong>
              <div class="doc-actions">
                <button class="btn-small">Voir</button>
                <button class="btn-small primary">Télécharger</button>
              </div>
            </div>

            <div class="doc-card">
              <div class="doc-icon">🔧</div>
              <strong>Rapport de Révision</strong>
              <div class="doc-actions">
                <button class="btn-small">Voir</button>
                <button class="btn-small primary">Télécharger</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="dashboard-col">
        <!-- Consumption Chart -->
        <div class="panel chart-section">
          <div class="panel-header">
            <h3>📊 Consommation Moyenne</h3>
            <span class="chart-unit">Litres / 100 km</span>
          </div>
          <TransactionChart :data="transactionData" />
        </div>

        <!-- Vehicle History -->
        <div class="panel history-section">
          <div class="panel-header">
            <h3>📍 Historique du Véhicule</h3>
            <span class="vehicle-name">{{ selectedVehicle }}</span>
          </div>

          <ul class="timeline">
            <li v-for="(item, index) in timeline" :key="index" class="timeline-item">
              <div class="timeline-marker" :class="item.type"></div>
              <div class="timeline-content">
                <div class="timeline-date">{{ item.date }}</div>
                <strong>{{ item.label }}</strong>
                <p>{{ item.description }}</p>
                <span v-if="item.km" class="timeline-km">{{ item.km }} km</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Alerts to Come -->
        <div class="panel alerts-upcoming">
          <div class="panel-header">
            <h3>✅ Alertes à Venir</h3>
          </div>

          <ul class="upcoming-list">
            <li v-for="(item, index) in upcomingAlerts" :key="index">
              <span class="upcoming-icon">{{ item.icon }}</span>
              <div class="upcoming-content">
                <strong>{{ item.title }}</strong>
                <p>{{ item.description }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import TransactionChart from '../components/TransactionChart.vue'

const auth = useAuthStore()
const dashboard = reactive({
  fleet: { total: 0, available: 0, assigned: 0, maintenance: 0 },
  alerts: [],
  vehicles: [],
  recent_timeline: [],
})
const loading = ref(false)
const error = ref('')
const transactionData = ref([])
const selectedVehicle = ref('')
const timeline = ref([])
const upcomingAlerts = ref([])

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/dashboard')
    Object.assign(dashboard, {
      fleet: data.fleet || { total: 0, available: 0, assigned: 0, maintenance: 0 },
      alerts: data.alerts || [],
      vehicles: data.vehicles || [],
      recent_timeline: data.recent_timeline || [],
    })
    timeline.value = dashboard.recent_timeline
    upcomingAlerts.value = dashboard.alerts.map((alert) => ({
      icon: '⚠️',
      title: alert.title,
      description: alert.message || alert.vehicle?.plate_number || 'Action requise',
    }))

    transactionData.value = []
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de charger le tableau de bord.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.dashboard-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.loading,
.error-box {
  padding: 24px;
  text-align: center;
  background: white;
  border-radius: 8px;
}

.error-box {
  background-color: #fee2e2;
  color: #991b1b;
}

/* ============ STATS GRID ============ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.stat-card {
  padding: 20px;
  border-radius: 8px;
  color: white;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-card.available {
  background: linear-gradient(135deg, #1e7e74 0%, #2a9b8f 100%);
}

.stat-card.assigned {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.stat-card.maintenance {
  background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
}

.stat-card.alerts {
  background: linear-gradient(135deg, #c1121f 0%, #e63946 100%);
}

.stat-number {
  font-size: 36px;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 12px;
  opacity: 0.9;
  line-height: 1.4;
}

/* ============ DASHBOARD GRID ============ */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}

.dashboard-col {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ============ PANELS ============ */
.panel {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.panel-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.panel-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.chart-unit,
.vehicle-name {
  font-size: 12px;
  color: #6b7280;
}

.refresh-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  color: #6b7280;
}

/* ============ ALERTS ============ */
.alert-section {
  padding: 20px;
}

.alert-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alert-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background-color: #f9fafb;
  border-radius: 6px;
}

.alert-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  font-size: 14px;
  display: block;
}

.alert-content p {
  margin: 4px 0 0;
  font-size: 12px;
  color: #6b7280;
}

.empty {
  text-align: center;
  color: #9ca3af;
  padding: 20px;
}

/* ============ VEHICLES TABLE ============ */
.vehicles-table {
  padding: 0;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

thead {
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

th {
  padding: 12px 20px;
  text-align: left;
  font-weight: 600;
  color: #374151;
}

td {
  padding: 12px 20px;
  border-bottom: 1px solid #e5e7eb;
}

tbody tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.En\ Mission {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.Disponible {
  background-color: #dcfce7;
  color: #166534;
}

/* ============ DOCUMENTS ============ */
.document-section {
  padding: 20px;
}

.document-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

@media (max-width: 768px) {
  .document-cards {
    grid-template-columns: 1fr;
  }
}

.doc-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 12px;
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  transition: all 0.2s;
}

.doc-card:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.doc-icon {
  font-size: 32px;
}

.doc-card strong {
  font-size: 13px;
}

.doc-actions {
  display: flex;
  gap: 8px;
  width: 100%;
}

.btn-small {
  flex: 1;
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-small:hover {
  background-color: #f3f4f6;
}

.btn-small.primary {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.btn-small.primary:hover {
  background-color: #2563eb;
}

/* ============ CHART ============ */
.chart-section {
  padding: 20px;
}

/* ============ HISTORY/TIMELINE ============ */
.history-section {
  padding: 20px;
}

.timeline {
  list-style: none;
  padding: 0;
  margin: 0;
  position: relative;
  padding-left: 40px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: #e5e7eb;
}

.timeline-item {
  margin-bottom: 20px;
  position: relative;
}

.timeline-marker {
  position: absolute;
  left: -35px;
  top: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: white;
  border: 3px solid #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.timeline-marker.mission {
  border-color: #2c3e50;
}

.timeline-marker.maintenance {
  border-color: #10b981;
}

.timeline-marker.inspection {
  border-color: #8b5cf6;
}

.timeline-content {
  background-color: #f9fafb;
  padding: 12px;
  border-radius: 6px;
}

.timeline-date {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
}

.timeline-content strong {
  display: block;
  margin-top: 4px;
  font-size: 14px;
}

.timeline-content p {
  margin: 4px 0;
  font-size: 13px;
  color: #6b7280;
}

.timeline-km {
  display: inline-block;
  margin-top: 4px;
  font-size: 12px;
  background-color: white;
  padding: 2px 8px;
  border-radius: 4px;
  color: #6b7280;
}

/* ============ UPCOMING ALERTS ============ */
.alerts-upcoming {
  padding: 20px;
}

.upcoming-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.upcoming-list li {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background-color: #f0fdf4;
  border-left: 3px solid #10b981;
  border-radius: 4px;
}

.upcoming-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.upcoming-content {
  flex: 1;
}

.upcoming-content strong {
  display: block;
  font-size: 14px;
}

.upcoming-content p {
  margin: 4px 0 0;
  font-size: 12px;
  color: #6b7280;
}
</style>
