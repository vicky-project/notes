<?php
namespace Modules\Notes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'remind_at' => $this->remind_at->toIso8601String(),
      'is_completed' => $this->is_completed,
      'note' => new NoteResource($this->whenLoaded('note')),
    ];
  }
}