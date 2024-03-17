<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswer extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'question_answer';
    protected $primaryKey = 'answer_id';
    
    public function QuestionAnswer()
    {
        return $this->hasOne(QuestionAnswer::class,'question_id','question_id');
    }
    
    public function QuestionOption()
    {
        return $this->hasMany(QuestionOption::class,'question_id','question_id');
    }
}
