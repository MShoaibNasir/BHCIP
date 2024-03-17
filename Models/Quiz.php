<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Database\Eloquent\SoftDeletes;
class Quiz extends Model
{
    use HasFactory,SoftDeletes;

    protected $table="quiz";
    protected $primaryKey = "quiz_id";
    protected $fillable = ['quiz_name', 'category_id','level_id','topic_id','created_by', 'created_at', 'updated_at','deleted_at'];


    public function QuizQuestions()
    {
        return $this->hasMany(Question::class,'quiz_id','quiz_id');
    }


}

