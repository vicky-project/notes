<?php
namespace Modules\Notes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'content' => $this->content,
      'type' => $this->type->value,
      'metadata' => $this->metadata,
      'created_at' => $this->created_at->toIso8601String(),
      'updated_at' => $this->updated_at->toIso8601String(),
      'tags' => $this->whenLoaded('tags', fn() => $this->tags->makeHidden('pivot')),
      'reminder' => new ReminderResource($this->whenLoaded('reminder')),
    ];
  }
}