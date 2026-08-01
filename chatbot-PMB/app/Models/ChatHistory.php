<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'question',
        'answer',
        'source_documents',
    ];

    protected $casts = [
        'source_documents' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
