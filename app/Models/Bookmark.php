<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bookmarks';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'story_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
