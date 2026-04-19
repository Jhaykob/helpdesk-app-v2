<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category_id',
        'user_id',
        'assigned_to',
        'rating',
        'csat_feedback',
        // I REMOVED attachment_path from here!
        'sla_deadline',
        'is_breaching_sla'
    ];

    // Ensure dates are parsed as Carbon instances and booleans are cast correctly
    protected $casts = [
        'sla_deadline' => 'datetime',
        'is_breaching_sla' => 'boolean',
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

    // Real-time check if the ticket is currently breaching its SLA
    public function getIsBreachingSlaAttribute()
    {
        // If it has no deadline, or it's resolved/closed, it's safe
        if (!$this->sla_deadline || in_array($this->status, ['Resolved', 'Closed'])) {
            return false;
        }

        // Return true if the current time is past the database deadline
        return now()->greaterThan($this->sla_deadline);
    }

    // The agents who have been invited to collaborate on this ticket
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'ticket_collaborators')->withTimestamps();
    }
}
