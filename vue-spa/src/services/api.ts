import axios from 'axios'

const ORIGIN = import.meta.env.VITE_API_ORIGIN || 'http://127.0.0.1:8000'

const api = axios.create({
  baseURL: ORIGIN,
  headers: { Accept: 'application/json' },
})

// Rutas SIN token (login/register)
const NO_AUTH = ['/api/login', '/api/register', '/login', '/register']

api.interceptors.request.use((config) => {
  const url = config.url?.startsWith('/') ? config.url : `/${config.url}`
  if (!NO_AUTH.includes(url)) {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
  }
  config.url = url
  return config
})

export default api
