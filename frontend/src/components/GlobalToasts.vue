<template>
  <div class="global-toasts" aria-live="polite">
    <div v-for="t in store.toasts" :key="t.id" :class="['toast', t.type]">
      <div class="message">{{ t.message }}</div>
      <button class="close" @click="store.remove(t.id)">×</button>
    </div>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast'
const store = useToastStore()
</script>

<style scoped>
.global-toasts { position: fixed; right: 1rem; top: 1rem; display:flex; flex-direction:column; gap:0.5rem; z-index:9999 }
.toast { min-width: 220px; max-width: 380px; padding:0.6rem 0.8rem; border-radius:6px; box-shadow:0 6px 18px rgba(0,0,0,0.08); color:#fff; display:flex; align-items:center; justify-content:space-between }
.toast .message { flex:1; margin-right:0.5rem }
.toast.info { background: linear-gradient(90deg,#3b82f6,#60a5fa) }
.toast.success { background: linear-gradient(90deg,#10b981,#34d399) }
.toast.error { background: linear-gradient(90deg,#ef4444,#f97316) }
.toast .close { background:transparent; border:0; color:rgba(255,255,255,0.9); font-weight:700; cursor:pointer }
</style>
