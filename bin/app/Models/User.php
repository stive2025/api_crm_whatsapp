<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens;

    protected $fillable=[
        'name',
        'email',
        'password',
        'role',
        'abilities'
    ];

    public function chats(){
        return $this->hasMany(Chat::class)->chaperone();
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->chaperone();
    }
}
