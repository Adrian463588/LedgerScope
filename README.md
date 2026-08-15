# LedgerScope

> **End-to-End Accounting, Financial Analysis & Audit Management Platform**
>
> Sebuah platform web modern yang dirancang untuk mengelola pembukuan perusahaan (bookkeeping), penutupan kuartalan (quarterly closing), penyusunan laporan keuangan, manajemen bukti audit, penilaian risiko (risk assessment), working papers, review notes, hingga pelacakan temuan audit (audit findings) dalam satu workspace terintegrasi.

---

## 🚀 Overview Proyek

LedgerScope dirancang untuk menjembatani kebutuhan antara tim akuntan internal, analis keuangan, auditor (internal & eksternal), manajer audit, hingga klien dalam satu ekosistem digital yang aman dan transparan. Platform ini memisahkan peran dengan ketat menggunakan **Role-Based Access Control (RBAC)** dan menyediakan laporan keuangan dinamis yang sesuai dengan standar akuntansi modern.

### Fitur Utama

- 🔒 **Autentikasi & RBAC Premium**: Menggunakan Laravel Sanctum dengan Multi-Factor Authentication (MFA) yang mendukung berbagai peran seperti Super Admin, Firm Admin, Partner, Auditor, Akuntan, Analis Keuangan, dan Klien.
- 🏢 **Multi-Company & Engagement**: Manajemen banyak entitas klien dan proyek audit yang fleksibel sesuai periode pelaporan.
- 📅 **Quarterly Bookkeeping**: Alur pembukuan bulanan & kuartalan lengkap dengan penutupan periode (lock/unlock) dan daftar periksa penutupan (closing checklist).
- 📊 **Trial Balance & Financial Statements**: Agregasi otomatis dari jurnal terposting dengan sistem double-entry yang presisi (menggunakan Value Object uang berbasis matematika bcmath). Pembuat Laporan Laba Rugi, Neraca, Arus Kas, dan perubahan ekuitas.
- 📈 **Financial Analysis Dashboard**: Visualisasi tren pendapatan/beban, metrik rasio keuangan penting (GPM, NPM, ROA, ROE, likuiditas, solvabilitas), serta analisis varians anggaran.
- 🕵️‍♂️ **Audit Fieldwork & Working Papers**: Penilaian risiko (Risk Assessment), Risk Control Matrix (RCM), program audit, kertas kerja (working papers) dengan tanda tangan elektronik (sign-off), review notes, dan pelacakan temuan audit (audit findings).
- 📁 **Evidence Vault & Client Portal**: Portal khusus klien untuk mengunggah dokumen bukti audit yang diminta dan berkomentar secara real-time.
- 🔄 **Audit Trail & Logs**: Pencatatan aktivitas sensitif secara append-only yang aman untuk kebutuhan kepatuhan hukum dan audit sistem informasi.
- 📨 **Antrean Pekerjaan (Queue)**: Menggunakan Laravel Horizon untuk memproses ekspor laporan PDF/Excel, notifikasi email, dan impor data berat di latar belakang.

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 13
- **Language**: PHP 8.4
- **Database**: PostgreSQL 17
- **Caching & Queue**: Redis 7
- **Testing**: Pest PHP 4
- **Static Analysis**: PHPStan / Larastan 3
- **Code Style**: Laravel Pint (PSR-12)

### Frontend
- **Framework**: Vue 3 SPA (Single Page Application)
- **Build Tool**: Vite 8
- **Styling**: TailwindCSS v4
- **State Management**: Pinia 3
- **Routing**: Vue Router 4
- **Testing**: Vitest & Cypress (E2E)

---

## 📂 Struktur Proyek

LedgerScope diorganisasikan dalam bentuk monorepo dengan pemisahan yang jelas antara backend dan frontend:

```
AuditorAccountant/
├── .agent/                 # Konfigurasi agen AI AntigravityKit
├── backend/                # Aplikasi Backend Laravel 13
│   ├── app/
│   │   ├── Actions/        # Class aksi tunggal (single-purpose action)
│   │   ├── Enums/          # Enum akuntansi, audit, dan status sistem
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/  # Controller API tipis
│   │   │   └── Requests/   # Form Request untuk validasi data masukan
│   │   ├── Models/         # Model Eloquent tanpa logika bisnis berat
│   │   └── Services/       # Logika bisnis inti aplikasi
│   │       ├── Accounting/ # Journal, TrialBalance, StatementBuilder
│   │       ├── Audit/      # Engagement, WorkingPaper, Finding
│   │       └── Reporting/  # Pembuat Laporan (PDF & Excel)
│   ├── config/             # Pengaturan konfigurasi Laravel
│   ├── database/           # Migrasi tabel dan database seeders
│   ├── routes/             # Definisi rute API & Web
│   ├── tests/              # Test suite menggunakan Pest PHP
│   ├── Dockerfile          # Docker setup untuk backend production
│   └── Dockerfile           # Development dan production image backend
│
├── frontend/               # Aplikasi Frontend Vue 3 SPA
│   ├── src/
│   │   ├── api/            # Pengaturan Client API & Interceptor Axios
│   │   ├── components/     # Komponen Vue reusable
│   │   ├── layouts/        # Layout aplikasi (App, Auth, Client Portal)
│   │   ├── pages/          # Halaman-halaman Vue berdasarkan modul
│   │   ├── router.ts       # Konfigurasi Vue Router (rute & guard layout)
│   │   ├── stores/         # Pinia stores untuk state management (Auth, dll.)
│   │   └── styles.css      # Custom styling & konfigurasi TailwindCSS v4
│   ├── cypress/            # Pengujian E2E menggunakan Cypress
│   ├── vite.config.ts      # Konfigurasi build Vite & Proxy API
│   └── tsconfig.json       # Konfigurasi TypeScript
│
├── docker-compose.yml      # Full stack: backend, worker, scheduler, frontend, DB, Redis, MinIO, Mailpit
└── PRD.md                  # Product Requirement Document utama
```

---

## 💻 Cara Menjalankan Proyek

Pastikan Anda telah menginstal perangkat lunak berikut sebelum memulai:
- [Docker & Docker Compose](https://www.docker.com/)
- [PHP 8.4+](https://www.php.net/) dan [Composer](https://getcomposer.org/)
- [Node.js 22 LTS](https://nodejs.org/) dan npm

---

### Langkah 1: Jalankan Full Stack (Docker)

Full Docker Compose adalah runtime utama LedgerScope. Dari root repository, jalankan:

```bash
docker compose up -d --build
```

Compose menunggu dependency healthcheck sebelum menjalankan backend, worker, scheduler, dan frontend:

- Frontend SPA: `http://localhost:5173`
- Backend API/health: `http://localhost:8000/api/health`
- PostgreSQL: `localhost:5433`
- Redis: `localhost:6379`
- MinIO API/Console: `localhost:9000` / `localhost:9001`
- Mailpit SMTP/UI: `localhost:1025` / `localhost:8025`

---

### Langkah 2: Migrasi dan Seed Database

Jika database masih kosong, jalankan migration dan seed melalui container backend:

```bash
docker compose exec backend php artisan migrate:fresh --seed --force
```

Untuk menjalankan database testing yang terisolasi:

```bash
docker compose --profile test run --rm backend-test php artisan migrate:fresh --seed --force
```

---

### Langkah 3: Akses Frontend SPA

Buka **`http://localhost:5173/login`**. Proxy Vite mengarahkan request `/api` ke service backend melalui jaringan Compose. Nilai `APP_KEY`, Sanctum, cookie, database, Redis, MinIO, dan Mailpit disediakan oleh environment Compose; gunakan `.env.example` hanya sebagai template lokal.

Untuk iterasi frontend tanpa Compose, jalankan `npm ci` lalu `npm run dev` dari direktori `frontend` dan pastikan backend API berjalan di port `8000`.

---

## 🔑 Kredensial Login Pengujian

Setelah database berhasil dimigrasi dengan `--seed`, Anda dapat menggunakan akun demo berikut untuk masuk ke sistem:

| Peran (Role) | Email | Password | Keterangan |
|---|---|---|---|
| **Super Admin** | `superadmin@ledgerscope.test` | `Admin@LedgerScope2026!` | Hak akses penuh di seluruh sistem. |
| **Firm Admin (Demo)** | `rina@ledgerscope.test` | `password` | Login sebagai pengguna Rina Sari, admin di perusahaan demo `PT Tech Nusantara`. |

---

## 🧪 Pengujian & Standar Kode

### Backend (Pest PHP & Analisis Statis)

Untuk menjaga kualitas kode tetap prima, jalankan pengujian dan penataan kode berikut secara berkala:

```bash
# Validasi dependency dan code style
composer validate --strict
./vendor/bin/pint --test

# Static analysis
./vendor/bin/phpstan analyse

# Menjalankan seluruh unit & feature test pada database test terisolasi
php artisan test
```

### Frontend (Vitest & Cypress)

```bash
# Menjalankan unit test di frontend
npm run test:unit

# Menjalankan E2E test dengan Cypress
npm run test:e2e

# Memeriksa linting kode TS dan Vue
npm run lint

# Type-check dan production build
npm run typecheck
npm run build

# Memeriksa format file
npm run format:check
```
