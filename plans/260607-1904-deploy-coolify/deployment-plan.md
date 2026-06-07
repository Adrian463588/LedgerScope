# Rencana Implementasi: Deployment LedgerScope ke Coolify di GCP

Rencana ini memandu pembuatan direktori deployment untuk mendeploy seluruh layanan LedgerScope (backend Laravel dan frontend Vue SPA) ke Coolify yang di-host di Google Cloud Platform (GCP) dengan praktik terbaik DevSecOps.

## User Review Required

> [!IMPORTANT]
> Prosedur ini melibatkan pembuatan instance VM Google Compute Engine (GCE), alokasi IP statis, dan pembuatan firewall rule di akun GCP Anda yang dapat menimbulkan biaya. Pastikan gcloud CLI Anda sudah terotentikasi dan mengarah ke project GCP yang benar sebelum menjalankan script.

> [!WARNING]
> Demi keamanan data audit dan akuntansi keuangan, sangat disarankan untuk tidak menggunakan kontainer database PostgreSQL default di dalam Coolify untuk produksi. Kami merekomendasikan penggunaan **GCP Cloud SQL (PostgreSQL)** untuk backup otomatis, enkripsi, dan redundansi.

## Open Questions

> [!IMPORTANT]
> Mohon berikan masukan Anda untuk beberapa pertanyaan berikut sebelum kita memulai penulisan kode:
> 1. **Database & Cache**: Apakah Anda ingin menggunakan GCP Cloud SQL (Managed PostgreSQL) dan GCP Memorystore (Managed Redis) demi standar keamanan produksi yang tinggi, atau cukup menjalankan kontainer PostgreSQL dan Redis yang dikelola secara internal di dalam VM Coolify?
> 2. **Object Storage**: Untuk penyimpanan bukti audit (Evidence Vault), apakah kita akan menggunakan Google Cloud Storage (GCS) dengan interoperabilitas S3 (direkomendasikan) atau tetap menggunakan kontainer MinIO di dalam VM Coolify?
> 3. **Domain & SSL**: Apakah Anda sudah menyiapkan domain (misal: `ledgerscope.com` dan `api.ledgerscope.com`)? Coolify secara otomatis akan mengurus sertifikat SSL Let's Encrypt untuk domain tersebut.
> 4. **GCP Machine Type**: Secara default, script akan membuat VM jenis `e2-standard-2` (2 vCPU, 8 GB RAM) yang ideal untuk Coolify dengan database internal. Apakah Anda ingin menyesuaikan spesifikasi VM ini?

## Proposed Changes

Kami akan membuat direktori baru `deployment/` di root project yang diorganisasikan secara modular untuk kebutuhan GCP dan Coolify. Seluruh nama file baru akan menggunakan format `kebab-case`.

### Deployment Config & Scripts

#### [NEW] [gcp-setup.sh](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/gcp/gcp-setup.sh)
Script shell untuk mengotomatiskan provisioning VM GCE, alokasi IP statis, pembuatan firewall rules (port 80, 443, 22), pembuatan service account dengan akses terbatas (Least Privilege), dan inisialisasi lingkungan GCP.

#### [NEW] [install-coolify.sh](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/gcp/install-coolify.sh)
Script helper untuk menginstal Docker dan Coolify secara aman di VM GCP yang baru dibuat.

#### [NEW] [frontend.dockerfile](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/docker/frontend.dockerfile)
Dockerfile multi-stage untuk frontend Vue 3 SPA:
- Stage 1: Build static assets menggunakan Node.js 20 Alpine.
- Stage 2: Serve menggunakan Nginx Alpine, berjalan dengan user non-root (`nginx`), dan dikonfigurasi dengan header keamanan HTTPS.

#### [NEW] [frontend-nginx.conf](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/docker/frontend-nginx.conf)
Konfigurasi Nginx untuk frontend yang mengaktifkan:
- Routing fallback ke `index.html` (untuk HTML5 History Mode Vue Router).
- Reverse proxy aman untuk `/api` dan `/sanctum` langsung ke kontainer backend Laravel.
- Security Headers (HSTS, CSP, X-Frame-Options, X-Content-Type-Options).

#### [NEW] [production-compose.yml](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/production-compose.yml)
Template file Docker Compose yang aman untuk diimpor ke Coolify sebagai satu Application Stack. Ini mencakup konfigurasi backend, frontend, redis, dan database dengan network isolation (hanya port frontend & backend yang terekspos ke reverse proxy).

#### [NEW] [env-example.env](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/env-example.env)
Template environment variables produksi yang aman dengan instruksi cara memasukkan secret key, kredensial database GCP Cloud SQL, dan kredensial GCS.

---

## Verification Plan

### Automated Tests
- Validasi sintaksis Dockerfile dan Nginx configuration menggunakan linter local (jika terinstal) atau validasi struktur konfigurasi.
- Menjalankan unit test frontend & backend sebelum build deployment.

### Manual Verification
- Pengujian deployment secara lokal menggunakan Docker Compose produksi terisolasi sebelum diunggah ke GCP/Coolify.
- Verifikasi rule firewall GCP dan status koneksi VM.
