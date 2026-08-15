import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: "http://localhost:5173",
    allowCypressEnv: false,
    defaultCommandTimeout: 10000,
    supportFile: false,
    specPattern: "cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
    video: false,
    screenshotOnRunFailure: false,
    setupNodeEvents(_on, _config) {
      // implement node event listeners here
    },
  },
});
