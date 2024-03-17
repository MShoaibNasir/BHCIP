<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class level extends Model
{
    use HasFactory;
    protected $table = 'level';
    Protected $primaryKey = 'level_id';
    protected $fillable = ['level_name', 'created_at'];
}
