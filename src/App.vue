<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useSiteSettingsStore } from '@/stores/siteSettings'
import ProductCard from '@/components/ProductCard.vue'
import ProductPopup from '@/components/ProductPopup.vue'
import CallbackPopup from '@/components/CallbackPopup.vue'

const productsStore = useProductsStore()
const siteSettingsStore = useSiteSettingsStore()
const route = useRoute()
const isRootRoute = computed(() => route.path === '/')
const activeProductId = ref<number | null>(null)
const activeProduct = computed(
  () => productsStore.products.find((p) => p.id === activeProductId.value) ?? null,
)

function openProduct(id: number) {
  activeProductId.value = id
}
function closeProduct() {
  activeProductId.value = null
}

const isMobileMenuOpen = ref(false)
const isCallbackOpen = ref(false)
const isHeaderPinned = ref(false)
const menuControlRef = ref<HTMLElement | null>(null)

const updateHeaderState = () => {
  isHeaderPinned.value = window.scrollY > 24
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const handleDocumentClick = (event: MouseEvent) => {
  const target = event.target as Node | null
  if (!target || !menuControlRef.value) return

  if (!menuControlRef.value.contains(target)) {
    closeMobileMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('scroll', updateHeaderState, { passive: true })
  updateHeaderState()
  siteSettingsStore.fetchSettings()
  productsStore.fetchProducts()
})

watch(
  () => route.path,
  (path) => {
    if (path === '/') {
      siteSettingsStore.fetchSettings()
    }
  },
)

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('scroll', updateHeaderState)
})
</script>

<template>
  <RouterView v-if="!isRootRoute" />
  <div
    v-else
    class="min-h-screen bg-[#EFEFEF] px-13.75 pt-6 max-[1500px]:px-7.5 max-[640px]:px-3 max-[640px]:pt-3"
  >
    <header
      class="site-header bg-white rounded-[50px] max-w-452.5 mx-auto"
      :class="{ 'site-header--pinned': isHeaderPinned }"
    >
      <div class="px-5 py-4 flex items-center justify-between max-[640px]:py-3 max-[640px]:px-3">
        <a href="/" class="logo inline-flex items-center gap-3 max-[1500px]:order-1">
          <div
            class="icon rounded-[50%] bg-[#222222] h-16 w-16 min-w-16 flex items-center justify-center max-[640px]:h-12 max-[640px]:w-12 max-[640px]:min-w-12"
          >
            <svg class="h-8 w-8 max-[640px]:h-6 max-[640px]:w-6">
              <use xlink:href="./assets/sprite.svg#logo"></use>
            </svg>
          </div>
          <p class="text-[20px] font-semibold">ЖБИ</p>
        </a>
        <div ref="menuControlRef" class="menu-control max-[1500px]:order-4">
          <button
            class="mobile-menu-toggle"
            type="button"
            :aria-expanded="isMobileMenuOpen"
            aria-controls="main-menu-list"
            aria-label="Открыть меню"
            @click="toggleMobileMenu"
          >
            <span></span>
            <span></span>
            <span></span>
          </button>
          <nav class="main-menu" :class="{ 'is-open': isMobileMenuOpen }">
            <ul id="main-menu-list" class="flex gap-12">
              <li>
                <a
                  href="#catalog"
                  class="hover:text-cyan-400 text-[16px] font-semibold"
                  @click="closeMobileMenu"
                  >Каталог продукции</a
                >
              </li>
              <li>
                <a
                  href="#benefits"
                  class="hover:text-cyan-400 text-[16px] font-semibold"
                  @click="closeMobileMenu"
                  >Преимущества</a
                >
              </li>
              <li>
                <a
                  href="#workflow"
                  class="hover:text-cyan-400 text-[16px] font-semibold"
                  @click="closeMobileMenu"
                  >Схема работы</a
                >
              </li>
            </ul>
            <div class="menu-contacts menu-contacts--mobile">
              <a
                :href="siteSettingsStore.phoneHref"
                class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
              >
                <svg class="h-5 w-5.25 inline-block">
                  <use xlink:href="./assets/sprite.svg#phone"></use>
                </svg>
                {{ siteSettingsStore.phoneDisplay }}
              </a>
              <a
                :href="siteSettingsStore.emailHref"
                class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
              >
                <svg class="h-5 w-4.5 inline-block">
                  <use xlink:href="./assets/sprite.svg#email"></use>
                </svg>
                {{ siteSettingsStore.emailDisplay }}
              </a>
            </div>
          </nav>
        </div>
        <div
          class="menu-contacts menu-contacts--header flex gap-10 max-[1500px]:order-2 max-[1500px]:ml-auto"
        >
          <a
            :href="siteSettingsStore.phoneHref"
            class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
          >
            <svg class="h-5 w-5.25 inline-block">
              <use xlink:href="./assets/sprite.svg#phone"></use>
            </svg>
            {{ siteSettingsStore.phoneDisplay }}
          </a>
          <a
            :href="siteSettingsStore.emailHref"
            class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
          >
            <svg class="h-5 w-4.5 inline-block">
              <use xlink:href="./assets/sprite.svg#email"></use>
            </svg>
            {{ siteSettingsStore.emailDisplay }}
          </a>
        </div>
        <button
          class="button button--black max-[1500px]:order-3 max-[1500px]:ml-auto max-[1500px]:mr-5 max-[640px]:mr-3"
          @click="isCallbackOpen = true"
        >
          Заказать звонок
        </button>
      </div>
    </header>

    <main>
      <section class="max-w-452.5 mx-auto mt-6 max-[640px]:mt-3">
        <div
          class="hero bg-cover bg-center rounded-[50px] h-227 p-6.5 flex flex-col justify-between max-[1500px]:h-auto max-[1500px]:p-4 max-[1500px]:gap-80 max-[640px]:rounded-[25px] max-[640px]:gap-20"
        >
          <div class="hero-text p-10 max-[640px]:p-5 max-[640px]:gap-4">
            <h1
              class="text-[84px] leading-none font-semibold max-w-300 max-[1500px]:text-[48px] max-[1500px]:max-w-full max-[640px]:text-[32px]"
            >
              Железобетонные изделия от производителя
            </h1>
            <div
              class="flex gap-2.5 items-center mt-6 max-[1500px]:flex-wrap max-[1500px]:mt-4 max-[640px]:flex-col max-[640px]:items-start max-[640px]:gap-1"
            >
              <p class="advantage text-[24px] max-[1500px]:text-[18px] max-[640px]:text-[16px]">
                Доставка на объект
              </p>
              <p
                class="text-[24px] max-[1500px]:text-[18px] max-[640px]:text-[16px] max-[640px]:hidden"
              >
                •
              </p>
              <p class="advantage text-[24px] max-[1500px]:text-[18px] max-[640px]:text-[16px]">
                Отгрузка в день оплаты
              </p>
              <p
                class="text-[24px] max-[1500px]:text-[18px] max-[640px]:text-[16px] max-[640px]:hidden"
              >
                •
              </p>
              <p class="advantage text-[24px] max-[1500px]:text-[18px] max-[640px]:text-[16px]">
                Собственный автопарк манипуляторов
              </p>
            </div>
          </div>
          <div
            class="hero-panel bg-white flex items-center p-10 rounded-[50px] justify-between max-[1800px]:p-6 max-[960px]:flex-col max-[960px]:gap-5 max-[640px]:rounded-[25px]"
          >
            <div
              class="flex items-center gap-15 max-[1800px]:gap-10 max-[1640px]:flex-wrap max-[960px]:gap-auto max-[960px]:justify-between max-[960px]:w-full max-[640px]:justify-around"
            >
              <div class="flex items-center gap-5 max-[1800px]:gap-3 max-[960px]:gap-2">
                <div
                  class="h-16 w-16 min-w-16 rounded-[50%] bg-[#EFEFEF] flex items-center justify-center text-4 font-black max-[960px]:h-10 max-[960px]:w-10 max-[960px]:min-w-10 max-[960px]:text-[12px]"
                >
                  НДС
                </div>
                <p
                  class="text-[18px] font-bold leading-[1.3] max-[1800px]:text-[16px] max-[960px]:text-[14px]"
                >
                  Работаем <br />с НДС
                </p>
              </div>
              <div class="flex items-center gap-5 max-[960px]:gap-2">
                <div
                  class="h-16 w-16 min-w-16 rounded-[50%] bg-[#EFEFEF] flex items-center justify-center max-[960px]:h-10 max-[960px]:w-10 max-[960px]:min-w-10"
                >
                  <svg class="h-6.25 w-5.75 max-[960px]:h-4.5 max-[960px]:w-4.25">
                    <use xlink:href="./assets/sprite.svg#laboratory"></use>
                  </svg>
                </div>
                <p
                  class="text-[18px] font-bold leading-[1.3] max-[1800px]:text-[16px] max-[960px]:text-[14px]"
                >
                  Лабораторный <br />контроль
                </p>
              </div>
              <div class="flex items-center gap-5 max-[960px]:gap-2">
                <div
                  class="h-16 w-16 min-w-16 rounded-[50%] bg-[#EFEFEF] flex items-center justify-center text-[27px] font-black max-[960px]:h-10 max-[960px]:w-10 max-[960px]:min-w-10 max-[960px]:text-[20px]"
                >
                  %
                </div>
                <p
                  class="text-[18px] font-bold leading-[1.3] max-[1800px]:text-[16px] max-[960px]:text-[14px]"
                >
                  Скидки <br />от объема
                </p>
              </div>
              <div class="flex items-center gap-5 max-[960px]:gap-2">
                <div
                  class="h-16 w-16 min-w-16 rounded-[50%] bg-[#EFEFEF] flex items-center justify-center max-[960px]:h-10 max-[960px]:w-10 max-[960px]:min-w-10"
                >
                  <svg class="h-6.5 w-7.75 max-[960px]:h-4.5 max-[960px]:w-5.25">
                    <use xlink:href="./assets/sprite.svg#crown"></use>
                  </svg>
                </div>
                <p
                  class="text-[18px] font-bold leading-[1.3] max-[1800px]:text-[16px] max-[960px]:text-[14px]"
                >
                  Индивидуальный <br />подход
                </p>
              </div>
            </div>
            <div class="flex gap-5.5 max-[640px]:flex-col max-[640px]:gap-3 max-[640px]:w-full">
              <button class="button button--white">Получить прайс-лист</button>
              <button class="button button--black">Рассчитать стоимость</button>
            </div>
          </div>
        </div>
      </section>

      <section
        id="catalog"
        class="mx-auto max-w-420 mt-20 max-[640px]:mt-10 max-[640px]:max-w-full"
      >
        <h2
          class="leading-none mb-6 text-[52px] font-semibold max-[1500px]:text-[36px] max-[640px]:text-[28px] max-[640px]:mb-4"
        >
          Каталог продукции
        </h2>
        <p class="max-w-147.5 text-[18px] font-medium max-[640px]:text-[16px]">
          Нажмите на категорию, чтобы запросить расчет стоимости или скачать спецификацию
        </p>

        <!-- Сетка карточек -->
        <div v-if="productsStore.loading" class="mt-16 text-lg text-[#666]">
          Загрузка каталога...
        </div>
        <div
          id="products"
          v-else-if="productsStore.products.length"
          class="mt-16 grid gap-6 [grid-template-columns:repeat(auto-fill,minmax(280px,1fr))] max-[640px]:gap-2 max-[640px]:mt-8 max-[640px]:[grid-template-columns:repeat(auto-fill,minmax(210px,1fr))]"
        >
          <ProductCard
            v-for="product in productsStore.products"
            :key="product.id"
            :product="product"
            @open="openProduct"
          />
        </div>
        <div class="flex justify-center pt-16 max-[640px]:pt-8">
          <button class="button button--black m-auto button-in-page">Связаться с нами</button>
        </div>
      </section>

      <section
        id="benefits"
        class="pt-22.5 flex mx-auto max-w-420 gap-8 max-[1024px]:flex-col max-[640px]:pt-10"
      >
        <div class="max-w-106.25 max-[1024px]:max-w-full">
          <h2
            class="mb-7.5 text-[52px] font-semibold leading-[120%] max-[1500px]:text-[36px] max-[640px]:text-[28px] max-[640px]:mb-4"
          >
            Преимущества работы с нами
          </h2>
          <p class="text-[18px] font-medium leading-[1.61] max-[640px]:text-[16px]">
            Прямые поставки ЖБИ от производителя по честным ценам: помогаем экономить бюджет без
            потери качества, беря на себя все вопросы логистики и комплектации
          </p>
        </div>
        <div class="grid gap-6 grid-cols-3 max-[1500px]:grid-cols-1 max-[640px]:gap-2">
          <article
            class="rounded-[50px] bg-white p-7.5 flex flex-col gap-7.5 max-[1500px]:flex-row max-[1500px]:flex-wrap max-[1500px]:gap-4 max-[1500px]:items-center max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-3"
          >
            <div>
              <svg class="h-8 w-8 max-[640px]:h-6 max-[640px]:w-6">
                <use xlink:href="./assets/sprite.svg#rubl"></use>
              </svg>
            </div>
            <h3
              class="text-[28px] font-semibold leading-none max-[1500px]:text-[24px] max-[640px]:text-[20px]"
            >
              Честная цена
            </h3>
            <p class="leading-[1.61]">
              Работаем без посредников напрямую с производства. Гибкая система скидок для постоянных
              клиентов и больших объемов.
            </p>
          </article>
          <article
            class="rounded-[50px] bg-white p-7.5 flex flex-col gap-7.5 max-[1500px]:flex-row max-[1500px]:flex-wrap max-[1500px]:gap-4 max-[1500px]:items-center max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div>
              <svg class="h-9 w-9 max-[640px]:h-7 max-[640px]:w-7">
                <use xlink:href="./assets/sprite.svg#futurepay"></use>
              </svg>
            </div>
            <h3
              class="text-[28px] font-semibold leading-none max-[1500px]:text-[24px] max-[640px]:text-[20px]"
            >
              Отсрочка платежа
            </h3>
            <p class="leading-[1.61]">
              Для постоянных партнеров и госзаказчиков предоставляем отсрочку платежа по договору.
              Работаем с НДС.
            </p>
          </article>
          <article
            class="rounded-[50px] bg-white p-7.5 flex flex-col gap-7.5 max-[1500px]:flex-row max-[1500px]:flex-wrap max-[1500px]:gap-4 max-[1500px]:items-center max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div>
              <svg class="h-9 w-10 max-[640px]:h-7 max-[640px]:w-8">
                <use xlink:href="./assets/sprite.svg#gost"></use>
              </svg>
            </div>
            <h3
              class="text-[28px] font-semibold leading-none max-[1500px]:text-[24px] max-[640px]:text-[20px]"
            >
              Соответствие ГОСТ
            </h3>
            <p class="leading-[1.61]">
              Собственная лаборатория, паспорта качества на каждую партию. Все изделия соответствуют
              требованиям государственных стандартов.
            </p>
          </article>
        </div>
      </section>
      <section
        id="about"
        class="flex w-full pt-22.5 mx-auto max-w-420 gap-6 flex-wrap max-[640px]:pt-10 max-[640px]:gap-2"
      >
        <div
          class="about-img basis-[calc(50%-12px)] max-[640px]:rounded-[30px] h-128.75 rounded-[50px] p-12.5 max-[1500px]:basis-full max-[1500px]:h-60 max-[1500px]:p-7.5"
        >
          <h2
            class="text-[52px] font-semibold leading-none max-[1500px]:text-[36px] max-[640px]:text-[28px]"
          >
            О компании
          </h2>
        </div>
        <div
          class="bg-white max-[640px]:gap-6 max-[640px]:p-6 max-[640px]:rounded-[30px] basis-[calc(50%-12px)] rounded-[50px] flex flex-col gap-6 p-15 max-[1500px]:basis-full max-[1500px]:p-10"
        >
          <p class="text-[18px] leading-[1.61] max-[640px]:text-[16px]">
            <b>Более 10 лет</b> мы занимаемся производством и поставкой качественных железобетонных
            изделий для строительных компаний, государственных подрядчиков и частных застройщиков.
          </p>
          <p class="text-[18px] leading-[1.61] max-[640px]:text-[16px]">
            Наше производство оснащено современным оборудованием, что позволяет выпускать продукцию
            строго по ГОСТ с соблюдением всех технологических процессов.
          </p>
          <p class="text-[18px] leading-[1.61] max-[640px]:text-[16px]">
            Собственная аккредитованная лаборатория контролирует качество на всех этапах
            производства. К каждой партии прилагаются паспорта качества и сертификаты соответствия.
          </p>
          <p class="text-[18px] leading-[1.61] max-[640px]:text-[16px]">
            Благодаря собственному автопарку манипуляторов и длинномеров мы гарантируем
            своевременную доставку в любую точку региона.
          </p>
        </div>
        <div
          class="bg-white max-[640px]:p-7 max-[640px]:gap-0 max-[640px]:rounded-[30px] max-[640px]:justify-between rounded-[50px] py-14.25 px-18 flex basis-full justify-around max-[1500px]:p-10 max-[760px]:flex-wrap max-[760px]:gap-6"
        >
          <div class="flex flex-col justify-center items-center">
            <p
              class="text-[84px] font-bold leading-[1.2] max-[1500px]:text-[48px] max-[640px]:text-[26px]"
            >
              10+
            </p>
            <p
              class="font-medium text-[28px] leading-[1.2] max-[1500px]:text-[18px] max-[640px]:text-[12px]"
            >
              лет на рынке
            </p>
          </div>
          <div class="flex flex-col justify-center items-center">
            <p
              class="text-[84px] font-bold leading-[1.2] max-[1500px]:text-[48px] max-[640px]:text-[26px]"
            >
              500+
            </p>
            <p
              class="font-medium text-[28px] leading-[1.2] max-[1500px]:text-[18px] max-[640px]:text-[12px]"
            >
              объектов
            </p>
          </div>
          <div class="flex flex-col justify-center items-center">
            <p
              class="text-[84px] font-bold leading-[1.2] max-[1500px]:text-[48px] max-[640px]:text-[26px]"
            >
              200+
            </p>
            <p
              class="font-medium text-[28px] leading-[1.2] max-[1500px]:text-[18px] max-[640px]:text-[12px]"
            >
              видов продукции
            </p>
          </div>
          <div class="flex flex-col justify-center items-center">
            <p
              class="text-[84px] font-bold leading-[1.2] max-[1500px]:text-[48px] max-[640px]:text-[26px]"
            >
              100%
            </p>
            <p
              class="font-medium text-[28px] leading-[1.2] max-[1500px]:text-[18px] max-[640px]:text-[12px]"
            >
              качество
            </p>
          </div>
        </div>
        <div class="flex justify-center pt-8 m-auto">
          <button class="button button--black m-auto button-in-page">Связаться с нами</button>
        </div>
      </section>
      <section
        class="flex gap-6 items-start mt-22.5 mx-auto max-w-420 max-[640px]:mt-0 max-[640px]:max-w-full max-[1100px]:flex-col"
        id="workflow"
      >
        <div class="min-w-106.25 flex flex-col h-full pt-12">
          <h2
            class="leading-none mb-6 text-[52px] font-semibold max-[1500px]:text-[36px] max-[640px]:text-[28px] max-[640px]:mb-4"
          >
            Схема работы
          </h2>
          <p class="max-w-146 text-[18px] font-medium max-[640px]:text-[16px]">
            Простой и прозрачный процесс от заявки до доставки
          </p>
        </div>
        <div class="flex flex-wrap justify-between max-[1500px]:gap-6 max-[640px]:gap-2">
          <div
            class="bg-white rounded-[50px] p-7.5 flex flex-col gap-8 basis-[23.5%] max-[1500px]:basis-[calc(50%-12px)] max-[640px]:basis-full max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div class="flex items-center gap-4">
              <div
                class="bg-[#222222] rounded-[50%] h-12.5 w-12.5 min-w-12.5 number text-[20px] font-bold inline-flex items-center justify-center"
              >
                01
              </div>
              <h3 class="text-[18px] font-semibold inline leading-[1.2]">
                Заявка на сайте или звонок
              </h3>
            </div>

            <p class="text-[16px] leading-[1.61]">
              Оставьте заявку на сайте, позвоните по телефону или напишите в мессенджер
            </p>
          </div>
          <div
            class="bg-white rounded-[50px] p-7.5 flex flex-col gap-8 basis-[23.5%] max-[1500px]:basis-[calc(50%-12px)] max-[640px]:basis-full max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div class="flex items-center gap-4">
              <div
                class="bg-[#222222] rounded-[50%] h-12.5 w-12.5 min-w-12.5 number text-[20px] font-bold inline-flex items-center justify-center"
              >
                02
              </div>
              <h3 class="text-[18px] font-semibold inline leading-[1.2]">
                Расчет <br class="max-[640px]:hidden" />стоимости
              </h3>
            </div>

            <p class="text-[16px] leading-[1.61]">
              Менеджер рассчитает стоимость, подберет оптимальное решение и выставит счет
            </p>
          </div>
          <div
            class="bg-white rounded-[50px] p-7.5 flex flex-col gap-8 basis-[23.5%] max-[1500px]:basis-[calc(50%-12px)] max-[640px]:basis-full max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div class="flex items-center gap-4">
              <div
                class="bg-[#222222] rounded-[50%] h-12.5 w-12.5 min-w-12.5 number text-[20px] font-bold inline-flex items-center justify-center"
              >
                03
              </div>
              <h3 class="text-[18px] font-semibold inline leading-[1.2]">
                Оплата <br class="max-[640px]:hidden" />и производство
              </h3>
            </div>

            <p class="text-[16px] leading-[1.61]">
              После оплаты начинается производство или комплектация заказа со склада
            </p>
          </div>
          <div
            class="bg-white rounded-[50px] p-7.5 flex flex-col gap-8 basis-[23.5%] max-[1500px]:basis-[calc(50%-12px)] max-[640px]:basis-full max-[640px]:rounded-[30px] max-[640px]:p-6 max-[640px]:gap-4"
          >
            <div class="flex items-center gap-4">
              <div
                class="bg-[#222222] rounded-[50%] h-12.5 w-12.5 min-w-12.5 number text-[20px] font-bold inline-flex items-center justify-center"
              >
                04
              </div>
              <h3 class="text-[18px] font-semibold inline leading-[1.2]">
                Доставка <br class="max-[640px]:hidden" />на объект
              </h3>
            </div>

            <p class="text-[16px] leading-[1.61]">
              Доставка манипулятором точно в срок с разгрузкой и складированием
            </p>
          </div>
        </div>
      </section>
    </main>

    <footer
      class="bg-white rounded-[50px] max-w-452.5 mx-auto p-7.5 flex items-center justify-between mt-20 max-[1024px]:flex-wrap max-[640px]:gap-10"
    >
      <a href="/" class="logo inline-flex items-center gap-3">
        <div
          class="icon rounded-[50%] bg-[#222222] h-16 w-16 min-w-16 flex items-center justify-center max-[640px]:h-12 max-[640px]:w-12 max-[640px]:min-w-12"
        >
          <svg class="h-8 w-8 max-[640px]:h-6 max-[640px]:w-6">
            <use xlink:href="./assets/sprite.svg#logo"></use>
          </svg>
        </div>
        <p class="text-[20px] font-semibold">ЖБИ</p>
      </a>
      <nav class="footer-menu">
        <ul id="footer-menu-list" class="flex gap-12 max-[1610px]:flex-col max-[1610px]:gap-3">
          <li>
            <a href="#catalog" class="hover:text-cyan-400 text-[16px] font-semibold"
              >Каталог продукции</a
            >
          </li>
          <li>
            <a href="#benefits" class="hover:text-cyan-400 text-[16px] font-semibold"
              >Преимущества</a
            >
          </li>
          <li>
            <a href="#workflow" class="hover:text-cyan-400 text-[16px] font-semibold"
              >Схема работы</a
            >
          </li>
        </ul>
      </nav>
      <div class="menu-contacts--footer flex gap-6 max-[1610px]:flex-col max-[1610px]:gap-3">
        <a
          :href="siteSettingsStore.phoneHref"
          class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
        >
          <svg class="h-5 w-5.25 inline-block">
            <use xlink:href="./assets/sprite.svg#phone"></use>
          </svg>
          {{ siteSettingsStore.phoneDisplay }}
        </a>
        <a
          :href="siteSettingsStore.emailHref"
          class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
        >
          <svg class="h-5 w-4.5 inline-block">
            <use xlink:href="./assets/sprite.svg#email"></use>
          </svg>
          {{ siteSettingsStore.emailDisplay }}
        </a>
      </div>
      <div
        v-if="
          siteSettingsStore.settings.whatsapp ||
          siteSettingsStore.settings.telegram ||
          siteSettingsStore.settings.max
        "
        class="flex gap-4"
      >
        <a
          v-if="siteSettingsStore.settings.whatsapp"
          :href="siteSettingsStore.settings.whatsapp"
          target="_blank"
          rel="noopener noreferrer"
          class="h-9 w-9 min-w-9 block"
        >
          <svg class="h-full w-full inline-block">
            <use xlink:href="./assets/sprite.svg#whatsapp"></use>
          </svg>
        </a>
        <a
          v-if="siteSettingsStore.settings.telegram"
          :href="siteSettingsStore.settings.telegram"
          target="_blank"
          rel="noopener noreferrer"
          class="h-9 w-9 min-w-9 block"
        >
          <svg class="h-full w-full inline-block">
            <use xlink:href="./assets/sprite.svg#tg"></use>
          </svg>
        </a>
        <a
          v-if="siteSettingsStore.settings.max"
          :href="siteSettingsStore.settings.max"
          target="_blank"
          rel="noopener noreferrer"
          class="h-9 w-9 min-w-9 bg-[#222222] flex items-center justify-center rounded-[50%]"
        >
          <svg class="h-5 w-4.5 inline-block">
            <use xlink:href="./assets/sprite.svg#max"></use>
          </svg>
        </a>
      </div>
      <button class="button button--black max-[640px]:m-auto" @click="isCallbackOpen = true">Заказать звонок</button>
    </footer>
    <p class="text-center py-6 m-auto">2026 ©ЖБИ. Все права защищены</p>
  </div>

  <ProductPopup
    v-if="isRootRoute && activeProduct"
    :product="activeProduct"
    @close="closeProduct"
  />

  <CallbackPopup
    v-if="isCallbackOpen"
    @close="isCallbackOpen = false"
  />
</template>
