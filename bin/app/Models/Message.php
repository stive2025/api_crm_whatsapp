<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;
    protected $fillable=[
        'id_message_wp',
        'body',
        'ack',
        'temp_signature',
        'from_me',
        'to',
        'media_type',
        'media_path',
        'timestamp_wp',
        'is_private',
        'state',
        'deleted_by',
        'created_by',
        'chat_id',
        'tag_id'
    ];

    public function chat(){
        return $this->belongsTo(Chat::class);
    }

    public function tag(){
        return $this->belongsTo(Tag::class);
    }
}
