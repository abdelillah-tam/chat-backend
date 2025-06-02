<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatChannel extends Model
{
    //

    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'first_user',
        'second_user'
    ];

    public function firstUser()
    {
        return $this->belongsTo(User::class, 'first_user');
    }

    public function secondUser()
    {
        return $this->belongsTo(User::class, 'second_user');
    }

    public function messages(){
        return $this->hasMany(Message::class, 'channel');
    }
}
