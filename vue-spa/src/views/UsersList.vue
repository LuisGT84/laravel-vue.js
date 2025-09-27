<template>
  <v-table density="comfortable">
    <thead>
      <tr>
        <th class="text-left">Nombre</th>
        <th class="text-left">Email</th>
        <th class="text-left">Rol</th>
        <th class="text-left">Creado</th>
        <th class="text-left">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="u in filtered" :key="u.id">
        <td>{{ u.nombre }}</td>
        <td>{{ u.email }}</td>
        <td>{{ u.rol }}</td>
        <td><small>{{ formatDateEs(u.created_at) }}</small></td>
        <td class="d-flex ga-2">
          <v-btn size="x-small" color="primary" variant="tonal" @click="$router.push(`/usuarios/${u.id}/editar`)">
            Editar
          </v-btn>
          <v-btn
            v-if="isAdmin"
            size="x-small"
            color="error"
            variant="tonal"
            @click="onDelete(u)"
          >
            Eliminar
          </v-btn>
        </td>
      </tr>

      <tr v-if="!filtered.length">
        <td colspan="5" class="text-center text-medium-emphasis py-6">No hay usuarios que coincidan.</td>
      </tr>
    </tbody>
  </v-table>

  <v-alert v-if="errorMsg" type="error" variant="tonal" class="mt-3" :text="errorMsg" />
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import api from '@/services/api'

type Usuario = { id:number; nombre:string; email:string; rol:'admin'|'usuario'; created_at:string }

const props = defineProps<{ searchTerm?: string }>()
const usuarios = ref<Usuario[]>([])
const errorMsg = ref('')

const user = ref<{ rol:'admin'|'usuario' } | null>(null)
onMounted(() => {
  const raw = localStorage.getItem('user')
  user.value = raw ? JSON.parse(raw) : null
})

const isAdmin = computed(() => user.value?.rol === 'admin')

async function cargar() {
  try {
    errorMsg.value = ''
    const { data } = await api.get('/api/usuarios/listUsers')
    usuarios.value = (data.data ?? data) as Usuario[]
  } catch (e:any) {
    errorMsg.value = e?.response?.data?.message || 'No se pudo cargar usuarios'
  }
}
onMounted(cargar)

const filtered = computed(() => {
  const q = (props.searchTerm || '').toLowerCase()
  if (!q) return usuarios.value
  return usuarios.value.filter(u =>
    [u.nombre, u.email, u.rol, u.created_at].some(v => String(v ?? '').toLowerCase().includes(q))
  )
})

function formatDateEs(value?: string) {
  if (!value) return '-'
  return new Date(value).toLocaleString('es-GT')
}

async function onDelete(u: Usuario) {
  if (!isAdmin.value) return
  const ok = confirm(`¿Eliminar al usuario "${u.nombre}"? Esta acción no se puede deshacer.`)
  if (!ok) return

  try {
    await api.delete(`/api/usuarios/deleteUser/${u.id}`)
    await cargar()
  } catch (e:any) {
    alert(e?.response?.data?.message || 'No se pudo eliminar el usuario')
  }
}
</script>
