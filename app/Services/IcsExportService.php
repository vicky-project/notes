<?php

namespace Modules\Notes\Services;

use Modules\Notes\Models\Note;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Components\Todo;
use Spatie\IcalendarGenerator\Enums\TodoStatus;
use DateTime;

class IcsExportService
{
  public function generateForNote(Note $note): string
  {
    $calendar = Calendar::create('Notes Export');
    $this->addNoteToCalendar($calendar, $note);
    return $calendar->get();
  }

  public function generateForUser(int $telegramUserId): string
  {
    $notes = Note::where('telegram_user_id', $telegramUserId)
    ->with('reminder')
    ->get();

    if ($notes->isEmpty()) {
      throw new \RuntimeException('Tidak ada catatan untuk diekspor.');
    }

    $calendar = Calendar::create('Notes Export');

    foreach ($notes as $note) {
      $this->addNoteToCalendar($calendar, $note);
    }

    return $calendar->get();
  }

  protected function addNoteToCalendar(Calendar $calendar, Note $note): void
  {
    $uid = $note->id . '@notes';

    if ($note->reminder && $note->reminder->remind_at) {
      // VEVENT untuk pengingat
      $dtstart = new DateTime($note->reminder->remind_at->toIso8601String());
      $dtend = (clone $dtstart)->modify('+1 hour');

      $event = Event::create($note->title)
      ->uniqueIdentifier($uid)
      ->startsAt($dtstart)
      ->endsAt($dtend);

      if ($note->content) {
        $event->description(strip_tags($note->content));
      }

      $calendar->event($event);
    } elseif ($note->type === 'checklist') {
      // VTODO untuk checklist
      $items = json_decode($note->content, true);
      $completed = is_array($items) ? count(array_filter($items, fn($i) => $i['done'] ?? false)) : 0;
      $total = is_array($items) ? count($items) : 0;
      $description = "Checklist: {$completed}/{$total} selesai\n" . strip_tags($note->content ?? '');

      $todo = Todo::create($note->title)
      ->uniqueIdentifier($uid)
      ->description($description)
      ->percentComplete($total > 0 ? (int) round(($completed / $total) * 100) : 0);

      if ($completed === $total) {
        $todo->status(TodoStatus::completed());
      } else {
        $todo->status(TodoStatus::needsAction());
      }

      $calendar->todo($todo);
    } else {
      // VEVENT untuk catatan biasa (fallback)
      $dtstart = new DateTime($note->created_at->toIso8601String());

      $event = Event::create($note->title)
      ->uniqueIdentifier($uid)
      ->startsAt($dtstart)
      ->description(strip_tags($note->content ?? ''));

      $calendar->event($event);
    }
  }
}