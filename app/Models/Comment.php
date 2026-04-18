<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    // Added 'is_internal' to the fillable array
    protected $fillable = ['content', 'ticket_id', 'user_id', 'is_internal', 'attachment_path'];

    protected $casts = [
        'is_internal' => 'boolean', // Ensures Laravel treats it as true/false
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
