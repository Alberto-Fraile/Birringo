<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    use HasFactory;
    protected $table = 'quests';

    public function users(){
        return $this->belongsToMany(User::class,'user_quests');
    }
    public function pub(){
        return $this->hasOne(Pub::class, 'id', 'pub_asociado');
    }
}
