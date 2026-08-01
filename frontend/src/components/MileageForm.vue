<template>
  <form @submit.prevent="submit" class="form-container">
    <div class="form-header">
      <h3>Ajouter un relevé kilométrique</h3>
      <p>Enregistrez le kilométrage actuel du véhicule.</p>
    </div>

    <div class="form-group">
      <label for="plate">Plaque d'immatriculation</label>
      <input
        id="plate"
        v-model="form.plate"
        type="text"
        placeholder="ex. AB-123-CD"
        required
        disabled
      />
    </div>

    <div class="form-group">
      <label for="km">Kilométrage (km)</label>
      <input
        id="km"
        v-model.number="form.km"
        type="number"
        placeholder="ex. 15000"
        required
        min="0"
      />
      <small class="helper">Kilomètres parcourus jusqu'à maintenant.</small>
    </div>

    <div class="form-actions">
      <button type="button" @click="$emit('cancel')" class="outline">
        Annuler
      </button>
      <button
        type="submit"
        :disabled="isSubmitting"
        class="primary"
      >
        {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
      </button>
    </div>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="success" class="success">{{ success }}</p>
  </form>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import api from '../services/api'

const props = defineProps({
  vehicleId: {
    type: Number,
    required: true,
  },
  plate: {
    type: String,
    required: true,
  },
  currentKm: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['cancel', 'success'])

const form = reactive({
  plate: props.plate,
  km: null,
})

const isSubmitting = ref(false)
const error = ref('')
const success = ref('')

watch(() => props.currentKm, (newVal) => {
  if (form.km === null) {
    form.km = newVal
  }
})

async function submit() {
  error.value = ''
  success.value = ''

  if (!form.km || form.km < 0) {
    error.value = 'Le kilométrage doit être positif.'
    return
  }

  isSubmitting.value = true
  try {
    const { data } = await api.post(
      `/vehicles/${props.vehicleId}/mileage`,
      {
        km: form.km,
      }
    )
    success.value = 'Kilométrage enregistré avec succès !'
    setTimeout(() => {
      emit('success', data)
    }, 1000)
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible d\'enregistrer le kilométrage.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.form-container {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-header {
  margin-bottom: 1.5rem;
}

.form-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.form-header p {
  margin: 0.5rem 0 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 0.875rem;
}

.form-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.helper {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.form-actions button {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.primary {
  background-color: #3b82f6;
  color: white;
}

.primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.outline {
  border: 1px solid #e5e7eb;
  background-color: white;
  color: #374151;
}

.outline:hover {
  background-color: #f9fafb;
}

.error {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #fee2e2;
  color: #991b1b;
  border-radius: 4px;
  font-size: 0.875rem;
}

.success {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #dbeafe;
  color: #1e40af;
  border-radius: 4px;
  font-size: 0.875rem;
}
</style>
