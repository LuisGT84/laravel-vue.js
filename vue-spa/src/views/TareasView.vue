<template>
  <v-container fluid>
    <v-row>
      <!-- Lateral -->
      <v-col cols="12" md="3">
        <v-card class="pa-4">
          <div class="text-subtitle-1 mb-2">Tareas</div>

          <v-text-field
            v-model="search"
            label="Buscar"
            prepend-inner-icon="mdi-magnify"
            density="comfortable"
            clearable
            class="mb-4"
          />

          <!-- Exportar CSV (pendientes) -->
          <v-btn block variant="tonal" color="primary" class="mb-2" @click="descargarPendientes">
            DESCARGAR FORMULARIO
          </v-btn>

          <v-divider class="my-4" />

          <div class="text-caption">
            Sesión: <strong>{{ user?.nombre }}</strong> ({{ user?.rol }})
          </div>
        </v-card>
      </v-col>

      <!-- Contenido principal -->
      <v-col cols="12" md="9">
        <v-card class="pa-4 mb-6">
          <div class="text-h6 mb-4">Listado de tareas</div>

          <v-table density="comfortable">
            <thead>
              <tr>
                <th class="text-left">Título</th>
                <th class="text-left">Asignada a</th>
                <th class="text-left">Estado</th>
                <th class="text-left">Vence</th>
                <th class="text-left">Creada</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in filtered" :key="t.id">
                <td>{{ t.titulo }}</td>
                <td>{{ t.usuario?.nombre || '-' }}</td>
                <td>
                  <v-chip :color="estadoColor(t.estado)" size="small" label>
                    {{ t.estado }}
                  </v-chip>
                </td>
                <td><small>{{ formatDateEs(t.fecha_vencimiento) }}</small></td>
                <td><small>{{ formatDateEs(t.created_at) }}</small></td>
              </tr>

              <tr v-if="!filtered.length">
                <td colspan="5" class="text-center text-medium-emphasis py-6">Sin resultados</td>
              </tr>
            </tbody>
          </v-table>

          <v-alert v-if="listError" type="error" variant="tonal" class="mt-3" :text="listError" />
        </v-card>

        <!-- Formulario: sólo admin puede crear -->
        <v-card v-if="isAdmin" class="pa-4">
          <div class="text-h6 mb-4">Crear tarea</div>

          <v-form v-model="valid" @submit.prevent="crearTarea">
            <v-text-field
              v-model="form.titulo"
              label="Título"
              :rules="[rules.required]"
              density="comfortable"
              clearable
            />
            <v-textarea
              v-model="form.descripcion"
              label="Descripción"
              auto-grow
              density="comfortable"
            />

            <v-select
              v-model="form.usuario_id"
              :items="usuariosOptions"
              item-title="nombre"
              item-value="id"
              label="Asignar a"
              :rules="[rules.required]"
              density="comfortable"
              clearable
            />

            <v-select
              v-model="form.estado"
              :items="estados"
              label="Estado"
              :rules="[rules.required]"
              density="comfortable"
            />

            <v-text-field
              v-model="form.fecha_vencimiento"
              label="Fecha de vencimiento"
              type="date"
              density="comfortable"
            />

            <v-alert v-if="formError" type="error" variant="tonal" class="mb-3" :text="formError" />

            <v-btn type="submit" color="primary" :loading="saving" :disabled="!valid || saving">
              Guardar
            </v-btn>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
/**
 * Vista de Tareas
 * - Lista tareas:   GET  /api/tareas
 * - Crear tarea:    POST /api/tareas
 * - Exportar CSV:   GET  /api/tareas/export  (responseType: 'blob')
 */
import { computed, onMounted, ref } from 'vue'
import api from '@/services/api'

type User = { id:number; nombre:string; email:string; rol:'admin'|'usuario' }

const user = ref<User | null>(null)
onMounted(() => {
  const raw = localStorage.getItem('user')
  user.value = raw ? JSON.parse(raw) as User : null
})
const isAdmin = computed(() => user.value?.rol === 'admin')

/* ===== Listado ===== */
const tareas = ref<any[]>([])
const listError = ref('')
const search = ref('')

async function cargarTareas() {
  try {
    listError.value = ''
    // 🔁 RUTA CORRECTA (REST):
    const { data } = await api.get('/api/tareas')
    tareas.value = data.data ?? data
  } catch (e:any) {
    listError.value = e?.response?.data?.message || 'No se pudo cargar tareas'
  }
}

const filtered = computed(() => {
  const q = (search.value || '').toLowerCase()
  if (!q) return tareas.value
  return tareas.value.filter((t:any) =>
    [t.titulo, t.descripcion, t.estado, t.usuario?.nombre, t.fecha_vencimiento, t.created_at]
      .some((v:any) => String(v || '').toLowerCase().includes(q))
  )
})

/* ===== Crear ===== */
const valid = ref(false)
const saving = ref(false)
const formError = ref('')

const form = ref({
  titulo: '',
  descripcion: '',
  usuario_id: null as number | null,
  estado: 'pendiente',
  fecha_vencimiento: '',
})

const estados = ['pendiente', 'en_progreso', 'completada']
const rules = { required: (v:any) => !!v || 'Requerido' }

/* Usuarios para el select */
const usuariosOptions = ref<any[]>([])
async function cargarUsuarios() {
  try {
    const { data } = await api.get('/api/usuarios/listUsers')
    usuariosOptions.value = (data.data ?? data) as any[]
  } catch {
    usuariosOptions.value = []
  }
}

async function crearTarea() {
  try {
    saving.value = true
    formError.value = ''

    const payload = {
      titulo: form.value.titulo,
      descripcion: form.value.descripcion || null,
      usuario_id: form.value.usuario_id,
      estado: form.value.estado,
      fecha_vencimiento: form.value.fecha_vencimiento || null,
    }

    // 🔁 RUTA CORRECTA (REST):
    await api.post('/api/tareas', payload)
    await cargarTareas()
    form.value = { titulo: '', descripcion: '', usuario_id: null, estado: 'pendiente', fecha_vencimiento: '' }
    valid.value = false
  } catch (e:any) {
    formError.value = e?.response?.data?.message || 'No se pudo crear la tarea'
  } finally {
    saving.value = false
  }
}

/* ===== Utilidades ===== */
function estadoColor(estado:string){
  if (estado === 'completada') return 'green'
  if (estado === 'en_progreso') return 'amber'
  return 'red'
}
function formatDateEs(value?: string) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('es-GT', { year:'numeric', month:'long', day:'2-digit' })
}

/* ===== Exportar CSV ===== */
async function descargarPendientes() {
  try {
    // 🔁 RUTA CORRECTA (REST):
    const res = await api.get('/api/tareas/export', { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'tareas_pendientes.csv'
    a.click()
    URL.revokeObjectURL(url)
  } catch (e:any) {
    alert(e?.response?.data?.message || 'No se pudo descargar el reporte')
  }
}

onMounted(async () => {
  await Promise.all([cargarUsuarios(), cargarTareas()])
})
</script>
