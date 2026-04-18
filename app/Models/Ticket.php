<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon; // <-- ADDED FOR DATE MATH

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'assigned_to',
        'sla_deadline',
        'rating',
        'csat_feedback',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    // NEW: Calculate the exact SLA deadline timestamp
    public function getSlaDeadlineAttribute()
    {
        $hours = match ($this->priority) {
            'High' => 24,
            'Medium' => 48,
            'Low' => 72,
            default => 72,
        };

        return $this->created_at->addHours($hours);
    }

    // NEW: Check if the ticket is currently breaching its SLA
    public function getIsBreachingSlaAttribute()
    {
        // If it's resolved or closed, it's no longer breaching
        if (in_array($this->status, ['Resolved', 'Closed'])) {
            return false;
        }

        // Return true if the current time is past the deadline
        return now()->greaterThan($this->slaDeadline);
    }
}
