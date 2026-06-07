# Rencana Implementasi: Perbaikan Masalah Proxy Starting & Sentinel Out Of Sync di Coolify

Rencana ini dibuat untuk mendiagnosis dan menyelesaikan masalah status **Proxy Starting** dan **Sentinel Out Of Sync** pada localhost server di Coolify.

## User Review Required

> [!IMPORTANT]
> Masalah status Proxy stuck di "Starting" dan Sentinel Out Of Sync biasanya disebabkan oleh:
> 1. **Resolusi Host/IP**: IP address/domain diatur ke `host.docker.internal`. Di lingkungan Linux Docker, resolusi ini terkadang tidak stabil jika pemetaan host-gateway tidak berjalan sempurna. Disarankan menggunakan IP default gateway docker0 (**`172.17.0.1`**).
> 2. **Proxy Container Crash**: Kontainer proxy (Coolify menggunakan Traefik sebagai default proxy) gagal melakukan binding port 80/443 atau crash karena konflik dengan service lain di VM.

## Open Questions

> [!IMPORTANT]
> 1. Apakah ada service lain yang sedang berjalan di VM GCP pada port 80 atau 443? (Coolify Proxy membutuhkan hak eksklusif untuk port 80/443 di VM).
> 2. Apakah Anda mengizinkan jika kami mengganti konfigurasi IP Address/Domain localhost di panel Coolify dari `host.docker.internal` menjadi `172.17.0.1`?

## Proposed Steps for Investigation & Resolution

Kami akan melakukan investigasi di dalam VM via SSH untuk melihat kondisi kontainer proxy dan sentinel:

### 1. Memeriksa Kontainer Docker yang Sedang Berjalan & Log-nya
Kami akan memeriksa apakah kontainer proxy (Traefik/Caddy) ada dan mengapa ia gagal berjalan atau tertahan di status starting.
- **Daftar Kontainer**: `gcloud compute ssh ledgerscope-coolify-server --zone=asia-southeast1-b --command="sudo docker ps -a"`
- **Log Proxy**: `gcloud compute ssh ledgerscope-coolify-server --zone=asia-southeast1-b --command="sudo docker logs coolify-proxy"` (atau nama kontainer proxy yang sesuai)

### 2. Memeriksa Konflik Port (Port 80/443)
Verifikasi jika ada proses lain di host VM yang sedang mendengarkan (listening) di port 80 atau 443 yang menghalangi proxy berjalan.
- **Perintah**: `gcloud compute ssh ledgerscope-coolify-server --zone=asia-southeast1-b --command="sudo ss -tulnp | grep -E ':80 |:443 '"`

### 3. Mengubah IP Address Localhost ke Gateway Docker
Mengubah entri IP Address/Domain di Coolify dari `host.docker.internal` menjadi `172.17.0.1` (IP gateway internal docker0) untuk komunikasi antar kontainer yang lebih stabil di Linux.

---

## Verification Plan

### Automated/Manual Verification
- Verifikasi bahwa kontainer proxy berjalan normal (`Up`).
- Status di UI Coolify berubah menjadi **Ready** (bukan Sentinel Out Of Sync) dan Proxy menjadi **Running**.
