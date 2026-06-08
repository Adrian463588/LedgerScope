# Rencana Perbaikan Error Deployment (502 Bad Gateway & 500 Internal Server Error)

Rencana ini dibuat untuk mendiagnosis dan memperbaiki dua masalah utama pada deployment LedgerScope di Coolify:
1. **Frontend 502 Bad Gateway**: Traefik secara default mencoba mengarahkan trafik ke port `80` (yang diexpose oleh base image nginx-alpine), padahal Nginx frontend dikonfigurasi berjalan secara non-root di port `8080`.
2. **Backend 500 Internal Server Error**: 
   - Direktori framework Laravel (`storage/framework/views`, `storage/framework/sessions`, `storage/framework/cache/data`) tidak ada di dalam container karena tidak ditrack oleh git. Hal ini menyebabkan error `InvalidArgumentException: Please provide a valid cache path.` saat compiler mencoba memproses template.
   - File `routes/web.php` masih menggunakan rendering Inertia mockup, yang tidak memiliki asset terkompilasi di backend.

---

## User Review Required

> [!IMPORTANT]
> - Kami menyarankan untuk membersihkan file `backend/routes/web.php` dan menggantinya dengan respons JSON status API yang standar untuk path `/`. 
> - Kami akan menambahkan label `coolify.port=8080` untuk frontend dan backend di `production-compose.yml` agar Traefik mengarahkan trafik ke port internal kontainer yang benar (`8080`).

---

## Open Questions

> [!NOTE]
> 1. Apakah Anda setuju jika kami membersihkan route default di `backend/routes/web.php` agar hanya mengembalikan status API JSON dan tidak merender halaman Inertia (karena rendering UI sepenuhnya dilakukan oleh Vue SPA)?
> 2. Apakah ada variabel lingkungan custom tambahan yang perlu dimasukkan ke `production-compose.yml` selain yang ada saat ini?
> 3. Apakah Anda menyetujui penambahan direktori penyimpanan framework di `backend/Dockerfile` agar Laravel dapat menulis session, views compiler, dan cache data dengan benar?

---

## Proposed Changes

### [Docker Compose Configuration]

#### [MODIFY] [production-compose.yml](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/deployment/coolify/production-compose.yml)
Kita akan menambahkan label `coolify.port=8080` pada service `ledgerscope-frontend` dan `ledgerscope-backend` untuk memastikan reverse proxy Coolify (Traefik) merutekan trafik HTTP ke port `8080`.

---

### [Backend Component]

#### [MODIFY] [Dockerfile](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/backend/Dockerfile)
Kita akan memodifikasi bagian pembuatan direktori dan permissions di akhir file Dockerfile untuk membuat direktori framework views, sessions, dan cache secara rekursif agar Laravel tidak gagal saat inisialisasi compiler.

#### [MODIFY] [web.php](file:///d:/SEMESTER12/ProjectKode/AuditorAccountant/backend/routes/web.php)
Kita akan mengganti file routing web bawaan dengan respons JSON status API, menghilangkan redirect ke `/dashboard` dan render Inertia.

---

## Verification Plan

### Manual Verification
1. Lakukan commit perubahan ke repositori GitHub Anda di branch `main`.
2. Lakukan **Redeploy** kembali melalui dashboard Coolify.
3. Setelah proses build selesai, pastikan status kontainer backend dan frontend berubah menjadi **Healthy** dan **Running**.
4. Akses URL Frontend (`https://app-34-143-222-59.sslip.io`) untuk memastikan halaman login/SPA Vue termuat dengan benar (tanpa 502 Bad Gateway).
5. Akses URL Backend API (`https://api-34-143-222-59.sslip.io`) untuk memastikan respons JSON status online berjalan dengan sukses (tanpa 500 Internal Server Error).
