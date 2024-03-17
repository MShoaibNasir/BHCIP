<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\SoftDeletes;
class Question extends Model
{
    use HasFactory,SoftDeletes;
    protected $table= 'quiz_questions';
    protected $primaryKey = 'question_id';
    protected $fillable = ['quiz_id', 'question', 'question_type','created_by', 'updated_by', 'created_at', 'updated_at','updated_by'];

   public function QuestionOption() {
        return $this->hasMany(QuestionOption::class,'question_id','question_id');
    }

    public function QuestionAnswer()
    {
        return $this->hasMany(QuestionAnswer::class, 'question_id', 'question_id');
    }
}
