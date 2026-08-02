import { defineStore } from 'pinia'
import { BrowserProvider, Contract, id } from 'ethers'
import registryArtifact from '../abi/VehicleRegistry.json'
import api from '../services/api'

function getContractAddress() {
  return registryArtifact.address || import.meta.env.VITE_CONTRACT_ADDRESS || ''
}

function getMetaMaskProvider() {
  if (typeof window === 'undefined' || !window.ethereum) {
    return null
  }
  const provider = window.ethereum?.providers?.find((p) => p.isMetaMask) || window.ethereum
  return provider
}

export const useWalletStore = defineStore('wallet', {
  state: () => ({
    address: '',
    chainId: null,
    error: '',
    lastTxHash: '',
  }),
  actions: {
    async connect() {
      this.error = ''
      const provider = getMetaMaskProvider()
      if (!provider) {
        this.error = 'MetaMask est requis pour signer les transactions critiques. Installez MetaMask ou ouvrez votre extension.'
        throw new Error(this.error)
      }

      const accounts = await provider.request({ method: 'eth_requestAccounts' })
      if (!accounts || !accounts.length) {
        throw new Error('Aucun compte MetaMask trouvé.')
      }
      this.address = accounts[0]
      const chainIdHex = await provider.request({ method: 'eth_chainId' })
      this.chainId = Number(chainIdHex)
      return this.address
    },

    async getSigner() {
      await this.connect()
      const provider = getMetaMaskProvider()
      if (!provider) {
        throw new Error('MetaMask est requis pour signer les transactions critiques.')
      }
      return new BrowserProvider(provider).getSigner()
    },

    async signMessage(message) {
      this.error = ''
      const provider = getMetaMaskProvider()
      if (!provider) {
        this.error = 'MetaMask est requis pour signer les transactions critiques. Installez MetaMask ou ouvrez votre extension.'
        throw new Error(this.error)
      }

      if (!this.address) {
        await this.connect()
      }

      const account = this.address
      const signature = await provider.request({
        method: 'personal_sign',
        params: [message, account],
      })
      return signature
    },

    async loginWithSignature() {
      const address = await this.connect()
      const message = `AutoChain Emma+ authentication request for ${address}`
      const signature = await this.signMessage(message)
      const { data } = await api.post('/login', { wallet_address: address, signature, message })
      return data
    },

    async getContract() {
      const signer = await this.getSigner()
      const address = getContractAddress()
      if (!address) {
        throw new Error('Adresse du contrat introuvable. Deployez Hardhat puis rechargez l ABI.')
      }
      return new Contract(address, registryArtifact.abi, signer)
    },

    async registerVehicleOnChain(vin, initialKm, vehicleId) {
      const contract = await this.getContract()
      const tx = await contract.registerVehicle(id(vin), BigInt(initialKm))
      this.lastTxHash = tx.hash
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'registerVehicle',
        vehicle_id: vehicleId,
        status: 'pending',
        payload: { vin_hash: id(vin), initial_km: initialKm },
      })
      await tx.wait()
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'registerVehicle',
        vehicle_id: vehicleId,
        status: 'confirmed',
        payload: { vin_hash: id(vin), initial_km: initialKm },
      })
      return tx.hash
    },

    async recordMileageOnChain(onChainVehicleId, km, backendVehicleId) {
      const contract = await this.getContract()
      const tx = await contract.recordMileage(BigInt(onChainVehicleId), BigInt(km))
      this.lastTxHash = tx.hash
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'mileage',
        vehicle_id: backendVehicleId,
        status: 'pending',
        payload: { on_chain_vehicle_id: onChainVehicleId, km },
      })
      await tx.wait()
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'mileage',
        vehicle_id: backendVehicleId,
        status: 'confirmed',
        payload: { on_chain_vehicle_id: onChainVehicleId, km },
      })
      return tx.hash
    },

    async recordMaintenanceOnChain(onChainVehicleId, serviceType, parts, backendVehicleId) {
      const contract = await this.getContract()
      const partsHash = id(JSON.stringify(parts || []))
      const tx = await contract.recordMaintenance(BigInt(onChainVehicleId), serviceType, partsHash)
      this.lastTxHash = tx.hash
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'maintenance',
        vehicle_id: backendVehicleId,
        status: 'pending',
        payload: { service_type: serviceType, parts_hash: partsHash },
      })
      await tx.wait()
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'maintenance',
        vehicle_id: backendVehicleId,
        status: 'confirmed',
        payload: { service_type: serviceType, parts_hash: partsHash },
      })
      return tx.hash
    },

    async recordDocumentHashOnChain(onChainVehicleId, docHash, docType, backendVehicleId) {
      const contract = await this.getContract()
      const bytesHash = docHash.startsWith('0x') ? docHash : id(docHash)
      const tx = await contract.recordDocumentHash(BigInt(onChainVehicleId), bytesHash, docType)
      this.lastTxHash = tx.hash
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'document',
        vehicle_id: backendVehicleId,
        status: 'pending',
        payload: { doc_hash: bytesHash, doc_type: docType },
      })
      await tx.wait()
      await api.post('/blockchain/txs', {
        tx_hash: tx.hash,
        action: 'document',
        vehicle_id: backendVehicleId,
        status: 'confirmed',
        payload: { doc_hash: bytesHash, doc_type: docType },
      })
      return tx.hash
    },
  },
})
