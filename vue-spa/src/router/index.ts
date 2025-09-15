import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/usuarios' },
    { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue') },

    { path: '/usuarios', name: 'usuarios', component: () => import('@/views/HomeView.vue'), meta: { requiresAuth: true } },

    { path: '/usuarios/nuevo', name: 'usuarios-nuevo', component: () => import('@/views/UserForm.vue'), meta: { requiresAuth: true } },
    { path: '/usuarios/:id/editar', name: 'usuarios-editar', component: () => import('@/views/UserForm.vue'), props: true, meta: { requiresAuth: true } },

    { path: '/tareas', name: 'tareas', component: () => import('@/views/TareasView.vue'), meta: { requiresAuth: true } },// Vista de lista de tareas

    { path: '/:pathMatch(.*)*', redirect: '/usuarios' },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const user = (() => { try { return JSON.parse(localStorage.getItem('user') || 'null') } catch { return null } })()

   // requiere autenticación
  if (to.meta.requiresAuth && !token) return { name: 'login', query: { redirect: to.fullPath } }
   // no permitir ir a /login si ya hay sesión
  if (to.name === 'login' && token) return { name: 'usuarios' }

  // rutas solo admin (crear/editar usuarios)
  if (to.meta.requiresAdmin && user?.rol !== 'admin') {
    return { name: 'usuarios' }
  }
  
  return true
})

export default router
