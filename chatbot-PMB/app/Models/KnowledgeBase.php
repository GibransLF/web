<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_name',
        'kategori',
        'deskripsi',
    ];

    public function chunks()
    {
        return $this->hasMany(KnowledgeChunk::class);
    }
}
