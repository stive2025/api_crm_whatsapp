<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory;
    protected $fillable=[
        'state',
        'last_message',
        'unread_message',
        'contact_id',
        'user_id',
        'tag_id'
    ];
    
    public function messages(){
        return $this->hasMany(Message::class)->chaperone();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function contact(){
        return $this->belongsTo(Contact::class);
    }

}
