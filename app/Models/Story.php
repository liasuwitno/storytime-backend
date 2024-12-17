<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'user_id',
        'category_id',
        'body',
        'images',
        'is_deleted',
    ];
}
