<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useProductsStore, type Product } from '@/stores/products'

const auth = useAuthStore()
const store = useProductsStore()

// ── авторизация ────────────────────────────────────────────────
const loginForm = reactive({ email: '', password: '' })

async function submitLogin() {
  await auth.login(loginForm.email, loginForm.password)
  if (auth.isAuthenticated) {
    await store.fetchProducts()
  }
}

// ── форма продукта ─────────────────────────────────────────────
const emptyForm = () => ({
  name: '',
  description: '',
  note: '',
  zones: [''] as string[],
  imageFile: null as File | null,
  imagePreview: '',
  specFile: null as File | null,
  specName: '',
})

const form = reactive(emptyForm())
const editingId = ref<number | null>(null)
const formError = ref<string | null>(null)
const formLoading = ref(false)
const showForm = ref(false)

function openAddForm() {
  Object.assign(form, emptyForm())
  editingId.value = null
  formError.value = null
  showForm.value = true
}

function openEditForm(product: Product) {
  Object.assign(form, {
    name: product.name,
    description: product.description,
    note: product.note,
    zones: product.zones.length ? [...product.zones] : [''],
    imageFile: null,
    imagePreview: product.image_url,
    specFile: null,
    specName: product.spec_name || '',
  })
  editingId.value = product.id
  formError.value = null
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
}

function onImageChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  form.imageFile = file
  form.imagePreview = URL.createObjectURL(file)
}

function onSpecChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  form.specFile = file
  form.specName = file.name
}

function addZone() {
  form.zones.push('')
}

function removeZone(idx: number) {
  form.zones.splice(idx, 1)
  if (form.zones.length === 0) form.zones.push('')
}

async function submitForm() {
  formLoading.value = true
  formError.value = null
  try {
    const fd = new FormData()
    fd.append('name', form.name)
    fd.append('description', form.description)
    fd.append('note', form.note)
    form.zones.filter((z) => z.trim()).forEach((z) => fd.append('zones[]', z.trim()))
    if (form.imageFile) fd.append('image', form.imageFile)
    if (form.specFile) fd.append('spec', form.specFile)

    if (editingId.value !== null) {
      await store.updateProduct(editingId.value, fd)
    } else {
      await store.addProduct(fd)
    }
    showForm.value = false
  } catch (e: unknown) {
    formError.value = e instanceof Error ? e.message : 'Ошибка сохранения'
  } finally {
    formLoading.value = false
  }
}

async function confirmDelete(id: number, name: string) {
  if (!confirm(`Удалить изделие «${name}»?`)) return
  try {
    await store.deleteProduct(id)
  } catch (e: unknown) {
    alert(e instanceof Error ? e.message : 'Ошибка удаления')
  }
}

// ── инициализация ──────────────────────────────────────────────
onMounted(async () => {
  await auth.checkAuth()
  if (auth.isAuthenticated) await store.fetchProducts()
})
</script>

<template>
  <!-- ═══ ЛОГИН ═══════════════════════════════════════════════════ -->
  <div
    v-if="!auth.isAuthenticated"
    class="min-h-screen flex items-center justify-center bg-[#efefef] p-5"
  >
    <form
      class="bg-white rounded-[32px] p-10 w-full max-w-[420px] flex flex-col gap-5"
      @submit.prevent="submitLogin"
    >
      <h1 class="text-[22px] font-bold">Вход в панель управления</h1>

      <label class="flex flex-col gap-1.5 text-sm font-semibold">
        <span>Email</span>
        <input
          v-model="loginForm.email"
          type="email"
          required
          autocomplete="username"
          class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222]"
        />
      </label>

      <label class="flex flex-col gap-1.5 text-sm font-semibold">
        <span>Пароль</span>
        <input
          v-model="loginForm.password"
          type="password"
          required
          autocomplete="current-password"
          class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222]"
        />
      </label>

      <p v-if="auth.error" class="text-[#c0392b] text-sm">{{ auth.error }}</p>

      <button type="submit" class="button button--black" :disabled="auth.loading">
        {{ auth.loading ? 'Вход...' : 'Войти' }}
      </button>
    </form>
  </div>

  <!-- ═══ ПАНЕЛЬ УПРАВЛЕНИЯ ════════════════════════════════════════ -->
  <div v-else class="min-h-screen bg-[#efefef]">
    <header class="bg-white px-8 py-4 flex items-center justify-between gap-4">
      <h1 class="text-xl font-bold">Панель управления — Каталог изделий</h1>
      <button type="button" class="button button--white" @click="auth.logout()">Выйти</button>
    </header>

    <main class="max-w-[1200px] mx-auto px-5 py-8">
      <!-- кнопка добавления -->
      <div class="mb-5">
        <button type="button" class="button button--black" @click="openAddForm">
          + Добавить изделие
        </button>
      </div>

      <!-- таблица -->
      <div v-if="store.loading" class="py-8 text-[#888] text-base">Загрузка...</div>
      <div v-else-if="store.error" class="text-[#c0392b] text-sm">{{ store.error }}</div>
      <div v-else-if="store.products.length === 0" class="py-8 text-[#888] text-base">
        Изделий пока нет. Добавьте первое!
      </div>
      <table
        v-else
        class="w-full border-collapse bg-white rounded-[20px] overflow-hidden shadow-[0_2px_20px_rgba(0,0,0,0.05)]"
      >
        <thead>
          <tr>
            <th
              class="px-4 py-3.5 text-left border-b border-[#f0f0f0] bg-[#f8f8f8] font-bold text-[13px] uppercase tracking-[0.04em] text-[#888]"
            >
              Фото
            </th>
            <th
              class="px-4 py-3.5 text-left border-b border-[#f0f0f0] bg-[#f8f8f8] font-bold text-[13px] uppercase tracking-[0.04em] text-[#888]"
            >
              Название
            </th>
            <th
              class="px-4 py-3.5 text-left border-b border-[#f0f0f0] bg-[#f8f8f8] font-bold text-[13px] uppercase tracking-[0.04em] text-[#888]"
            >
              Зоны применения
            </th>
            <th
              class="px-4 py-3.5 text-left border-b border-[#f0f0f0] bg-[#f8f8f8] font-bold text-[13px] uppercase tracking-[0.04em] text-[#888]"
            >
              Спецификация
            </th>
            <th
              class="px-4 py-3.5 text-left border-b border-[#f0f0f0] bg-[#f8f8f8] font-bold text-[13px] uppercase tracking-[0.04em] text-[#888]"
            ></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in store.products" :key="p.id">
            <td class="px-4 py-3.5 text-left border-b border-[#f0f0f0] text-[15px]">
              <img
                v-if="p.image_url"
                :src="p.image_url"
                :alt="p.name"
                class="w-[60px] h-12 object-cover rounded-lg"
              />
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3.5 text-left border-b border-[#f0f0f0] text-[15px]">
              {{ p.name }}
            </td>
            <td class="px-4 py-3.5 text-left border-b border-[#f0f0f0] text-[15px]">
              {{ p.zones.join(', ') || '—' }}
            </td>
            <td class="px-4 py-3.5 text-left border-b border-[#f0f0f0] text-[15px]">
              <a
                v-if="p.spec_url"
                :href="p.spec_url"
                target="_blank"
                class="text-blue-600 underline text-sm"
                >{{ p.spec_name || 'Файл' }}</a
              >
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3.5 text-left border-b border-[#f0f0f0] text-[15px] flex gap-2">
              <button
                type="button"
                class="px-3.5 py-1.5 rounded-lg text-[13px] font-semibold cursor-pointer border-0 bg-[#222] text-white"
                @click="openEditForm(p)"
              >
                Редактировать
              </button>
              <button
                type="button"
                class="px-3.5 py-1.5 rounded-lg text-[13px] font-semibold cursor-pointer border-0 bg-[#fee2e2] text-[#c0392b]"
                @click="confirmDelete(p.id, p.name)"
              >
                Удалить
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ═══ ФОРМА ════════════════════════════════════════════════ -->
      <div
        v-if="showForm"
        class="fixed inset-0 bg-black/[0.45] z-[200] flex items-center justify-center p-5"
        @click.self="cancelForm"
      >
        <form
          class="bg-white rounded-[28px] p-8 w-full max-w-[560px] max-h-[90vh] overflow-y-auto flex flex-col gap-[18px]"
          @submit.prevent="submitForm"
        >
          <h2 class="text-xl font-bold">
            {{ editingId !== null ? 'Редактировать изделие' : 'Новое изделие' }}
          </h2>

          <label class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Название *</span>
            <input
              v-model="form.name"
              type="text"
              required
              class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222]"
            />
          </label>

          <div class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Фото изделия</span>
            <div class="flex flex-col gap-2.5">
              <img
                v-if="form.imagePreview"
                :src="form.imagePreview"
                alt="preview"
                class="max-h-[140px] rounded-xl object-cover w-full"
              />
              <label
                class="px-3.5 py-2.5 border border-dashed border-[#999] rounded-xl cursor-pointer text-sm text-[#555] font-normal"
              >
                {{ form.imageFile ? form.imageFile.name : 'Выбрать файл (JPG, PNG, WebP)' }}
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="hidden"
                  @change="onImageChange"
                />
              </label>
            </div>
          </div>

          <label class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Описание</span>
            <textarea
              v-model="form.description"
              rows="3"
              class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222] resize-y min-h-[72px]"
            />
          </label>

          <div class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Зоны применения</span>
            <div v-for="(_, idx) in form.zones" :key="idx" class="flex gap-2 mb-2">
              <input
                v-model="form.zones[idx]"
                type="text"
                class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222]"
                placeholder="Например: Фундаменты"
              />
              <button
                type="button"
                class="shrink-0 w-9 h-9 rounded-lg border border-[#e0e0e0] bg-white cursor-pointer text-sm mt-1"
                @click="removeZone(idx)"
              >
                ✕
              </button>
            </div>
            <button
              type="button"
              class="bg-transparent border border-dashed border-[#999] rounded-[10px] py-[7px] px-3.5 cursor-pointer text-[13px] font-semibold text-[#555]"
              @click="addZone"
            >
              + Добавить зону
            </button>
          </div>

          <label class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Примечание</span>
            <textarea
              v-model="form.note"
              rows="2"
              class="px-3.5 py-2.5 border border-[#d9d9d9] rounded-xl text-[15px] outline-none w-full transition-colors duration-150 [font-family:inherit] focus:border-[#222] resize-y min-h-[72px]"
            />
          </label>

          <div class="flex flex-col gap-1.5 text-sm font-semibold">
            <span>Спецификация (PDF, DOC, DOCX, XLS, XLSX)</span>
            <label
              class="px-3.5 py-2.5 border border-dashed border-[#999] rounded-xl cursor-pointer text-sm text-[#555] font-normal"
            >
              {{ form.specFile ? form.specFile.name : form.specName || 'Выбрать файл' }}
              <input
                type="file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                class="hidden"
                @change="onSpecChange"
              />
            </label>
          </div>

          <p v-if="formError" class="text-[#c0392b] text-sm">{{ formError }}</p>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              class="button button--white !min-w-0 !h-12 !px-5 !text-[15px]"
              @click="cancelForm"
            >
              Отмена
            </button>
            <button
              type="submit"
              class="button button--black !min-w-0 !h-12 !px-5 !text-[15px]"
              :disabled="formLoading"
            >
              {{ formLoading ? 'Сохранение...' : 'Сохранить' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
