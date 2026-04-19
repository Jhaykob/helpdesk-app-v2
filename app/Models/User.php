<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Spatie Trait

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // Removed SoftDeletes from here

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    // The Magic Accessor!
    // This tricks your old code into thinking the old Role system still exists
    public function getRoleAttribute()
    {
        return $this->roles->first() ?? (object) ['name' => 'user'];
    }

    public function collaboratedTickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_collaborators')->withTimestamps();
    }
}
