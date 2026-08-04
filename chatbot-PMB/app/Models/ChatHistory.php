<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'guest_id',
        'question',
        'answer',
        'status',
        'pending_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
