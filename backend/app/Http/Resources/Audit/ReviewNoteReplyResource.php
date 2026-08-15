<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\ReviewNoteReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReviewNoteReply */
final class ReviewNoteReplyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review_note_id' => $this->review_note_id,
            'created_by' => $this->user_id,
            'message' => $this->message,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
