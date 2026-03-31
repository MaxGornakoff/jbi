<script setup lang="ts">
import type { Product } from '@/stores/products'
import { useSiteSettingsStore } from '@/stores/siteSettings'

defineProps<{ product: Product }>()
const emit = defineEmits<{ (e: 'close'): void }>()
const siteSettingsStore = useSiteSettingsStore()

function onBackdropClick(e: MouseEvent) {
  if (e.target === e.currentTarget) emit('close')
}
</script>

<template>
  <div
    class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-5"
    @click="onBackdropClick"
  >
    <div
      class="relative bg-white rounded-[32px] max-w-[900px] w-full max-h-[90vh] overflow-y-auto"
      role="dialog"
      aria-modal="true"
      :aria-label="product.name"
    >
      <button
        class="sticky top-4 float-right mt-4 mr-4 w-10 h-10 rounded-full bg-[#f0f0f0] border-0 cursor-pointer flex flex-col items-center justify-center shrink-0 z-[1]"
        type="button"
        aria-label="Закрыть"
        @click="emit('close')"
      >
        <span class="block w-[18px] h-0.5 bg-[#222] rounded-sm absolute rotate-45"></span>
        <span class="block w-[18px] h-0.5 bg-[#222] rounded-sm absolute -rotate-45"></span>
      </button>

      <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:gap-8 sm:p-8">
        <div class="rounded-[20px] overflow-hidden aspect-square bg-[#f5f5f5]">
          <img
            v-if="product.image_url"
            :src="product.image_url"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-[#aaa]">
            Нет фото
          </div>
        </div>

        <div class="flex flex-col gap-4">
          <h2 class="text-[22px] sm:text-[28px] font-bold leading-tight">{{ product.name }}</h2>

          <p v-if="product.description" class="text-base leading-relaxed text-[#444]">
            {{ product.description }}
          </p>

          <div v-if="product.zones.length">
            <p class="text-sm font-bold uppercase tracking-[0.05em] text-[#888] mb-2">
              Зоны применения
            </p>
            <ul class="list-none p-0 m-0 flex flex-col gap-1.5">
              <li
                v-for="zone in product.zones"
                :key="zone"
                class="relative pl-4 text-[15px] before:content-[''] before:absolute before:left-0 before:top-2 before:size-1.5 before:rounded-full before:bg-[#222]"
              >
                {{ zone }}
              </li>
            </ul>
          </div>

          <p
            v-if="product.note"
            class="text-sm text-[#888] border-l-[3px] border-[#e0e0e0] pl-3 leading-relaxed"
          >
            {{ product.note }}
          </p>

          <div class="flex flex-wrap gap-3 mt-auto">
            <a
              :href="siteSettingsStore.phoneHref"
              class="button button--black !min-w-0 !h-[52px] !px-5 !text-[15px] no-underline"
              >Запросить стоимость</a
            >
            <a
              v-if="product.spec_url"
              :href="product.spec_url"
              :download="product.spec_name || undefined"
              target="_blank"
              rel="noopener"
              class="button button--white !min-w-0 !h-[52px] !px-5 !text-[15px] no-underline"
            >
              Скачать спецификацию
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
