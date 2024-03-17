<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResponse extends Model
{
    use HasFactory;
    protected $table = 'quiz_response'; 
    protected $primaryKey = 'id';
    
    protected $fillable = ['user_id', 'quiz_id', 'quiz_response','status','level_status'];

    
}
