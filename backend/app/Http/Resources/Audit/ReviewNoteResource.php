<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\ReviewNote;
use App\Models\ReviewNoteReply;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReviewNote */
final class ReviewNoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var ReviewNote $reviewNote */
        $reviewNote = $this->resource;

        return [
            'id' => $this->id,
            'working_paper_id' => $this->working_paper_id,
            'created_by' => $this->created_by,
            'content' => $this->content,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'replies' => $this->when($reviewNote->relationLoaded('replies'), static function () use ($reviewNote): array {
                /** @var Collection<int, ReviewNoteReply> $replies */
                $replies = $reviewNote->getRelation('replies');

                return $replies->map(static fn (ReviewNoteReply $reply): array => [
                    'id' => $reply->id,
                    'message' => $reply->message,
                    'created_by' => $reply->user_id,
                    'created_at' => $reply->created_at?->toIso8601String(),
                ])->values()->all();
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
