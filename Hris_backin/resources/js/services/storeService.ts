import axios from 'axios'

export interface TrackingClientStoreAddress {
  id: number
  client_id: number
  store_code: string
  store_name: string
  address: string
  city: string
  state: string
  postal_code: string
  country: string
  contact_person: string
  contact_phone: string
  contact_email: string
  latitude?: number
  longitude?: number
  is_active: boolean
  created_at: string
  updated_at: string
  client_name?: string
  creator?: string
}

/**
 * Fetch stores by client ID
 * @param clientId - The client ID to fetch stores for
 * @returns Promise<TrackingClientStoreAddress[]>
 */
export const getStoresByClientId = async (clientId: number): Promise<TrackingClientStoreAddress[]> => {
  try {
    const response = await axios.get(`/tracking/store/api/by-client/${clientId}`)
    return response.data || []
  } catch (error) {
    console.error('Error fetching stores by client ID:', error)
    throw error
  }
}

/**
 * Fetch all active stores
 * @returns Promise<TrackingClientStoreAddress[]>
 */
export const getAllActiveStores = async (): Promise<TrackingClientStoreAddress[]> => {
  try {
    const response = await axios.get('/tracking/store')
    return response.data || []
  } catch (error) {
    console.error('Error fetching all active stores:', error)
    throw error
  }
}