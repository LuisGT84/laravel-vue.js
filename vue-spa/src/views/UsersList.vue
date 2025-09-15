<template>
  <div>
    <v-table density="comfortable">
      <thead>
        <tr>
          <th class="text-left">Nombre</th>
          <th class="text-left">Email</th>
          <th class="text-left">Rol</th>
          <th class="text-left">Creado</th> 
          <th v-if="isAdmin" class="text-left">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="u in filtered" :key="u.id">
          <td>{{ u.nombre }}</td>
          <td>{{ u.email }}</td>
          <td>{{ u.rol }}</td>
          
          <td><small>{{ u.created_at }}</small></td>
          <td v-if="isAdmin">
            <v-btn size="small" variant="text" color="primary" @click="goEdit(u.id)">
              Editar
            </v-btn>
          </td>
        </tr>

        <tr v-if="!filtered.length">
          <td :colspan="isAdmin ? 5 : 4" class="text-center text-medium-emphasis py-6">
            No hay usuarios que coincidan.
          </td>
        </tr>
      </tbody>
    </v-table>

    <v-alert v-if="error" type="error" variant="tonal" class="mt-3" :text="error" />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const props = defineProps<{ searchTerm?: string; isAdmin?: boolean }>()
const router = useRouter()
const usuarios = ref<any[]>([])
const loading = ref(false)
const error = ref('')

async function cargar() {
  try {
    loading.value = true
    // endpoint real de listado
    const { data } = await api.get('/api/usuarios/listUsers')
    usuarios.value = data.data ?? data
  } catch (e:any) {
    error.value = e?.response?.data?.message || 'No se pudo cargar usuarios'
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
watch(() => props.searchTerm, () => {})

const filtered = computed(() => {
  const q = (props.searchTerm || '').toLowerCase()
  if (!q) return usuarios.value
  return usuarios.value.filter((u:any) =>
    [u.nombre, u.email, u.rol, u.created_at].some((v:any) =>
      String(v || '').toLowerCase().includes(q)
    )
  )
})

function goEdit(id:number){ router.push(`/usuarios/${id}/editar`) }

// formatear la fecha
function formatDateEs(value?: string) {
  if (!value) return '-'
  return new Date(value).toLocaleString('es-GT', {
    year: 'numeric', month: 'long', day: '2-digit',
    hour: '2-digit', minute: '2-digit'
  })
}
</script>
