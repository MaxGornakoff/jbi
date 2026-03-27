<script setup lang="ts">
import type { Product } from '@/stores/products'

defineProps<{ product: Product }>()
const emit = defineEmits<{ (e: 'open', id: number): void }>()
</script>

<template>
  <button class="product-card" type="button" @click="emit('open', product.id)">
    <div class="product-card__image-wrap">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="product-card__image"
      />
      <div v-else class="product-card__image-placeholder">
        <span>Нет фото</span>
      </div>
    </div>
    <p class="product-card__name">{{ product.name }}</p>
  </button>
</template>
