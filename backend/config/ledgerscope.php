<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum File Upload Size (MB)
    |--------------------------------------------------------------------------
    | Applied to all evidence, working paper, and import file uploads.
    */
    'max_file_size_mb' => (int) env('LEDGERSCOPE_MAX_FILE_SIZE_MB', 50),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types for File Uploads
    |--------------------------------------------------------------------------
    | Validated using BOTH file extension AND finfo (PHP fileinfo extension).
    | Never trust extension alone.
    */
    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-excel',                                           // xls
        'text/csv',
        'application/csv',
        'text/plain',
        'application/zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Retention (Years)
    |--------------------------------------------------------------------------
    | Audit logs must be kept for this many years before physical deletion.
    | Application code must NEVER delete audit logs before this period.
    */
    'audit_log_retention_years' => (int) env('LEDGERSCOPE_AUDIT_RETENTION_YEARS', 7),

    /*
    |--------------------------------------------------------------------------
    | Report Generation: Sync vs Async Threshold
    |--------------------------------------------------------------------------
    | Reports with fewer rows than this threshold are generated synchronously.
    | Reports above this threshold are dispatched to the 'reports' queue.
    */
    'report_sync_row_threshold' => (int) env('LEDGERSCOPE_REPORT_SYNC_THRESHOLD', 500),

    /*
    |--------------------------------------------------------------------------
    | Signed URL Expiry (minutes)
    |--------------------------------------------------------------------------
    | Duration for temporary signed download URLs for files and reports.
    */
    'signed_url_expiry_minutes' => (int) env('LEDGERSCOPE_SIGNED_URL_EXPIRY', 60),

    /*
    |--------------------------------------------------------------------------
    | User Invitation Expiry (hours)
    |--------------------------------------------------------------------------
    | After this period, invitation tokens are considered expired.
    */
    'invitation_expiry_hours' => (int) env('LEDGERSCOPE_INVITATION_EXPIRY_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Login Rate Limiting
    |--------------------------------------------------------------------------
    */
    'login_max_attempts' => (int) env('LEDGERSCOPE_LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => (int) env('LEDGERSCOPE_LOGIN_DECAY_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting (requests per minute per user)
    |--------------------------------------------------------------------------
    */
    'api_rate_limit_per_minute' => (int) env('LEDGERSCOPE_API_RATE_LIMIT', 120),
    'upload_rate_limit_per_minute' => (int) env('LEDGERSCOPE_UPLOAD_RATE_LIMIT', 20),

    'red_flag' => [
        'large_entry_threshold' => env('LEDGERSCOPE_RED_FLAG_LARGE_ENTRY_THRESHOLD', '100000'),
        'near_threshold_percent' => env('LEDGERSCOPE_RED_FLAG_NEAR_THRESHOLD_PERCENT', '0.02'),
    ],

    'integrations' => [
        'erp' => ['enabled' => (bool) env('LEDGERSCOPE_ERP_ENABLED', false), 'mode' => env('LEDGERSCOPE_ERP_MODE', 'unavailable')],
        'payroll' => ['enabled' => (bool) env('LEDGERSCOPE_PAYROLL_ENABLED', false), 'mode' => env('LEDGERSCOPE_PAYROLL_MODE', 'unavailable')],
        'inventory' => ['enabled' => (bool) env('LEDGERSCOPE_INVENTORY_ENABLED', false), 'mode' => env('LEDGERSCOPE_INVENTORY_MODE', 'unavailable')],
        'tax' => ['enabled' => (bool) env('LEDGERSCOPE_TAX_ENABLED', false), 'mode' => env('LEDGERSCOPE_TAX_MODE', 'unavailable')],
        'banking' => ['enabled' => (bool) env('LEDGERSCOPE_BANKING_ENABLED', false), 'mode' => env('LEDGERSCOPE_BANKING_MODE', 'unavailable')],
        'sso' => ['enabled' => (bool) env('LEDGERSCOPE_SSO_ENABLED', false), 'mode' => env('LEDGERSCOPE_SSO_MODE', 'unavailable')],
        'ocr' => ['enabled' => (bool) env('LEDGERSCOPE_OCR_ENABLED', false), 'mode' => env('LEDGERSCOPE_OCR_MODE', 'unavailable')],
        'ai' => ['enabled' => (bool) env('LEDGERSCOPE_AI_ENABLED', false), 'mode' => env('LEDGERSCOPE_AI_MODE', 'unavailable')],
        'mobile_sync' => ['enabled' => (bool) env('LEDGERSCOPE_MOBILE_SYNC_ENABLED', false), 'mode' => env('LEDGERSCOPE_MOBILE_SYNC_MODE', 'unavailable')],
        'anomaly' => ['enabled' => (bool) env('LEDGERSCOPE_ANOMALY_ENABLED', false), 'mode' => env('LEDGERSCOPE_ANOMALY_MODE', 'unavailable')],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths (relative to the private disk root)
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'evidence_path' => 'companies/{company_id}/engagements/{engagement_id}/evidence',
        'working_papers_path' => 'companies/{company_id}/engagements/{engagement_id}/working-papers',
        'reports_path' => 'companies/{company_id}/engagements/{engagement_id}/reports',
        'imports_path' => 'companies/{company_id}/bookkeeping/imports',
        'financial_stmt_path' => 'companies/{company_id}/financial-statements',
        'temp_uploads_path' => 'temp-uploads',
    ],

];
