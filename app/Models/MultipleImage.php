<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultipleImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'related_id',
        'related_type',
        'image_url',
        'identifier'
    ];

    public function related()
    {
        return $this->morphTo(null, 'related_id', 'related_type', 'image_url');
    }
}
