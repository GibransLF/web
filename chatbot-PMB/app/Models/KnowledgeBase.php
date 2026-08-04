<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'status',
        'deskripsi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chunks()
    {
        return $this->hasMany(KnowledgeChunk::class);
    }
}
