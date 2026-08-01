import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'

const apiMock = {
  post: vi.fn(),
  get: vi.fn(),
}

vi.mock('../services/api', () => ({
  __esModule: true,
  default: apiMock,
}))

import AdminView from './AdminView.vue'
import { useToastStore } from '../stores/toast'
import { useAuthStore } from '../stores/auth'

let pinia

beforeEach(() => {
  pinia = createPinia()
  setActivePinia(pinia)
  const auth = useAuthStore(pinia)
  auth.token = 'fake-token'
  auth.user = { roles: [{ name: 'admin' }] }
  const toast = useToastStore(pinia)
  toast.clear()
})

test('renders admin controls and shows sync mapping', async () => {
  apiMock.post.mockResolvedValueOnce({ data: { mapping: { admin: 1, driver: null } } })

  const wrapper = mount(AdminView, {
    global: {
      plugins: [pinia],
    },
  })

  expect(wrapper.text()).toContain('Synchroniser les rôles')

  await wrapper.find('button').trigger('click')
  await vi.nextTick()

  expect(apiMock.post).toHaveBeenCalledWith('/blockchain/sync-roles')
  expect(wrapper.text()).toContain('Rôles synchronisés')
  expect(wrapper.text()).toContain('admin')
  expect(wrapper.text()).toContain('1')
  expect(wrapper.text()).toContain('driver')
})

test('loads contract info when fetchContract is clicked', async () => {
  apiMock.get.mockResolvedValueOnce({ data: { address: '0x123', network: 'local' } })
  const wrapper = mount(AdminView, {
    global: {
      plugins: [pinia],
    },
  })

  await wrapper.findAll('button')[1].trigger('click')
  await vi.nextTick()

  expect(apiMock.get).toHaveBeenCalledWith('/blockchain/contract')
  expect(wrapper.text()).toContain('Contrat:')
  expect(wrapper.text()).toContain('0x123')
})
