<template>
  <div class="vehicle-timeline">
    <div class="timeline-header">
      <h2>Timeline du véhicule</h2>
      <p class="subtitle">Événements certifiés Web3 et données administratives.</p>
    </div>

    <ul class="timeline-list">
      <li v-for="(event, index) in events" :key="index" class="timeline-item">
        <div class="timeline-marker" :class="event.status">
          <span>{{ event.type === 'maintenance' ? '🔧' : event.type === 'mileage' ? '🛣️' : '📄' }}</span>
        </div>
        <div class="timeline-content">
          <div class="timeline-top">
            <strong>{{ event.label }}</strong>
            <span class="badge" :class="event.certified ? 'certified' : 'backend'">
              {{ event.certified ? 'Blockchain Sepolia' : 'Backend' }}
            </span>
          </div>
          <div class="timeline-meta">
            <span>{{ formatDate(event.date) }}</span>
            <span v-if="event.tx_hash">Tx: {{ event.tx_hash }}</span>
          </div>
          <p>{{ event.summary }}</p>
        </div>
      </li>
      <li v-if="!events?.length" class="timeline-empty">
        Aucune donnée de timeline disponible.
      </li>
    </ul>
  </div>
</template>

<script setup>
import { toRefs } from 'vue'

const props = defineProps({
  events: { type: Array, default: () => [] },
})

const { events } = toRefs(props)

function formatDate(value) {
  if (!value) return 'Date inconnue'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.vehicle-timeline {
  background: white;
  border-radius: 16px;
  padding: 1.2rem;
  box-shadow: 0 0 28px rgba(17, 24, 39, 0.05);
}
.timeline-header {
  margin-bottom: 1rem;
}
.timeline-header h2 {
  margin: 0;
  font-size: 1.15rem;
}
.timeline-header .subtitle {
  margin: 0.4rem 0 0;
  color: #55647d;
  font-size: 0.95rem;
}
.timeline-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.timeline-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid #f0f2f6;
}
.timeline-item:last-child { border-bottom: none; }
.timeline-marker {
  min-width: 48px;
  min-height: 48px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: #eef2ff;
  color: #4338ca;
  font-size: 1.1rem;
}
.timeline-marker.admin { background: #fde68a; color: #92400e; }
.timeline-marker.ok { background: #d1fae5; color: #166534; }
.timeline-marker.pending { background: #fbe4ff; color: #7c3aed; }
.timeline-content { flex: 1; }
.timeline-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
}
.badge {
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.badge.certified { background: #d1fae5; color: #166534; }
.badge.backend { background: #e2e8f0; color: #334155; }
.timeline-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.9rem;
  color: #64748b;
  margin: 0.35rem 0;
  flex-wrap: wrap;
}
.timeline-empty {
  color: #64748b;
  padding: 1rem 0;
}
</style>
