<script setup lang="ts">
import type { Product } from '@/stores/products'

defineProps<{ product: Product }>()
const emit = defineEmits<{ (e: 'open', id: number): void }>()
</script>

<template>
  <button
    class="bg-white max-h-49 max-[640px]:rounded-[20px] max-[640px]:pb-5 max-[640px]:pt-4 pt-5 pb-7.5 rounded-[30px] overflow-hidden text-left cursor-pointer border-0 transition-all duration-200 ease-in-out shadow-[1px_2px_20px_1px_rgba(0,0,0,0.05)] hover:-translate-y-1 hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)]"
    type="button"
    @click="emit('open', product.id)"
  >
    <div
      class="w-full aspect-[4/3] overflow-hidden flex items-center justify-center max-h-29 max-[640px]:max-h-20"
    >
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-auto h-full object-contain max-w-62"
      />
      <div v-else class="w-auto h-28.5 flex items-center justify-center text-[#aaa] text-sm">
        <span>Нет фото</span>
      </div>
    </div>
    <p class="px-7 pt-2.5 text-[16px] font-semibold leading-[1.3] text-center max-[640px]:px-4">
      {{ product.name }}
    </p>
  </button>
</template>
