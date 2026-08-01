const BrowserProvider = vi.fn()
const Contract = vi.fn()
const id = vi.fn((value) => `0x${String(value)}`)

vi.mock('ethers', () => ({
  __esModule: true,
  BrowserProvider,
  Contract,
  id,
}))

import { createPinia, setActivePinia } from 'pinia'
import { BrowserProvider as MockBrowserProvider, Contract as MockContract, id as mockId } from 'ethers'
import { useWalletStore } from './wallet'

let sendMock
let getNetworkMock
let getSignerMock

describe('wallet store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    sendMock = vi.fn()
    getNetworkMock = vi.fn()
    getSignerMock = vi.fn()

    BrowserProvider.mockImplementation(() => ({
      send: sendMock,
      getNetwork: getNetworkMock,
      getSigner: getSignerMock,
    }))
    Contract.mockReturnValue({})
    id.mockImplementation((value) => `0x${String(value)}`)

    Object.defineProperty(global, 'window', {
      value: { ethereum: {} },
      configurable: true,
    })
  })

  it('waits for the MetaMask signer before creating the contract instance', async () => {
    sendMock.mockResolvedValue(['0x123'])
    getNetworkMock.mockResolvedValue({ chainId: 1n })
    getSignerMock.mockResolvedValue({ signMessage: vi.fn() })
    Contract.mockReturnValue({})

    const store = useWalletStore()
    await store.getContract()

    expect(getSignerMock).toHaveBeenCalledTimes(1)
    expect(Contract).toHaveBeenCalledWith(
      '0x5FbDB2315678afecb367f032d93F642f64180aa3',
      expect.any(Array),
      expect.objectContaining({ signMessage: expect.any(Function) }),
    )
  })
})
