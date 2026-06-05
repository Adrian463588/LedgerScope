<template>
  <main class="auth-shell">
    <section class="auth-left">
      <div>
        <h1>LedgerScope</h1>
        <p>Financial Precision.<br /><em>Audit Confidence.</em></p>
      </div>
    </section>
    <section class="auth-right">
      <slot />
    </section>
  </main>
</template>

<style scoped>
.auth-shell {
  display: grid;
  min-height: 100vh;
  grid-template-columns: 42% 58%;
}

.auth-left {
  display: flex;
  align-items: end;
  background: var(--shell-bg);
  color: white;
  padding: 56px;
}

h1 {
  margin: 0 0 28px;
  font-size: 1.5rem;
}

p {
  margin: 0;
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: clamp(2.4rem, 5vw, 4.5rem);
  line-height: 1;
}

em {
  color: var(--brand-red);
}

.auth-right {
  display: grid;
  place-items: center;
  background: var(--page-bg);
  padding: 32px;
}

@media (max-width: 900px) {
  .auth-shell {
    grid-template-columns: 1fr;
  }

  .auth-left {
    min-height: 260px;
  }
}
</style>
