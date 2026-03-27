<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

const isMobileMenuOpen = ref(false)
const menuControlRef = ref<HTMLElement | null>(null)

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
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <div
    class="min-h-screen bg-[#EFEFEF] px-13.75 pt-6 max-[1500px]:px-7.5 max-[640px]:px-3 max-[640px]:pt-3"
  >
    <header class="bg-white rounded-[50px] max-w-452.5 mx-auto">
      <div class="px-5 py-4 flex items-center justify-between max-[640px]:py-3 max-[640px]:px-3">
        <a href="/" class="logo inline-flex items-center gap-3 max-[1500px]:order-1">
          <div
            class="icon rounded-[50%] bg-[#333] h-16 w-16 min-w-16 flex items-center justify-center max-[640px]:h-12 max-[640px]:w-12 max-[640px]:min-w-12"
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
                  href="#products"
                  class="hover:text-cyan-400 text-[16px] font-semibold"
                  @click="closeMobileMenu"
                  >Каталог продукции</a
                >
              </li>
              <li>
                <a
                  href="#advantages"
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
                href="tel:"
                class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
              >
                <svg class="h-5 w-5.25 inline-block">
                  <use xlink:href="./assets/sprite.svg#phone"></use>
                </svg>
                +7(918)654-32-10
              </a>
              <a
                href="mailto:hello@example.com"
                class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
              >
                <svg class="h-5 w-4.5 inline-block">
                  <use xlink:href="./assets/sprite.svg#email"></use>
                </svg>
                hello@example.com
              </a>
            </div>
          </nav>
        </div>
        <div
          class="menu-contacts menu-contacts--header flex gap-10 max-[1500px]:order-2 max-[1500px]:ml-auto"
        >
          <a
            href="tel:"
            class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
          >
            <svg class="h-5 w-5.25 inline-block">
              <use xlink:href="./assets/sprite.svg#phone"></use>
            </svg>
            +7(918)654-32-10
          </a>
          <a
            href="mailto:hello@example.com"
            class="hover:text-cyan-400 text-[16px] font-semibold flex items-center gap-2"
          >
            <svg class="h-5 w-4.5 inline-block">
              <use xlink:href="./assets/sprite.svg#email"></use>
            </svg>
            hello@example.com
          </a>
        </div>
        <button
          class="button button--black max-[1500px]:order-3 max-[1500px]:ml-auto max-[1500px]:mr-5 max-[640px]:mr-3"
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

      <section id="catalog" class="mx-auto max-w-420 mt-20">
        <h2 class="mb-6 text-[52px] font-semibold">Каталог продукции</h2>
        <p class="max-w-147.5 text-[18px] font-medium">
          Нажмите на категорию, чтобы запросить расчет стоимости или скачать спецификацию
        </p>
        <div id="products"></div>
      </section>

      <section id="services" class="mx-auto max-w-6xl px-6 py-16">
        <h2 class="mb-6 text-2xl font-semibold">Услуги</h2>
        <div class="grid gap-4 md:grid-cols-3">
          <article class="rounded-2xl border border-white/10 bg-white/5 p-6">Landing pages</article>
          <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
            Vue development
          </article>
          <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
            UI refactoring
          </article>
        </div>
      </section>
    </main>

    <footer id="contact" class="border-t border-white/10">
      <div
        class="mx-auto flex max-w-6xl flex-col gap-2 px-6 py-8 text-sm text-slate-300 md:flex-row md:items-center md:justify-between"
      >
        <p>© 2026 jbi</p>
        <a href="mailto:hello@example.com" class="hover:text-white">hello@example.com</a>
      </div>
    </footer>
  </div>
</template>
