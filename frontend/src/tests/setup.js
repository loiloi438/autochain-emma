import { setActivePinia, createPinia } from 'pinia'

beforeEach(() => {
  setActivePinia(createPinia())
})

afterEach(() => {
  localStorage.clear()
})
