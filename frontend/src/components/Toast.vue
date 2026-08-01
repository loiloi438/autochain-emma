<template>
  <div v-if="visible" class="toast" :class="type">{{ message }}</div>
</template>

<script setup>
import { ref } from 'vue'
defineProps(['message','type','visible'])
</script>

<style scoped>
.toast { position: fixed; right: 1rem; top: 1rem; padding: 0.6rem 1rem; border-radius: 6px; color: white; z-index: 1000 }
.toast.success { background: #2e7d32 }
.toast.error { background: #b00020 }
.toast.info { background: #1565c0 }
</style>
