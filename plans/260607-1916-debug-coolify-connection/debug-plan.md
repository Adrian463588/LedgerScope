# Rencana Implementasi: Investigasi & Penyelesaian Masalah Koneksi Coolify Ditolak

Rencana ini dibuat untuk mendiagnosis dan menyelesaikan error `ERR_CONNECTION_REFUSED` ketika mengakses dashboard Coolify di `http://34.143.222.59:8000`.

## User Review Required

> [!IMPORTANT]
> Masalah koneksi ditolak umumnya disebabkan oleh dua kemungkinan utama:
> 1. **Proses Instalasi Masih Berjalan**: Script bootstrap (`startup-script.sh`) sedang melakukan `apt-get upgrade` dan mengunduh image Docker Coolify di latar belakang. Proses ini memakan waktu 3–10 menit tergantung bandwidth GCP.
> 2. **Error pada Script Bootstrap**: Terjadi kegagalan saat menjalankan instalasi Coolify atau Docker di dalam VM.

## Open Questions

> [!IMPORTANT]
> 1. Apakah Anda sudah menunggu sekitar 5–10 menit sejak perintah `terraform apply` selesai? Instalasi Coolify di awal membutuhkan proses unduhan container Docker yang cukup besar.
> 2. Apakah Anda memiliki kunci SSH private yang sesuai dengan public key `~/.ssh/id_rsa.pub` di mesin lokal Anda untuk melakukan SSH langsung jika dibutuhkan? (Sebagai alternatif, kita bisa menggunakan `gcloud compute ssh`).

## Proposed Steps for Investigation & Resolution

Kami akan menggunakan serangkaian perintah gcloud CLI untuk memeriksa status sistem tanpa mengubah kode aplikasi terlebih dahulu:

### 1. Memeriksa Log Boot & Startup Script VM
Kami akan mengambil output serial port dari instance VM GCP untuk melihat jalannya `startup-script.sh` dan memverifikasi apakah ada error atau proses yang menggantung (hanging).
- **Perintah**: `gcloud compute instances get-serial-port-output ledgerscope-coolify-server --zone=asia-southeast1-b`

### 2. Melakukan SSH ke VM untuk Investigasi Internal
Jika log boot tidak konklusif, kami akan masuk ke dalam VM untuk memeriksa status Docker dan proses instalasi:
- **Perintah SSH**: `gcloud compute ssh coolify@ledgerscope-coolify-server --zone=asia-southeast1-b`
- **Pengecekan Log**: `sudo cat /var/log/coolify-bootstrap.log`
- **Pengecekan Port**: `sudo ss -tulnp | grep 8000` atau `sudo docker ps` (memastikan kontainer Coolify running).

### 3. Memverifikasi Konfigurasi Firewall Rule
Memastikan VPC `ledgerscope-vpc` memiliki firewall rule port 8000 yang aktif dan terasosiasi dengan tag `coolify-server` pada VM.

---

## Verification Plan

### Automated/Manual Verification
- Setelah penyebab ditemukan dan diperbaiki (atau setelah instalasi selesai), kami akan memverifikasi akses HTTP port 8000 menggunakan perintah `curl` lokal atau browser.
