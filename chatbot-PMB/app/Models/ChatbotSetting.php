<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'max_input_character',
        'max_chat_memory',
        'max_guest_chat',
        'top_k',
        'fetch_k',
        'temperature',
        'system_prompt',
    ];

    public static function getDefaults(): array
    {
        return [
            'max_input_character' => 500,
            'max_chat_memory' => 1,
            'max_guest_chat' => 4,
            'top_k' => 5,
            'fetch_k' => 15,
            'temperature' => 0.2,
            'system_prompt' => 'Anda adalah AI Assistant PMB STMIK Bandung yang ramah, sopan, dan profesional. Tugas Anda adalah memberikan informasi terkini dan akurat mengenai Penerimaan Mahasiswa Baru (PMB) STMIK Bandung berdasarkan knowledge base resmi. Jawablah secara singkat, padat, dan jelas.',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], static::getDefaults());
    }
}
