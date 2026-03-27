import { defineStore } from 'pinia'
import { ref } from 'vue'

const API = import.meta.env.VITE_API_BASE ?? '/api'

export const useAuthStore = defineStore('auth', () => {
  const isAuthenticated = ref(false)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function checkAuth() {
    try {
      const res = await fetch(`${API}/auth.php`, { credentials: 'include' })
      const data = await res.json()
      isAuthenticated.value = data.authenticated === true
    } catch {
      isAuthenticated.value = false
    }
  }

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${API}/auth.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      })
      const data = await res.json()
      if (!res.ok) throw new Error(data.error ?? 'Ошибка входа')
      isAuthenticated.value = true
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    await fetch(`${API}/auth.php`, { method: 'DELETE', credentials: 'include' })
    isAuthenticated.value = false
  }

  return { isAuthenticated, loading, error, checkAuth, login, logout }
})
