<template>
  <section class="wrap">
    <header class="bar">
      <h2>Tareas</h2>
      <button @click="descargar">DESCARGAR FORMULARIO</button>
    </header>

    <form class="card" @submit.prevent="crear">
      <h3>Nueva tarea</h3>
      <input v-model="form.titulo" placeholder="Título" required />
      <textarea v-model="form.descripcion" placeholder="Descripción"></textarea>

      <label>Asignar a:</label>
      <select v-model.number="form.usuario_id" required>
        <option value="" disabled>Seleccione usuario</option>
        <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nombre }}</option>
      </select>

      <label>Fecha vencimiento:</label>
      <input v-model="form.fecha_vencimiento" type="date" />

      <label>Estado:</label>
      <select v-model="form.estado">
        <option value="pendiente">pendiente</option>
        <option value="en_progreso">en_progreso</option>
        <option value="completada">completada</option>
      </select>

      <button>Crear</button>
    </form>

    <table class="list">
      <thead>
        <tr>
          <th>Título</th><th>Usuario</th><th>Estado</th><th>Vence</th><th>Creada</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in tareas" :key="t.id">
          <td>{{ t.titulo }}</td>
          <td>{{ t.usuario?.nombre }}</td>
          <td>{{ t.estado }}</td>
          <td>{{ t.fecha_vencimiento ?? '-' }}</td>
          <td>{{ new Date(t.created_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>

    <p v-if="error" class="error">{{ error }}</p>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const usuarios = ref<any[]>([])
const tareas = ref<any[]>([])
const error = ref('')
const form = ref({ titulo:'', descripcion:'', usuario_id:'', fecha_vencimiento:'', estado:'pendiente' })

const cargarUsuarios = async () => {
  const { data } = await api.get('/usuarios')
  usuarios.value = data.data ?? data
}
const cargarTareas = async () => {
  const { data } = await api.get('/tareas')
  tareas.value = data.data ?? data
}
const crear = async () => {
  try {
    await api.post('/tareas', form.value)
    form.value = { titulo:'', descripcion:'', usuario_id:'', fecha_vencimiento:'', estado:'pendiente' }
    await cargarTareas()
  } catch (e:any) {
    error.value = e?.response?.data?.message || 'Error al crear'
  }
}
const descargar = async () => {
  const res = await api.get('/tareas/export', { responseType: 'blob' })
  const url = URL.createObjectURL(new Blob([res.data]))
  const a = document.createElement('a'); a.href = url; a.download = 'tareas_pendientes.csv'; a.click()
  URL.revokeObjectURL(url)
}

onMounted(async () => { await cargarUsuarios(); await cargarTareas(); })
</script>

<style>
.wrap { max-width: 900px; margin: 24px auto; padding: 12px; }
.card { display: grid; gap: 8px; border: 1px solid #4443; padding: 12px; border-radius: 8px; margin: 12px 0; }
.bar { display:flex; justify-content:space-between; align-items:center; }
.list { width:100%; border-collapse: collapse }
.list th, .list td { border:1px solid #ddd; padding:6px; }
.error { color: red; }
</style>
