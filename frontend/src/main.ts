import './styles.css';

import { createPinia } from 'pinia';
import { createApp } from 'vue';

import App from './App.vue';
import { router } from './router';
import { useAuthStore } from './stores/auth.store';

const pinia = createPinia();
const app = createApp(App);

app.use(pinia);
app.use(router);
app.mount('#app');

// After mounting, attempt to rehydrate auth from an existing session.
// This makes hard-refreshes keep the user logged in.
const auth = useAuthStore();
auth.fetchMe();
