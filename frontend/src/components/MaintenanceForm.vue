<template>
  <form @submit.prevent="submit" class="form-container">
    <div class="form-header">
      <h3>Ajouter une opération de maintenance</h3>
      <p>Enregistrez une intervention d'entretien du véhicule.</p>
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
      <label for="type">Type d'opération</label>
      <select id="type" v-model="form.type" required>
        <option value="">-- Sélectionner --</option>
        <option value="vidange">Vidange</option>
        <option value="freins">Freins</option>
        <option value="filtre">Filtre</option>
        <option value="pneus">Pneus</option>
        <option value="autre">Autre</option>
      </select>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea
        id="description"
        v-model="form.description"
        placeholder="ex. Remplacement des plaquettes de frein avant et arrière"
        required
        rows="4"
      ></textarea>
      <small class="helper">Détails de l'opération effectuée.</small>
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
import { ref, reactive } from 'vue'
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
})

const emit = defineEmits(['cancel', 'success'])

const form = reactive({
  plate: props.plate,
  type: '',
  description: '',
})

const isSubmitting = ref(false)
const error = ref('')
const success = ref('')

async function submit() {
  error.value = ''
  success.value = ''

  if (!form.type || !form.description) {
    error.value = 'Tous les champs sont obligatoires.'
    return
  }

  isSubmitting.value = true
  try {
    const { data } = await api.post(
      `/vehicles/${props.vehicleId}/maintenance`,
      {
        service_type: form.type,
        description: form.description,
      }
    )
    success.value = 'Maintenance enregistrée avec succès !'
    setTimeout(() => {
      emit('success', data)
    }, 1000)
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible d\'enregistrer la maintenance.'
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

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 0.875rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
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
