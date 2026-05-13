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
    Schema::create('notes_tags', function (Blueprint $table) {
      $table->id();
      $table->foreignId('telegram_user_id')->constrained('telegram_users')->onDelete('cascade');
      $table->string('name');
      $table->string('color', 7)->nullable();
      $table->timestamps();
      $table->unique(['telegram_user_id', 'name']);
    });
  }

  /**
  * Reverse the migrations.
  */
  public function down(): void
  {
    Schema::dropIfExists('notes_tags');
  }
};