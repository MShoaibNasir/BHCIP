<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory,SoftDeletes;
    protected $table= 'category';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'created_by','creates_by','created_at','updated_at'];
}
