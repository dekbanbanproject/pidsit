<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Env_water extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'env_water';
    protected $primaryKey = 'water_id';
    // public $timestamps = false;  
    protected $fillable = [
        'water_date',
        'water_comment',
        'water_user',
        'water_location',
        'water_group_excample'       
        
        
    ];

  
}
