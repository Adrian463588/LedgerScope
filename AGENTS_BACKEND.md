# LedgerScope Backend Engineering Contract

Requirement bisnis bersumber dari `PRD.md`, API/UI hybrid dari root `AGENTS.md` dan `AGENTS_FRONTEND.md`, dan visual tidak dikerjakan di backend. Backend utama adalah Laravel REST API `/api/v1`.

## Runtime baseline

- PHP 8.4, Laravel 13, PostgreSQL 17, Redis 7, Horizon, Sanctum SPA.
- Private evidence/report/import storage memakai S3-compatible disk (MinIO lokal); local disk hanya untuk testing atau fallback yang eksplisit.
- PDF/XLSX/CSV harus menghasilkan file nyata melalui DomPDF/Laravel Excel. Status `completed` dilarang jika file tidak ada.
- Provider eksternal ERP, payroll, inventory, tax, banking, SSO, OCR, AI, mobile, dan anomaly memakai adapter contract; tanpa provider/credential tervalidasi hasilnya `feature_unavailable`.

## Aturan domain dan API

1. Controller harus tipis: authorize, panggil FormRequest/Action/Service, dan kembalikan API Resource melalui `ApiResponse`.
2. Semua response success memakai `success`, `message`, `data`, optional `meta`. Error memakai `success`, `message`, optional `code`, dan `errors`.
3. Kode error standar: `validation_failed`, `unauthorized`, `forbidden`, `not_found`, `domain_error`, `feature_unavailable`, `session_expired`, `server_error`.
4. Semua input mutasi memakai FormRequest; semua output resource utama memakai API Resource, bukan raw Eloquent response.
5. Parent-child route wajib memakai scoped binding dan policy/query authorization. Cross-company dan cross-engagement access harus gagal tertutup.
6. Journal, import, period/quarter lock, approval, reconciliation, evidence, working paper, finding, dan report mutation harus atomik dalam `DB::transaction()`.
7. Posted journal, locked period/reconciliation, approved statement/report, signed working paper, dan audit log append-only tidak boleh diedit atau dihapus secara ilegal.
8. Uang dan perhitungan finansial memakai `DECIMAL(20,2)`, BCMath, `Decimal`, atau `Money`; `float`/`double` dilarang untuk amount, total, balance, threshold, dan export.
9. Sensitive action wajib mengirim auditable event. Audit ditulis `after commit`, tidak boleh menyimpan stack trace atau menghapus audit log otomatis.
10. Evidence/report/import selalu memakai disk private yang konsisten, signed temporary URL, validasi MIME/size, dan lifecycle yang dapat diaudit.
11. Exception API tidak boleh mengembalikan stack trace. Unexpected error harus menjadi JSON 500 dengan `server_error`.

## Struktur kode

```text
backend/app/
├── Contracts/       # adapter dan public contracts
├── Enums/           # status/type domain
├── Events/Listeners/ # audit dan domain events
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/    # validation + authorization input
│   ├── Resources/   # response shape
│   └── Middleware/
├── Models/          # relations, casts, scopes only
├── Policies/        # company/engagement/resource authorization
├── Services/        # business workflows dan transactions
├── Support/         # Decimal dan guards
└── ValueObjects/    # Money
```

## Migration dan data safety

- Migration additive dan tidak menghapus data/volume existing.
- Money columns `DECIMAL(20,2)`; index/unique name harus eksplisit.
- Company-scoped table memiliki `company_id` index dan invariant penting ditegakkan di DB serta service.
- Seeder demo hanya untuk local/testing, tidak boleh berjalan sebagai production bootstrap tanpa opt-in.

## Quality gate

Jalankan melalui runtime Docker atau PHP environment yang ekuivalen:

```bash
composer validate --strict
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan migrate:fresh --seed --env=testing --force
php artisan test
```
