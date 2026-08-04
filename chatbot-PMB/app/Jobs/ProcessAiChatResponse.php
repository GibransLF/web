<?php

namespace App\Jobs;

use App\Models\ChatHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

class ProcessAiChatResponse implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param  array<int, array{question: string, answer: string}>  $historyPayload
     */
    public function __construct(
        public int $chatHistoryId,
        public string $userQuestion,
        public array $historyPayload = []
    ) {}

    /**
     * The number of seconds the job can run before timing out.
     */
    public function timeout(): int
    {
        return (int) config('services.ai_service.timeout', 180) + 10;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chatHistory = ChatHistory::find($this->chatHistoryId);
        if (! $chatHistory || $chatHistory->status !== 'pending') {
            return;
        }

        $aiServiceUrl = config('services.ai_service.url', 'http://127.0.0.1:8080');
        $timeout = (int) config('services.ai_service.timeout', 180);
        $aiAnswerText = '';

        try {
            $response = Http::timeout($timeout)->post("{$aiServiceUrl}/service/chat", [
                'newMessage' => $this->userQuestion,
                'history' => $this->historyPayload,
            ]);

            if ($response->successful() && $response->json('success')) {
                $aiAnswerText = $response->json('response');
                $chatHistory->update([
                    'answer' => $aiAnswerText,
                    'status' => 'completed',
                ]);
            } else {
                $errorDetail = $response->json('detail') ?? $response->json('message') ?? 'Terjadi kesalahan pada layanan AI.';
                $aiAnswerText = "Maaf, sistem AI sedang mengalami kendala ({$errorDetail}). Silakan coba beberapa saat lagi atau hubungi panitia PMB melalui tombol WhatsApp di bawah.";
                $chatHistory->update([
                    'answer' => $aiAnswerText,
                    'status' => 'failed',
                ]);
            }
        } catch (Throwable $e) {
            $aiAnswerText = 'Maaf, server AI saat ini sedang tidak dapat dijangkau. Silakan pastikan layanan AI aktif atau hubungi panitia PMB melalui tombol WhatsApp di bawah.';
            $chatHistory->update([
                'answer' => $aiAnswerText,
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $chatHistory = ChatHistory::find($this->chatHistoryId);
        if ($chatHistory && $chatHistory->status === 'pending') {
            $chatHistory->update([
                'answer' => 'Maaf, pemrosesan pesan mengalami waktu tunggu habis (timeout). Silakan coba beberapa saat lagi atau hubungi panitia PMB melalui tombol WhatsApp di bawah.',
                'status' => 'failed',
            ]);
        }
    }
}
