<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CentralUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'central_users';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];
}
