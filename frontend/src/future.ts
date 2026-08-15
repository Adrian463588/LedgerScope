import { createInertiaApp } from "@inertiajs/vue3";
import type { DefineComponent } from "vue";
import { createApp, h } from "vue";

import "./styles.css";

const pages = import.meta.glob<{ default: DefineComponent }>(
  "./pages/FutureInertia/**/*.vue",
);

void createInertiaApp({
  resolve: async (name) => {
    const page = pages[`./pages/FutureInertia/${name}.vue`];

    if (!page) {
      throw new Error(`Unknown future Inertia page: ${name}`);
    }

    return (await page()).default;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});
