import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

const API = import.meta.env.VITE_API_BASE ?? '/api'
const STORAGE_KEY = 'jbi.site-settings'

export interface SiteSettings {
  phone: string
  email: string
  whatsapp: string
  telegram: string
  max: string
}

const defaultSettings: SiteSettings = {
  phone: '+7(918)654-32-10',
  email: 'hello@example.com',
  whatsapp: '',
  telegram: '',
  max: '',
}

function isSiteSettings(value: unknown): value is SiteSettings {
  return Boolean(
    value &&
    typeof value === 'object' &&
    'phone' in value &&
    'email' in value &&
    typeof value.phone === 'string' &&
    typeof value.email === 'string',
  )
}

function str(value: unknown): string {
  return typeof value === 'string' ? value : ''
}

function readStoredSettings() {
  if (typeof window === 'undefined') {
    return null
  }

  try {
    const rawValue = window.localStorage.getItem(STORAGE_KEY)
    if (!rawValue) {
      return null
    }

    const parsedValue = JSON.parse(rawValue) as unknown
    return isSiteSettings(parsedValue) ? parsedValue : null
  } catch {
    return null
  }
}

function writeStoredSettings(settings: SiteSettings) {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(settings))
}

function normalizePhoneHref(phone: string) {
  const trimmed = phone.trim()
  if (!trimmed) {
    return 'tel:'
  }

  const hasLeadingPlus = trimmed.startsWith('+')
  const digits = trimmed.replace(/\D/g, '')
  if (!digits) {
    return 'tel:'
  }

  return `tel:${hasLeadingPlus ? '+' : ''}${digits}`
}

function normalizeEmailHref(email: string) {
  const trimmed = email.trim()
  return trimmed ? `mailto:${trimmed}` : 'mailto:'
}

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

export const useSiteSettingsStore = defineStore('siteSettings', () => {
  const settings = ref<SiteSettings>(readStoredSettings() ?? { ...defaultSettings })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  const phoneDisplay = computed(() => settings.value.phone.trim() || defaultSettings.phone)
  const emailDisplay = computed(() => settings.value.email.trim() || defaultSettings.email)
  const phoneHref = computed(() => normalizePhoneHref(phoneDisplay.value))
  const emailHref = computed(() => normalizeEmailHref(emailDisplay.value))

  function applySettings(nextSettings: Partial<SiteSettings>) {
    settings.value = {
      phone: str(nextSettings.phone) || defaultSettings.phone,
      email: str(nextSettings.email) || defaultSettings.email,
      whatsapp: str(nextSettings.whatsapp),
      telegram: str(nextSettings.telegram),
      max: str(nextSettings.max),
    }
    writeStoredSettings(settings.value)
  }

  async function fetchSettings() {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${API}/site-settings.php`)
      if (!res.ok) {
        throw new Error(await getApiError(res, 'Ошибка загрузки контактов'))
      }

      const data = (await res.json()) as Partial<SiteSettings>
      applySettings(data)
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
    } finally {
      loading.value = false
    }
  }

  async function saveSettings(nextSettings: SiteSettings) {
    saving.value = true
    error.value = null
    try {
      const res = await fetch(`${API}/site-settings.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(nextSettings),
      })

      if (!res.ok) {
        throw new Error(await getApiError(res, 'Ошибка сохранения контактов'))
      }

      const data = (await res.json()) as Partial<SiteSettings>
      applySettings({
        phone: str(data.phone) || nextSettings.phone,
        email: str(data.email) || nextSettings.email,
        whatsapp: str(data.whatsapp),
        telegram: str(data.telegram),
        max: str(data.max),
      })
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
      throw e
    } finally {
      saving.value = false
    }
  }

  return {
    settings,
    loading,
    saving,
    error,
    phoneDisplay,
    phoneHref,
    emailDisplay,
    emailHref,
    fetchSettings,
    saveSettings,
  }
})
