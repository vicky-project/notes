<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
  * Run the migrations.
  */
  public function up(): void
  {
    Schema::create('notes_reminders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('note_id')->constrained('notes')->onDelete('cascade');
      $table->timestamp('remind_at');
      $table->boolean('is_completed')->default(false);
        $table->timestamps();
      });
    }

    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
      Schema::dropIfExists('notes_reminders');
    }
  };