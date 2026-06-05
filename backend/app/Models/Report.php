<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Reporting\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Report extends Model
{
    protected $fillable = [
        'company_id', 'report_type', 'title', 'status', 'format',
        'parameters', 'file_path', 'file_size_bytes',
        'requested_by', 'approved_by', 'approved_at', 'generated_at', 'expires_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'status' => ReportStatus::class,
            'approved_at' => 'datetime',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
