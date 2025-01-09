<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $fillable = [
        'unique_id',
        'title',
        'user_id',
        'category_id',
        'slug',
        'body',
        'is_deleted',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'unique_id');
    }

    public function images()
    {
        return $this->morphMany(MultipleImage::class, 'related', 'related_type', 'related_unique_id', 'unique_id');
    }
}
