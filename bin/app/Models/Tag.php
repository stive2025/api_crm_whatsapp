<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable=[
        'name',
        'description',
        'color'
    ];

    public function messages(){
        return $this->hasMany(Message::class)->chaperone();
    }
    
}
