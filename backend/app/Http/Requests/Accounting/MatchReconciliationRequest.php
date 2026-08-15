<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class MatchReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:reconciliation_items,id'],
            'journal_line_id' => ['required', 'integer', 'exists:journal_entry_lines,id'],
        ];
    }
}
