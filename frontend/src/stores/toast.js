import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  function push(message, type = 'info', timeout = 3500) {
    const id = Date.now() + Math.random()
    toasts.value.push({ id, message, type })
    if (timeout > 0) {
      const timer = setTimeout(() => remove(id), timeout)
      if (timer && typeof timer.unref === 'function') timer.unref()
    }
    return id
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  function clear() {
    toasts.value = []
  }

  return { toasts, push, remove, clear }
})
