import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './echo';
import '../css/app.css'

const app = createApp(App)
const pinia = createPinia()

// Install Pinia first, then router
app.use(pinia)
app.use(router)

app.mount('#app')
