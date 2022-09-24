<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomMember extends Model
{
    protected $table = 'room_members';

    protected $fillable = [
        'room_id',
        'user_id',
    ];

}
