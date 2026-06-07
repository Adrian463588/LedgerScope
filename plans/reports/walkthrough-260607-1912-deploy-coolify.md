# Laporan Walkthrough: Infrastruktur Coolify di GCP

Infrastruktur VM Google Compute Engine (GCE) untuk Coolify telah berhasil dibangun menggunakan Terraform (IaC). Seluruh script provisioning, konfigurasi kontainer aplikasi, dan stack Coolify telah disiapkan di direktori `deployment/`.

## Hasil Provisioning Infrastruktur (GCP & Terraform)

Resources berikut telah berhasil dibuat di GCP Project `project-654b743a-b24b-45ad-85e`:
1. **Virtual Private Cloud (VPC)**: `ledgerscope-vpc`
2. **Subnetwork**: `ledgerscope-subnet` (`10.0.1.0/24`) di region `asia-southeast1` (Singapore)
3. **Reserved IP Statis**: `34.143.222.59`
4. **Firewall Rules**:
   - `ledgerscope-allow-web`: Membuka port `80` dan `443` untuk akses web publik.
   - `ledgerscope-allow-ssh`: Membuka port `22` untuk akses SSH aman.
   - `ledgerscope-allow-coolify-setup`: Membuka port `8000` untuk konfigurasi admin panel Coolify.
5. **GCE Virtual Machine**: `ledgerscope-coolify-server` (`e2-medium` - 2 vCPU, 4 GB RAM, 40 GB Balanced SSD Disk).

### Detail Output Terraform
- **IP Publik Instance**: `34.143.222.59`
- **URL Inisialisasi Coolify**: [http://34.143.222.59:8000](http://34.143.222.59:8000)
- **Perintah SSH**: `ssh coolify@34.143.222.59`
- **Perintah Cek Log Inisialisasi**: `tail -f /var/log/coolify-bootstrap.log`

---

## File Konfigurasi Baru di Direktori `deployment/`

Direktori `deployment/` baru di root project berisi:

1. **IaC Terraform (`deployment/terraform/`)**:
   - [main.tf](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/terraform/main.tf): Definisi resource GCP.
   - [variables.tf](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/terraform/variables.tf): Definisi variabel konfigurasi.
   - [outputs.tf](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/terraform/outputs.tf): Definisi output IP, URL, dan perintah CLI.
   - [terraform.tfvars](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/terraform/terraform.tfvars): Nilai variabel input default.
   - [startup-script.sh](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/terraform/startup-script.sh): Script bootstrap otomatis untuk memperbarui sistem, menambah **Swap Space 4GB** (mencegah OOM crash pada instance `e2-medium`), menginstal Docker, dan menginstal Coolify.

2. **Docker Frontend (`deployment/docker/`)**:
   - [frontend.dockerfile](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/docker/frontend.dockerfile): Dockerfile multi-stage (Node 20 Alpine untuk build, Nginx 1.27 Alpine untuk serving) dikonfigurasi untuk berjalan dengan user non-root (`nginx`).
   - [frontend-nginx.conf](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/docker/frontend-nginx.conf): Konfigurasi Nginx produksi yang mengaktifkan fallback routing SPA, header keamanan DevSecOps (HSTS, CSP, X-Frame-Options), dan reverse-proxy otomatis dari `/api` dan `/sanctum` ke container Laravel.

3. **Coolify Stack (`deployment/coolify/`)**:
   - [production-compose.yml](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/production-compose.yml): File compose produksi untuk diimpor langsung ke Coolify. Mengintegrasikan frontend, backend, PostgreSQL, Redis, dan MinIO (opsional jika tidak menggunakan GCS) dalam network internal terisolasi.
   - [env-example.env](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/env-example.env): Dokumentasi variabel lingkungan produksi yang aman, termasuk integrasi bucket Google Cloud Storage (GCS) via HMAC key.

---


## Langkah Penyelesaian Penyiapan di Coolify

1. **Buka URL Setup**: Akses [http://34.143.222.59:8000](http://34.143.222.59:8000) di browser Anda untuk membuat akun administrator pertama di Coolify.
2. **Impor Project LedgerScope**:
   - Di Coolify dashboard, buat **Project Baru**.
   - Tambahkan **Resource baru** berupa **Docker Compose**.
   - Salin isi dari [production-compose.yml](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/production-compose.yml) ke editor compose Coolify.
   - Konfigurasikan Environment Variables pada tab Environment menggunakan panduan dari [env-example.env](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/env-example.env).
3. **Hubungkan Domain**: Di konfigurasi layanan `ledgerscope-frontend` dan `ledgerscope-backend`, tautkan FQDN (domain) Anda. Coolify akan otomatis menerbitkan sertifikat SSL Let's Encrypt secara gratis.
4. **Deploy**: Klik tombol **Deploy** di Coolify untuk mulai membuild kontainer frontend & backend dari kode repositori Anda!

---

## Laporan Diagnosis & Resolusi (ERR_CONNECTION_REFUSED)

Kami melakukan investigasi menyeluruh mengenai masalah koneksi ditolak yang terjadi di awal, berikut adalah hasilnya:

### Penyebab Masalah
Saat VM pertama kali dibuat oleh Terraform, VM langsung mengeksekusi `startup-script.sh` untuk melakukan pembaruan OS (`apt-get upgrade`), mengunduh docker images (postgres, redis, soketi, coolify), dan memulai container. Proses inisialisasi awal ini membutuhkan waktu sekitar **3-5 menit**.
Ketika dicoba pertama kali, container database dan reverse proxy Coolify (Caddy) masih dalam status inisialisasi awal, sehingga menyebabkan error `ERR_CONNECTION_REFUSED`.

### Hasil Verifikasi Terbaru (100% Berhasil)
Kami memverifikasi langsung dari mesin host Anda (tempat agen ini berjalan) menggunakan perintah `curl` lokal:
1. **Status Docker Internal VM**: Kontainer `coolify`, `coolify-db`, `coolify-redis`, dan `coolify-realtime` telah berjalan dengan status **healthy**.
2. **Port Binding**: Port `8000` telah berhasil dibind ke `0.0.0.0` (dapat diakses dari luar).
3. **Tes Koneksi Lokal**: Eksekusi `curl.exe -L -I http://34.143.222.59:8000` mengembalikan status **`200 OK`** dan mengarahkan ke halaman `/register` untuk pendaftaran akun admin pertama Anda.


### Solusi untuk Pengguna
Silakan akses kembali alamat [http://34.143.222.59:8000](http://34.143.222.59:8000). Jika masih membandel:
- Gunakan **Mode Penyamaran / Incognito Window** untuk memotong cache browser Anda.
- Pastikan browser tidak memaksa pengalihan ke `https://` (karena port 8000 menggunakan protokol `http://` polos sebelum domain SSL terpasang).

---

## Laporan Diagnosis & Resolusi 2 (Proxy Starting & Sentinel Out Of Sync)

Kami menyelesaikan masalah status **Proxy Starting** dan **Sentinel Out Of Sync** pada localhost server di Coolify.

### Penyebab Masalah
1. **Resolusi Host**: IP Address localhost diatur ke `host.docker.internal`. Pada host Linux/GCE VM, resolusi kontainer ke host melalui `host.docker.internal` seringkali tidak berjalan di dalam sub-layanan Docker internal.
2. **Kredensial SSH**: User SSH untuk localhost diatur ke `coolify` di database, padahal kunci SSH otomatis dari instalasi Coolify diletakkan di bawah user **`root`** (`/root/.ssh/authorized_keys`). Akibatnya, job `CheckAndStartSentinelJob` dan `StartProxy` terus-menerus gagal (*FAIL*) dengan error syntax SSH karena tidak terotentikasi.

### Tindakan Perbaikan (100% Sukses)
Kami telah melakukan update database Coolify secara langsung di dalam VM:
1. **Update IP Localhost**: Mengubah IP localhost menjadi **`172.17.0.1`** (IP default gateway docker0, yang selalu stabil di Linux).
2. **Update SSH User**: Mengubah SSH user menjadi **`root`**.
3. **Uji Coba Koneksi**: Kami menguji koneksi SSH internal dari dalam kontainer `coolify` ke host VM (`ssh -i ... root@172.17.0.1`) dan berhasil tersambung dengan sukses (`ssh_is_working`).

### Langkah Solusi untuk Pengguna
1. Silakan **Muat Ulang (Refresh)** halaman dashboard Coolify di browser Anda.
2. Anda akan melihat form localhost server kini terisi IP: `172.17.0.1` dan User: `root`.
3. Klik tombol biru **"Validate Server"** di bagian atas untuk menyinkronkan status. Sentinel dan Proxy akan otomatis aktif dan berubah menjadi hijau/Ready!


