<script setup lang="ts">
import { navigateTo } from '@/router';
</script>

<template>
  <div class="not-found">
    <h1>404</h1>
    <h2>Page Not Found</h2>
    <p>The page you are looking for does not exist or has been moved.</p>
    <button @click="navigateTo('/dashboard')">Go to Dashboard</button>
  </div>
</template>

<style scoped>
.not-found {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
  color: var(--text-primary);
}

h1 {
  font-size: 6rem;
  font-weight: 800;
  margin: 0;
  background: linear-gradient(135deg, var(--brand-red) 0%, #7b1fa2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

h2 {
  font-size: 2rem;
  margin: 10px 0 20px;
}

p {
  color: var(--text-secondary);
  margin-bottom: 30px;
  max-width: 400px;
}

button {
  background: var(--brand-red);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 140ms ease;
}

button:hover {
  opacity: 0.9;
}
</style>
