<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $attributes = [
        'is_validated' => false,
    ];

    protected $fillable = [
        'user_id',
        'guest_id',
        'question',
        'answer',
        'status',
        'pending_id',
        'is_validated',
    ];

    protected $casts = [
        'is_validated' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
