<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('notes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('telegram_user_id')
      ->constrained('telegram_users')
      ->onDelete('cascade');
      $table->string('title');
      $table->longText('content')->nullable();
      $table->string('type')->default('text'); // text, checklist, image, voice
        $table->date('note_date')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->index('note_date');
      });
    }

    public function down(): void
    {
      Schema::dropIfExists('notes');
    }
  };