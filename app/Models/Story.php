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
        'images',
        'is_deleted',
    ];
}
