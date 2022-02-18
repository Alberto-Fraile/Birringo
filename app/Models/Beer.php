<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beer extends Model
{
    use HasFactory;
    protected $table = 'beers';
    public $timestamps = false;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function pubs(){
        return $this->belongsToMany(Pub::class,'pub_beers');
    }
    public function users(){
        return $this->belongsToMany(User::class,'user_beers');
    }
}
