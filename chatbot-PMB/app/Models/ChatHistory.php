<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'question',
        'answer',
        'source_documents',
    ];

    protected $casts = [
        'source_documents' => 'array',
    ];
}
