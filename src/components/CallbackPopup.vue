<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{ (e: 'close'): void }>()

const API = import.meta.env.VITE_API_BASE ?? '/api'

const phone = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref<string | null>(null)

function onBackdropClick(e: MouseEvent) {
  if (e.target === e.currentTarget) emit('close')
}

async function submit() {
  error.value = null
  loading.value = true
  try {
    const res = await fetch(`${API}/callback.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ phone: phone.value.trim() }),
    })
    const json = await res.json()
    if (!res.ok) throw new Error(json.error ?? 'Ошибка отправки')
    success.value = true
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Не удалось отправить заявку'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    class="fixed inset-0 bg-black/50 z-100 flex items-center justify-center p-5"
    @click="onBackdropClick"
  >
    <div
      class="relative bg-white rounded-4xl w-full max-w-115 p-8 max-[640px]:p-6"
      role="dialog"
      aria-modal="true"
      aria-label="Заказать звонок"
    >
      <!-- Кнопка закрытия -->
      <button
        class="absolute top-4 right-4 w-10 h-10 rounded-full bg-[#f0f0f0] border-0 cursor-pointer flex items-center justify-center shrink-0"
        type="button"
        aria-label="Закрыть"
        @click="emit('close')"
      >
        <span class="block w-4.5 h-0.5 bg-[#222] rounded-sm absolute rotate-45"></span>
        <span class="block w-4.5 h-0.5 bg-[#222] rounded-sm absolute -rotate-45"></span>
      </button>

      <!-- Успех -->
      <div v-if="success" class="flex flex-col items-center gap-4 py-4 text-center">
        <div
          class="w-16 h-16 rounded-full bg-[#e8f5ee] flex items-center justify-center shrink-0"
        >
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
            <path
              d="M7 17l6 6 12-12"
              stroke="#2d7a46"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
        <h2 class="text-[22px] font-bold">Заявка отправлена!</h2>
        <p class="text-[#666] text-[15px]">
          Мы получили ваш номер и свяжемся с вами в&nbsp;ближайшее рабочее время.
        </p>
        <button type="button" class="button button--black mt-2" @click="emit('close')">
          Закрыть
        </button>
      </div>

      <!-- Форма -->
      <template v-else>
        <h2 class="text-[22px] font-bold pr-12">Заказать звонок</h2>
        <p class="mt-1.5 text-[14px] text-[#666]">
          Оставьте номер — мы перезвоним в&nbsp;течение рабочего дня.
        </p>

        <form class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
          <label class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Ваш номер телефона</span>
            <input
              v-model="phone"
              type="tel"
              required
              placeholder="+7 (___) ___-__-__"
              autocomplete="tel"
              class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 font-[inherit] focus:border-[#222]"
            />
          </label>

          <p v-if="error" class="text-[#c0392b] text-sm">{{ error }}</p>

          <button type="submit" class="button button--black" :disabled="loading">
            {{ loading ? 'Отправка...' : 'Перезвоните мне' }}
          </button>
        </form>
      </template>
    </div>
  </div>
</template>
