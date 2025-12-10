<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    /** @use HasFactory<\Database\Factories\ConnectionFactory> */
    use HasFactory;

    protected $fillable=[
        'qr_code',
        'status',
        'name',
        'is_default',
        'greeting_message',
        'farewell_message',
        'number',
        'user_id'
    ];
}
