<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_base_id',
        'chunk_text',
        'kategori',
        'chunk_order',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function knowledgeBase()
    {
        return $this->belongsTo(KnowledgeBase::class);
    }
}
