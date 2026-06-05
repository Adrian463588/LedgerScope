# AGENTS.md — LedgerScope Frontend Architecture

> **Scope:** Frontend only — Inertia.js + Vue 3 + TypeScript + Tailwind CSS v4.
> **Backend Contract:** Laravel 13 REST API (see `AGENTS.md` backend & `Infrastructure.md`).
> **Design Contract:** Strictly follows `DESIGN.md` — Financial Precision aesthetic.
> **Philosophy:** Spec-Driven Development — every component, page, and interaction is specified before it is built.

---

## Prinsip: Build More Architect Dreams

> Jangan langsung code. Baca spec dulu. Buat kontrak dulu. Baru build.
> Setiap perubahan harus traceable ke spec. Setiap komponen harus punya alasan.

Tiga hukum utama:

1. **Spec sebelum code.** Tidak ada komponen yang dibangun tanpa spec yang jelas di AGENTS.md ini atau DESIGN.md.
2. **Contract sebelum integrasi.** Tidak ada API call yang dibuat tanpa API contract yang didefinisikan terlebih dahulu.
3. **Gate sebelum merge.** Tidak ada fase yang dianggap selesai sebelum lulus quality gate.

---

## Mandatory Agent Workflow

Workflow berikut berlaku sebelum semua aturan project-spesifik di bawah:

1. **Baca spec terlebih dahulu.** Sebelum menulis satu baris kode, baca seksi yang relevan dari AGENTS.md ini, DESIGN.md, dan PRD.md.
2. **Ikuti RTK cycle untuk setiap komponen:**
   - **RED** → Tulis failing Vitest/Cypress spec terlebih dahulu.
   - **TICKET** → Implementasi minimum yang membuat spec pass.
   - **KEEP** → Refactor, lint, type-check, lalu commit.
3. **Jalankan KEEP gate sebelum menganggap fase selesai:**
   ```bash
   npx vue-tsc --noEmit
   npx eslint "resources/js/**/*.{ts,vue}" --max-warnings 0
   npx vitest run
   npx prettier --check "resources/js/**/*.{ts,vue}"
   ```
4. **Jangan tinggalkan debug artifacts.** Hapus semua `console.log`, `debugger`, komentar `TODO` tanpa tiket, dan kode dummy sebelum fase dinyatakan selesai.
5. **Setiap komponen harus ada di Component Registry** (Seksi 7 dokumen ini) sebelum diimplementasi.

---

## 1. Project Identity

| Key              | Value                                                            |
| ---------------- | ---------------------------------------------------------------- |
| Product          | LedgerScope                                                      |
| Frontend Stack   | Inertia.js + Vue 3 (Composition API) + TypeScript                |
| Styling          | Tailwind CSS v4 (custom `@theme` tokens dari DESIGN.md)          |
| Component Engine | Vue 3 `<script setup lang="ts">` — wajib, tanpa Options API      |
| State Management | Pinia (store per domain, bukan satu global store)                |
| HTTP Client      | Inertia.js `router` untuk navigasi; `axios` untuk API calls      |
| Form Handling    | VeeValidate + Zod (schema-first validation)                      |
| Icons            | Lucide Vue Next                                                  |
| Charts           | Chart.js + vue-chartjs                                           |
| Testing          | Vitest (unit) + Cypress (E2E)                                    |
| Build Tool       | Vite 6 (via Laravel Vite Plugin)                                 |
| Linter           | ESLint (flat config) + Prettier                                  |
| TypeScript       | Strict mode, `noUncheckedIndexedAccess: true`                    |

---

## 2. Absolute Rules (Never Violate)

Aturan ini berlaku untuk setiap file, setiap komponen, setiap halaman:

1. **Jangan pernah gunakan Options API.** Selalu `<script setup lang="ts">`.
2. **Jangan pernah gunakan `any` di TypeScript.** Gunakan `unknown` lalu narrow, atau definisikan type yang benar.
3. **Jangan pernah hardcode warna atau spacing.** Selalu gunakan CSS variables dari DESIGN.md atau Tailwind token dari `@theme`.
4. **Jangan pernah tampilkan angka finansial dengan `float`.** Semua angka uang dirender via `formatCurrency()` composable.
5. **Jangan pernah mutate Pinia state langsung dari komponen.** Selalu gunakan store actions.
6. **Jangan pernah buat API call dari dalam komponen langsung.** Selalu melalui composable atau store action.
7. **Setiap tabel finansial harus menggunakan `tabular-nums` dan right-align untuk kolom angka.**
8. **Setiap form harus menggunakan Zod schema.** Tidak ada validasi ad-hoc dengan `if/else` manual.
9. **Setiap route yang butuh autentikasi harus diproteksi via Inertia middleware.** Tidak ada client-side-only guard.
10. **Locked/posted records harus secara visual dikomunikasikan sebagai immutable** (lihat DESIGN.md Section 2 & 5).

---

## 3. Tech Stack (Exact Versions)

```
Node.js          22 LTS
Vue              3.5+
TypeScript       5.5+
Inertia.js       2.x (vue3 adapter)
Tailwind CSS     4.x
Vite             6.x
Pinia            3.x
VeeValidate      4.x
Zod              3.x
axios            1.x
Chart.js         4.x
vue-chartjs      5.x
Lucide Vue Next  latest
Vitest           2.x
@vue/test-utils  2.x
Cypress          14.x
ESLint           9.x (flat config)
Prettier         3.x
```

Jangan tambahkan library di luar list ini tanpa justifikasi eksplisit.

---

## 4. Folder & Module Structure

Struktur ini wajib diikuti. Jangan buat folder di luar layout ini tanpa justifikasi.

```
resources/
├── js/
│   ├── app.ts                    ← Entry point Inertia
│   ├── bootstrap.ts              ← Axios config, CSRF
│   │
│   ├── types/                    ← Global TypeScript types & API contracts
│   │   ├── api.ts                ← Generic API response types
│   │   ├── auth.ts
│   │   ├── company.ts
│   │   ├── accounting.ts
│   │   ├── audit.ts
│   │   ├── evidence.ts
│   │   ├── reporting.ts
│   │   └── index.ts              ← Re-export semua types
│   │
│   ├── composables/              ← Reusable logic (satu file per concern)
│   │   ├── useAuth.ts
│   │   ├── useCurrency.ts        ← formatCurrency, parseCurrency
│   │   ├── usePeriod.ts          ← formatPeriod, isPeriodLocked
│   │   ├── useTable.ts           ← sorting, pagination, filtering
│   │   ├── useForm.ts            ← VeeValidate + Zod wrapper
│   │   ├── useNotification.ts    ← in-app toast/alert system
│   │   ├── usePermission.ts      ← hasPermission, canAccess
│   │   ├── useFileUpload.ts      ← drag/drop, progress, validation
│   │   ├── useExport.ts          ← PDF/Excel download dengan signed URL
│   │   └── useInertia.ts         ← Inertia router helpers
│   │
│   ├── stores/                   ← Pinia stores (satu store per domain)
│   │   ├── auth.store.ts
│   │   ├── company.store.ts
│   │   ├── accounting.store.ts
│   │   ├── engagement.store.ts
│   │   ├── notification.store.ts
│   │   └── ui.store.ts           ← sidebar collapse, theme, breadcrumb
│   │
│   ├── schemas/                  ← Zod validation schemas
│   │   ├── auth.schema.ts
│   │   ├── company.schema.ts
│   │   ├── journal.schema.ts
│   │   ├── evidence.schema.ts
│   │   └── engagement.schema.ts
│   │
│   ├── layouts/
│   │   ├── AppLayout.vue         ← Shell: sidebar + topbar + main
│   │   ├── AuthLayout.vue        ← Login/Reset: split dark/light
│   │   └── ClientPortalLayout.vue← Simplified layout for client users
│   │
│   ├── components/
│   │   ├── ui/                   ← Design system primitives (lihat Seksi 7)
│   │   │   ├── Button/
│   │   │   ├── Input/
│   │   │   ├── Select/
│   │   │   ├── Badge/
│   │   │   ├── Table/
│   │   │   ├── Modal/
│   │   │   ├── Drawer/
│   │   │   ├── Card/
│   │   │   ├── Tabs/
│   │   │   ├── Dropdown/
│   │   │   ├── Tooltip/
│   │   │   ├── Skeleton/
│   │   │   ├── EmptyState/
│   │   │   ├── StatusBadge/
│   │   │   ├── AmountDisplay/    ← Finansial amount renderer
│   │   │   └── PageHeader/
│   │   │
│   │   ├── shared/               ← Cross-domain komponen yang reusable
│   │   │   ├── Sidebar/
│   │   │   ├── Topbar/
│   │   │   ├── Breadcrumb/
│   │   │   ├── NotificationBell/
│   │   │   ├── CompanySwitcher/
│   │   │   ├── PeriodSelector/
│   │   │   ├── FileUploader/
│   │   │   ├── RichTextEditor/
│   │   │   ├── ConfirmDialog/
│   │   │   ├── LockBanner/       ← Locked period indicator
│   │   │   └── SignOffBlock/     ← Prepared/Reviewed/Approved signatures
│   │   │
│   │   ├── accounting/           ← Domain-spesifik komponen
│   │   │   ├── JournalEntryForm/
│   │   │   ├── JournalLineTable/
│   │   │   ├── TrialBalanceTable/
│   │   │   ├── AccountSelector/
│   │   │   ├── PeriodStatusCard/
│   │   │   ├── QuarterClosingChecklist/
│   │   │   └── ReconciliationMatcher/
│   │   │
│   │   ├── financial/
│   │   │   ├── FinancialStatementViewer/
│   │   │   ├── RatioCard/
│   │   │   ├── TrendChart/
│   │   │   ├── VarianceTable/
│   │   │   └── KPIDashboard/
│   │   │
│   │   ├── audit/
│   │   │   ├── WorkingPaperCard/
│   │   │   ├── ReviewNoteThread/
│   │   │   ├── FindingCard/
│   │   │   ├── RiskHeatmap/
│   │   │   ├── AuditProgressTracker/
│   │   │   └── ProcedureChecklist/
│   │   │
│   │   └── evidence/
│   │       ├── EvidenceCard/
│   │       ├── DocumentRequestRow/
│   │       └── EvidenceViewer/
│   │
│   └── pages/                    ← Inertia page components
│       ├── Auth/
│       │   ├── Login.vue
│       │   ├── ForgotPassword.vue
│       │   └── ResetPassword.vue
│       ├── Dashboard/
│       │   └── Index.vue
│       ├── Companies/
│       │   ├── Index.vue
│       │   ├── Create.vue
│       │   ├── Show.vue
│       │   └── Edit.vue
│       ├── Accounting/
│       │   ├── FiscalYears/
│       │   ├── Periods/
│       │   ├── ChartOfAccounts/
│       │   ├── JournalEntries/
│       │   ├── TrialBalance/
│       │   ├── Reconciliation/
│       │   └── QuarterlyClosing/
│       ├── Financial/
│       │   ├── Statements/
│       │   └── Analysis/
│       ├── Audit/
│       │   ├── Engagements/
│       │   ├── WorkingPapers/
│       │   ├── Evidence/
│       │   ├── Findings/
│       │   ├── RiskAssessment/
│       │   └── Controls/
│       ├── Reports/
│       │   └── Index.vue
│       ├── Settings/
│       │   ├── Users/
│       │   └── Roles/
│       └── Client/               ← Client portal pages
│           ├── Dashboard.vue
│           └── DocumentRequests/

resources/
└── css/
    └── app.css                   ← Tailwind v4 entry, @theme tokens, @layer components
```

---

## 5. TypeScript Contract

### 5.1 API Response Types

Semua API response harus di-type dengan generik berikut:

```typescript
// resources/js/types/api.ts

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data: T
  meta?: PaginationMeta
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface ApiError {
  success: false
  message: string
  errors?: Record<string, string[]>
}
```

### 5.2 Domain Types (Contoh: Journal)

```typescript
// resources/js/types/accounting.ts

export type JournalStatus =
  | 'draft'
  | 'submitted'
  | 'reviewed'
  | 'approved'
  | 'posted'
  | 'rejected'
  | 'reversed'

export interface JournalEntry {
  id: number
  journal_number: string
  company_id: number
  period_id: number
  date: string           // ISO 8601
  description: string
  reference_number: string | null
  status: JournalStatus
  source_type: 'manual' | 'import' | 'recurring' | 'reversal' | 'system'
  reversed_from_id: number | null
  prepared_by: UserSummary
  reviewed_by: UserSummary | null
  approved_by: UserSummary | null
  lines: JournalEntryLine[]
  created_at: string
  updated_at: string
}

export interface JournalEntryLine {
  id: number
  account_id: number
  account_code: string
  account_name: string
  debit: string          // SELALU string — tidak pernah number untuk uang
  credit: string         // SELALU string — tidak pernah number untuk uang
  description: string | null
}
```

> **Penting:** Semua field uang (`debit`, `credit`, `amount`, `balance`) HARUS bertipe `string` di TypeScript,
> karena backend mengirim `DECIMAL(20,2)` sebagai string JSON untuk menghindari float precision loss.
> Gunakan `useCurrency().formatCurrency(value)` untuk rendering.

### 5.3 Inertia Page Props

```typescript
// resources/js/types/inertia.d.ts
import type { PageProps as InertiaPageProps } from '@inertiajs/core'

export interface SharedProps extends InertiaPageProps {
  auth: {
    user: AuthUser
    permissions: string[]
    company: CompanySummary | null
  }
  flash: {
    success?: string
    error?: string
  }
  errors: Record<string, string>
}

// Gunakan ini di setiap page component:
// const props = defineProps<{ /* page-specific props */ } & SharedProps>()
```

---

## 6. Composables Spec

### `useCurrency()`

```typescript
// resources/js/composables/useCurrency.ts
export function useCurrency() {
  // Format: IDR 1.234.567,00
  function formatCurrency(value: string | number, currency = 'IDR'): string

  // Format tanpa symbol: 1.234.567,00
  function formatAmount(value: string | number): string

  // Parse dari user input string ke decimal string
  function parseCurrency(value: string): string

  // Determine color class berdasarkan nilai
  function amountColorClass(value: string, type: 'debit' | 'credit'): string

  return { formatCurrency, formatAmount, parseCurrency, amountColorClass }
}
```

### `usePermission()`

```typescript
// resources/js/composables/usePermission.ts
export function usePermission() {
  const { auth } = usePage<SharedProps>().props

  function can(permission: string): boolean
  function canAny(permissions: string[]): boolean
  function canAll(permissions: string[]): boolean

  return { can, canAny, canAll }
}
```

Digunakan di template:
```html
<Button v-if="can('journal.post')" @click="postJournal">Post</Button>
```

### `useTable()`

```typescript
// resources/js/composables/useTable.ts
export function useTable<T>(options: TableOptions<T>) {
  const sortBy = ref<string | null>(null)
  const sortDirection = ref<'asc' | 'desc'>('asc')
  const currentPage = ref(1)
  const perPage = ref(25)
  const filters = reactive<Record<string, unknown>>({})

  function toggleSort(column: string): void
  function goToPage(page: number): void
  function setFilter(key: string, value: unknown): void
  function clearFilters(): void

  const queryParams = computed(/* builds URLSearchParams for API */)

  return { sortBy, sortDirection, currentPage, perPage, filters, queryParams, toggleSort, goToPage, setFilter, clearFilters }
}
```

---

## 7. Component Registry

Setiap komponen UI di-spec di sini sebelum diimplementasi. Tidak ada komponen yang boleh dibuat tanpa entri di registry ini.

### 7.1 Primitive Components (`components/ui/`)

#### `<AppButton>`

| Prop         | Type                                                      | Default     |
| ------------ | --------------------------------------------------------- | ----------- |
| `variant`    | `'primary' \| 'secondary' \| 'ghost' \| 'danger' \| 'locked'` | `'secondary'` |
| `size`       | `'sm' \| 'md' \| 'lg'`                                   | `'md'`      |
| `loading`    | `boolean`                                                 | `false`     |
| `icon`       | `Component` (Lucide icon)                                 | `undefined` |
| `iconRight`  | `boolean`                                                 | `false`     |
| `type`       | `'button' \| 'submit' \| 'reset'`                        | `'button'`  |

Emit: tidak ada (gunakan native `@click`).

Aturan:
- `variant="locked"` otomatis set `disabled` dan `cursor-not-allowed`.
- `loading=true` menampilkan spinner, menonaktifkan click, tidak mengubah lebar tombol.
- Selalu render sebagai `<button>`, bukan `<div>` atau `<a>`.

#### `<AppInput>`

| Prop        | Type                             | Default     |
| ----------- | -------------------------------- | ----------- |
| `modelValue`| `string \| number`               | —           |
| `label`     | `string`                         | —           |
| `hint`      | `string`                         | —           |
| `error`     | `string`                         | —           |
| `required`  | `boolean`                        | `false`     |
| `disabled`  | `boolean`                        | `false`     |
| `type`      | `HTMLInputTypeAttribute`         | `'text'`    |
| `amount`    | `boolean`                        | `false`     |

Aturan:
- `amount=true` mengaktifkan font IBM Plex Mono, right-align, dan masking ribuan.
- Error message ditampilkan di bawah input dengan `--status-danger`.
- Label wajib ada untuk aksesibilitas — gunakan `.sr-only` jika tidak ingin terlihat.

#### `<AppTable>`

Wrapper untuk data tables finansial.

| Prop         | Type                                      | Default |
| ------------ | ----------------------------------------- | ------- |
| `columns`    | `TableColumn[]`                           | —       |
| `data`       | `T[]`                                     | —       |
| `loading`    | `boolean`                                 | `false` |
| `empty-text` | `string`                                  | —       |
| `sortable`   | `boolean`                                 | `false` |
| `selectable` | `boolean`                                 | `false` |

```typescript
interface TableColumn {
  key: string
  label: string
  align?: 'left' | 'right' | 'center'
  sortable?: boolean
  isAmount?: boolean      // Aktifkan IBM Plex Mono + right-align + warna debit/credit
  isStatus?: boolean      // Render sebagai <StatusBadge>
  width?: string
}
```

Aturan:
- `loading=true` render skeleton rows, bukan spinner.
- `isAmount=true` wajib menggunakan `formatAmount()` dari `useCurrency()`.
- Header kolom amount selalu right-aligned.
- Tidak ada border-left berwarna pada rows. Gunakan `surface-hover` untuk hover state.

#### `<StatusBadge>`

| Prop     | Type                                                                                          |
| -------- | --------------------------------------------------------------------------------------------- |
| `status` | `JournalStatus \| WorkingPaperStatus \| FindingStatus \| EngagementStatus \| EvidenceStatus` |

Otomatis memetakan status → warna badge dari DESIGN.md Section 6.

#### `<AmountDisplay>`

| Prop       | Type                          | Default |
| ---------- | ----------------------------- | ------- |
| `value`    | `string`                      | —       |
| `type`     | `'debit' \| 'credit' \| 'balance' \| 'zero'` | `'balance'` |
| `currency` | `string`                      | `'IDR'` |
| `bold`     | `boolean`                     | `false` |

Render sebagai `<span class="font-mono tabular-nums text-right">` dengan warna yang sesuai.

---

## 8. Page Specs

### 8.1 Authentication

#### `Login.vue`

Layout: `AuthLayout` — split 40/60 dark/light.

Kiri (dark shell):
- Logo LedgerScope wordmark (`Ledger` putih + `Scope` brand-red).
- Tagline: *"Financial Precision. Audit Confidence."* — DM Serif Display italic.
- Geometric red diagonal line accent (via `::after` pseudo-element).

Kanan (light):
- Card login centered, max-width 380px.
- Heading `"Welcome back"` — DM Serif Display 28px.
- Form fields: Email, Password.
- `"Forgot password?"` link — right-aligned, text-muted.
- `[Sign In]` button — primary, full-width, 42px height.
- MFA code input — muncul setelah password submit jika MFA enabled (slide-down animation).

Zod schema:
```typescript
const loginSchema = z.object({
  email: z.string().email('Email tidak valid'),
  password: z.string().min(8, 'Password minimal 8 karakter'),
})
```

---

### 8.2 Dashboard

Layout: `AppLayout`.

Konten:
- **Page Header:** "Dashboard" + Company Switcher.
- **KPI Row (3 kartu):** Total Aktif Engagement, Outstanding Document Requests, Open Findings (critical + high).
- **Quarterly Snapshot:** Revenue, Expense, Net Profit — 3 kolom dengan angka besar dan trend indicator.
- **Recent Activity Table:** 10 aktivitas terakhir dari audit log.
- **Quick Access:** Shortcut ke Journal Entries, Trial Balance, Working Papers, Reports.

Filter: Company Switcher di header.

---

### 8.3 Accounting — Journal Entries

#### Index (`JournalEntries/Index.vue`)

Komponen yang digunakan:
- `<AppTable>` dengan kolom: No. Journal, Tanggal, Deskripsi, Debit Total, Credit Total, Status, Prepared By, Aksi.
- `<StatusBadge>` di kolom Status.
- `<AmountDisplay>` di kolom Debit/Credit.
- Filter bar: Period Selector, Status filter, Date range.
- Tombol `[+ New Journal]`, `[Import from Excel]`.
- Empty state jika tidak ada data.

#### Create/Edit (`JournalEntries/Create.vue`)

Form dengan:
- Period Selector (disabled jika locked).
- `<LockBanner>` muncul jika periode terkunci.
- Journal header fields: Tanggal, Deskripsi, Referensi.
- **Journal Lines Table** — editable table dengan:
  - Account selector (searchable dropdown).
  - Debit input (IBM Plex Mono, right-aligned).
  - Credit input (IBM Plex Mono, right-aligned).
  - Constraint: satu baris tidak boleh ada debit DAN credit sekaligus.
  - Tombol `[+ Add Line]` dan `[Remove]` per baris.
- **Balance Indicator:** Real-time tampilkan Total Debit vs Total Credit. Jika tidak balance → teks merah + ikon warning.
- Attachment upload section.
- Actions: `[Save Draft]`, `[Submit for Review]`.

Zod schema:
```typescript
const journalSchema = z.object({
  period_id: z.number().positive(),
  date: z.string().date(),
  description: z.string().min(3).max(500),
  reference_number: z.string().optional(),
  lines: z.array(z.object({
    account_id: z.number().positive(),
    debit: z.string().regex(/^\d+(\.\d{1,2})?$/),
    credit: z.string().regex(/^\d+(\.\d{1,2})?$/),
    description: z.string().optional(),
  })).min(2, 'Minimal 2 baris diperlukan'),
}).refine(
  (data) => {
    const totalDebit = data.lines.reduce((sum, l) => /* bcadd */ sum + parseFloat(l.debit || '0'), 0)
    const totalCredit = data.lines.reduce((sum, l) => /* bcadd */ sum + parseFloat(l.credit || '0'), 0)
    return Math.abs(totalDebit - totalCredit) < 0.001
  },
  { message: 'Total debit harus sama dengan total credit', path: ['lines'] }
)
```

---

### 8.4 Accounting — Trial Balance

Layout: Full-width table.

Fitur:
- Period selector (month / quarter / year).
- Comparison toggle: Tampilkan prior period column.
- Export: `[Export PDF]`, `[Export Excel]`.
- Kolom: Kode Akun, Nama Akun, Saldo Awal, Debit, Kredit, Saldo Akhir.
- Footer: Total row dengan semua kolom.
- Indicator: ✓ "Balanced" atau ⚠ "Unbalanced" — ditampilkan prominently di atas tabel.

---

### 8.5 Audit — Working Papers

#### Index (`WorkingPapers/Index.vue`)

Grouped by audit area (Cash & Bank, AR, Inventory, etc.).

Setiap audit area:
- Expandable section header dengan progress indicator (X of Y prepared).
- Cards per working paper: WP Reference, Title, Prepared By, Reviewer, Status, Last Updated.

#### Show (`WorkingPapers/Show.vue`)

Layout: Split — left 60% content, right 40% review panel.

Kiri:
- WP header: Reference, Title, Audit Area, Engagement.
- `<SignOffBlock>` — Prepared By + Reviewed By + Approved By dengan tanggal sign-off.
- Content area: Rich text / structured template content.
- Evidence attachments.
- Linked procedures.

Kanan:
- `<ReviewNoteThread>` — semua review notes dengan reply thread.
- Tombol `[Add Review Note]`.
- WP status actions: `[Mark as Prepared]`, `[Approve]`, `[Request Changes]`.

`<ReviewNoteThread>` spec:
- Setiap note: avatar, nama, waktu, konten, tombol `[Reply]`.
- Reply terindentasi 24px.
- Note yang resolved ditampilkan dengan opacity lebih rendah + label "Resolved".
- Tombol `[Resolve]` hanya muncul untuk reviewer/manager.

---

### 8.6 Evidence Management

#### Document Request List (`Evidence/Index.vue`)

Komponen:
- Filter: Status (Requested / Submitted / Under Review / Accepted / Rejected / Overdue).
- Progress bar: X of Y requests completed.
- Table: Request Name, Category, Assigned To (client), Due Date, Status, Evidence Count, Aksi.
- Overdue rows: background `--status-warning-bg`, teks due date merah.
- Tombol `[+ New Request]`, `[Bulk Import]`.

#### Evidence Upload (Client View)

- File drag-and-drop area dengan progress indicator.
- File preview setelah upload (nama, ukuran, tipe).
- Tombol `[Submit Evidence]`.
- Komentar textarea per upload.

---

### 8.7 Financial Analysis Dashboard

Komponen:
- `<KPIDashboard>` — grid 3 kolom: Current Ratio, Net Profit Margin, ROE, Debt-to-Equity, Quick Ratio, Operating Cash Flow Ratio.
- `<TrendChart>` — Revenue & Expense line chart dengan Chart.js (4 quarters).
- `<VarianceTable>` — Budget vs Actual per account category.
- Period selector: Quarter / Year comparison.

Chart.js color rules (dari DESIGN.md):
- Revenue line: `--debit-color` (#0D6B3E).
- Expense line: `--brand-red` (#C0190A).
- Net Profit area: semi-transparent `--debit-color`.
- Tidak boleh ada gradient fill yang mencolok.

---

### 8.8 Reports

#### Index (`Reports/Index.vue`)

- Grid kartu per report type (lihat PRD Section 6.23).
- Setiap kartu: icon, nama report, deskripsi singkat, tombol `[Generate]`.
- Generated reports list: nama, tanggal, versi, status, tombol `[Download]`.
- Status `generating` menampilkan progress spinner.
- Download menggunakan signed URL via `useExport()` composable.

---

## 9. State Management (Pinia)

### Store Structure

```typescript
// stores/accounting.store.ts
export const useAccountingStore = defineStore('accounting', () => {
  // State
  const journalEntries = ref<JournalEntry[]>([])
  const currentJournal = ref<JournalEntry | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Actions — semua API calls ada di sini
  async function fetchJournals(companyId: number, params: Record<string, unknown>): Promise<void>
  async function createJournal(companyId: number, payload: CreateJournalDTO): Promise<JournalEntry>
  async function postJournal(journalId: number): Promise<void>
  async function reverseJournal(journalId: number, reason: string): Promise<JournalEntry>

  // Getters
  const postedJournals = computed(() => journalEntries.value.filter(j => j.status === 'posted'))
  const draftJournals = computed(() => journalEntries.value.filter(j => j.status === 'draft'))

  return { journalEntries, currentJournal, isLoading, error, fetchJournals, createJournal, postJournal, reverseJournal, postedJournals, draftJournals }
})
```

Aturan:
- Setiap store harus `reset()` saat user logout.
- Jangan cache data cross-company — clear store saat company berpindah.
- Error state harus selalu di-handle, bukan diabaikan.

---

## 10. Routing

Semua route didefinisikan di Laravel `routes/web.php` dan di-pass ke Inertia. Frontend tidak mendefinisikan route sendiri.

### Route Groups

```php
// Semua route di bawah auth middleware + Inertia
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Companies
    Route::resource('companies', CompanyController::class);

    // Accounting
    Route::prefix('companies/{company}')->name('companies.')->group(function () {
        Route::resource('fiscal-years', FiscalYearController::class);
        Route::resource('accounts', ChartOfAccountsController::class);
        Route::resource('journals', JournalEntryController::class);
        Route::get('trial-balance', [TrialBalanceController::class, 'show'])->name('trial-balance');
        Route::resource('reconciliations', ReconciliationController::class);
    });

    // Engagements & Audit
    Route::resource('engagements', EngagementController::class);
    Route::prefix('engagements/{engagement}')->name('engagements.')->group(function () {
        Route::resource('working-papers', WorkingPaperController::class);
        Route::resource('document-requests', DocumentRequestController::class);
        Route::resource('findings', FindingController::class);
    });

    // Reports
    Route::resource('reports', ReportController::class)->only(['index', 'show', 'store']);
});

// Client Portal
Route::middleware(['auth', 'role:client'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'index'])->name('index');
    Route::resource('document-requests', ClientDocumentRequestController::class)->only(['index', 'show', 'update']);
});
```

---

## 11. Build Phases

Eksekusi fase secara berurutan. Jangan mulai Fase N+1 sebelum Fase N selesai dan semua tests pass.

---

### Phase 1 — Foundation & Design System

**Goal:** Setup proyek, design tokens, layout shell, dan komponen primitif.

#### 1.1 Project Setup

```bash
# Di dalam project Laravel (setelah backend setup)
npm install
# Verifikasi: tailwindcss v4, vue 3.5+, typescript strict, inertia 2.x
```

Konfigurasi `tsconfig.json`:
```json
{
  "compilerOptions": {
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "moduleResolution": "bundler",
    "jsx": "preserve"
  }
}
```

#### 1.2 Design Tokens

Implementasi seluruh `@theme` block dari DESIGN.md Section 2.2 ke `resources/css/app.css`.
Implementasi seluruh CSS variables dari DESIGN.md Section 2.1 ke dalam `:root`.

Verifikasi:
- `var(--brand-red)` → `#C0190A`.
- `var(--shell-bg)` → `#0C0D10`.
- IBM Plex Sans dan DM Serif Display sudah loaded via Google Fonts.

#### 1.3 App Shell (`AppLayout.vue`)

Implementasi layout utama sesuai DESIGN.md Section 4.2 dan 5.1:
- Sidebar 240px (collapsed: 64px).
- Topbar 56px sticky.
- Main content area flex-1.
- Company Switcher di sidebar.
- Notification Bell di topbar.
- Sidebar collapse toggle.
- Navigation groups: ACCOUNTING, FINANCIAL, AUDIT dengan icon Lucide.

Aturan:
- Active nav item: `border-left: 3px solid var(--brand-red)` + `--shell-elevated` background.
- Section labels: uppercase, 10px, opacity 50%, `--text-inverse-muted`.

#### 1.4 Komponen Primitif

Bangun semua komponen di `components/ui/` sesuai Seksi 7:
- `<AppButton>` — 5 variants, 3 sizes, loading state.
- `<AppInput>` — dengan label, error, hint, amount mode.
- `<AppTable>` — dengan skeleton loading, empty state, sortable headers.
- `<StatusBadge>` — mapping dari semua status enums.
- `<AmountDisplay>` — debit/credit/balance/zero dengan IBM Plex Mono.
- `<AppModal>` — overlay dengan backdrop, title, close button.
- `<AppDrawer>` — slide-in dari kanan.
- `<EmptyState>` — icon, title, body, optional CTA.
- `<Skeleton>` — shimmer animation, berbagai shapes.

#### 1.5 Phase 1 Gate

```bash
npx vue-tsc --noEmit           # Zero errors
npx eslint "resources/js/**"   # Zero warnings
npx vitest run                 # Semua unit tests pass
```

Visual check:
- [ ] Sidebar render dengan benar di 1280px dan 768px.
- [ ] Semua button variants terlihat sesuai DESIGN.md Section 5.3.
- [ ] Semua status badges menggunakan warna yang benar.
- [ ] IBM Plex Mono digunakan untuk semua amount display.
- [ ] Tidak ada elemen dengan warna hardcoded di luar CSS variables.

---

### Phase 2 — Authentication

**Goal:** Login, logout, forgot/reset password, MFA flow.

#### 2.1 `AuthLayout.vue`

Implementasi sesuai DESIGN.md Section 12.

#### 2.2 `Login.vue`

- Zod schema dan VeeValidate integration.
- Inertia form submit ke `POST /api/v1/auth/login`.
- MFA step: slide-down animation setelah password verify.
- Error handling: tampilkan pesan dari `flash.error`.

#### 2.3 Stores

- `auth.store.ts` — `user`, `permissions`, `logout()`.
- `usePermission()` composable.

#### 2.4 Phase 2 Tests

```
tests/Feature/Auth/LoginPageTest.cy.ts  (Cypress)
tests/Unit/composables/usePermission.spec.ts
tests/Unit/schemas/auth.schema.spec.ts
```

---

### Phase 3 — Company Management

**Goal:** CRUD perusahaan, company switcher, user assignment.

#### 3.1 Pages

- `Companies/Index.vue` — searchable table, create button.
- `Companies/Create.vue` — form lengkap dengan semua field dari PRD 6.2.
- `Companies/Show.vue` — detail + engagement history + user list.
- `Companies/Edit.vue` — form edit.

#### 3.2 `CompanySwitcher` Component

- Dropdown di sidebar menampilkan daftar companies yang assigned ke user.
- Trigger Inertia navigation saat berganti company.
- Update `company.store.ts` → clear accounting store dan engagement store.

---

### Phase 4 — Accounting Core

**Goal:** Chart of Accounts, Journal Entries, Trial Balance.

#### 4.1 Chart of Accounts

- Hierarchical tree view dengan expand/collapse.
- Import via Excel dengan preview dan column mapping UI.
- `<AccountSelector>` — searchable dropdown dengan account code + name.

#### 4.2 Journal Entries

- Index dengan filter lengkap (period, status, date range, preparer).
- Create/Edit dengan live balance indicator.
- Workflow actions: Submit → Approve → Post (sesuai permission).
- Locked period guard: tampilkan `<LockBanner>` jika periode locked.

#### 4.3 Trial Balance

- Table dengan sticky header.
- Period comparison mode.
- Balance indicator prominent di atas table.
- Export PDF/Excel via `useExport()`.

---

### Phase 5 — Audit Workflow

**Goal:** Engagements, Working Papers, Review Notes, Findings.

#### 5.1 Engagements

- Kanban-style status board untuk engagement manager.
- Detail page dengan progress tracker (Planning → Data Collection → Fieldwork → Review → Reporting).
- Team assignment UI.

#### 5.2 Working Papers

- Grouped by audit area dengan completion percentage.
- Split-pane view: content + review notes.
- `<SignOffBlock>` dengan timestamp dan user info.
- Cross-reference picker: link ke working paper lain.

#### 5.3 Review Notes

- Thread UI mirip GitHub PR comments.
- Resolve/reopen toggle.
- Badge count di working paper card.

#### 5.4 Findings

- Severity-coded cards: Critical (merah), High (oranye), Medium (kuning), Low (abu-abu).
- Management response textarea (khusus client user).
- Status workflow dengan approval gate untuk High/Critical.

---

### Phase 6 — Evidence & Client Portal

**Goal:** Document requests, file upload, client portal.

#### 6.1 Document Request Management

- Request list dengan overdue highlighting.
- Bulk request import dari Excel.
- Per-request chat thread antara auditor dan client.

#### 6.2 Evidence Upload

- Drag-and-drop dengan `useFileUpload()` composable.
- Progress indicator per file.
- File type/size validation di client sebelum upload.
- Preview untuk PDF dan image.

#### 6.3 Client Portal

- Simplified `ClientPortalLayout.vue` tanpa sidebar kompleks.
- Hanya tampilkan document requests yang assigned ke user ini.

---

### Phase 7 — Financial Analysis & Reports

**Goal:** Analysis dashboard, financial statements, report generator.

#### 7.1 Financial Analysis

- KPI cards dengan trend arrows (up/down vs prior period).
- Chart.js line charts untuk revenue/expense trend.
- Ratio cards dengan benchmark indicator (Good/Warning/Critical).

#### 7.2 Financial Statements

- Statement viewer dengan collapsible sections.
- Comparison columns (current vs prior).
- Approval workflow visual indicator.
- Export actions.

#### 7.3 Reports

- Report generation modal dengan period + type selector.
- Real-time status polling untuk background reports.
- Download via signed URL.

---

### Phase 8 — Polish & Production Hardening

**Goal:** Performance, aksesibilitas, empty states, error boundaries.

#### 8.1 Performance

```typescript
// Lazy load heavy pages
const JournalEntries = defineAsyncComponent(() =>
  import('@/pages/Accounting/JournalEntries/Index.vue')
)
```

- `content-visibility: auto` pada tabel panjang.
- Chart.js import secara tree-shaking (hanya import chart types yang digunakan).
- Image lazy loading untuk evidence previews.

#### 8.2 Aksesibilitas

- Setiap interactive element punya `aria-label` jika tidak ada visible text.
- Focus trap di modal dan drawer.
- Keyboard navigation untuk table sorting.
- Skip link: `<a href="#main-content" class="sr-only focus:not-sr-only">`.
- WCAG AA contrast check untuk semua text/background combinations.

#### 8.3 Error Boundary

```vue
<!-- components/ui/ErrorBoundary.vue -->
<!-- Wrapper untuk page sections yang bisa fail secara independent -->
```

- 404 page: minimal, dengan link kembali ke dashboard.
- 403 page: informasi permission yang kurang, dengan link ke admin.
- Network error: retry button + jelas pesan error.

#### 8.4 Phase 8 Gate

```bash
npx vue-tsc --noEmit
npx eslint "resources/js/**"
npx vitest run --coverage        # Coverage > 70% untuk composables
npx cypress run                  # Semua E2E tests pass
npx lighthouse-ci                # LCP < 2.5s, CLS < 0.1
```

---

## 12. Design Rules (Summary dari DESIGN.md)

Ini adalah rules yang paling sering dilanggar. Baca setiap kali sebelum membuat komponen baru.

### Warna
- Brand red (`--brand-red`) hanya boleh muncul di **≤3 tempat per screen**.
- Jangan pernah gunakan brand red sebagai dekorasi — hanya untuk primary action, active state, atau critical indicator.
- Debit amounts: `--debit-color` (hijau). Credit amounts: `--credit-color` (brand red).
- Locked/posted records: `--locked-color` (#4A5261) dan `--surface-alt` background.

### Typography
- Semua angka finansial: `font-family: 'IBM Plex Mono'`, `tabular-nums`, right-aligned.
- Heading/display: `font-family: 'DM Serif Display'` hanya untuk page titles dan auth screens.
- Body/UI: `font-family: 'IBM Plex Sans'` untuk semua UI text.
- Jangan gunakan DM Serif Display di bawah 24px.

### Tables
- Header: uppercase, 12px, letter-spacing 0.04em, `--text-muted`.
- Row height: 48px.
- Tidak ada colored side borders pada rows.
- Selected row: `--brand-red-muted` background + `border-left: 3px solid var(--brand-red)`.
- Locked row: `--surface-alt` background + `--text-muted` color.

### Forms
- Required field: asterisk `*` dengan `--brand-red` color di label.
- Focus state: `border-color: var(--brand-red)` + `box-shadow: var(--shadow-focus)`.
- Error: `border-color: var(--status-danger)` + error message di bawah.

### Anti-Patterns
- ❌ Gradient buttons.
- ❌ Colored side borders pada cards.
- ❌ Icon dalam colored circles.
- ❌ 3-column feature grid yang identik.
- ❌ Semua heading center-aligned.
- ❌ Float/double untuk angka uang.
- ❌ `any` type di TypeScript.

---

## 13. API Integration Contract

### Axios Instance

```typescript
// resources/js/bootstrap.ts

import axios from 'axios'

const api = axios.create({
  baseURL: '/api/v1',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,   // Untuk Sanctum session-based auth
})

// Interceptor: handle 401 → redirect ke login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export { api }
```

### Endpoint Map (Frontend Reference)

| Domain | Method | Endpoint | Digunakan di |
|--------|--------|----------|--------------|
| Auth | POST | `/auth/login` | `Login.vue` |
| Auth | POST | `/auth/logout` | `auth.store.ts` |
| Companies | GET | `/companies` | `Companies/Index.vue` |
| Companies | POST | `/companies` | `Companies/Create.vue` |
| Journals | GET | `/companies/{id}/journals` | `JournalEntries/Index.vue` |
| Journals | POST | `/companies/{id}/journals` | `JournalEntries/Create.vue` |
| Journals | POST | `/companies/{id}/journals/{id}/post` | `JournalEntries/Show.vue` |
| Trial Balance | GET | `/companies/{id}/trial-balance` | `TrialBalance/Show.vue` |
| Engagements | GET | `/engagements` | `Engagements/Index.vue` |
| Working Papers | GET | `/engagements/{id}/working-papers` | `WorkingPapers/Index.vue` |
| Evidence | POST | `/document-requests/{id}/evidence` | `Evidence/Upload.vue` |
| Reports | POST | `/companies/{id}/reports/generate` | `Reports/Index.vue` |
| Reports | GET | `/reports/{id}/download` | `useExport.ts` |

---

## 14. Testing Strategy

### Unit Tests (Vitest)

**Wajib** untuk:
- Semua composables di `composables/`.
- Semua Zod schemas di `schemas/`.
- Semua Pinia store actions.
- `useCurrency()` — semua edge cases angka IDR.
- `usePermission()` — permission check logic.

### Component Tests (Vitest + @vue/test-utils)

**Wajib** untuk:
- `<AppTable>` — render, loading state, empty state.
- `<StatusBadge>` — semua status values.
- `<AmountDisplay>` — debit/credit/zero rendering.
- `<AppButton>` — semua variants dan loading state.

### E2E Tests (Cypress)

**Wajib** untuk:
- Login flow (success, failed, MFA).
- Create dan post journal entry.
- Upload evidence dan accept.
- Generate dan download report.

---

## 15. Deployment Notes

Frontend di-build via Vite dan di-serve oleh Laravel (bukan standalone SPA).

```bash
# Development
npm run dev

# Production build
npm run build

# Type check
npx vue-tsc --noEmit
```

`vite.config.ts`:
```typescript
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: { base: null, includeAbsolute: false },
      },
    }),
  ],
  resolve: {
    alias: { '@': '/resources/js' },
  },
})
```

---

*AGENTS.md Frontend — LedgerScope v1.0 | Spec-Driven Development | Build More Architect Dreams*
