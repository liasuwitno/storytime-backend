<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    // Model: Category.php
    public function stories()
    {
        return $this->hasMany(Story::class)
            ->orderBy('created_at', 'desc')
            ->take(3);
    }
}
