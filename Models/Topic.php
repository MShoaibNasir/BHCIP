<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'topic';
    Protected $primaryKey = 'id';
    protected $fillable = ['topic_name', 'created_at','updated_at','created_by','updated_by','cat_id','deleted_at'];

}
