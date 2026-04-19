<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Macro extends Model
{
    protected $fillable = ['title', 'content', 'user_id', 'is_global'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
