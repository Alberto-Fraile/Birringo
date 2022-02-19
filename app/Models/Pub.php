<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pub extends Model
{
    use HasFactory;
    protected $table = 'pubs';
    public $timestamps = false;


    protected $fillable = [
        'titulo',
        'calle',
        'latitud',
        'longitud',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function beers(){
        return $this->belongsToMany(Beer::class,'pub_beers');
    }
    public function quests(){
        return $this -> hasMany(Quest::class, 'pub_asociado');
    }
}
