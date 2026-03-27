<script setup lang="ts">
import type { Product } from '@/stores/products'

defineProps<{ product: Product }>()
const emit = defineEmits<{ (e: 'close'): void }>()

function onBackdropClick(e: MouseEvent) {
  if (e.target === e.currentTarget) emit('close')
}
</script>

<template>
  <div class="popup-backdrop" @click="onBackdropClick">
    <div class="popup" role="dialog" aria-modal="true" :aria-label="product.name">
      <button class="popup__close" type="button" aria-label="Закрыть" @click="emit('close')">
        <span></span>
        <span></span>
      </button>

      <div class="popup__inner">
        <div class="popup__image-wrap">
          <img
            v-if="product.image_url"
            :src="product.image_url"
            :alt="product.name"
            class="popup__image"
          />
          <div v-else class="popup__image-placeholder">Нет фото</div>
        </div>

        <div class="popup__content">
          <h2 class="popup__name">{{ product.name }}</h2>

          <p v-if="product.description" class="popup__description">{{ product.description }}</p>

          <div v-if="product.zones.length" class="popup__section">
            <p class="popup__section-title">Зоны применения</p>
            <ul class="popup__zones">
              <li v-for="zone in product.zones" :key="zone">{{ zone }}</li>
            </ul>
          </div>

          <p v-if="product.note" class="popup__note">{{ product.note }}</p>

          <div class="popup__actions">
            <a href="tel:" class="button button--black">Запросить стоимость</a>
            <a
              v-if="product.spec_url"
              :href="product.spec_url"
              :download="product.spec_name || undefined"
              target="_blank"
              rel="noopener"
              class="button button--white"
            >
              Скачать спецификацию
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
