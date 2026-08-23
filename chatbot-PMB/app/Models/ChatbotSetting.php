<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotSetting extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'max_input_character',
        'max_chat_memory',
        'max_guest_chat',
        'top_k',
        'fetch_k',
        'temperature',
        'system_prompt',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaults(): array
    {
        return [
            'max_input_character' => 50,
            'max_chat_memory' => 0,
            'max_guest_chat' => 4,
            'top_k' => 7,
            'fetch_k' => 15,
            'temperature' => 0.2,
            'system_prompt' => "Anda adalah asisten akademik PMB (Penerimaan Mahasiswa Baru).\nAturan MUTLAK:\n- HANYA gunakan informasi dari konteks di bawah. JANGAN gunakan pengetahuan umum Anda sama sekali, walau Anda tahu jawabannya.\n- Jawaban maksimal 3-5 kalimat.",
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], static::getDefaults());
    }
}
