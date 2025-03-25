<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['UserName', 'password'];

    public function isAdmin()
    {
        return in_array($this->UserID, [1, 2, 3]);
    }

    public function isFarmer()
    {
        return !$this->isAdmin();
    }


    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];
}
