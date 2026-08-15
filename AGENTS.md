# LedgerScope Repository Contract

LedgerScope adalah monorepo hybrid:

- Backend Laravel 13 REST API pada `backend/`, kontrak dan aturan domain di `AGENTS_BACKEND.md`.
- Frontend Vue 3 SPA pada `frontend/`, kontrak UI/API di `AGENTS_FRONTEND.md`.
- Inertia hanya digunakan untuk future modules pada `/future/*`; route SPA existing tetap Vue Router.
- Requirement bisnis: `PRD.md`. Design token/accessibility: `DESIGN.md`.
- Runtime utama: root `docker-compose.yml` dengan backend, worker, scheduler, frontend, PostgreSQL, Redis, MinIO, dan Mailpit.

## Aturan repository

- Pertahankan perubahan dirty worktree dan file untracked user. Jangan reset, clean, stash, atau menghapus volume/data tanpa instruksi eksplisit.
- Jalankan command workspace melalui `rtk`.
- Perubahan lintas boundary wajib menjaga API envelope, authorization, decimal precision, private storage, dan fail-closed unavailable state.
- Jangan menambahkan demo/fake success untuk menutupi provider atau modul yang belum tersedia.

## Gate akhir

`docker compose config --quiet`, full Compose build/healthcheck, backend gate, frontend gate, dan Cypress terhadap stack penuh harus lulus sebelum project dinyatakan release-ready.
