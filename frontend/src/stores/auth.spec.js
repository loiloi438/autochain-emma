vi.mock('../services/api', () => ({
  __esModule: true,
  default: {
    post: vi.fn(),
    get: vi.fn(),
  },
}))

import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from './auth'
import api from '../services/api'

const apiMock = api

describe('auth store', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
    vi.clearAllMocks()
    // ensure axios instance methods are stubbed to prevent real requests
    api.post = apiMock.post
    api.get = apiMock.get
  })

  it('stores the token and user after a classic login', async () => {
    // api.post/get have been stubbed in beforeEach
    apiMock.post.mockResolvedValueOnce({
      data: { token: 'abc', user: { id: 1, name: 'Ada', roles: [] } },
    })

    const auth = useAuthStore()
    await auth.login('ada@example.com', 'password')

    expect(auth.token).toBe('abc')
    expect(auth.user.name).toBe('Ada')
    expect(localStorage.getItem('autochain_token')).toBe('abc')
  })

  it('stores the token and user after wallet login', async () => {
    apiMock.post.mockResolvedValueOnce({
      data: { token: 'wallet-token', user: { id: 2, name: 'Wallet', roles: [] } },
    })

    const auth = useAuthStore()
    await auth.loginWithWallet('0xabc', 'hello', 'sig')

    expect(apiMock.post).toHaveBeenCalledWith('/login', {
      wallet_address: '0xabc',
      message: 'hello',
      signature: 'sig',
    })
    expect(auth.token).toBe('wallet-token')
  })

  it('selects the dedicated home page from the user role', () => {
    const auth = useAuthStore()

    auth.user = { roles: [{ name: 'DRIVER' }] }

    expect(auth.roles).toEqual(['driver'])
    expect(auth.hasRole('driver')).toBe(true)
    expect(auth.homeRoute).toBe('driver')
  })
})
