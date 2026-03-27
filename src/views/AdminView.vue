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
  <div v-if="!auth.isAuthenticated" class="admin-login">
    <form class="admin-login__form" @submit.prevent="submitLogin">
      <h1 class="admin-login__title">Вход в панель управления</h1>

      <label class="admin-field">
        <span>Email</span>
        <input
          v-model="loginForm.email"
          type="email"
          required
          autocomplete="username"
          class="admin-input"
        />
      </label>

      <label class="admin-field">
        <span>Пароль</span>
        <input
          v-model="loginForm.password"
          type="password"
          required
          autocomplete="current-password"
          class="admin-input"
        />
      </label>

      <p v-if="auth.error" class="admin-error">{{ auth.error }}</p>

      <button type="submit" class="button button--black" :disabled="auth.loading">
        {{ auth.loading ? 'Вход...' : 'Войти' }}
      </button>
    </form>
  </div>

  <!-- ═══ ПАНЕЛЬ УПРАВЛЕНИЯ ════════════════════════════════════════ -->
  <div v-else class="admin-panel">
    <header class="admin-header">
      <h1 class="admin-header__title">Панель управления — Каталог изделий</h1>
      <button type="button" class="button button--white" @click="auth.logout()">Выйти</button>
    </header>

    <main class="admin-main">
      <!-- кнопка добавления -->
      <div class="admin-toolbar">
        <button type="button" class="button button--black" @click="openAddForm">
          + Добавить изделие
        </button>
      </div>

      <!-- таблица -->
      <div v-if="store.loading" class="admin-status">Загрузка...</div>
      <div v-else-if="store.error" class="admin-error">{{ store.error }}</div>
      <div v-else-if="store.products.length === 0" class="admin-status">
        Изделий пока нет. Добавьте первое!
      </div>
      <table v-else class="admin-table">
        <thead>
          <tr>
            <th>Фото</th>
            <th>Название</th>
            <th>Зоны применения</th>
            <th>Спецификация</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in store.products" :key="p.id">
            <td>
              <img v-if="p.image_url" :src="p.image_url" :alt="p.name" class="admin-table__thumb" />
              <span v-else class="admin-table__no-image">—</span>
            </td>
            <td>{{ p.name }}</td>
            <td>{{ p.zones.join(', ') || '—' }}</td>
            <td>
              <a v-if="p.spec_url" :href="p.spec_url" target="_blank" class="admin-link">{{
                p.spec_name || 'Файл'
              }}</a>
              <span v-else>—</span>
            </td>
            <td class="admin-table__actions">
              <button type="button" class="admin-btn-edit" @click="openEditForm(p)">
                Редактировать
              </button>
              <button type="button" class="admin-btn-delete" @click="confirmDelete(p.id, p.name)">
                Удалить
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ═══ ФОРМА ════════════════════════════════════════════════ -->
      <div v-if="showForm" class="admin-form-overlay" @click.self="cancelForm">
        <form class="admin-form" @submit.prevent="submitForm">
          <h2 class="admin-form__title">
            {{ editingId !== null ? 'Редактировать изделие' : 'Новое изделие' }}
          </h2>

          <label class="admin-field">
            <span>Название *</span>
            <input v-model="form.name" type="text" required class="admin-input" />
          </label>

          <div class="admin-field">
            <span>Фото изделия</span>
            <div class="admin-upload">
              <img
                v-if="form.imagePreview"
                :src="form.imagePreview"
                alt="preview"
                class="admin-upload__preview"
              />
              <label class="admin-upload__label">
                {{ form.imageFile ? form.imageFile.name : 'Выбрать файл (JPG, PNG, WebP)' }}
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="admin-upload__input"
                  @change="onImageChange"
                />
              </label>
            </div>
          </div>

          <label class="admin-field">
            <span>Описание</span>
            <textarea v-model="form.description" rows="3" class="admin-input admin-textarea" />
          </label>

          <div class="admin-field">
            <span>Зоны применения</span>
            <div v-for="(_, idx) in form.zones" :key="idx" class="admin-zone-row">
              <input
                v-model="form.zones[idx]"
                type="text"
                class="admin-input"
                placeholder="Например: Фундаменты"
              />
              <button type="button" class="admin-zone-remove" @click="removeZone(idx)">✕</button>
            </div>
            <button type="button" class="admin-btn-add-zone" @click="addZone">
              + Добавить зону
            </button>
          </div>

          <label class="admin-field">
            <span>Примечание</span>
            <textarea v-model="form.note" rows="2" class="admin-input admin-textarea" />
          </label>

          <div class="admin-field">
            <span>Спецификация (PDF, DOC, DOCX, XLS, XLSX)</span>
            <label class="admin-upload__label">
              {{ form.specFile ? form.specFile.name : form.specName || 'Выбрать файл' }}
              <input
                type="file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                class="admin-upload__input"
                @change="onSpecChange"
              />
            </label>
          </div>

          <p v-if="formError" class="admin-error">{{ formError }}</p>

          <div class="admin-form__footer">
            <button type="button" class="button button--white" @click="cancelForm">Отмена</button>
            <button type="submit" class="button button--black" :disabled="formLoading">
              {{ formLoading ? 'Сохранение...' : 'Сохранить' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
