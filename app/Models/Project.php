<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'description',
    ];
}
