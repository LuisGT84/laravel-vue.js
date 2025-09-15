<template>
  <v-container class="py-6" style="max-width:720px">
    <h2 class="text-h6 mb-4">{{ isEdit ? 'Editar usuario' : 'Nuevo usuario' }}</h2>

    <v-form ref="formRef" v-model="valid" @submit.prevent="onSubmit" autocomplete="off">
      <v-text-field
        v-model="form.nombre"
        label="Nombre"
        :rules="[rules.required]"
        density="comfortable"
        clearable
        autocomplete="off"
      />

      <v-text-field
        v-model="form.email"
        label="Email"
        type="email"
        :rules="[rules.required, rules.email]"
        density="comfortable"
        clearable
        autocomplete="off"
        autocapitalize="off"
        spellcheck="false"
      />

      <v-select
        v-model="form.rol"
        :items="roles"
        label="Rol"
        :rules="[rules.required]"
        density="comfortable"
      />

      <v-text-field
        v-model="form.password"
        :label="isEdit ? 'Password ' : 'Password'"
        type="password"
        :rules="isEdit ? [rules.min6Opt] : [rules.required, rules.min6]"
        density="comfortable"
        clearable
        autocomplete="new-password"
      />

      <v-alert v-if="errorMsg" type="error" variant="tonal" class="mb-3" :text="errorMsg" />

      <div class="d-flex gap-2">
        <v-btn type="submit" color="primary" :loading="loading" :disabled="!valid || loading">
          {{ isEdit ? 'Actualizar' : 'Crear' }}
        </v-btn>
        <v-btn variant="tonal" @click="cancel">Cancelar</v-btn>
      </div>
    </v-form>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const formRef = ref<any>()
const valid = ref(false)
const loading = ref(false)
const errorMsg = ref('')

const form = ref({
  nombre: '',
  email: '',
  rol: 'usuario' as 'admin'|'usuario',
  password: ''
})

const roles = ['admin','usuario']
const rules = {
  required: (v:string) => !!v || 'Requerido',
  email:    (v:string) => /.+@.+\..+/.test(v) || 'Email inválido',
  min6:     (v:string) => (v?.length ?? 0) >= 6 || 'Mínimo 6 caracteres',
  min6Opt:  (v:string) => !v || v.length >= 6 || 'Mínimo 6 caracteres',
}

function resetForm(){
  form.value = { nombre:'', email:'', rol:'usuario', password:'' }
  valid.value = false
}

async function prepare() {
  if (!isEdit.value) { resetForm(); return }
  try {
    loading.value = true
    // obtiene un usuario por id
    const { data } = await api.get(`/api/usuarios/getUser/${route.params.id}`)
    const u = data.data ?? data
    form.value.nombre = u.nombre
    form.value.email  = u.email
    form.value.rol    = u.rol
    form.value.password = ''
    await nextTick()
    formRef.value?.validate()
  } catch (e:any) {
    errorMsg.value = e?.response?.data?.message || 'No se pudo cargar el usuario'
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  try {
    errorMsg.value = ''
    loading.value = true

    const payload:any = { nombre: form.value.nombre, email: form.value.email, rol: form.value.rol }
    if (form.value.password) payload.password = form.value.password

    if (isEdit.value) {
      // actualizar
      await api.put(`/api/usuarios/updateUser/${route.params.id}`, payload)
    } else {
      //  crear
      await api.post('/api/usuarios/addUser', payload)
      resetForm()
    }
    router.push('/usuarios')
  } catch (e:any) {
    errorMsg.value =
      e?.response?.data?.message ||
      e?.response?.data?.errors?.email?.[0] ||
      'No se pudo guardar el usuario'
  } finally {
    loading.value = false
  }
}

function cancel(){ router.push('/usuarios') }
onMounted(prepare)
</script>
