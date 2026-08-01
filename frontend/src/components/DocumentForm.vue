<template>
  <form @submit.prevent="submit" class="form-container">
    <div class="form-header">
      <h3>Ajouter un document certifié</h3>
      <p>Enregistrez un document dans la blockchain avec une certité immuable.</p>
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
      <label for="type">Type de document</label>
      <select id="type" v-model="form.type" required>
        <option value="">-- Sélectionner --</option>
        <option value="assurance">Assurance</option>
        <option value="inspection">Inspection technique</option>
        <option value="facture">Facture</option>
        <option value="photo">Photo</option>
        <option value="autre">Autre</option>
      </select>
    </div>

    <div class="form-group">
      <label for="file">Fichier à uploader</label>
      <div class="file-input-wrapper">
        <input
          id="file"
          type="file"
          @change="onFileChange"
          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          required
        />
        <span v-if="fileName" class="file-name">{{ fileName }}</span>
      </div>
      <small class="helper">Formats acceptés : PDF, JPG, PNG, DOC, DOCX.</small>
    </div>

    <div class="form-group">
      <label for="description">Description (optionnel)</label>
      <textarea
        id="description"
        v-model="form.description"
        placeholder="ex. Assurance auto valide jusqu'au 31/12/2024"
        rows="3"
      ></textarea>
    </div>

    <div v-if="fileHash" class="hash-info">
      <strong>Hash SHA256 :</strong>
      <code>{{ fileHash.slice(0, 16) }}...{{ fileHash.slice(-16) }}</code>
    </div>

    <div class="form-actions">
      <button type="button" @click="$emit('cancel')" class="outline">
        Annuler
      </button>
      <button
        type="submit"
        :disabled="isSubmitting || !fileHash"
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
  file: null,
})

const fileName = ref('')
const fileHash = ref('')
const isSubmitting = ref(false)
const error = ref('')
const success = ref('')

async function computeFileHash(file) {
  const buffer = await file.arrayBuffer()
  const hashBuffer = await crypto.subtle.digest('SHA-256', buffer)
  const hashArray = Array.from(new Uint8Array(hashBuffer))
  const hashHex = hashArray.map((b) => b.toString(16).padStart(2, '0')).join('')
  return hashHex
}

async function onFileChange(event) {
  error.value = ''
  const file = event.target.files?.[0]
  if (!file) return

  if (file.size > 10 * 1024 * 1024) {
    error.value = 'Le fichier ne doit pas dépasser 10 MB.'
    return
  }

  form.file = file
  fileName.value = file.name
  fileHash.value = await computeFileHash(file)
}

async function submit() {
  error.value = ''
  success.value = ''

  if (!form.type || !form.file || !fileHash.value) {
    error.value = 'Tous les champs sont obligatoires.'
    return
  }

  isSubmitting.value = true
  try {
    const { data } = await api.post(
      `/vehicles/${props.vehicleId}/document`,
      {
        doc_type: form.type,
        doc_hash: fileHash.value,
        description: form.description || '',
      }
    )
    success.value = 'Document enregistré avec succès sur la blockchain !'
    setTimeout(() => {
      emit('success', data)
    }, 1000)
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible d\'enregistrer le document.'
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
  box-sizing: border-box;
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

.file-input-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
}

.file-input-wrapper input[type='file'] {
  display: none;
}

.file-input-wrapper::before {
  content: 'Cliquez pour sélectionner un fichier';
  display: block;
  padding: 0.75rem;
  border: 2px dashed #d1d5db;
  border-radius: 4px;
  background-color: #f9fafb;
  color: #6b7280;
  text-align: center;
  cursor: pointer;
  font-size: 0.875rem;
}

.file-input-wrapper:hover::before {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.file-name {
  display: block;
  margin-top: 0.5rem;
  padding: 0.5rem;
  background-color: #dbeafe;
  color: #1e40af;
  border-radius: 4px;
  font-size: 0.75rem;
  word-break: break-all;
}

.helper {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

.hash-info {
  margin: 1rem 0;
  padding: 0.75rem;
  background-color: #f3f4f6;
  border-radius: 4px;
  font-size: 0.75rem;
  font-family: monospace;
}

.hash-info code {
  display: block;
  margin-top: 0.5rem;
  word-break: break-all;
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
