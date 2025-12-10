<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable=[
        'user_id',
        'contact_id',
        'transfered_by',
        'type',
        'message',
        'state'
    ];
}
