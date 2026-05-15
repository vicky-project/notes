<?php

namespace Modules\Notes\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
  protected string $apiKey;
  protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

  public function __construct() {
    $this->apiKey = config('notes.ai.api_key', '');
  }

  public function isEnabled(): bool
  {
    return !empty($this->apiKey);
  }

  /**
  * Mencari catatan secara semantik berdasarkan query bahasa alami.
  * Mengembalikan array ID catatan yang relevan.
  */
  public function semanticSearch(array $notes, string $query): array
  {
    if (!$this->isEnabled() || empty($notes) || empty(trim($query))) {
      return [];
    }

    // Siapkan konteks untuk Gemini
    $notesContext = [];
    foreach ($notes as $note) {
      $plainContent = strip_tags($note['content'] ?? '');
      $notesContext[] = [
        'id' => $note['id'],
        'title' => $note['title'],
        'content' => mb_substr($plainContent, 0, 300),
      ];
    }

    $prompt = $this->buildSearchPrompt($notesContext, $query);
    $response = $this->callGemini($prompt);

    return $this->parseSearchResponse($response);
  }

  /**
  * Merangkum konten catatan.
  */
  public function summarize(string $content, string $title = ''): string
  {
    if (!$this->isEnabled() || empty(trim($content))) {
      return '(Fitur AI tidak tersedia atau konten kosong)';
    }

    $plainContent = strip_tags($content);
    $prompt = "Ringkaslah catatan berikut dalam bahasa Indonesia, maksimal 3 paragraf singkat. Gunakan bahasa yang natural dan mudah dipahami.\n\n";
    if ($title) {
      $prompt .= "Judul: {$title}\n\n";
    }
    $prompt .= "Isi:\n{$plainContent}";

    return $this->callGemini($prompt) ?? '(Gagal merangkum)';
  }

  /**
  * Memanggil Gemini API.
  */
  protected function callGemini(string $prompt): ?string
  {
    if (empty($this->apiKey)) {
      Log::warning('Gemini API key tidak tersedia');
      return null;
    }

    try {
      $response = Http::timeout(30)
      ->post($this->baseUrl . '?key=' . $this->apiKey, [
        'contents' => [
          [
            'parts' => [
              ['text' => $prompt]
            ]
          ]
        ],
        'generationConfig' => [
          'temperature' => 0.3,
          'maxOutputTokens' => 1000,
        ]
      ]);

      if ($response->successful()) {
        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
      }

      Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
      return null;
    } catch (\Exception $e) {
      Log::error('Gemini API exception', ['message' => $e->getMessage()]);
      return null;
    }
  }

  /**
  * Membangun prompt untuk pencarian semantik.
  */
  protected function buildSearchPrompt(array $notes, string $query): string
  {
    $notesJson = json_encode($notes, JSON_UNESCAPED_UNICODE);
    $prompt = "Kamu adalah asisten pencarian cerdas untuk aplikasi catatan pribadi.\n";
    $prompt .= "Pengguna akan memberikan query dalam bahasa Indonesia, dan kamu harus mencari catatan yang paling relevan.\n\n";
    $prompt .= "Berikut adalah daftar catatan pengguna (dalam format JSON):\n";
    $prompt .= $notesJson . "\n\n";
    $prompt .= "Query pencarian: \"{$query}\"\n\n";
    $prompt .= "Tugas kamu:\n";
    $prompt .= "1. Analisis query pencarian dan semua catatan di atas.\n";
    $prompt .= "2. Pilih catatan yang paling relevan dengan query (maksimal 5).\n";
    $prompt .= "3. Kembalikan HANYA array JSON berisi ID catatan yang relevan, misalnya: [1, 5, 12].\n";
    $prompt .= "4. Jika tidak ada yang relevan, kembalikan array kosong: [].\n";
    $prompt .= "5. JANGAN berikan penjelasan apapun, HANYA array JSON.\n\n";
    $prompt .= "Jawaban:";

    return $prompt;
  }

  /**
  * Parse hasil pencarian dari Gemini.
  */
  protected function parseSearchResponse(?string $response): array
  {
    if (!$response) return [];

    // Bersihkan respons: kadang Gemini membungkus JSON dalam ```json ... ```
    $response = trim($response);
    $response = preg_replace('/^```(?:json)?\s*|```$/i', '', $response);

    $ids = json_decode($response, true);
    return is_array($ids) ? $ids : [];
  }
}