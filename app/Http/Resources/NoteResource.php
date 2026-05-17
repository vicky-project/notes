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
      'type' => $this->type,
      'metadata' => $this->metadata,
      'note_date' => $this->note_date->toIso8601String(),
      'created_at' => $this->created_at->toIso8601String(),
      'updated_at' => $this->updated_at->toIso8601String(),
      'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
      'tags' => $this->whenLoaded('tags', function () {
        return $this->tags->map(fn($tag) => [
          'id' => $tag->id,
          'name' => $tag->name,
          'color' => $tag->color,
        ]);
      }) ?? [],
      // fallback array kosong jika tidak diload
      'reminder' => new ReminderResource($this->whenLoaded('reminder')),
    ];
  }
}