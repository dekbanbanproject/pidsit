<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class D_api12002_iop extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'd_api12002_iop';
    protected $primaryKey = 'd_api12002_iop_id';
    protected $fillable = [
        'blobName',
        'blobType',
        'blob'         
    ];

  
}
