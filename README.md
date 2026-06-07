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
- **Testing**: Pest PHP 3
- **Static Analysis**: PHPStan / Larastan 2 (level 8)
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
│   └── docker-compose.yml  # Docker compose lokal khusus backend
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
├── docker-compose.yml      # Docker compose utama di root (Postgres, Redis, MinIO, Mailpit)
└── PRD.md                  # Product Requirement Document utama
```

---

## 💻 Cara Menjalankan Proyek

Pastikan Anda telah menginstal perangkat lunak berikut sebelum memulai:
- [Docker & Docker Compose](https://www.docker.com/)
- [PHP 8.4+](https://www.php.net/) dan [Composer](https://getcomposer.org/)
- [Node.js (v18+)](https://nodejs.org/) dan npm

---

### Langkah 1: Jalankan Layanan Pendukung (Docker)

Di root folder proyek, jalankan Docker Compose untuk menyalakan database PostgreSQL, Redis cache, MinIO Object Storage, dan Mailpit SMTP server:

```bash
docker compose up -d
```

Layanan yang akan berjalan:
- **PostgreSQL**: Port `5433` (lokal) -> `5432` (container)
- **Redis**: Port `6379`
- **MinIO**: Port `9000` (API) & `9001` (Web Console)
- **Mailpit**: Port `1025` (SMTP) & `8025` (Web Mail UI)

---

### Langkah 2: Setup & Jalankan Backend (Laravel)

1. Masuk ke direktori `backend`:
   ```bash
   cd backend
   ```

2. Salin file environment:
   ```bash
   cp .env.example .env
   ```

3. Sesuaikan konfigurasi database dan layanan di `.env` jika diperlukan. Secara bawaan, pengaturannya telah disesuaikan dengan Docker root:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5433
   DB_DATABASE=ledgerscope
   DB_USERNAME=ledgerscope_user
   DB_PASSWORD=secret

   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379

   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=ledgerscope_minio
   AWS_SECRET_ACCESS_KEY=minio_secret_key
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=ledgerscope-evidence
   AWS_ENDPOINT=http://127.0.0.1:9000
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```

4. Instal dependensi PHP:
   ```bash
   composer install
   ```

5. Buat application key baru:
   ```bash
   php artisan key:generate
   ```

6. Jalankan migrasi database beserta data awal (seeder):
   ```bash
   php artisan migrate --seed
   ```

7. Jalankan server lokal Laravel:
   ```bash
   php artisan serve
   ```
   Server backend akan berjalan di **`http://127.0.0.1:8000`**.

---

### Langkah 3: Setup & Jalankan Frontend (Vue 3)

1. Buka terminal baru dan masuk ke direktori `frontend`:
   ```bash
   cd frontend
   ```

2. Instal dependensi Node:
   ```bash
   npm install
   ```

3. Jalankan server pengembangan Vite:
   ```bash
   npm run dev
   ```
   Aplikasi frontend akan berjalan di **`http://localhost:5173`**. Rute API eksternal secara otomatis diproksi ke `http://127.0.0.1:8000` melalui konfigurasi proxy Vite.

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
# Menjalankan seluruh unit & feature test secara parallel
php artisan test --parallel

# Menjalankan static analysis (Larastan Level 8)
./vendor/bin/phpstan analyse

# Merapikan gaya penulisan kode sesuai PSR-12
./vendor/bin/pint
```

### Frontend (Vitest & Cypress)

```bash
# Menjalankan unit test di frontend
npm run test:unit

# Menjalankan E2E test dengan Cypress
npm run test:e2e

# Memeriksa linting kode TS dan Vue
npm run lint

# Memeriksa format file
npm run format:check
```
