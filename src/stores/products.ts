import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Product {
  id: number
  name: string
  image_url: string
  description: string
  zones: string[]
  note: string
  spec_url: string
  spec_name: string
}

const API = import.meta.env.VITE_API_BASE ?? '/api'

async function getApiError(response: Response, fallback: string) {
  try {
    const data = await response.json()
    if (typeof data?.error === 'string' && data.error.trim()) {
      return data.error
    }
  } catch {
    try {
      const text = await response.text()
      if (text.trim()) {
        return text
      }
    } catch {
      return fallback
    }
  }

  return fallback
}

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchProducts() {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${API}/products.php`)
      if (!res.ok) throw new Error('Ошибка загрузки продуктов')
      products.value = await res.json()
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
    } finally {
      loading.value = false
    }
  }

  async function addProduct(formData: FormData) {
    const res = await fetch(`${API}/products.php`, {
      method: 'POST',
      credentials: 'include',
      body: formData,
    })
    if (!res.ok) {
      throw new Error(await getApiError(res, 'Ошибка создания'))
    }
    await fetchProducts()
  }

  async function updateProduct(id: number, formData: FormData) {
    formData.append('_method', 'PUT')
    formData.append('id', String(id))
    const res = await fetch(`${API}/products.php`, {
      method: 'POST',
      credentials: 'include',
      body: formData,
    })
    if (!res.ok) {
      throw new Error(await getApiError(res, 'Ошибка обновления'))
    }
    await fetchProducts()
  }

  async function deleteProduct(id: number) {
    const res = await fetch(`${API}/products.php?id=${id}`, {
      method: 'DELETE',
      credentials: 'include',
    })
    if (!res.ok) {
      throw new Error(await getApiError(res, 'Ошибка удаления'))
    }
    await fetchProducts()
  }

  return { products, loading, error, fetchProducts, addProduct, updateProduct, deleteProduct }
})
