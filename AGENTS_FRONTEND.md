# LedgerScope Frontend Engineering Contract

Dokumen ini adalah aturan operasional frontend LedgerScope. Requirement bisnis tetap bersumber dari `PRD.md`, visual dari `DESIGN.md`, dan aturan backend dari `AGENTS_BACKEND.md`.

## Arsitektur runtime

- `frontend/src` adalah Vue Router SPA utama.
- SPA memakai Laravel REST API pada `/api/v1` melalui Axios dan Sanctum cookie.
- `frontend/future.html` dan `frontend/src/future.ts` hanya untuk entrypoint Inertia future pada `/future/*`; entrypoint ini tidak boleh mengubah route SPA existing.
- Docker Compose adalah runtime utama: Vite dev service pada port 5173 dan Nginx production image pada port 8080.
- API call dari page/component dilarang. Gunakan Pinia store action atau composable domain yang typed.

## Baseline dependency yang diuji

| Komponen | Baseline |
| --- | --- |
| Node.js | 22 LTS |
| Vue / Vue Router | Vue 3.5+, Router 4 |
| TypeScript | 6.x strict, `noUncheckedIndexedAccess` |
| Vite / Tailwind | Vite 8, Tailwind CSS 4 |
| Pinia / Axios | Pinia 3, Axios 1 |
| Inertia Vue adapter | 2.x |
| VeeValidate / Zod | 4.x / 3.x |
| Vitest / Cypress | Vitest 4, Cypress 15 |
| ESLint / Prettier | ESLint 10 flat config, Prettier 3 |

Versi tersebut dipertahankan karena sudah terkunci di `package-lock.json` dan lulus gate. Jangan melakukan downgrade major tanpa alasan kompatibilitas yang terukur.

## Aturan wajib

1. Gunakan `<script setup lang="ts">`; Options API dan `any` dilarang.
2. Boundary API wajib memakai type domain, payload, `ApiResponse<T>`, pagination, dan error envelope. Jangan memakai `Partial<Model>` atau cast yang menyembunyikan contract.
3. Semua form memakai schema Zod. Validasi manual `if/else` tidak boleh menjadi sumber validasi domain.
4. Semua amount, total, balance, ratio, threshold, dan chart input finansial tetap decimal string. Jangan memakai `parseFloat`, `Number`, atau toleransi floating-point untuk uang.
5. Komponen tidak boleh memutasi state Pinia langsung. Perubahan state harus melalui action.
6. Tidak boleh ada demo credential, KPI/chart fake, generated fallback period, fake upload, atau fake success. Jika API belum tersedia, tampilkan `loading`, `empty`, `error`, `permission-denied`, atau `feature_unavailable` secara eksplisit.
7. Company/engagement/period context wajib valid. Jangan memakai fallback ID seperti `companyId ?? 1`.
8. Record posted, locked, approved, atau signed harus terlihat immutable dan tombol mutasinya dinonaktifkan sesuai permission/state API.
9. Semua tabel finansial memakai semantic table, `tabular-nums`, dan alignment angka ke kanan.
10. Warna, spacing, radius, focus state, dan status harus memakai token `DESIGN.md`/`src/styles.css`; gradient dan warna ad-hoc dilarang kecuali skeleton yang memang ditentukan DESIGN.
11. Interaksi keyboard, focus-visible, label, `aria-*`, modal focus trap, skip link, dan responsive layout wajib dipertahankan.

## Struktur kode

```text
frontend/src/
├── api/          # Axios client dan endpoint functions typed
├── components/   # ui, shared, accounting, audit, evidence, charts
├── composables/  # reusable behavior, API orchestration, formatting
├── layouts/      # App, Auth, Client Portal
├── pages/        # Vue Router SPA pages
├── schemas/      # Zod schemas untuk semua form
├── stores/       # Pinia store per domain
├── types/        # API dan domain types
├── utils/        # decimal dan pure utilities
└── styles.css    # DESIGN tokens dan shared CSS
```

Store/composable bertanggung jawab atas request lifecycle, error normalization, dan state transition. Page hanya mengorkestrasi view state dan event UI.

## Auth dan navigation

- `auth.store` adalah single-flight bootstrap untuk `fetchMe()`.
- Router guard membedakan unauthenticated dari network/server error dan tidak menimpa error API dengan redirect palsu.
- Login harus menguji hasil MFA/non-MFA sebagai discriminated union.
- SPA tetap memakai Vue Router. Laravel/Inertia middleware hanya berlaku untuk `/future/*`.

## Quality gate

Jalankan dari `frontend`:

```bash
npm ci
npm run lint
npm run typecheck
npm run format:check
npm run build
npm run test:unit -- --run
npm run test:e2e
```

Cypress binary dipasang oleh host/CI E2E runner; image Docker frontend sengaja memakai `CYPRESS_INSTALL_BINARY=0`.
